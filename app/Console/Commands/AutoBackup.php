<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use ZipArchive;

class AutoBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:auto {type=database} {--retention=30}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '自动创建系统备份';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = $this->argument('type');
        $retention = $this->option('retention');
        
        $this->info("开始自动备份...");
        $this->info("备份类型: {$type}");
        $this->info("保留天数: {$retention} 天");
        
        try {
            switch ($type) {
                case 'database':
                    $this->createDatabaseBackup();
                    break;
                case 'files':
                    $this->createFileBackup();
                    break;
                case 'full':
                    $this->createFullBackup();
                    break;
                default:
                    $this->error("不支持的备份类型: {$type}");
                    return 1;
            }
            
            // 清理旧备份
            $this->cleanOldBackups($retention);
            
            $this->info("自动备份完成！");
            return 0;
            
        } catch (\Exception $e) {
            $this->error("备份失败: " . $e->getMessage());
            Log::error("Auto backup failed", [
                'type' => $type,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }

    /**
     * 创建数据库备份
     */
    private function createDatabaseBackup()
    {
        $this->info("创建数据库备份...");
        
        $filename = 'auto_database_backup_' . now()->format('Y-m-d_H-i-s') . '.sql';
        $path = storage_path('app/backups/' . $filename);
        
        // 确保备份目录存在
        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }
        
        // 使用Laravel的数据库备份功能
        $tables = DB::select('SHOW TABLES');
        $tableNames = [];
        foreach ($tables as $table) {
            $tableNames[] = array_values((array) $table)[0];
        }
        
        $sql = '';
        
        // 获取数据库结构
        foreach ($tableNames as $tableName) {
            // 获取建表语句
            $createTable = DB::select("SHOW CREATE TABLE `{$tableName}`");
            $sql .= "\n\n" . $createTable[0]->{'Create Table'} . ";\n\n";
            
            // 获取数据
            $data = DB::table($tableName)->get();
            if ($data->count() > 0) {
                $sql .= "INSERT INTO `{$tableName}` VALUES\n";
                $values = [];
                foreach ($data as $row) {
                    $rowValues = [];
                    foreach ((array) $row as $value) {
                        if ($value === null) {
                            $rowValues[] = 'NULL';
                        } else {
                            $rowValues[] = "'" . addslashes($value) . "'";
                        }
                    }
                    $values[] = "(" . implode(', ', $rowValues) . ")";
                }
                $sql .= implode(",\n", $values) . ";\n";
            }
        }
        
        // 写入文件
        if (file_put_contents($path, $sql) === false) {
            throw new \Exception('无法写入备份文件');
        }
        
        $this->info("数据库备份创建成功: {$filename}");
        $this->logBackup('database', $filename, filesize($path));
    }

    /**
     * 创建文件备份
     */
    private function createFileBackup()
    {
        $this->info("创建文件备份...");
        
        $filename = 'auto_files_backup_' . now()->format('Y-m-d_H-i-s') . '.zip';
        $path = storage_path('app/backups/' . $filename);
        
        // 确保备份目录存在
        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }
        
        $zip = new ZipArchive();
        if ($zip->open($path, ZipArchive::CREATE) !== TRUE) {
            throw new \Exception('无法创建ZIP文件');
        }
        
        // 添加storage/app/public目录
        $publicPath = storage_path('app/public');
        if (is_dir($publicPath)) {
            $this->addFolderToZip($zip, $publicPath, 'public');
        }
        
        // 添加uploads目录
        $uploadsPath = public_path('uploads');
        if (is_dir($uploadsPath)) {
            $this->addFolderToZip($zip, $uploadsPath, 'uploads');
        }
        
        $zip->close();
        
        $this->info("文件备份创建成功: {$filename}");
        $this->logBackup('files', $filename, filesize($path));
    }

    /**
     * 创建完整备份
     */
    private function createFullBackup()
    {
        $this->info("创建完整备份...");
        
        $filename = 'auto_full_backup_' . now()->format('Y-m-d_H-i-s') . '.zip';
        $path = storage_path('app/backups/' . $filename);
        
        // 确保备份目录存在
        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }
        
        $zip = new ZipArchive();
        if ($zip->open($path, ZipArchive::CREATE) !== TRUE) {
            throw new \Exception('无法创建ZIP文件');
        }
        
        // 1. 添加数据库备份
        $dbFilename = 'database_backup_' . now()->format('Y-m-d_H-i-s') . '.sql';
        $dbPath = storage_path('app/backups/' . $dbFilename);
        
        // 使用Laravel的数据库备份功能
        $tables = DB::select('SHOW TABLES');
        $tableNames = [];
        foreach ($tables as $table) {
            $tableNames[] = array_values((array) $table)[0];
        }
        
        $sql = '';
        
        // 获取数据库结构
        foreach ($tableNames as $tableName) {
            // 获取建表语句
            $createTable = DB::select("SHOW CREATE TABLE `{$tableName}`");
            $sql .= "\n\n" . $createTable[0]->{'Create Table'} . ";\n\n";
            
            // 获取数据
            $data = DB::table($tableName)->get();
            if ($data->count() > 0) {
                $sql .= "INSERT INTO `{$tableName}` VALUES\n";
                $values = [];
                foreach ($data as $row) {
                    $rowValues = [];
                    foreach ((array) $row as $value) {
                        if ($value === null) {
                            $rowValues[] = 'NULL';
                        } else {
                            $rowValues[] = "'" . addslashes($value) . "'";
                        }
                    }
                    $values[] = "(" . implode(', ', $rowValues) . ")";
                }
                $sql .= implode(",\n", $values) . ";\n";
            }
        }
        
        // 写入文件
        if (file_put_contents($dbPath, $sql) !== false) {
            $zip->addFile($dbPath, 'database/' . $dbFilename);
        }
        
        // 2. 添加文件
        $publicPath = storage_path('app/public');
        if (is_dir($publicPath)) {
            $this->addFolderToZip($zip, $publicPath, 'files/public');
        }
        
        $uploadsPath = public_path('uploads');
        if (is_dir($uploadsPath)) {
            $this->addFolderToZip($zip, $uploadsPath, 'files/uploads');
        }
        
        $zip->close();
        
        // 清理临时数据库文件
        if (file_exists($dbPath)) {
            unlink($dbPath);
        }
        
        $this->info("完整备份创建成功: {$filename}");
        $this->logBackup('full', $filename, filesize($path));
    }

    /**
     * 清理旧备份
     */
    private function cleanOldBackups($retention)
    {
        $this->info("清理旧备份文件...");
        
        $backupPath = storage_path('app/backups');
        if (!is_dir($backupPath)) {
            return;
        }
        
        $files = scandir($backupPath);
        $cutoffDate = Carbon::now()->subDays($retention);
        $deletedCount = 0;
        
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            
            $filePath = $backupPath . '/' . $file;
            if (is_file($filePath)) {
                $fileTime = Carbon::createFromTimestamp(filemtime($filePath));
                
                if ($fileTime->lt($cutoffDate)) {
                    unlink($filePath);
                    $deletedCount++;
                    $this->line("已删除旧备份: {$file}");
                }
            }
        }
        
        $this->info("清理完成，删除了 {$deletedCount} 个旧备份文件");
    }

    /**
     * 添加文件夹到ZIP
     */
    private function addFolderToZip($zip, $folderPath, $zipPath)
    {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($folderPath),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );
        
        foreach ($files as $name => $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = $zipPath . '/' . substr($filePath, strlen($folderPath) + 1);
                $zip->addFile($filePath, $relativePath);
            }
        }
    }

    /**
     * 记录备份信息
     */
    private function logBackup($type, $filename, $size)
    {
        Log::info("Auto backup created", [
            'type' => $type,
            'filename' => $filename,
            'size' => $size,
            'created_at' => now()
        ]);
    }
} 