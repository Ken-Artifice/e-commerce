<?php
require_once 'config/paths.php';
session_start();
session_destroy();
header('Location: ' . url('login.php'));
exit();
?>

