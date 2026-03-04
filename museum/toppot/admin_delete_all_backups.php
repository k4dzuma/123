<?php
session_start();
require 'db_connection.php';
require 'functions.php';

if (!isset($_SESSION['is_admin'])) {
    header("Location: login.php");
    exit();
}

// Удаление всех резервных копий
$backup_dir = 'backups/';
$files = glob($backup_dir . 'backup_*.sql');
$deleted_count = 0;

foreach ($files as $file) {
    if (is_file($file)) {
        unlink($file);
        $deleted_count++;
    }
}

// Логирование действия
log_action($_SESSION['user_id'], 'Удаление всех резервных копий', "Удалено копий: $deleted_count");

$_SESSION['success_message'] = "Удалено $deleted_count резервных копий";
header("Location: admin_backup.php");
exit();
?>