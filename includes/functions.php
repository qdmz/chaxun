<?php
// 文件编码: UTF-8
?>
<?php
// 文件编码: UTF-8
?>
<?php
// includes/functions.php

/**
 * 格式化文件大小
 * @param int $bytes 字节数
 * @return string 格式化后的文件大小
 */
function formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    
    return round($bytes, 2) . ' ' . $units[$pow];
}

/**
 * 计算文件总大小
 * @param array $files 文件数组
 * @return string 格式化后的总大小
 */
function getTotalFileSize($files) {
    $total = 0;
    foreach($files as $file) {
        if (isset($file['file_path']) && file_exists($file['file_path'])) {
            $total += filesize($file['file_path']);
        }
    }
    return formatFileSize($total);
}

/**
 * 安全过滤输出
 * @param string $string 输入字符串
 * @return string 过滤后的字符串
 */
function escape($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}
?>