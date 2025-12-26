<?php
// includes/excel-reader.php

// 直接指定PHPExcel路径
function loadPHPExcelDirectly() {
    $paths = [
        __DIR__ . '/../lib/PHPExcel/Classes/PHPExcel/IOFactory.php',
        __DIR__ . '/../lib/PHPExcel/Classes/PHPExcel.php',
        __DIR__ . '/../lib/PHPExcel/PHPExcel/IOFactory.php',
        __DIR__ . '/../lib/PHPExcel/PHPExcel.php',
        'C:/xampp/htdocs/lib/PHPExcel/Classes/PHPExcel.php', // Windows路径示例
        '/usr/share/php/lib/PHPExcel.php', // Linux路径示例
    ];
    
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return true;
        }
    }
    
    // 尝试从include_path加载
    $include_paths = explode(PATH_SEPARATOR, get_include_path());
    foreach ($include_paths as $include_path) {
        $testFile = rtrim($include_path, '/') . '/PHPExcel/IOFactory.php';
        if (file_exists($testFile)) {
            require_once $testFile;
            return true;
        }
    }
    
    return false;
}

// 加载PHPExcel
if (!class_exists('PHPExcel_IOFactory', false)) {
    if (!loadPHPExcelDirectly()) {
        die("<div style='padding: 20px; background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 5px; margin: 20px;'>
            <h3>错误: PHPExcel库未找到</h3>
            <p>请确保PHPExcel库已正确安装。尝试运行以下命令：</p>
            <pre style='background: #333; color: #fff; padding: 10px; border-radius: 5px;'>
cd " . __DIR__ . "/..
mkdir -p lib
cd lib
wget https://github.com/PHPOffice/PHPExcel/archive/1.8.1.zip
unzip 1.8.1.zip
mv PHPExcel-1.8.1 PHPExcel
            </pre>
            <p>或访问 <a href='install_phpexcel.php'>install_phpexcel.php</a> 自动安装</p>
        </div>");
    }
}

class ExcelReader {
    private $filePath;
    private $cacheDir;
    
    public function __construct($filePath) {
        $this->filePath = $filePath;
        $this->cacheDir = __DIR__ . '/../cache/excel/';
        
        if (!file_exists($this->cacheDir)) {
            mkdir($this->cacheDir, 0777, true);
        }
    }
    
    private function convertEncoding($value) {
        // 如果值是字符串，处理编码
        if (is_string($value)) {
            // 检测是否需要编码转换
            if (!mb_check_encoding($value, 'UTF-8')) {
                // 尝试从GBK转换为UTF-8
                $converted = @mb_convert_encoding($value, 'UTF-8', 'GBK');
                if ($converted && mb_check_encoding($converted, 'UTF-8')) {
                    return $converted;
                }
                
                // 如果GBK转换失败，尝试GB2312
                $converted = @mb_convert_encoding($value, 'UTF-8', 'GB2312');
                if ($converted && mb_check_encoding($converted, 'UTF-8')) {
                    return $converted;
                }
                
                // 如果GB2312也失败，尝试自动检测
                $converted = @mb_convert_encoding($value, 'UTF-8', 'auto');
                if ($converted && mb_check_encoding($converted, 'UTF-8')) {
                    return $converted;
                }
            }
            
            // 如果已经是UTF-8或转换失败，返回原值
            return $value;
        }
        
        // 非字符串值直接返回
        return $value;
    }
    
    public function readData($sheetIndex = 0, $limit = 1000) {
        $fileExtension = strtolower(pathinfo($this->filePath, PATHINFO_EXTENSION));
        
        // 生成缓存键
        $cacheKey = md5($this->filePath . '_' . $sheetIndex . '_' . $limit);
        $cacheFile = $this->cacheDir . $cacheKey . '.cache';
        
        // 检查缓存
        if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < 3600) {
            return unserialize(file_get_contents($cacheFile));
        }
        
        try {
            if ($fileExtension == 'csv') {
                $objReader = PHPExcel_IOFactory::createReader('CSV');
                $objReader->setDelimiter(',');
                $objReader->setEnclosure('"');
                $objReader->setLineEnding("\r\n");
                $objReader->setSheetIndex(0);
            } else {
                $objReader = PHPExcel_IOFactory::createReader(
                    $fileExtension == 'xlsx' ? 'Excel2007' : 'Excel5'
                );
            }
            
            // 只读取数据，不读取格式
            $objReader->setReadDataOnly(true);
            $objPHPExcel = $objReader->load($this->filePath);
            $worksheet = $objPHPExcel->getSheet($sheetIndex);
            
            // 获取数据范围
            $highestRow = $worksheet->getHighestDataRow();
            $highestColumn = $worksheet->getHighestDataColumn();
            
            $data = [];
            $headers = [];
            
            // 读取表头
            $colIndex = 0;
            for ($col = 'A'; $col <= $highestColumn; $col++) {
                $cellValue = $worksheet->getCell($col . '1')->getValue();
                // 确保正确处理中文字符编码
                $cellValue = $this->convertEncoding($cellValue);
                $headers[] = $cellValue ? trim($cellValue) : "列" . ($colIndex + 1);
                $colIndex++;
            }
            
            // 读取数据行
            $rowLimit = min($limit + 1, $highestRow);
            for ($row = 2; $row <= $rowLimit; $row++) {
                $rowData = [];
                $colIndex = 0;
                
                for ($col = 'A'; $col <= $highestColumn && $colIndex < count($headers); $col++) {
                    $cellValue = $worksheet->getCell($col . $row)->getValue();
                    // 确保正确处理中文字符编码
                    $cellValue = $this->convertEncoding($cellValue);
                    $rowData[$headers[$colIndex++]] = $cellValue;
                }
                
                $data[] = $rowData;
            }
            
            $result = [
                'headers' => $headers,
                'data' => $data,
                'total_rows' => $highestRow - 1
            ];
            
            // 保存到缓存
            file_put_contents($cacheFile, serialize($result));
            
            return $result;
            
        } catch (Exception $e) {
            throw new Exception("读取Excel文件失败: " . $e->getMessage());
        }
    }
    
    public function searchData($keywords, $sheetIndex = 0) {
        // 生成搜索缓存键
        $cacheKey = md5($this->filePath . '_' . $sheetIndex . '_search_' . implode('_', $keywords));
        $cacheFile = $this->cacheDir . $cacheKey . '.cache';
        
        // 检查缓存
        if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < 1800) {
            return unserialize(file_get_contents($cacheFile));
        }
        
        $allData = $this->readData($sheetIndex, 10000);
        $results = [];
        $keywords = array_map('strtolower', $keywords);
        
        foreach ($allData['data'] as $row) {
            $match = false;
            
            foreach ($row as $cell) {
                $cellText = strtolower(strval($cell));
                
                foreach ($keywords as $keyword) {
                    if ($keyword !== '' && strpos($cellText, $keyword) !== false) {
                        $match = true;
                        break 2;
                    }
                }
            }
            
            if ($match) {
                $results[] = $row;
            }
        }
        
        $result = [
            'headers' => $allData['headers'],
            'data' => $results,
            'total_found' => count($results)
        ];
        
        // 保存到缓存
        if (!empty($results)) {
            file_put_contents($cacheFile, serialize($result));
        }
        
        return $result;
    }
    
    public static function getAvailableFiles() {
        global $db;
        
        if (!$db) {
            throw new Exception("数据库连接未初始化");
        }
        
        $sql = "SELECT * FROM excel_files ORDER BY upload_time DESC";
        $files = $db->fetchAll($sql);
        
        return $files;
    }
}
?>
