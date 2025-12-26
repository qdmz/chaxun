<?php
// user/change-password.php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/user-auth.php';

$userAuth = new UserAuth();
$userAuth->requireLogin();

$user = $userAuth->getCurrentUser();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = "所有字段都必须填写";
    } elseif (strlen($new_password) < 6) {
        $error = "新密码长度至少6位";
    } elseif ($new_password !== $confirm_password) {
        $error = "两次输入的新密码不一致";
    } else {
        if ($userAuth->changePassword($user['id'], $current_password, $new_password)) {
            $success = "密码修改成功！";
            
            // 清除输入
            $_POST = [];
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
    <title>修改密码 - Excel数据查询系统</title>
    <link rel="stylesheet" href="../assets/css/style.css">
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
        
        header h1 {
            font-size: 24px;
            font-weight: 600;
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
            border-radius: 25px;
            background: rgba(255, 255, 255, 0.15);
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        nav a:hover {
            background: rgba(255, 255, 255, 0.25);
        }
        
        .password-container {
            max-width: 600px;
            margin: 0 auto;
        }
        
        .password-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.15);
            overflow: hidden;
        }
        
        .card-header {
            background: linear-gradient(135deg, #2c3e50 0%, #4a6491 100%);
            color: white;
            padding: 30px 40px;
            text-align: center;
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
        }
        
        .card-icon i {
            font-size: 36px;
        }
        
        .card-header h2 {
            font-size: 24px;
            font-weight: 600;
        }
        
        .card-body {
            padding: 40px;
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .alert-danger {
            background: linear-gradient(135deg, #ff6b6b, #ff4757);
            color: white;
        }
        
        .alert-success {
            background: linear-gradient(135deg, #1dd1a1, #10ac84);
            color: white;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 10px;
            color: #2c3e50;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .form-control {
            width: 100%;
            padding: 16px 20px;
            border: 2px solid #e8f0fe;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .password-requirements {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            border-left: 4px solid #667eea;
        }
        
        .password-requirements h4 {
            color: #2c3e50;
            margin-bottom: 15px;
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
        }
        
        .form-actions {
            display: flex;
            gap: 15px;
        }
        
        .btn {
            padding: 16px 30px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 10px;
            flex: 1;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .btn-secondary {
            background: #f8f9fa;
            color: #667eea;
            border: 2px solid #667eea;
        }
        
        .btn-secondary:hover {
            background: #667eea;
            color: white;
        }
        
        .input-wrapper {
            position: relative;
        }
        
        .password-toggle {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #95a5a6;
            cursor: pointer;
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
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1><i class="fas fa-key"></i> 修改密码</h1>
            <nav>
                <span>欢迎, <?= htmlspecialchars($user['real_name'] ?? $user['username']) ?></span>
                <a href="../index.php"><i class="fas fa-home"></i> 首页</a>
                <a href="change-password.php" class="active"><i class="fas fa-key"></i> 修改密码</a>
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
                        <h2>修改密码</h2>
                    </div>
                    
                    <div class="card-body">
                        <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
                        </div>
                        <?php endif; ?>
                        
                        <form method="POST" id="passwordForm">
                            <div class="form-group">
                                <label for="current_password"><i class="fas fa-lock"></i> 当前密码</label>
                                <div class="input-wrapper">
                                    <input type="password" id="current_password" name="current_password" 
                                           class="form-control" required 
                                           placeholder="请输入当前密码">
                                    <button type="button" class="password-toggle" data-target="current_password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="new_password"><i class="fas fa-key"></i> 新密码</label>
                                <div class="input-wrapper">
                                    <input type="password" id="new_password" name="new_password" 
                                           class="form-control" required 
                                           placeholder="请输入新密码（至少6位）"
                                           minlength="6">
                                    <button type="button" class="password-toggle" data-target="new_password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="password-strength">
                                    <div class="strength-meter">
                                        <div class="strength-fill" id="strengthFill"></div>
                                    </div>
                                    <div class="strength-text" id="strengthText">密码强度: 弱</div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm_password"><i class="fas fa-key"></i> 确认新密码</label>
                                <div class="input-wrapper">
                                    <input type="password" id="confirm_password" name="confirm_password" 
                                           class="form-control" required 
                                           placeholder="请再次输入新密码"
                                           minlength="6">
                                    <button type="button" class="password-toggle" data-target="confirm_password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="match-indicator" id="matchIndicator"></div>
                            </div>
                            
                            <div class="password-requirements">
                                <h4><i class="fas fa-info-circle"></i> 密码要求</h4>
                                <ul class="requirements-list" id="requirementsList">
                                    <li data-requirement="length"><i class="fas fa-times-circle"></i> 至少6个字符</li>
                                    <li data-requirement="uppercase"><i class="fas fa-times-circle"></i> 包含大写字母</li>
                                    <li data-requirement="lowercase"><i class="fas fa-times-circle"></i> 包含小写字母</li>
                                    <li data-requirement="number"><i class="fas fa-times-circle"></i> 包含数字</li>
                                </ul>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> 修改密码
                                </button>
                                <a href="../index.php" class="btn btn-secondary">
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
        // 密码显示/隐藏
        const passwordToggles = document.querySelectorAll('.password-toggle');
        passwordToggles.forEach(toggle => {
            toggle.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const targetInput = document.getElementById(targetId);
                
                if (targetInput) {
                    const type = targetInput.type === 'password' ? 'text' : 'password';
                    targetInput.type = type;
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
                
                strengthFill.style.width = strength.percentage + '%';
                strengthFill.style.background = strength.color;
                strengthText.textContent = '密码强度: ' + strength.text;
                strengthText.style.color = strength.color;
                
                updateRequirements(password);
                checkPasswordMatch();
            });
        }
        
        // 确认密码检查
        if (confirmPasswordInput) {
            confirmPasswordInput.addEventListener('input', checkPasswordMatch);
        }
        
        function checkPasswordStrength(password) {
            let score = 0;
            const requirements = {
                length: password.length >= 6,
                uppercase: /[A-Z]/.test(password),
                lowercase: /[a-z]/.test(password),
                number: /[0-9]/.test(password)
            };
            
            if (requirements.length) score += 25;
            if (requirements.uppercase) score += 25;
            if (requirements.lowercase) score += 25;
            if (requirements.number) score += 25;
            
            let strength, color;
            if (score >= 75) {
                strength = '强';
                color = '#1dd1a1';
            } else if (score >= 50) {
                strength = '中';
                color = '#feca57';
            } else if (score >= 25) {
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
                }
                
                if (isMet) {
                    req.querySelector('i').className = 'fas fa-check-circle';
                    req.style.color = '#1dd1a1';
                } else {
                    req.querySelector('i').className = 'fas fa-times-circle';
                    req.style.color = '#ff6b6b';
                }
            });
        }
        
        function checkPasswordMatch() {
            const newPassword = newPasswordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            
            if (!confirmPassword) {
                matchIndicator.innerHTML = '';
                return;
            }
            
            if (newPassword === confirmPassword) {
                matchIndicator.innerHTML = '<i class="fas fa-check-circle"></i> 密码匹配';
                matchIndicator.style.color = '#1dd1a1';
            } else {
                matchIndicator.innerHTML = '<i class="fas fa-times-circle"></i> 密码不匹配';
                matchIndicator.style.color = '#ff6b6b';
            }
        }
    });
    </script>
</body>
</html>