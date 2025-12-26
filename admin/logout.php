<?php
// 文件编码: UTF-8
?>
<?php
// 文件编码: UTF-8
?>
<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

$auth = new Auth();
$auth->logout();

header('Location: login.php');
exit();
?>