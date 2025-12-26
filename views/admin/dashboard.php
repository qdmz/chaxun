<?php
// views/admin/dashboard.php
require_once __DIR__ . '/../../includes/functions.php';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>系统仪表盘</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .stat-card {
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-value {
            font-size: 2.5rem;
            font-weight: bold;
        }
        .nav-pills .nav-link.active {
            background-color: #667eea;
        }
        .file-table th {
            background-color: #f8f9fa;
        }
        .sidebar {
            position: sticky;
            top: 20px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- 侧边栏导航 -->
            <div class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column nav-pills">
                        <li class="nav-item">
                            <a class="nav-link active" href="#">
                                <i class="fas fa-tachometer-alt me-2"></i>仪表盘
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="users.php">
                                <i class="fas fa-users me-2"></i>用户管理
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="upload.php">
                                <i class="fas fa-upload me-2"></i>文件上传
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="settings.php">
                                <i class="fas fa-cog me-2"></i>系统设置
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>退出登录
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- 主内容区 -->
            <div class="col-md-9 col-lg-10 ms-sm-auto px-md-4 py-4">
                <h1 class="mb-4"><i class="fas fa-tachometer-alt me-2"></i>欢迎, <?= escape($admin['username']) ?></h1>
                
                <!-- 统计卡片 -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stat-card bg-primary text-white">
                            <div class="stat-value"><?= $stats['total_files'] ?></div>
                            <div>总文件数</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card bg-success text-white">
                            <div class="stat-value"><?= $stats['total_users'] ?></div>
                            <div>总用户数</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card bg-info text-white">
                            <div class="stat-value"><?= $stats['active_users'] ?></div>
                            <div>活跃用户</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card bg-warning text-dark">
                            <div class="stat-value"><?= $stats['pending_users'] ?></div>
                            <div>待审核用户</div>
                        </div>
                    </div>
                </div>
                
                <!-- 部门分布 -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-light">
                                <h5 class="mb-0"><i class="fas fa-building me-2"></i>部门分布</h5>
                            </div>
                            <div class="card-body">
                                <ul class="list-group">
                                    <?php foreach ($stats['by_department'] as $dept): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <?= escape($dept['department'] ?: '未分配部门') ?>
                                        <span class="badge bg-primary rounded-pill"><?= $dept['count'] ?></span>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 最近文件 -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-light">
                                <h5 class="mb-0"><i class="fas fa-file-excel me-2"></i>最近上传的文件</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover file-table">
                                        <thead>
                                            <tr>
                                                <th>文件名</th>
                                                <th>上传时间</th>
                                                <th>大小</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($files as $file): ?>
                                            <tr>
                                                <td><?= escape($file['original_name']) ?></td>
                                                <td><?= date('Y-m-d H:i', strtotime($file['upload_time'])) ?></td>
                                                <td><?= formatFileSize($file['file_size']) ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // 激活当前导航项
    document.querySelectorAll('.nav-link').forEach(link => {
        if (link.href === window.location.href) {
            link.classList.add('active');
        }
    });
    </script>
</body>
</html>
