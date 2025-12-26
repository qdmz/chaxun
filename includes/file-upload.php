<?php
// includes/file-upload.php - 修复版本
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';

class FileUploader {
    private $db;
    
    public function __construct() {
        global $db;
        $this->db = $db;
    }
    
    public function uploadExcel($file, $uploaderId) {
        // 检查上传错误
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return [
                'success' => false,
                'message' => $this->getUploadError($file['error'])
            ];
        }
        
        // 检查文件大小
        if ($file['size'] > MAX_FILE_SIZE) {
            return [
                'success' => false,
                'message' => '文件大小超过限制 (最大: ' . (MAX_FILE_SIZE / 1024 / 1024) . 'MB)'
            ];
        }
        
        // 检查文件类型
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($fileExtension, $GLOBALS['ALLOWED_EXTENSIONS'])) {
            return [
                'success' => false,
                'message' => '不支持的文件类型。仅支持: ' . implode(', ', $GLOBALS['ALLOWED_EXTENSIONS'])
            ];
        }
        
        // 处理文件名编码
        $originalName = $this->processFileName($file['name']);
        $safeFilename = $this->generateSafeFilename($originalName, $fileExtension);
        $filePath = UPLOAD_DIR . $safeFilename;
        
        // 确保上传目录存在
        if (!is_dir(UPLOAD_DIR)) {
            mkdir(UPLOAD_DIR, 0777, true);
        }
        
        // 移动上传的文件
        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            $error = error_get_last();
            return [
                'success' => false,
                'message' => '文件上传失败: ' . ($error['message'] ?? '未知错误')
            ];
        }
        
        // 验证文件
        if (!$this->validateFile($filePath)) {
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            return [
                'success' => false,
                'message' => '无效的文件或文件已损坏'
            ];
        }
        
        // 保存到数据库
        $sql = "INSERT INTO excel_files (filename, original_name, file_path, uploader_id) 
                VALUES (?, ?, ?, ?)";
        
        try {
            $this->db->query($sql, [$safeFilename, $originalName, $filePath, $uploaderId]);
            
            return [
                'success' => true,
                'message' => '文件上传成功',
                'filename' => $safeFilename,
                'filepath' => $filePath
            ];
            
        } catch (Exception $e) {
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            return [
                'success' => false,
                'message' => '数据库保存失败: ' . $e->getMessage()
            ];
        }
    }
    
    private function validateFile($filePath) {
        if (!file_exists($filePath)) {
            return false;
        }
        
        if (filesize($filePath) == 0) {
            return false;
        }
        
        $fileExtension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        
        // 对于CSV文件，简单验证
        if ($fileExtension == 'csv') {
            $handle = fopen($filePath, 'r');
            if ($handle === false) {
                return false;
            }
            fclose($handle);
            return true;
        }
        
        // 对于Excel文件，如果有PHPExcel就验证，没有就直接返回true
        if (class_exists('PHPExcel_IOFactory')) {
            try {
                $reader = $fileExtension == 'xlsx' 
                    ? PHPExcel_IOFactory::createReader('Excel2007')
                    : PHPExcel_IOFactory::createReader('Excel5');
                
                $excel = $reader->load($filePath);
                return true;
            } catch (Exception $e) {
                return false;
            }
        }
        
        // 如果没有PHPExcel，只检查文件是否可读
        return is_readable($filePath);
    }
    
    private function processFileName($filename) {
        // 移除BOM
        if (substr($filename, 0, 3) == pack('CCC', 0xef, 0xbb, 0xbf)) {
            $filename = substr($filename, 3);
        }
        
        // 尝试检测编码
        $encodings = ['UTF-8', 'GBK', 'GB2312', 'BIG5', 'ISO-8859-1'];
        $detected = mb_detect_encoding($filename, $encodings, true);
        
        if ($detected && $detected != 'UTF-8') {
            $filename = mb_convert_encoding($filename, 'UTF-8', $detected);
        }
        
        return $this->sanitizeFileName($filename);
    }
    
    private function sanitizeFileName($filename) {
        // 移除非法字符
        $filename = preg_replace('/[\/:*?"<>|\\\\]/', '', $filename);
        $filename = preg_replace('/[\x00-\x1F\x7F]/', '', $filename);
        return trim($filename, ' .');
    }
    
    private function generateSafeFilename($originalName, $extension) {
        $nameWithoutExt = pathinfo($originalName, PATHINFO_FILENAME);
        $safeName = $this->sanitizeFileName($nameWithoutExt);
        
        if (empty($safeName)) {
            $safeName = 'file';
        }
        
        $timestamp = time();
        $random = bin2hex(random_bytes(4));
        
        return $safeName . '_' . $timestamp . '_' . $random . '.' . $extension;
    }
    
    private function getUploadError($errorCode) {
        $errors = [
            UPLOAD_ERR_INI_SIZE => '文件大小超过服务器限制',
            UPLOAD_ERR_FORM_SIZE => '文件大小超过表单限制',
            UPLOAD_ERR_PARTIAL => '文件只有部分被上传',
            UPLOAD_ERR_NO_FILE => '没有文件被上传',
            UPLOAD_ERR_NO_TMP_DIR => '缺少临时文件夹',
            UPLOAD_ERR_CANT_WRITE => '文件写入失败',
            UPLOAD_ERR_EXTENSION => 'PHP扩展阻止了文件上传'
        ];
        
        return $errors[$errorCode] ?? '未知上传错误 (代码: ' . $errorCode . ')';
    }
}
?>