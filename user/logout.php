<?php
// user/logout.php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/user-auth.php';

$userAuth = new UserAuth();
$userAuth->logout();

header('Location: login.php');
exit();
?>