<?php
// index.php - 修复未登录显示问题
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/user-auth.php';

$userAuth = new UserAuth();
$isLoggedIn = $userAuth->isLoggedIn();
$user = $isLoggedIn ? $userAuth->getCurrentUser() : null;

$searchResults = null;
$searchKeyword = '';
$selectedFileId = 0;
$files = [];
$searchError = '';
$hasSearched = false;

if ($isLoggedIn) {
    if (!class_exists('ExcelReader', false)) {
        $excelReaderFile = __DIR__ . '/includes/excel-reader.php';
        if (file_exists($excelReaderFile)) {
            require_once $excelReaderFile;
        } else {
            die("错误: 找不到excel-reader.php文件");
        }
    }

    $files = ExcelReader::getAvailableFiles();
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['search'])) {
        $hasSearched = true;
        $searchKeyword = trim($_POST['keyword']);
        $fileId = intval($_POST['file_id']);
        $selectedFileId = $fileId;
        
        if (!empty($searchKeyword) && $fileId > 0) {
            $sql = "SELECT * FROM excel_files WHERE id = ?";
            $file = $db->fetchOne($sql, [$fileId]);
            
            if ($file && file_exists($file['file_path'])) {
                try {
                    $excelReader = new ExcelReader($file['file_path']);
                    $keywords = preg_split('/\s+/', $searchKeyword);
                    set_time_limit(300);
                    $searchResults = $excelReader->searchData($keywords);
                } catch (Exception $e) {
                    $searchError = "搜索失败: " . $e->getMessage();
                }
            } else {
                $searchError = "选择的文件不存在";
            }
        } else {
            $searchError = "请填写搜索关键词并选择文件";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Excel数据查询系统</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', 'Microsoft YaHei', Arial, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        nav {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        nav a, nav span {
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 25px;
            background: rgba(255, 255, 255, 0.15);
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        nav a:hover, nav span {
            background: rgba(255, 255, 255, 0.25);
            transform: translateY(-2px);
        }
        
        .main-content {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .login-prompt {
            text-align: center;
            padding: 40px 20px;
        }
        
        .login-prompt i {
            font-size: 60px;
            color: #667eea;
            margin-bottom: 20px;
        }
        
        .login-prompt h2 {
            color: #2c3e50;
            margin-bottom: 15px;
        }
        
        .login-prompt p {
            color: #666;
            margin-bottom: 25px;
            line-height: 1.6;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 25px;
            border-radius: 25px;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .instructions {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .instructions h3 {
            color: #2c3e50;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .instructions ul {
            list-style: none;
            padding-left: 0;
        }
        
        .instructions li {
            margin-bottom: 10px;
            padding-left: 30px;
            position: relative;
        }
        
        .instructions li:before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #667eea;
            font-weight: bold;
        }
        
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .feature-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .feature-card i {
            font-size: 24px;
            color: #667eea;
            margin-bottom: 15px;
        }
        
        .feature-card h4 {
            color: #2c3e50;
            margin-bottom: 10px;
        }
        
        .feature-card p {
            color: #666;
            font-size: 14px;
        }
        
        .search-section {
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #2c3e50;
            font-weight: 600;
        }
        
        .form-control, select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            font-size: 16px;
        }
        
        #keyword {
            width: 100%;
            min-width: 300px;
            padding: 15px 20px;
            font-size: 16px;
        }
        
        #keyword {
            width: 100%;
            min-width: 300px;
            padding: 15px 20px;
            font-size: 16px;
        }
        
        .form-control:focus, select:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
        }
        
        .table-responsive {
            overflow-x: auto;
            margin: 20px 0;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        
        .data-table th,
        .data-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }
        
        .data-table th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .data-table tr {
            transition: background-color 0.3s ease;
        }
        
        .data-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .data-table tr:hover {
            background: #e9ecef;
        }
        
        .data-table td {
            transition: background-color 0.3s ease;
        }
        
        #loading {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 9999;
            display: none;
        }
    </style>
</head>
<body>
    <div id="loading">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 30px; border-radius: 10px; text-align: center;">
            <i class="fas fa-spinner fa-spin" style="font-size: 40px;"></i>
            <h3>正在搜索数据，请稍候...</h3>
        </div>
    </div>
    
    <div class="container">
        <header>
            <div class="header-content">
                <h1><i class="fas fa-file-excel"></i> Excel数据查询系统</h1>
                <nav>
                    <?php if ($isLoggedIn): ?>
                        <span>欢迎, <?= escape($user['real_name'] ?? $user['username']) ?></span>
                        <a href="user/change-password.php"><i class="fas fa-key"></i> 修改密码</a>
                        <a href="user/logout.php"><i class="fas fa-sign-out-alt"></i> 退出登录</a>
                    <?php else: ?>
                        <a href="user/login.php"><i class="fas fa-sign-in-alt"></i> 用户登录</a>
                        <a href="admin/login.php"><i class="fas fa-user-cog"></i> 管理员登录</a>
                        <a href="readme.html"><i class="fas fa-user-cog"></i> 关于本系统</a>
                    <?php endif; ?>
                </nav>
            </div>
        </header>
        
        <div class="main-content">
            <?php if (!$isLoggedIn): ?>
            <!-- 未登录时显示使用说明 -->
            <div class="login-prompt">
                <i class="fas fa-file-excel"></i>
                <h2>Excel数据查询系统</h2>
                <p>请先登录以使用数据查询功能。如果您还没有账号，请联系管理员获取。</p>
                <a href="user/login.php" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i> 立即登录
                </a>
            </div>
            
            <div class="instructions">
                <h3><i class="fas fa-info-circle"></i> 系统使用说明</h3>
                <ul>
                    <li>本系统用于查询和管理Excel格式的数据文件</li>
                    <li>支持 .xls, .xlsx, .csv 格式文件</li>
                    <li>管理员可以上传和管理Excel文件</li>
                    <li>用户可以搜索和查看文件内容</li>
                    <li>多关键词搜索，支持模糊匹配</li>
                    <li>安全的用户认证和权限管理</li>
                </ul>
            </div>
            
            <div class="features">
                <div class="feature-card">
                    <i class="fas fa-search"></i>
                    <h4>智能搜索</h4>
                    <p>支持多关键词搜索，快速定位所需数据</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-shield-alt"></i>
                    <h4>安全可靠</h4>
                    <p>多重安全验证，保护您的数据安全</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-mobile-alt"></i>
                    <h4>响应式设计</h4>
                    <p>支持各种设备，随时随地访问</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-bolt"></i>
                    <h4>快速高效</h4>
                    <p>优化算法，海量数据快速查询</p>
                </div>
            </div>
            
            <?php else: ?>
            <!-- 已登录时显示搜索功能 -->
            <div class="search-section">
                <h2 class="section-title"><i class="fas fa-search"></i> 数据查询</h2>
                
                <?php if ($searchError): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?= escape($searchError) ?>
                </div>
                <?php endif; ?>
                
                <form method="POST" class="search-form" onsubmit="showLoading()">
                    <div class="form-group">
                        <label for="file_select"><i class="fas fa-file"></i> 选择Excel文件:</label>
                        <select id="file_select" name="file_id" required>
                            <option value="">请选择文件...</option>
                            <?php if (!empty($files)): ?>
                                <?php foreach($files as $file): ?>
                                <option value="<?= escape($file['id']) ?>" <?= $selectedFileId == $file['id'] ? 'selected' : '' ?>>
                                    <?= escape($file['original_name']) ?>
                                </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="keyword"><i class="fas fa-key"></i> 搜索关键词:</label>
                        <input type="text" id="keyword" name="keyword" 
                               placeholder="输入搜索关键词，多个词用空格分隔" 
                               value="<?= escape($searchKeyword) ?>" 
                               required>
                    </div>
                    
                    <button type="submit" name="search" class="btn btn-primary btn-block">
                        <i class="fas fa-search"></i> 开始搜索
                    </button>
                </form>
            </div>
            
            <?php if ($hasSearched): ?>
            <div class="results-section">
                <h2 class="section-title">
                    <i class="fas fa-list"></i> 查询结果
                    <?php if ($searchResults !== null): ?>
                    <span class="result-count">共找到 <?= $searchResults['total_found'] ?> 条记录</span>
                    <?php endif; ?>
                </h2>
                
                <?php if ($searchResults !== null && $searchResults['total_found'] > 0): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> 搜索完成，找到 <?= $searchResults['total_found'] ?> 条匹配记录
                </div>
                
                <div class="actions-section" style="margin-bottom: 15px; display: flex; gap: 10px;">
                    <button type="button" class="btn btn-primary" onclick="downloadCSV()">
                        <i class="fas fa-download"></i> 下载数据 (CSV)
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="printData()">
                        <i class="fas fa-print"></i> 打印数据
                    </button>
                </div>
                
                <div class="table-responsive" id="data-table">
                    <table class="data-table" id="results-table">
                        <thead>
                            <tr>
                                <?php foreach($searchResults['headers'] as $header): ?>
                                <th><?= escape($header) ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($searchResults['data'] as $row): ?>
                            <tr>
                                <?php foreach($row as $cell): ?>
                                <td><?= escape($cell) ?></td>
                                <?php endforeach; ?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php elseif ($searchResults !== null && $searchResults['total_found'] == 0): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> 没有找到匹配的记录
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
    function showLoading() {
        const keyword = document.getElementById('keyword').value.trim();
        const fileSelect = document.getElementById('file_select');
        
        if (!keyword) {
            alert('请输入搜索关键词');
            document.getElementById('keyword').focus();
            return false;
        }
        
        if (!fileSelect.value) {
            alert('请选择要搜索的文件');
            fileSelect.focus();
            return false;
        }
        
        document.getElementById('loading').style.display = 'flex';
        return true;
    }
    
    function downloadCSV() {
        const table = document.getElementById('results-table');
        if (!table) {
            alert('没有数据可下载');
            return;
        }
        
        let csv = '';
        const rows = table.querySelectorAll('tr');
        
        for (let i = 0; i < rows.length; i++) {
            const row = [];
            const cols = rows[i].querySelectorAll('th, td');
            
            for (let j = 0; j < cols.length; j++) {
                let data = cols[j].innerText;
                // 处理包含逗号或引号的单元格内容
                if (data.includes(',') || data.includes('"') || data.includes('\n')) {
                    data = '"' + data.replace(/"/g, '""') + '"';
                }
                row.push(data);
            }
            
            csv += row.join(',') + '\r\n'; // 使用\r\n确保跨平台兼容性
        }
        
        // 创建下载链接
        const blob = new Blob(['\ufeff' + csv], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', 'search_results.csv');
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
    
    function printData() {
        const table = document.getElementById('data-table');
        if (!table) {
            alert('没有数据可打印');
            return;
        }
        
        const originalContent = document.body.innerHTML;
        document.body.innerHTML = `
            <html>
                <head>
                    <title>打印数据</title>
                    <style>
                        @page {
                            size: A4 landscape;
                            margin: 0.5cm;
                        }
                        body { 
                            font-family: Arial, sans-serif; 
                            margin: 0;
                            padding: 0.5cm;
                        }
                        .print-container {
                            width: 100%;
                            overflow-x: auto;
                        }
                        table { 
                            border-collapse: collapse; 
                            width: 100%; 
                            min-width: 100%;
                            table-layout: auto;
                        }
                        th, td { 
                            border: 1px solid #000; 
                            padding: 4px; 
                            text-align: left; 
                            word-wrap: break-word;
                            font-size: 10px;
                        }
                        /* 优化横向打印的表格显示 */
                        @media print {
                            table { page-break-inside: auto; }
                            tr { page-break-inside: avoid; page-break-after: auto; }
                            th, td { padding: 2px; font-size: 9px; }
                        }
                        th { 
                            background-color: #f2f2f2; 
                            font-weight: bold;
                        }
                        /* 防止表格行在打印时被分割 */
                        tr { page-break-inside: avoid; }
                        /* 确保表格可以水平滚动 */
                        table, tr, td, th {
                            page-break-inside: avoid;
                        }
                    </style>
                </head>
                <body>
                    <div class="print-container">
                        <h2>搜索结果数据</h2>
                        ${table.innerHTML}
                    </div>
                </body>
            </html>
        `;
        window.print();
        document.body.innerHTML = originalContent;
        window.location.reload(); // 重新加载页面以恢复原始状态
    }
    </script>
</body>
</html>
