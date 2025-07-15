<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Carbon\Carbon;
use ZipArchive;

class BackupController extends Controller
{
    /**
     * 显示备份管理页面
     */
    public function index()
    {
        $backups = $this->getBackupList();
        $backupStats = $this->getBackupStats();
        
        return view('backup.index', compact('backups', 'backupStats'));
    }

    /**
     * 创建数据库备份
     */
    public function createDatabaseBackup(Request $request)
    {
        try {
            $filename = 'database_backup_' . now()->format('Y-m-d_H-i-s') . '.sql';
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
            
            // 记录备份信息
            $this->logBackup('database', $filename, filesize($path));
            
            return response()->json([
                'success' => true,
                'message' => '数据库备份创建成功',
                'filename' => $filename
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '备份失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 创建文件备份
     */
    public function createFileBackup(Request $request)
    {
        try {
            $filename = 'files_backup_' . now()->format('Y-m-d_H-i-s') . '.zip';
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
            
            // 记录备份信息
            $this->logBackup('files', $filename, filesize($path));
            
            return response()->json([
                'success' => true,
                'message' => '文件备份创建成功',
                'filename' => $filename
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '备份失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 创建完整备份（数据库+文件）
     */
    public function createFullBackup(Request $request)
    {
        try {
            $filename = 'full_backup_' . now()->format('Y-m-d_H-i-s') . '.zip';
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
            
            // 记录备份信息
            $this->logBackup('full', $filename, filesize($path));
            
            return response()->json([
                'success' => true,
                'message' => '完整备份创建成功',
                'filename' => $filename
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '备份失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 下载备份文件
     */
    public function download(Request $request, $filename)
    {
        $path = storage_path('app/backups/' . $filename);
        
        if (!file_exists($path)) {
            return back()->with('error', '备份文件不存在');
        }
        
        return response()->download($path);
    }

    /**
     * 删除备份文件
     */
    public function destroy(Request $request, $filename)
    {
        try {
            $path = storage_path('app/backups/' . $filename);
            
            if (file_exists($path)) {
                unlink($path);
            }
            
            // 从备份记录中删除
            $this->removeBackupLog($filename);
            
            return response()->json([
                'success' => true,
                'message' => '备份文件删除成功'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '删除失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 恢复数据库备份
     */
    public function restoreDatabase(Request $request, $filename)
    {
        try {
            $path = storage_path('app/backups/' . $filename);
            
            if (!file_exists($path)) {
                throw new \Exception('备份文件不存在');
            }
            
            // 执行数据库恢复
            $command = sprintf(
                'mysql -u%s -p%s %s < %s',
                config('database.connections.mysql.username'),
                config('database.connections.mysql.password'),
                config('database.connections.mysql.database'),
                $path
            );
            
            exec($command, $output, $returnCode);
            
            if ($returnCode !== 0) {
                throw new \Exception('数据库恢复失败');
            }
            
            return response()->json([
                'success' => true,
                'message' => '数据库恢复成功'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '恢复失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取备份列表
     */
    private function getBackupList()
    {
        $backups = [];
        $backupPath = storage_path('app/backups');
        
        if (is_dir($backupPath)) {
            $files = scandir($backupPath);
            
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..' && is_file($backupPath . '/' . $file)) {
                    $backups[] = [
                        'filename' => $file,
                        'size' => $this->formatFileSize(filesize($backupPath . '/' . $file)),
                        'created_at' => date('Y-m-d H:i:s', filemtime($backupPath . '/' . $file)),
                        'type' => $this->getBackupType($file)
                    ];
                }
            }
        }
        
        // 按创建时间倒序排列
        usort($backups, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
        return $backups;
    }

    /**
     * 获取备份统计信息
     */
    private function getBackupStats()
    {
        $backups = $this->getBackupList();
        $totalSize = 0;
        $typeCounts = [
            'database' => 0,
            'files' => 0,
            'full' => 0
        ];
        
        foreach ($backups as $backup) {
            $type = $backup['type'];
            if (!isset($typeCounts[$type])) {
                $typeCounts[$type] = 0;
            }
            $typeCounts[$type]++;
        }
        
        return [
            'total_count' => count($backups),
            'type_counts' => $typeCounts,
            'oldest_backup' => count($backups) > 0 ? end($backups)['created_at'] : null,
            'newest_backup' => count($backups) > 0 ? $backups[0]['created_at'] : null
        ];
    }

    /**
     * 记录备份信息
     */
    private function logBackup($type, $filename, $size)
    {
        // 这里可以记录到数据库或日志文件
        \Log::info("Backup created", [
            'type' => $type,
            'filename' => $filename,
            'size' => $size,
            'created_at' => now()
        ]);
    }

    /**
     * 删除备份记录
     */
    private function removeBackupLog($filename)
    {
        \Log::info("Backup deleted", [
            'filename' => $filename,
            'deleted_at' => now()
        ]);
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
     * 格式化文件大小
     */
    private function formatFileSize($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * 获取备份类型
     */
    private function getBackupType($filename)
    {
        if (strpos($filename, 'database_backup') === 0) {
            return 'database';
        } elseif (strpos($filename, 'files_backup') === 0) {
            return 'files';
        } elseif (strpos($filename, 'full_backup') === 0) {
            return 'full';
        }
        
        return 'unknown';
    }
} 