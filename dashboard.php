<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

if ($_SESSION['role'] == 'admin') {
    header("Location: admin/dashboard_admin.php");
} else {
    header("Location: user/dashboard_user.php");
}
exit;
?>
