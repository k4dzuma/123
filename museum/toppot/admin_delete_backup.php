<?php
session_start();
require 'db_connection.php';
require 'functions.php'; // Добавляем подключение файла с функцией log_action()

if (!isset($_SESSION['is_admin'])) {
    header("Location: login.php");
    exit();
}

// Безопасное получение имени файла
$filename = $_GET['file'] ?? '';
$backup_file = 'backups/' . basename($filename);

// Проверка существования файла и что он находится в нужной директории
if ($filename && file_exists($backup_file) && strpos($backup_file, 'backup_') !== false) {
    if (unlink($backup_file)) {
        // Логирование успешного удаления
        log_action($_SESSION['user_id'], 'Удаление резервной копии', basename($backup_file));
        $_SESSION['success_message'] = "Резервная копия удалена: " . basename($backup_file);
    } else {
        // Логирование ошибки удаления
        log_action($_SESSION['user_id'], 'Ошибка удаления резервной копии', basename($backup_file));
        $_SESSION['error_message'] = "Ошибка при удалении файла";
    }
} else {
    // Логирование попытки удаления несуществующего файла
    log_action($_SESSION['user_id'], 'Попытка удаления несуществующей копии', $filename);
    $_SESSION['error_message'] = "Файл не найден или неверный формат";
}

header("Location: admin_backup.php");
exit();
?>