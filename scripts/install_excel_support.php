<?php

/**
 * 安装Excel支持脚本
 * 用于安装PhpSpreadsheet库以支持Excel导出功能
 */

echo "=== 库存系统Excel支持安装脚本 ===\n\n";

// 检查是否已安装PhpSpreadsheet
if (class_exists('\PhpOffice\PhpSpreadsheet\Spreadsheet')) {
    echo "✅ PhpSpreadsheet库已安装\n";
    echo "✅ Excel导出功能已可用\n\n";
    exit(0);
}

echo "❌ PhpSpreadsheet库未安装\n";
echo "正在尝试安装...\n\n";

// 尝试使用composer安装
$composerCommand = 'composer require phpoffice/phpspreadsheet';
echo "执行命令: {$composerCommand}\n";

$output = [];
$returnCode = 0;
exec($composerCommand . ' 2>&1', $output, $returnCode);

if ($returnCode === 0) {
    echo "✅ PhpSpreadsheet库安装成功！\n";
    echo "✅ Excel导出功能现在可以使用了\n\n";
    echo "使用说明：\n";
    echo "1. 在库存页面点击导出按钮\n";
    echo "2. 选择'导出Excel（含图片）'选项\n";
    echo "3. 导出的Excel文件将包含商品图片\n\n";
} else {
    echo "❌ 自动安装失败\n";
    echo "请手动执行以下命令：\n";
    echo "composer require phpoffice/phpspreadsheet\n\n";
    echo "安装完成后，Excel导出功能即可使用\n";
    exit(1);
}

echo "安装完成！\n"; 