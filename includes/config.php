<?php
// includes/config.php - 修复编码版本

// 强制设置编码
@ini_set('default_charset', 'UTF-8');
@ini_set('internal_encoding', 'UTF-8');
@ini_set('output_encoding', 'UTF-8');
@ini_set('input_encoding', 'UTF-8');

// 设置HTTP头
if (!headers_sent()) {
    header('Content-Type: text/html; charset=utf-8');
}

// 设置时区
date_default_timezone_set('Asia/Shanghai');

// 错误报告
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 数据库配置
define('DB_HOST', 'localhost'); //请修改成自己的数据信息
define('DB_NAME', 'chaxun');  //请修改成自己的数据信息
define('DB_USER', 'root');  //请修改成自己的数据信息
define('DB_PASS', '123456');  //请修改成自己的数据信息

// 文件上传配置
define('MAX_FILE_SIZE', 10485760); // 10MB
define('UPLOAD_DIR', dirname(__DIR__) . '/uploads/excel/');
define('TEMP_DIR', dirname(__DIR__) . '/uploads/temp/');

// 允许的扩展名
$GLOBALS['ALLOWED_EXTENSIONS'] = ['xls', 'xlsx', 'csv'];

// 启动会话
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 设置数据库连接选项
$db_options = [
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false
];

// 检查目录
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0777, true);
}
if (!file_exists(TEMP_DIR)) {
    mkdir(TEMP_DIR, 0777, true);
}

// 自动加载函数
spl_autoload_register(function ($class_name) {
    $paths = [
        __DIR__ . '/' . $class_name . '.php',
        __DIR__ . '/../lib/' . $class_name . '.php',
    ];
    
    foreach ($paths as $file) {
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
    
    // 处理PHPExcel相关类
    if (strpos($class_name, 'PHPExcel') === 0) {
        $class_file = __DIR__ . '/../lib/PHPExcel/Classes/' . str_replace('_', '/', $class_name) . '.php';
        if (file_exists($class_file)) {
            require_once $class_file;
            return;
        }
    }
}, true, false);
?>
