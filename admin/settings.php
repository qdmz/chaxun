<?php
// admin/settings.php
header('Content-Type: text/html; charset=utf-8');

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

$auth = new Auth();
$auth->requireLogin();

$admin = $auth->getCurrentAdmin();

$message = '';
$error = '';

// 配置文件路径
$config_file = __DIR__ . '/../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $allow_registration = isset($_POST['allow_registration']) ? 1 : 0;
    
    try {
        // 读取配置文件
        $config_content = file_get_contents($config_file);
        if ($config_content === false) {
            throw new Exception("无法读取配置文件");
        }
        
        // 检查是否已存在注册设置
        if (strpos($config_content, "define('ALLOW_REGISTRATION'") !== false) {
            // 替换现有设置
            $new_config_content = preg_replace(
                "/define\('ALLOW_REGISTRATION', \d\);/",
                "define('ALLOW_REGISTRATION', $allow_registration);",
                $config_content
            );
        } else {
            // 在安全设置后添加新设置
            $config_content_lines = explode("\n", $config_content);
            $new_config_lines = [];
            $added = false;
            
            foreach ($config_content_lines as $line) {
                $new_config_lines[] = $line;
                // 在安全设置注释后添加新设置
                if (strpos($line, '// 安全设置') !== false) {
                    $new_config_lines[] = "define('ALLOW_REGISTRATION', $allow_registration); // 1-允许注册, 0-关闭注册";
                    $added = true;
                }
            }
            
            if (!$added) {
                // 如果没有找到安全设置注释，添加到文件末尾
                $new_config_lines[] = "// 注册开关设置";
                $new_config_lines[] = "define('ALLOW_REGISTRATION', $allow_registration); // 1-允许注册, 0-关闭注册";
            }
            
            $new_config_content = implode("\n", $new_config_lines);
        }
        
        if ($new_config_content !== $config_content) {
            // 保存配置文件
            if (file_put_contents($config_file, $new_config_content) === false) {
                throw new Exception("无法写入配置文件");
            }
            $message = "系统设置已更新！";
        } else {
            $message = "设置没有变化";
        }
        
    } catch (Exception $e) {
        $error = "保存设置失败: " . $e->getMessage();
    }
}

// 获取当前设置
$allow_registration = defined('ALLOW_REGISTRATION') ? constant('ALLOW_REGISTRATION') : 1;
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>系统设置 - Excel数据查询系统</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Microsoft YaHei', 'Segoe UI', Arial, sans-serif; background: #f5f7fa; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        
        header { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            color: white; 
            padding: 20px; 
            border-radius: 15px; 
            margin-bottom: 20px; 
        }
        
        header h1 { 
            font-size: 24px; 
            margin-bottom: 15px; 
            display: flex; 
            align-items: center; 
            gap: 10px; 
        }
        
        nav { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }
        nav a, nav span { 
            color: white; 
            text-decoration: none; 
            padding: 8px 15px; 
            background: rgba(255,255,255,0.2); 
            border-radius: 20px; 
            font-size: 14px;
        }
        
        nav a:hover { background: rgba(255,255,255,0.3); }
        
        .alert { 
            padding: 15px; 
            border-radius: 8px; 
            margin: 15px 0; 
            display: flex; 
            align-items: center; 
            gap: 10px; 
        }
        
        .alert-success { 
            background: #d4edda; 
            color: #155724; 
            border: 1px solid #c3e6cb; 
        }
        
        .alert-danger { 
            background: #f8d7da; 
            color: #721c24; 
            border: 1px solid #f5c6cb; 
        }
        
        .card { 
            background: white; 
            padding: 25px; 
            border-radius: 12px; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.08); 
            margin-bottom: 20px; 
        }
        
        .card h2 { 
            color: #2c3e50; 
            margin-bottom: 20px; 
            padding-bottom: 10px; 
            border-bottom: 2px solid #f1f3f5; 
            display: flex; 
            align-items: center; 
            gap: 10px; 
        }
        
        .setting-item { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            padding: 15px 0; 
            border-bottom: 1px solid #eee; 
        }
        
        .setting-info h4 { 
            color: #2c3e50; 
            margin-bottom: 5px; 
        }
        
        .setting-info p { 
            color: #666; 
            font-size: 14px; 
            line-height: 1.5; 
        }
        
        /* 开关样式 */
        .switch { 
            position: relative; 
            display: inline-block; 
            width: 60px; 
            height: 34px; 
        }
        
        .switch input { 
            opacity: 0; 
            width: 0; 
            height: 0; 
        }
        
        .slider { 
            position: absolute; 
            cursor: pointer; 
            top: 0; 
            left: 0; 
            right: 0; 
            bottom: 0; 
            background-color: #ccc; 
            transition: .4s; 
        }
        
        .slider:before { 
            position: absolute; 
            content: ""; 
            height: 26px; 
            width: 26px; 
            left: 4px; 
            bottom: 4px; 
            background-color: white; 
            transition: .4s; 
        }
        
        input:checked + .slider { 
            background-color: #667eea; 
        }
        
        input:checked + .slider:before { 
            transform: translateX(26px); 
        }
        
        .slider.round { 
            border-radius: 34px; 
        }
        
        .slider.round:before { 
            border-radius: 50%; 
        }
        
        /* 按钮样式 */
        .btn { 
            display: inline-flex; 
            align-items: center; 
            gap: 8px; 
            padding: 10px 20px; 
            border: none; 
            border-radius: 6px; 
            font-size: 16px; 
            font-weight: 500; 
            cursor: pointer; 
            transition: all 0.3s ease; 
        }
        
        .btn-primary { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            color: white; 
        }
        
        .btn-primary:hover { 
            transform: translateY(-2px); 
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3); 
        }
        
        /* 系统信息卡片 */
        .info-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); 
            gap: 15px; 
            margin-top: 15px; 
        }
        
        .info-item { 
            background: #f8f9fa; 
            padding: 15px; 
            border-radius: 8px; 
        }
        
        .info-label { 
            color: #666; 
            font-size: 13px; 
            margin-bottom: 5px; 
        }
        
        .info-value { 
            color: #2c3e50; 
            font-size: 16px; 
            font-weight: 600; 
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1><i class="fas fa-cog"></i> 系统设置</h1>
            <nav>
                <span><?= escape($admin['real_name'] ?? $admin['username']) ?></span>
                <a href="dashboard.php"><i class="fas fa-home"></i> 首页</a>
                <a href="users.php"><i class="fas fa-users"></i> 用户管理</a>
                <a href="upload.php"><i class="fas fa-upload"></i> 上传文件</a>
                <a href="settings.php" style="background: rgba(255,255,255,0.3);"><i class="fas fa-cog"></i> 系统设置</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> 退出登录</a>
            </nav>
        </header>
        
        <?php if ($message): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
        </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </div>
        <?php endif; ?>
        
        <div class="card">
            <h2><i class="fas fa-user-plus"></i> 注册设置</h2>
            <form method="POST">
                <div class="setting-item">
                    <div class="setting-info">
                        <h4>允许用户注册</h4>
                        <p>用户可以通过注册页面创建新账户，需要管理员审核后才能登录</p>
                    </div>
                    <label class="switch">
                        <input type="checkbox" name="allow_registration" value="1" 
                               <?= $allow_registration ? 'checked' : '' ?>>
                        <span class="slider round"></span>
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> 保存设置
                </button>
            </form>
        </div>
        
        <div class="card">
            <h2><i class="fas fa-info-circle"></i> 系统信息</h2>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">PHP版本</div>
                    <div class="info-value"><?= htmlspecialchars(phpversion(), ENT_QUOTES, 'UTF-8') ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">数据库状态</div>
                    <div class="info-value" style="color: #28a745;">
                        <i class="fas fa-check-circle"></i> 正常
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-label">注册开关状态</div>
                    <div class="info-value" style="color: <?= $allow_registration ? '#28a745' : '#dc3545' ?>;">
                        <?= $allow_registration ? '已开启' : '已关闭' ?>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-label">服务器时间</div>
                    <div class="info-value"><?= date('Y-m-d H:i:s') ?></div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    // 防止重复提交
    document.querySelector('form').addEventListener('submit', function(e) {
        const submitBtn = this.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 保存中...';
        }
    });
    </script>
</body>
</html>