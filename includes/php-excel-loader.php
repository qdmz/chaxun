<?php
// 文件编码: UTF-8
?>
<?php
// 文件编码: UTF-8
?>
<?php
// includes/php-excel-loader.php

if (!defined('PHPEXCEL_ROOT')) {
    define('PHPEXCEL_ROOT', dirname(__DIR__) . '/lib/PHPExcel/');
    
    // 加载核心文件
    $coreFile = PHPEXCEL_ROOT . 'Classes/PHPExcel.php';
    if (!file_exists($coreFile)) {
        $coreFile = PHPEXCEL_ROOT . 'PHPExcel.php';
    }
    
    if (file_exists($coreFile)) {
        require_once $coreFile;
    } else {
        die("错误: 找不到PHPExcel核心文件");
    }
}

// 加载必要的工厂类
if (!class_exists('PHPExcel_IOFactory', false)) {
    $ioFactoryFile = PHPEXCEL_ROOT . 'Classes/PHPExcel/IOFactory.php';
    if (file_exists($ioFactoryFile)) {
        require_once $ioFactoryFile;
    } else {
        // 尝试另一种路径
        $ioFactoryFile = dirname(__DIR__) . '/lib/PHPExcel/Classes/PHPExcel/IOFactory.php';
        if (file_exists($ioFactoryFile)) {
            require_once $ioFactoryFile;
        }
    }
}

// 加载自动加载器
if (!class_exists('PHPExcel_Autoloader', false)) {
    $autoloadFile = PHPEXCEL_ROOT . 'Classes/PHPExcel/Autoloader.php';
    if (file_exists($autoloadFile)) {
        require_once $autoloadFile;
        
        // 注册PHPExcel的自动加载
        if (class_exists('PHPExcel_Autoloader')) {
            PHPExcel_Autoloader::Register();
        }
    }
}

// 检查必要的类
function ensurePHPExcelClasses() {
    $required_classes = [
        'PHPExcel_IOFactory',
        'PHPExcel_Reader_Excel5',
        'PHPExcel_Reader_Excel2007',
        'PHPExcel_Reader_CSV'
    ];
    
    foreach ($required_classes as $class) {
        if (!class_exists($class, false)) {
            $file = PHPEXCEL_ROOT . 'Classes/' . str_replace('_', '/', $class) . '.php';
            if (file_exists($file)) {
                require_once $file;
            }
        }
    }
}
?>