<?php
session_start();
$_SESSION['admin_logged_in'] = true;
$_SESSION['admin_role'] = 'admin';

// Test with week period
$_GET['period'] = 'week';

include 'staff_get_report_data.php';
?>
