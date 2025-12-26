<?php
// admin/upload.php - 修复编码版本
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

// 检查并加载必要的类
if (!class_exists('FileUploader', false)) {
    $fileUploaderFile = __DIR__ . '/../includes/file-upload.php';
    if (file_exists($fileUploaderFile)) {
        require_once $fileUploaderFile;
    } else {
        die("错误: 找不到file-upload.php文件");
    }
}

$auth = new Auth();
$auth->requireLogin();

$admin = $auth->getCurrentAdmin();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['excel_file'])) {
    $uploader = new FileUploader();
    
    try {
        $result = $uploader->uploadExcel($_FILES['excel_file'], $admin['id']);
        
        if ($result['success']) {
            header('Location: dashboard.php?msg=uploaded');
            exit();
        } else {
            $error = $result['message'];
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>上传Excel文件 - Excel数据查询系统</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Microsoft YaHei', '微软雅黑', Arial, sans-serif;
            background-color: #f5f7fa;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        header h1 {
            font-size: 24px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        nav {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        nav a, nav span {
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 5px;
            background: rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            gap: 5px;
            transition: background-color 0.3s;
        }
        
        nav a:hover, nav span {
            background: rgba(255, 255, 255, 0.2);
        }
        
        .upload-container {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .upload-header {
            margin-bottom: 30px;
        }
        
        .upload-header h2 {
            color: #333;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .upload-header p {
            color: #666;
        }
        
        .upload-instructions {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 0 5px 5px 0;
        }
        
        .upload-instructions h3 {
            color: #333;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .upload-instructions ul {
            list-style: none;
            padding-left: 0;
        }
        
        .upload-instructions li {
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert-danger {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        
        .alert-success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .form-control, textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            border-radius: 5px;
            font-size: 16px;
            font-family: inherit;
            transition: border-color 0.3s;
        }
        
        .form-control:focus, textarea:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .upload-preview {
            border: 2px dashed #ddd;
            padding: 40px 20px;
            text-align: center;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .upload-preview:hover, .upload-preview.active {
            border-color: #667eea;
            background: #f8f9fa;
        }
        
        .upload-preview i {
            font-size: 48px;
            color: #667eea;
            margin-bottom: 15px;
        }
        
        .upload-preview p {
            color: #666;
            margin-bottom: 10px;
        }
        
        .file-info {
            color: #666;
            font-size: 14px;
        }
        
        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }
        
        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-primary:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1><i class="fas fa-upload"></i> 上传Excel文件</h1>
            <nav>
                <span>欢迎, <?= htmlspecialchars($admin['username'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                <a href="dashboard.php"><i class="fas fa-home"></i> 面板首页</a>
                <a href="upload.php" class="active"><i class="fas fa-upload"></i> 上传文件</a>
                <a href="change-password.php"><i class="fas fa-key"></i> 修改密码</a>
                <a href="../index.php" target="_blank"><i class="fas fa-external-link-alt"></i> 前台查看</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> 退出登录</a>
            </nav>
        </header>
        
        <main>
            <div class="upload-container">
                <div class="upload-header">
                    <h2><i class="fas fa-file-excel"></i> 上传Excel文件</h2>
                </div>
                
                <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
                </div>
                <?php endif; ?>
                
                <div class="upload-instructions">
                    <h3><i class="fas fa-info-circle"></i> 上传说明</h3>
                    <ul>
                        <li><i class="fas fa-check-circle text-success"></i> 支持的文件格式: .xls, .xlsx, .csv</li>
                        <li><i class="fas fa-check-circle text-success"></i> 最大文件大小: 10MB</li>
                        <li><i class="fas fa-check-circle text-success"></i> 文件第一行应包含列标题</li>
                        <li><i class="fas fa-check-circle text-success"></i> 支持中文文件名</li>
                    </ul>
                </div>
                
                <form method="POST" enctype="multipart/form-data" id="uploadForm">
                    <div class="form-group">
                        <label for="excel_file"><i class="fas fa-file-excel"></i> 选择Excel文件:</label>
                        <div class="upload-preview" id="uploadPreview">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <p>点击或拖拽文件到此处</p>
                            <input type="file" id="excel_file" name="excel_file" 
                                   accept=".xls,.xlsx,.csv" required 
                                   style="display: none;">
                            <div class="file-info" id="fileInfo"></div>
                        </div>
                        <small style="display: block; margin-top: 8px; color: #666;">
                            支持的格式: .xls, .xlsx, .csv (最大10MB)
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <label for="description"><i class="fas fa-comment"></i> 文件描述 (可选):</label>
                        <textarea id="description" name="description" 
                                 rows="3" placeholder="输入文件描述信息..."></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload"></i> 上传文件
                        </button>
                        <a href="dashboard.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> 取消
                        </a>
                    </div>
                </form>
            </div>
        </main>
    </div>
    
    <script>
    // 文件上传预览
    const uploadPreview = document.getElementById('uploadPreview');
    const fileInput = document.getElementById('excel_file');
    const fileInfo = document.getElementById('fileInfo');
    const uploadForm = document.getElementById('uploadForm');
    
    // 点击预览区域选择文件
    uploadPreview.addEventListener('click', function(e) {
        if (e.target !== fileInput) {
            fileInput.click();
        }
    });
    
    // 文件选择变化
    fileInput.addEventListener('change', function() {
        if (fileInput.files.length > 0) {
            const file = fileInput.files[0];
            uploadPreview.classList.add('active');
            
            // 验证文件大小
            const maxSize = 10 * 1024 * 1024; // 10MB
            if (file.size > maxSize) {
                alert('文件大小超过10MB限制！');
                fileInput.value = '';
                uploadPreview.classList.remove('active');
                fileInfo.innerHTML = '';
                return;
            }
            
            // 验证文件类型
            const allowedTypes = ['.xls', '.xlsx', '.csv'];
            const fileName = file.name.toLowerCase();
            const isValidType = allowedTypes.some(type => fileName.endsWith(type));
            
            if (!isValidType) {
                alert('只支持.xls, .xlsx, .csv格式的文件！');
                fileInput.value = '';
                uploadPreview.classList.remove('active');
                fileInfo.innerHTML = '';
                return;
            }
            
            fileInfo.innerHTML = `
                <div><strong>文件名:</strong> ${file.name}</div>
                <div><strong>大小:</strong> ${formatFileSize(file.size)}</div>
                <div><strong>类型:</strong> ${file.type || 'Excel文件'}</div>
            `;
        }
    });
    
    // 拖放功能
    uploadPreview.addEventListener('dragover', function(e) {
        e.preventDefault();
        uploadPreview.classList.add('active');
    });
    
    uploadPreview.addEventListener('dragleave', function() {
        uploadPreview.classList.remove('active');
    });
    
    uploadPreview.addEventListener('drop', function(e) {
        e.preventDefault();
        uploadPreview.classList.remove('active');
        
        if (e.dataTransfer.files.length > 0) {
            fileInput.files = e.dataTransfer.files;
            const file = fileInput.files[0];
            
            // 触发change事件
            const event = new Event('change', { bubbles: true });
            fileInput.dispatchEvent(event);
        }
    });
    
    // 表单提交验证
    uploadForm.addEventListener('submit', function(e) {
        if (!fileInput.files.length) {
            e.preventDefault();
            alert('请选择要上传的文件！');
            return;
        }
        
        const file = fileInput.files[0];
        const maxSize = 10 * 1024 * 1024; // 10MB
        
        if (file.size > maxSize) {
            e.preventDefault();
            alert('文件大小超过10MB限制！');
            return;
        }
        
        // 显示上传中提示
        const submitBtn = this.querySelector('button[type="submit"]');
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 上传中...';
        submitBtn.disabled = true;
    });
    
    // 格式化文件大小
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
    </script>
</body>
</html>