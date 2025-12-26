<?php
// admin/users.php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/user-manager.php';

$auth = new Auth();
$auth->requireLogin();

$admin = $auth->getCurrentAdmin();
$userManager = new UserManager();

$error = '';
$success = '';
$current_tab = $_GET['tab'] ?? 'list';

// 分页参数
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 20;
$search = trim($_GET['search'] ?? '');

// 处理用户操作
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'create':
            try {
                $data = [
                    'username' => $_POST['username'],
                    'password' => $_POST['password'],
                    'real_name' => $_POST['real_name'],
                    'email' => $_POST['email'] ?? '',
                    'phone' => $_POST['phone'] ?? '',
                    'department' => $_POST['department'] ?? '',
                    'status' => $_POST['status'] ?? 1
                ];
                
                $userId = $userManager->createUser($data);
                $success = "用户创建成功！";
                $current_tab = 'list';
                
            } catch (Exception $e) {
                $error = "创建用户失败: " . $e->getMessage();
                $current_tab = 'create';
            }
            break;
            
        case 'update':
            try {
                $userId = intval($_POST['user_id']);
                $data = [];
                
                if (!empty($_POST['email'])) $data['email'] = $_POST['email'];
                if (!empty($_POST['real_name'])) $data['real_name'] = $_POST['real_name'];
                if (!empty($_POST['phone'])) $data['phone'] = $_POST['phone'];
                if (!empty($_POST['department'])) $data['department'] = $_POST['department'];
                if (isset($_POST['status'])) $data['status'] = intval($_POST['status']);
                if (!empty($_POST['password'])) $data['password'] = $_POST['password'];
                
                $userManager->updateUser($userId, $data);
                $success = "用户更新成功！";
                
            } catch (Exception $e) {
                $error = "更新用户失败: " . $e->getMessage();
            }
            break;
            
        case 'update_password':
            try {
                $userId = intval($_POST['user_id']);
                $newPassword = trim($_POST['new_password']);
                
                if (strlen($newPassword) < 6) {
                    throw new Exception('密码长度至少为6位');
                }
                
                $data = ['password' => password_hash($newPassword, PASSWORD_DEFAULT)];
                $userManager->updateUser($userId, $data);
                $success = '用户密码更新成功！';
                
            } catch (Exception $e) {
                $error = '更新用户密码失败: ' . $e->getMessage();
            }
            break;
            
        case 'update_status':
            try {
                $userId = intval($_POST['user_id']);
                $newStatus = intval($_POST['new_status']);
                
                $userManager->updateUserStatus($userId, $newStatus);
                $success = $newStatus ? '用户已激活！' : '用户已禁用！';
                
            } catch (Exception $e) {
                $error = '更新用户状态失败: ' . $e->getMessage();
            }
            break;
            
        case 'delete':
            $userId = intval($_POST['user_id']);
            try {
                $userManager->deleteUser($userId);
                $success = "用户删除成功！";
            } catch (Exception $e) {
                $error = "删除用户失败: " . $e->getMessage();
            }
            break;
    }
}

// 获取用户数据
$user_data = $userManager->getAllUsers($page, $limit, $search);
$users = $user_data['users'];
$total = $user_data['total'];
$pages = $user_data['pages'];

// 获取统计数据
$stats = $userManager->getStatistics();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>用户管理 - Excel数据查询系统</title>
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
            background: #f5f7fa;
        }
        
        .container {
            max-width: 1400px;
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
        
        nav a:hover, nav span {
            background: rgba(255, 255, 255, 0.25);
        }
        
        .main-content {
            display: flex;
            gap: 30px;
        }
        
        .sidebar {
            width: 250px;
            flex-shrink: 0;
        }
        
        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .stats-card h3 {
            color: #2c3e50;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .stat-item {
            text-align: center;
            padding: 15px;
            border-radius: 10px;
            background: #f8f9fa;
        }
        
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 12px;
            color: #7f8c8d;
            text-transform: uppercase;
        }
        
        .content {
            flex: 1;
        }
        
        .tabs {
            display: flex;
            background: white;
            border-radius: 10px 10px 0 0;
            overflow: hidden;
        }
        
        .tab {
            padding: 15px 30px;
            background: #f8f9fa;
            border: none;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .tab:hover {
            background: #e9ecef;
        }
        
        .tab.active {
            background: white;
            color: #667eea;
            border-bottom: 3px solid #667eea;
        }
        
        .tab-content {
            display: none;
            background: white;
            padding: 30px;
            border-radius: 0 0 10px 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .tab-content.active {
            display: block;
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
        
        .search-box {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .search-box input {
            flex: 1;
            padding: 12px 20px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 16px;
        }
        
        .search-box button {
            padding: 12px 25px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }
        
        .table-responsive {
            overflow-x: auto;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .data-table th,
        .data-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }
        
        .data-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .data-table tr:hover {
            background: #f8f9fa;
        }
        
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .status-active {
            background: #d4edda;
            color: #155724;
        }
        
        .status-inactive {
            background: #f8d7da;
            color: #721c24;
        }
        
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        
        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .btn-sm {
            padding: 5px 10px;
            font-size: 12px;
        }
        
        .btn-edit {
            background: #17a2b8;
            color: white;
        }
        
        .btn-delete {
            background: #dc3545;
            color: white;
        }
        
        .btn-reset {
            background: #ffc107;
            color: #212529;
        }
        
        .btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
            gap: 5px;
        }
        
        .page-link {
            padding: 8px 15px;
            background: #f8f9fa;
            color: #667eea;
            text-decoration: none;
            border-radius: 5px;
        }
        
        .page-link:hover {
            background: #667eea;
            color: white;
        }
        
        .page-link.active {
            background: #667eea;
            color: white;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 16px;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1><i class="fas fa-users"></i> 用户管理</h1>
            <nav>
                <span>欢迎, <?= escape($admin['username']) ?></span>
                <a href="dashboard.php"><i class="fas fa-home"></i> 面板首页</a>
                <a href="users.php" class="active"><i class="fas fa-users"></i> 用户管理</a>
                <a href="upload.php"><i class="fas fa-upload"></i> 上传文件</a>
                <a href="change-password.php"><i class="fas fa-key"></i> 修改密码</a>
                <a href="../index.php" target="_blank"><i class="fas fa-external-link-alt"></i> 前台查看</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> 退出登录</a>
            </nav>
        </header>
        
        <div class="main-content">
            <div class="sidebar">
                <div class="stats-card">
                    <h3><i class="fas fa-chart-bar"></i> 用户统计</h3>
                    <div class="stats-grid">
                        <div class="stat-item">
                            <div class="stat-value"><?= $stats['total_users'] ?></div>
                            <div class="stat-label">总用户数</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value"><?= $stats['active_users'] ?></div>
                            <div class="stat-label">活跃用户</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value"><?= $stats['today_login'] ?></div>
                            <div class="stat-label">今日登录</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="content">
                <div class="tabs">
                    <button class="tab <?= $current_tab == 'list' ? 'active' : '' ?>" onclick="showTab('list')">
                        <i class="fas fa-list"></i> 用户列表
                    </button>
                    <button class="tab <?= $current_tab == 'create' ? 'active' : '' ?>" onclick="showTab('create')">
                        <i class="fas fa-user-plus"></i> 创建用户
                    </button>
                    <button class="tab" id="edit-tab" onclick="showTab('edit')" style="display: none;">
                        <i class="fas fa-edit"></i> 编辑用户
                    </button>
                </div>
                
                <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?= escape($error) ?>
                </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?= escape($success) ?>
                </div>
                <?php endif; ?>
                
                <!-- 用户列表 -->
                <div id="tab-list" class="tab-content <?= $current_tab == 'list' ? 'active' : '' ?>">
                    <div class="search-box">
                        <input type="text" id="searchInput" placeholder="搜索用户名、姓名、邮箱或部门..." 
                               value="<?= escape($search) ?>">
                        <button onclick="performSearch()"><i class="fas fa-search"></i> 搜索</button>
                        <button onclick="clearSearch()"><i class="fas fa-times"></i> 清除</button>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>用户名</th>
                                    <th>姓名</th>
                                    <th>邮箱</th>
                                    <th>部门</th>
                                    <th>状态</th>
                                    <th>最后登录</th>
                                    <th>创建时间</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($users)): ?>
                                <tr>
                                    <td colspan="9" style="text-align: center; padding: 40px;">暂无用户数据</td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?= $user['id'] ?></td>
                                        <td><?= escape($user['username']) ?></td>
                                        <td><?= escape($user['real_name']) ?></td>
                                        <td><?= escape($user['email']) ?></td>
                                        <td><?= escape($user['department']) ?></td>
                                        <td>
                                            <span class="status-badge <?= $user['status'] ? 'status-active' : 'status-inactive' ?>">
                                                <?= $user['status'] ? '正常' : '禁用' ?>
                                            </span>
                                        </td>
                                        <td><?= $user['last_login'] ? date('Y-m-d H:i', strtotime($user['last_login'])) : '从未登录' ?></td>
                                        <td><?= date('Y-m-d H:i', strtotime($user['created_at'])) ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <button type="button" class="btn btn-edit btn-sm" onclick="editUser(<?= $user['id'] ?>, '<?= escape($user['username']) ?>', '<?= escape($user['real_name']) ?>', '<?= escape($user['email']) ?>', '<?= escape($user['phone']) ?>', '<?= escape($user['department']) ?>', <?= $user['status'] ?>)">
                                                    <i class="fas fa-edit"></i> 编辑
                                                </button>
                                                <form method="POST" style="display: inline;" id="status-form-<?= $user['id'] ?>">
                                                    <input type="hidden" name="action" value="update_status">
                                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                    <input type="hidden" name="new_status" value="<?= $user['status'] ? 0 : 1 ?>">
                                                    <button type="submit" class="btn btn-sm <?= $user['status'] ? 'btn-delete' : 'btn-primary' ?>" onclick="return confirm('确定要<?= $user['status'] ? '禁用' : '激活' ?>用户 <?= escape($user['username']) ?> 吗？')">
                                                        <i class="fas fa-<?= $user['status'] ? 'ban' : 'check' ?>"></i> <?= $user['status'] ? '禁用' : '激活' ?>
                                                    </button>
                                                </form>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                    <button type="submit" class="btn btn-delete btn-sm" onclick="return confirm('确定要删除用户 <?= escape($user['username']) ?> 吗？')">
                                                        <i class="fas fa-trash"></i> 删除
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php if ($pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                        <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&tab=list" class="page-link">
                            <i class="fas fa-chevron-left"></i> 上一页
                        </a>
                        <?php endif; ?>
                        
                        <?php 
                        $start = max(1, $page - 2);
                        $end = min($pages, $page + 2);
                        
                        if ($start > 1) echo '<span>...</span>';
                        for ($i = $start; $i <= $end; $i++): ?>
                        <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&tab=list" 
                           class="page-link <?= $i == $page ? 'active' : '' ?>">
                            <?= $i ?>
                        </a>
                        <?php endfor; ?>
                        
                        <?php if ($end < $pages) echo '<span>...</span>'; ?>
                        
                        <?php if ($page < $pages): ?>
                        <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&tab=list" class="page-link">
                            下一页 <i class="fas fa-chevron-right"></i>
                        </a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- 创建用户 -->
                <div id="tab-create" class="tab-content <?= $current_tab == 'create' ? 'active' : '' ?>">
                    <form method="POST">
                        <input type="hidden" name="action" value="create">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="username"><i class="fas fa-user"></i> 用户名 *</label>
                                <input type="text" id="username" name="username" class="form-control" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="password"><i class="fas fa-lock"></i> 密码 *</label>
                                <input type="password" id="password" name="password" class="form-control" required minlength="6">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="real_name"><i class="fas fa-user-tag"></i> 真实姓名 *</label>
                                <input type="text" id="real_name" name="real_name" class="form-control" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="email"><i class="fas fa-envelope"></i> 邮箱</label>
                                <input type="email" id="email" name="email" class="form-control">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="phone"><i class="fas fa-phone"></i> 电话</label>
                                <input type="tel" id="phone" name="phone" class="form-control">
                            </div>
                            
                            <div class="form-group">
                                <label for="department"><i class="fas fa-building"></i> 部门</label>
                                <input type="text" id="department" name="department" class="form-control">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="status"><i class="fas fa-toggle-on"></i> 状态</label>
                            <select id="status" name="status" class="form-control">
                                <option value="1" selected>正常</option>
                                <option value="0">禁用</option>
                            </select>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> 创建用户
                            </button>
                            <button type="reset" class="btn btn-secondary">
                                <i class="fas fa-redo"></i> 重置
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- 编辑用户 -->
                <div id="tab-edit" class="tab-content" style="display: none;">
                    <h3><i class="fas fa-edit"></i> 编辑用户信息</h3>
                    <form method="POST">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" id="edit_user_id" name="user_id" value="">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="edit_username"><i class="fas fa-user"></i> 用户名</label>
                                <input type="text" id="edit_username" name="username" class="form-control" readonly>
                            </div>
                            
                            <div class="form-group">
                                <label for="edit_password"><i class="fas fa-lock"></i> 新密码 (留空则不修改)</label>
                                <input type="password" id="edit_password" name="password" class="form-control" placeholder="留空则不修改密码">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="edit_real_name"><i class="fas fa-user-tag"></i> 真实姓名</label>
                                <input type="text" id="edit_real_name" name="real_name" class="form-control" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="edit_email"><i class="fas fa-envelope"></i> 邮箱</label>
                                <input type="email" id="edit_email" name="email" class="form-control">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="edit_phone"><i class="fas fa-phone"></i> 电话</label>
                                <input type="tel" id="edit_phone" name="phone" class="form-control">
                            </div>
                            
                            <div class="form-group">
                                <label for="edit_department"><i class="fas fa-building"></i> 部门</label>
                                <input type="text" id="edit_department" name="department" class="form-control">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_status"><i class="fas fa-toggle-on"></i> 状态</label>
                            <select id="edit_status" name="status" class="form-control">
                                <option value="1">正常</option>
                                <option value="0">禁用</option>
                            </select>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> 保存更改
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="showTab('list')">
                                <i class="fas fa-arrow-left"></i> 返回列表
                            </button>
                        </div>
                    </form>
                    
                    <!-- 重置密码表单 -->
                    <h3 style="margin-top: 30px;"><i class="fas fa-key"></i> 重置用户密码</h3>
                    <form method="POST" id="reset_password_form">
                        <input type="hidden" name="action" value="update_password">
                        <input type="hidden" id="reset_user_id" name="user_id" value="">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="new_password"><i class="fas fa-lock"></i> 新密码</label>
                                <input type="password" id="new_password" name="new_password" class="form-control" required minlength="6" placeholder="输入新密码">
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-reset">
                                <i class="fas fa-key"></i> 重置密码
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    // 标签页切换
    function showTab(tabName) {
        // 隐藏所有标签页
        document.querySelectorAll('.tab-content').forEach(tab => {
            tab.classList.remove('active');
        });
        document.querySelectorAll('.tab').forEach(tabBtn => {
            tabBtn.classList.remove('active');
        });
        
        // 显示选中的标签页
        const tabContent = document.getElementById('tab-' + tabName);
        if (tabContent) {
            tabContent.classList.add('active');
        }
        
        // 为选中的标签按钮添加激活状态
        if (tabName === 'edit') {
            // 特殊处理编辑标签页
            const editTabBtn = document.getElementById('edit-tab');
            if (editTabBtn) {
                editTabBtn.classList.add('active');
            }
        } else {
            const tabBtn = document.querySelector('.tab[onclick="showTab(\'' + tabName + '\')"]');
            if (tabBtn) {
                tabBtn.classList.add('active');
            }
        }
    }
    
    // 搜索功能
    function performSearch() {
        const searchTerm = document.getElementById('searchInput').value;
        const url = new URL(window.location);
        url.searchParams.set('search', searchTerm);
        url.searchParams.set('page', '1');
        url.searchParams.set('tab', 'list');
        window.location.href = url.toString();
    }
    
    function clearSearch() {
        const url = new URL(window.location);
        url.searchParams.delete('search');
        url.searchParams.set('page', '1');
        url.searchParams.set('tab', 'list');
        window.location.href = url.toString();
    }
    
    // 编辑用户信息
    function editUser(id, username, realName, email, phone, department, status) {
        document.getElementById('edit_user_id').value = id;
        document.getElementById('edit_username').value = username;
        document.getElementById('edit_real_name').value = realName;
        document.getElementById('edit_email').value = email;
        document.getElementById('edit_phone').value = phone;
        document.getElementById('edit_department').value = department;
        document.getElementById('edit_status').value = status;
        
        // 显示编辑标签页按钮
        document.getElementById('edit-tab').style.display = 'block';
        
        // 显示编辑表单标签页
        showTab('edit');
        
        // 滚动到表单位置
        document.getElementById('tab-edit').scrollIntoView({ behavior: 'smooth' });
    }
    
    // 重置密码
    function resetPassword(id, username) {
        if (confirm('确定要重置用户 ' + username + ' 的密码吗？')) {
            document.getElementById('reset_user_id').value = id;
            document.getElementById('reset_password_form').submit();
        }
    }
    </script>
</body>
</html>