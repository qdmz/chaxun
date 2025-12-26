<?php
// 文件编码: UTF-8
?>
<?php
// 文件编码: UTF-8
?>
<?php
// includes/auth.php
require_once __DIR__ . '/config.php';

class Auth {
    private $db;
    
    public function __construct() {
        global $db;
        $this->db = $db;
    }
    
    public function login($username, $password) {
        $sql = "SELECT * FROM admins WHERE username = ?";
        $admin = $this->db->fetchOne($sql, [$username]);
        
        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['logged_in'] = true;
            return true;
        }
        
        return false;
    }
    
    public function logout() {
        session_destroy();
        session_start();
    }
    
    public function isLoggedIn() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
    
    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header('Location: /excel-query-system/admin/login.php');
            exit();
        }
    }
    
    public function getCurrentAdmin() {
        if ($this->isLoggedIn()) {
            return [
                'id' => $_SESSION['admin_id'],
                'username' => $_SESSION['admin_username']
            ];
        }
        return null;
    }
}
?>