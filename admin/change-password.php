<?php
// admin/change-password.php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

$auth = new Auth();
$auth->requireLogin();

$admin = $auth->getCurrentAdmin();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // 验证输入
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = "所有字段都必须填写";
    } elseif (strlen($new_password) < 6) {
        $error = "新密码长度至少6位";
    } elseif ($new_password !== $confirm_password) {
        $error = "两次输入的新密码不一致";
    } else {
        // 验证当前密码
        $sql = "SELECT password FROM admins WHERE id = ?";
        $result = $db->fetchOne($sql, [$admin['id']]);
        
        if ($result && password_verify($current_password, $result['password'])) {
            // 更新密码
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $sql = "UPDATE admins SET password = ? WHERE id = ?";
            $db->query($sql, [$hashed_password, $admin['id']]);
            
            $success = "密码修改成功！";
            
        } else {
            $error = "当前密码不正确";
        }
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
    <title>修改密码 - Excel数据查询系统</title>
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
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            overflow-x: hidden;
        }
        
        .container {
            min-height: 100vh;
            width: 100%;
            padding: 20px;
            display: flex;
            flex-direction: column;
        }
        
        header {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            padding: 20px 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            position: relative;
            overflow: hidden;
        }
        
        header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 100"><path fill="rgba(255,255,255,0.1)" d="M0,0V100H1000V0C800,50 600,30 400,50 200,70 100,30 0,0Z"/></svg>') no-repeat bottom;
            background-size: 100% 50px;
        }
        
        header h1 {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 12px;
            position: relative;
            z-index: 1;
        }
        
        nav {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            position: relative;
            z-index: 1;
        }
        
        nav a, nav span {
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 25px;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            font-weight: 500;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        nav a:hover, nav span {
            background: rgba(255, 255, 255, 0.25);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        nav a.active {
            background: rgba(255, 255, 255, 0.3);
            box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.5);
        }
        
        main {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px 0;
        }
        
        .password-container {
            width: 100%;
            max-width: 600px;
        }
        
        .password-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            transform: translateY(0);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .password-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.2);
        }
        
        .card-header {
            background: linear-gradient(135deg, #2c3e50 0%, #4a6491 100%);
            color: white;
            padding: 30px 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .card-header::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transform: translateX(-100%);
        }
        
        .password-card:hover .card-header::after {
            animation: slide 1s ease;
        }
        
        @keyframes slide {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }
        
        .card-icon {
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
        
        .card-icon i {
            font-size: 36px;
            color: white;
        }
        
        .card-header h2 {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }
        
        .card-body {
            padding: 40px;
        }
        
        .alert {
            padding: 18px 25px;
            border-radius: 12px;
            margin-bottom: 30px;
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
        
        .alert-success {
            background: linear-gradient(135deg, #1dd1a1, #10ac84);
            border-left-color: #00b894;
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
        
        .input-icon {
            position: absolute;
            right: 20px;
            top: 50px;
            color: #6a11cb;
            pointer-events: none;
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
            z-index: 2;
        }
        
        .password-toggle:hover {
            color: #6a11cb;
        }
        
        .password-requirements {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-left: 4px solid #6a11cb;
            padding: 20px;
            border-radius: 0 10px 10px 0;
            margin: 20px 0;
        }
        
        .password-requirements h4 {
            color: #2c3e50;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .requirements-list {
            list-style: none;
            padding-left: 0;
        }
        
        .requirements-list li {
            margin-bottom: 8px;
            color: #666;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: color 0.3s;
        }
        
        .requirements-list li.valid {
            color: #1dd1a1;
        }
        
        .requirements-list li.invalid {
            color: #ff6b6b;
        }
        
        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        
        .btn {
            padding: 16px 30px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-decoration: none;
            font-family: inherit;
            flex: 1;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(106, 17, 203, 0.3);
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #95a5a6, #7f8c8d);
            color: white;
        }
        
        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(127, 140, 141, 0.3);
        }
        
        .password-strength {
            margin-top: 10px;
        }
        
        .strength-meter {
            height: 6px;
            background: #e8f0fe;
            border-radius: 3px;
            overflow: hidden;
            margin-bottom: 8px;
        }
        
        .strength-fill {
            height: 100%;
            width: 0;
            background: #ff6b6b;
            border-radius: 3px;
            transition: width 0.3s, background 0.3s;
        }
        
        .strength-text {
            font-size: 12px;
            color: #95a5a6;
            text-align: right;
        }
        
        .match-indicator {
            font-size: 12px;
            margin-top: 8px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .match-indicator.match {
            color: #1dd1a1;
        }
        
        .match-indicator.no-match {
            color: #ff6b6b;
        }
        
        /* 响应式设计 */
        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }
            
            header {
                padding: 20px;
            }
            
            nav {
                flex-direction: column;
            }
            
            nav a, nav span {
                width: 100%;
                justify-content: center;
            }
            
            .card-body {
                padding: 30px 20px;
            }
            
            .form-actions {
                flex-direction: column;
            }
        }
        
        @media (max-width: 480px) {
            .password-container {
                max-width: 100%;
            }
            
            .card-header {
                padding: 20px 15px;
            }
            
            .card-body {
                padding: 20px 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1><i class="fas fa-key"></i> 修改密码</h1>
            <nav>
                <span>欢迎, <?= htmlspecialchars($admin['username'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                <a href="dashboard.php"><i class="fas fa-home"></i> 面板首页</a>
                <a href="change-password.php" class="active"><i class="fas fa-key"></i> 修改密码</a>
                <a href="../index.php" target="_blank"><i class="fas fa-external-link-alt"></i> 前台查看</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> 退出登录</a>
            </nav>
        </header>
        
        <main>
            <div class="password-container">
                <div class="password-card">
                    <div class="card-header">
                        <div class="card-icon">
                            <i class="fas fa-lock"></i>
                        </div>
                        <h2><i class="fas fa-user-shield"></i> 修改管理员密码</h2>
                    </div>
                    
                    <div class="card-body">
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
                        
                        <form method="POST" id="passwordForm">
                            <div class="form-group">
                                <label for="current_password"><i class="fas fa-lock"></i> 当前密码</label>
                                <input type="password" id="current_password" name="current_password" 
                                       class="form-control" required 
                                       placeholder="请输入当前密码"
                                       autocomplete="current-password">
                                <button type="button" class="password-toggle" data-target="current_password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            
                            <div class="form-group">
                                <label for="new_password"><i class="fas fa-key"></i> 新密码</label>
                                <input type="password" id="new_password" name="new_password" 
                                       class="form-control" required 
                                       placeholder="请输入新密码（至少6位）"
                                       minlength="6"
                                       autocomplete="new-password">
                                <button type="button" class="password-toggle" data-target="new_password">
                                    <i class="fas fa-eye"></i>
                                </button>
                                
                                <div class="password-strength">
                                    <div class="strength-meter">
                                        <div class="strength-fill" id="strengthFill"></div>
                                    </div>
                                    <div class="strength-text" id="strengthText">密码强度: 弱</div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm_password"><i class="fas fa-key"></i> 确认新密码</label>
                                <input type="password" id="confirm_password" name="confirm_password" 
                                       class="form-control" required 
                                       placeholder="请再次输入新密码"
                                       minlength="6"
                                       autocomplete="new-password">
                                <button type="button" class="password-toggle" data-target="confirm_password">
                                    <i class="fas fa-eye"></i>
                                </button>
                                
                                <div class="match-indicator" id="matchIndicator"></div>
                            </div>
                            
                            <div class="password-requirements">
                                <h4><i class="fas fa-info-circle"></i> 密码要求</h4>
                                <ul class="requirements-list" id="requirementsList">
                                    <li data-requirement="length"><i class="fas fa-times-circle"></i> 至少6个字符</li>
                                    <li data-requirement="uppercase"><i class="fas fa-times-circle"></i> 包含大写字母</li>
                                    <li data-requirement="lowercase"><i class="fas fa-times-circle"></i> 包含小写字母</li>
                                    <li data-requirement="number"><i class="fas fa-times-circle"></i> 包含数字</li>
                                    <li data-requirement="special"><i class="fas fa-times-circle"></i> 包含特殊字符</li>
                                </ul>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> 修改密码
                                </button>
                                <a href="dashboard.php" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> 取消
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // 密码显示/隐藏切换
        const passwordToggles = document.querySelectorAll('.password-toggle');
        passwordToggles.forEach(toggle => {
            toggle.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const targetInput = document.getElementById(targetId);
                
                if (targetInput) {
                    const type = targetInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    targetInput.setAttribute('type', type);
                    this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
                }
            });
        });
        
        const newPasswordInput = document.getElementById('new_password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        const strengthFill = document.getElementById('strengthFill');
        const strengthText = document.getElementById('strengthText');
        const matchIndicator = document.getElementById('matchIndicator');
        const requirements = document.querySelectorAll('[data-requirement]');
        
        // 密码强度检查
        if (newPasswordInput) {
            newPasswordInput.addEventListener('input', function() {
                const password = this.value;
                const strength = checkPasswordStrength(password);
                
                // 更新强度指示器
                strengthFill.style.width = strength.percentage + '%';
                strengthFill.style.background = strength.color;
                strengthText.textContent = '密码强度: ' + strength.text;
                strengthText.style.color = strength.color;
                
                // 更新要求列表
                updateRequirements(password);
                
                // 检查密码匹配
                checkPasswordMatch();
            });
        }
        
        // 确认密码检查
        if (confirmPasswordInput) {
            confirmPasswordInput.addEventListener('input', checkPasswordMatch);
        }
        
        // 检查密码强度
        function checkPasswordStrength(password) {
            let score = 0;
            const requirements = {
                length: password.length >= 8,
                uppercase: /[A-Z]/.test(password),
                lowercase: /[a-z]/.test(password),
                number: /[0-9]/.test(password),
                special: /[^A-Za-z0-9]/.test(password)
            };
            
            // 计算分数
            if (requirements.length) score += 20;
            if (requirements.uppercase) score += 20;
            if (requirements.lowercase) score += 20;
            if (requirements.number) score += 20;
            if (requirements.special) score += 20;
            
            // 确定强度和颜色
            let strength, color;
            if (score >= 80) {
                strength = '强';
                color = '#1dd1a1';
            } else if (score >= 60) {
                strength = '中';
                color = '#feca57';
            } else if (score >= 40) {
                strength = '弱';
                color = '#ff9f43';
            } else {
                strength = '很弱';
                color = '#ff6b6b';
            }
            
            return {
                score: score,
                text: strength,
                color: color,
                percentage: score
            };
        }
        
        // 更新要求列表
        function updateRequirements(password) {
            requirements.forEach(req => {
                const type = req.getAttribute('data-requirement');
                let isMet = false;
                
                switch (type) {
                    case 'length':
                        isMet = password.length >= 6;
                        break;
                    case 'uppercase':
                        isMet = /[A-Z]/.test(password);
                        break;
                    case 'lowercase':
                        isMet = /[a-z]/.test(password);
                        break;
                    case 'number':
                        isMet = /[0-9]/.test(password);
                        break;
                    case 'special':
                        isMet = /[^A-Za-z0-9]/.test(password);
                        break;
                }
                
                if (isMet) {
                    req.classList.remove('invalid');
                    req.classList.add('valid');
                    req.querySelector('i').className = 'fas fa-check-circle';
                } else {
                    req.classList.remove('valid');
                    req.classList.add('invalid');
                    req.querySelector('i').className = 'fas fa-times-circle';
                }
            });
        }
        
        // 检查密码匹配
        function checkPasswordMatch() {
            const newPassword = newPasswordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            
            if (!confirmPassword) {
                matchIndicator.innerHTML = '';
                return;
            }
            
            if (newPassword === confirmPassword) {
                matchIndicator.innerHTML = '<i class="fas fa-check-circle"></i> 密码匹配';
                matchIndicator.className = 'match-indicator match';
            } else {
                matchIndicator.innerHTML = '<i class="fas fa-times-circle"></i> 密码不匹配';
                matchIndicator.className = 'match-indicator no-match';
            }
        }
        
        // 表单验证
        const passwordForm = document.getElementById('passwordForm');
        if (passwordForm) {
            passwordForm.addEventListener('submit', function(e) {
                const currentPassword = document.getElementById('current_password').value;
                const newPassword = document.getElementById('new_password').value;
                const confirmPassword = document.getElementById('confirm_password').value;
                
                if (!currentPassword) {
                    e.preventDefault();
                    showError('请输入当前密码');
                    return;
                }
                
                if (!newPassword) {
                    e.preventDefault();
                    showError('请输入新密码');
                    return;
                }
                
                if (newPassword.length < 6) {
                    e.preventDefault();
                    showError('新密码长度至少6位');
                    return;
                }
                
                if (newPassword !== confirmPassword) {
                    e.preventDefault();
                    showError('两次输入的密码不一致');
                    return;
                }
                
                // 显示加载状态
                const submitBtn = this.querySelector('button[type="submit"]');
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 修改中...';
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
            
            const cardBody = document.querySelector('.card-body');
            if (cardBody) {
                const form = cardBody.querySelector('form');
                if (form) {
                    cardBody.insertBefore(alertDiv, form);
                } else {
                    cardBody.prepend(alertDiv);
                }
            }
        }
    });
    </script>
</body>
</html>