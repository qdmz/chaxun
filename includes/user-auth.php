<?php
// includes/user-auth.php
require_once __DIR__ . '/config.php';

class UserAuth {
    private $db;
    
    public function __construct() {
        global $db;
        $this->db = $db;
    }
    
    public function login($username, $password) {
        // 首先检查用户是否存在
        $sql = "SELECT * FROM users WHERE username = ?";
        $user = $this->db->fetchOne($sql, [$username]);
        
        if (!$user) {
            // 用户不存在
            return false;
        }
        
        if ($user['status'] == 0) {
            // 用户被禁用或未激活
            return 'inactive';
        }
        
        if ($user && password_verify($password, $user['password'])) {
            // 更新最后登录时间
            $update_sql = "UPDATE users SET last_login = NOW() WHERE id = ?";
            $this->db->query($update_sql, [$user['id']]);
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_username'] = $user['username'];
            $_SESSION['user_real_name'] = $user['real_name'];
            $_SESSION['user_logged_in'] = true;
            
            return true;
        }
        
        return false;
    }
    
    public function logout() {
        unset($_SESSION['user_id']);
        unset($_SESSION['user_username']);
        unset($_SESSION['user_real_name']);
        unset($_SESSION['user_logged_in']);
    }
    
    public function isLoggedIn() {
        return isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true;
    }
    
    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header('Location: /user/login.php');
            exit();
        }
    }
    
    public function getCurrentUser() {
        if ($this->isLoggedIn()) {
            return [
                'id' => $_SESSION['user_id'],
                'username' => $_SESSION['user_username'],
                'real_name' => $_SESSION['user_real_name']
            ];
        }
        return null;
    }
    
    public function changePassword($userId, $currentPassword, $newPassword) {
        $sql = "SELECT password FROM users WHERE id = ?";
        $user = $this->db->fetchOne($sql, [$userId]);
        
        if ($user && password_verify($currentPassword, $user['password'])) {
            $hashed_password = password_hash($newPassword, PASSWORD_DEFAULT);
            $update_sql = "UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?";
            $this->db->query($update_sql, [$hashed_password, $userId]);
            
            return true;
        }
        
        return false;
    }
    
    public function resetPassword($userId, $newPassword) {
        $hashed_password = password_hash($newPassword, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?";
        $this->db->query($sql, [$hashed_password, $userId]);
        
        return true;
    }
}
?>