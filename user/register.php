<?php
// user/register.php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/user-manager.php';

$userManager = new UserManager();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = [
        'username' => trim($_POST['username']),
        'password' => $_POST['password'],
        'real_name' => trim($_POST['real_name']),
        'email' => trim($_POST['email']),
        'phone' => trim($_POST['phone']),
        'department' => trim($_POST['department'])
    ];

    try {
        // 设置新用户默认为非激活状态
        $data['status'] = 0; // 0 表示未激活/禁用
        $userId = $userManager->createUser($data);
        $success = "注册成功！您的账号需要管理员激活后才能登录。用户名: " . $data['username'];
        
        // 清除输入
        $_POST = [];
        
    } catch (Exception $e) {
        $error = "注册失败: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>用户注册 - Excel数据查询系统</title>
    <style>
        /* 注册页面的样式，可以参考登录页面的样式 */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', 'Microsoft YaHei', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .register-container {
            width: 100%;
            max-width: 500px;
        }
        
        .register-box {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 40px;
        }
        
        /* 其他样式... */
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-box">
            <h1 style="text-align: center; margin-bottom: 30px; color: #2c3e50;">
                <i class="fas fa-user-plus"></i> 用户注册
            </h1>
            
            <?php if ($error): ?>
            <div style="background: #ff6b6b; color: white; padding: 15px; border-radius: 10px; margin-bottom: 20px;">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
            <div style="background: #1dd1a1; color: white; padding: 15px; border-radius: 10px; margin-bottom: 20px;">
                <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
            </div>
            <?php endif; ?>
            
            <form method="POST">
                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; color: #2c3e50;">
                        <i class="fas fa-user"></i> 用户名 *
                    </label>
                    <input type="text" name="username" required style="width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 5px;">
                </div>
                
                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; color: #2c3e50;">
                        <i class="fas fa-lock"></i> 密码 *
                    </label>
                    <input type="password" name="password" required style="width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 5px;">
                </div>
                
                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; color: #2c3e50;">
                        <i class="fas fa-user-tag"></i> 真实姓名 *
                    </label>
                    <input type="text" name="real_name" required style="width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 5px;">
                </div>
                
                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; color: #2c3e50;">
                        <i class="fas fa-envelope"></i> 邮箱
                    </label>
                    <input type="email" name="email" style="width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 5px;">
                </div>
                
                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; color: #2c3e50;">
                        <i class="fas fa-phone"></i> 电话
                    </label>
                    <input type="tel" name="phone" style="width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 5px;">
                </div>
                
                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; color: #2c3e50;">
                        <i class="fas fa-building"></i> 部门
                    </label>
                    <input type="text" name="department" style="width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 5px;">
                </div>
                
                <button type="submit" style="width: 100%; padding: 12px; background: #667eea; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;">
                    <i class="fas fa-user-plus"></i> 注册
                </button>
                
                <div style="text-align: center; margin-top: 20px;">
                    <a href="login.php" style="color: #667eea; text-decoration: none;">
                        <i class="fas fa-arrow-left"></i> 返回登录
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</body>
</html>