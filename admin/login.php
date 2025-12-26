<?php
// admin/login.php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/auth.php';

$auth = new Auth();

// 如果已登录，跳转到管理面板
if ($auth->isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if ($auth->login($username, $password)) {
        header('Location: dashboard.php');
        exit();
    } else {
        $error = '用户名或密码错误';
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>管理员登录 - Excel数据查询系统</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        html, body {
            height: 100%;
            width: 100%;
            font-family: 'Segoe UI', 'Microsoft YaHei', '微软雅黑', Arial, sans-serif;
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            overflow-x: hidden;
        }
        
        .login-wrapper {
            min-height: 100vh;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
        }
        
        .login-container {
            width: 100%;
            max-width: 450px;
            position: relative;
            z-index: 2;
        }
        
        .login-box {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transform: translateY(0);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .login-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 25px 80px rgba(0, 0, 0, 0.4);
        }
        
        .login-header {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .login-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transform: rotate(45deg);
            animation: shine 3s infinite linear;
        }
        
        @keyframes shine {
            0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
            100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
        }
        
        .logo {
            position: relative;
            z-index: 1;
            margin-bottom: 20px;
        }
        
        .logo-icon {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            border: 3px solid rgba(255, 255, 255, 0.3);
        }
        
        .logo-icon i {
            font-size: 36px;
            color: white;
        }
        
        .login-header h1 {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 10px;
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }
        
        .login-header p {
            font-size: 15px;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }
        
        .login-body {
            padding: 40px 30px 30px;
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideIn 0.3s ease;
            border-left: 4px solid transparent;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .alert-danger {
            background: linear-gradient(135deg, #ff6b6b, #ff4757);
            border-left-color: #ff3838;
            color: white;
        }
        
        .form-group {
            margin-bottom: 25px;
            position: relative;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 10px;
            color: #2c3e50;
            font-weight: 600;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .form-control {
            width: 100%;
            padding: 16px 20px;
            border: 2px solid #e8f0fe;
            border-radius: 12px;
            font-size: 16px;
            background: #f8f9fa;
            color: #2c3e50;
            transition: all 0.3s ease;
            font-family: inherit;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #6a11cb;
            background: white;
            box-shadow: 0 0 0 3px rgba(106, 17, 203, 0.1);
        }
        
        .form-control::placeholder {
            color: #95a5a6;
        }
        
        .input-icon {
            position: absolute;
            right: 20px;
            top: 50px;
            color: #6a11cb;
            pointer-events: none;
        }
        
        .btn {
            display: block;
            width: 100%;
            padding: 16px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            text-decoration: none;
            font-family: inherit;
            position: relative;
            overflow: hidden;
        }
        
        .btn::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 5px;
            height: 5px;
            background: rgba(255, 255, 255, 0.5);
            opacity: 0;
            border-radius: 100%;
            transform: scale(1, 1) translate(-50%);
            transform-origin: 50% 50%;
        }
        
        .btn:focus:not(:active)::after {
            animation: ripple 1s ease-out;
        }
        
        @keyframes ripple {
            0% {
                transform: scale(0, 0);
                opacity: 0.5;
            }
            20% {
                transform: scale(25, 25);
                opacity: 0.3;
            }
            100% {
                transform: scale(40, 40);
                opacity: 0;
            }
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            position: relative;
            z-index: 1;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(106, 17, 203, 0.3);
        }
        
        .btn-primary:active {
            transform: translateY(0);
        }
        
        .login-footer {
            margin-top: 25px;
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #e8f0fe;
        }
        
        .login-footer a {
            color: #6a11cb;
            text-decoration: none;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }
        
        .login-footer a:hover {
            color: #2575fc;
            gap: 12px;
        }
        
        .password-toggle {
            position: absolute;
            right: 20px;
            top: 50px;
            background: none;
            border: none;
            color: #95a5a6;
            cursor: pointer;
            font-size: 18px;
            transition: color 0.3s;
        }
        
        .password-toggle:hover {
            color: #6a11cb;
        }
        
        /* 响应式设计 */
        @media (max-width: 768px) {
            .login-container {
                max-width: 90%;
            }
            
            .login-header {
                padding: 30px 20px;
            }
            
            .login-body {
                padding: 30px 20px 20px;
            }
            
            .login-header h1 {
                font-size: 24px;
            }
        }
        
        @media (max-width: 480px) {
            .login-wrapper {
                padding: 10px;
            }
            
            .login-container {
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-container">
            <div class="login-box">
                <div class="login-header">
                    <div class="logo">
                        <div class="logo-icon">
                            <i class="fas fa-user-lock"></i>
                        </div>
                        <h1><i class="fas fa-user-cog"></i> 管理员登录</h1>
                        <p>Excel数据查询系统后台管理</p>
                    </div>
                </div>
                
                <div class="login-body">
                    <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                    </div>
                    <?php endif; ?>
                    
                    <form method="POST" class="login-form" id="loginForm">
                        <div class="form-group">
                            <label for="username"><i class="fas fa-user-circle"></i> 用户名</label>
                            <input type="text" id="username" name="username" 
                                   class="form-control" required 
                                   placeholder="请输入管理员用户名"
                                   autocomplete="username">
                            <i class="fas fa-user input-icon"></i>
                        </div>
                        
                        <div class="form-group">
                            <label for="password"><i class="fas fa-key"></i> 密码</label>
                            <input type="password" id="password" name="password" 
                                   class="form-control" required 
                                   placeholder="请输入密码"
                                   autocomplete="current-password">
                            <i class="fas fa-lock input-icon"></i>
                            <button type="button" class="password-toggle" id="passwordToggle">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt"></i> 登录系统
                        </button>
                        
                        <div class="login-footer">
                            <a href="../index.php">
                                <i class="fas fa-arrow-left"></i> 返回首页
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    // 自动焦点到用户名输入框
    document.addEventListener('DOMContentLoaded', function() {
        const usernameInput = document.getElementById('username');
        if (usernameInput) {
            setTimeout(() => {
                usernameInput.focus();
            }, 300);
        }
        
        // 密码显示/隐藏切换
        const passwordToggle = document.getElementById('passwordToggle');
        const passwordInput = document.getElementById('password');
        
        if (passwordToggle && passwordInput) {
            passwordToggle.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
            });
        }
        
        // 表单验证
        const loginForm = document.getElementById('loginForm');
        if (loginForm) {
            loginForm.addEventListener('submit', function(e) {
                const username = document.getElementById('username').value.trim();
                const password = document.getElementById('password').value;
                
                if (!username) {
                    e.preventDefault();
                    showError('请输入用户名');
                    return;
                }
                
                if (!password) {
                    e.preventDefault();
                    showError('请输入密码');
                    return;
                }
                
                // 显示加载状态
                const submitBtn = this.querySelector('button[type="submit"]');
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 登录中...';
                submitBtn.disabled = true;
            });
        }
        
        // 显示错误提示
        function showError(message) {
            // 如果已有错误提示，先移除
            const existingAlert = document.querySelector('.alert');
            if (existingAlert) {
                existingAlert.remove();
            }
            
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-danger';
            alertDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;
            
            const loginBody = document.querySelector('.login-body');
            if (loginBody) {
                const form = loginBody.querySelector('form');
                if (form) {
                    loginBody.insertBefore(alertDiv, form);
                } else {
                    loginBody.prepend(alertDiv);
                }
            }
            
            // 自动消失
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.style.opacity = '0';
                    alertDiv.style.transform = 'translateY(-20px)';
                    setTimeout(() => {
                        if (alertDiv.parentNode) {
                            alertDiv.remove();
                        }
                    }, 300);
                }
            }, 5000);
        }
    });
    
    // 回车键提交表单
    document.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            const activeElement = document.activeElement;
            if (activeElement.tagName === 'INPUT' && activeElement.type !== 'submit') {
                e.preventDefault();
                const submitBtn = document.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.click();
                }
            }
        }
    });
    
    // 输入框输入效果
    const inputs = document.querySelectorAll('.form-control');
    inputs.forEach(input => {
        // 输入时添加动画效果
        input.addEventListener('input', function() {
            if (this.value) {
                this.style.borderColor = '#6a11cb';
            } else {
                this.style.borderColor = '#e8f0fe';
            }
        });
        
        // 聚焦效果
        input.addEventListener('focus', function() {
            this.parentElement.style.transform = 'translateY(-2px)';
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.style.transform = 'translateY(0)';
        });
    });
    </script>
</body>
</html>