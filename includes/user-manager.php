<?php
// includes/user-manager.php
require_once __DIR__ . '/config.php';

class UserManager {
    private $db;
    
    public function __construct() {
        global $db;
        $this->db = $db;
    }
    
    public function getAllUsers($page = 1, $limit = 20, $search = '') {
        $offset = ($page - 1) * $limit;
        
        $where = '';
        $params = [];
        
        if (!empty($search)) {
            $where = "WHERE username LIKE ? OR real_name LIKE ? OR email LIKE ? OR department LIKE ?";
            $search_param = "%{$search}%";
            $params = [$search_param, $search_param, $search_param, $search_param];
        }
        
        // 获取总数
        $count_sql = "SELECT COUNT(*) as total FROM users {$where}";
        $total_result = $this->db->fetchOne($count_sql, $params);
        $total = $total_result['total'];
        
        // 获取用户列表
        $sql = "SELECT * FROM users {$where} ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $params[] = (int)$limit;
        $params[] = (int)$offset;
        
        $users = $this->db->fetchAll($sql, $params);
        
        return [
            'users' => $users,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => $total > 0 ? ceil($total / $limit) : 1
        ];
    }
    
    public function getUser($id) {
        $sql = "SELECT * FROM users WHERE id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }
    
    public function createUser($data) {
        $required = ['username', 'password', 'real_name'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new Exception("字段 {$field} 不能为空");
            }
        }
        
        // 检查用户名是否已存在
        $check_sql = "SELECT id FROM users WHERE username = ?";
        $exists = $this->db->fetchOne($check_sql, [$data['username']]);
        if ($exists) {
            throw new Exception("用户名已存在");
        }
        
        $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO users (username, password, email, real_name, phone, department, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $data['username'],
            $hashed_password,
            $data['email'] ?? '',
            $data['real_name'],
            $data['phone'] ?? '',
            $data['department'] ?? '',
            $data['status'] ?? 1
        ];
        
        $this->db->query($sql, $params);
        return $this->db->getLastInsertId();
    }
    
    public function updateUser($id, $data) {
        $user = $this->getUser($id);
        if (!$user) {
            throw new Exception("用户不存在");
        }
        
        $updates = [];
        $params = [];
        
        if (isset($data['email'])) {
            $updates[] = "email = ?";
            $params[] = $data['email'];
        }
        
        if (isset($data['real_name'])) {
            $updates[] = "real_name = ?";
            $params[] = $data['real_name'];
        }
        
        if (isset($data['phone'])) {
            $updates[] = "phone = ?";
            $params[] = $data['phone'];
        }
        
        if (isset($data['department'])) {
            $updates[] = "department = ?";
            $params[] = $data['department'];
        }
        
        if (isset($data['status'])) {
            $updates[] = "status = ?";
            $params[] = $data['status'];
        }
        
        if (isset($data['password']) && !empty($data['password'])) {
            $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
            $updates[] = "password = ?";
            $params[] = $hashed_password;
        }
        
        if (empty($updates)) {
            throw new Exception("没有更新数据");
        }
        
        $updates[] = "updated_at = NOW()";
        
        $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";
        $params[] = $id;
        
        $this->db->query($sql, $params);
        return true;
    }
    
    public function deleteUser($id) {
        $sql = "DELETE FROM users WHERE id = ?";
        $this->db->query($sql, [$id]);
        return true;
    }
    
    public function getStatistics() {
        $stats = [];
        
        // 总用户数
        $sql = "SELECT COUNT(*) as total FROM users";
        $result = $this->db->fetchOne($sql);
        $stats['total_users'] = $result['total'];
        
        // 活跃用户数
        $sql = "SELECT COUNT(*) as active FROM users WHERE status = 1";
        $result = $this->db->fetchOne($sql);
        $stats['active_users'] = $result['active'];
        
        // 今日登录用户数
        $sql = "SELECT COUNT(*) as today_login FROM users WHERE DATE(last_login) = CURDATE()";
        $result = $this->db->fetchOne($sql);
        $stats['today_login'] = $result['today_login'];
        
        // 按部门统计
        $sql = "SELECT department, COUNT(*) as count FROM users WHERE department IS NOT NULL AND department != '' GROUP BY department";
        $result = $this->db->fetchAll($sql);
        $stats['by_department'] = $result;
        
        return $stats;
    }
    
    public function updateUserStatus($id, $status) {
        $sql = "UPDATE users SET status = ?, updated_at = NOW() WHERE id = ?";
        $this->db->query($sql, [$status, $id]);
        return true;
    }
}
?>