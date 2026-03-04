<?php
session_start();
require 'db_connection.php';

if (!isset($_SESSION['is_admin'])) {
    header("Location: login.php");
    exit();
}

$db->query("TRUNCATE TABLE admin_logs");
$_SESSION['success_message'] = "Журнал событий очищен";

header("Location: admin_logs.php");
exit();
?>