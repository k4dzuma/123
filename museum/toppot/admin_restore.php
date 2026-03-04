<?php
session_start();
require 'db_connection.php';
require 'functions.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: login.php");
    exit();
}

$file_param = $_GET['file'] ?? '';
$backup_name = basename($file_param);

if ($backup_name === '' || !preg_match('/\.sql$/i', $backup_name)) {
    $_SESSION['error_message'] = "Некорректное имя файла резервной копии";
    header("Location: admin_backup.php");
    exit();
}

$backup_file = 'backups/' . $backup_name;

if (!file_exists($backup_file)) {
    $_SESSION['error_message'] = "Файл резервной копии не найден";
    header("Location: admin_backup.php");
    exit();
}

try {
    // Читаем содержимое бэкапа
    $sql = file_get_contents($backup_file);
    
    // Выполняем SQL запросы по очереди
    $db->exec($sql);
    
    // Логирование
    log_action($_SESSION['user_id'], 'Восстановление базы', basename($backup_file));
    
    $_SESSION['success_message'] = "База данных успешно восстановлена из резервной копии: " . basename($backup_file);
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Ошибка при восстановлении: " . $e->getMessage();
}

header("Location: admin_backup.php");
exit();
?>