<?php
session_start();
$_SESSION['admin_logged_in'] = true;
$_SESSION['admin_role'] = 'admin';

include 'staff_get_room_stats.php';
?>
