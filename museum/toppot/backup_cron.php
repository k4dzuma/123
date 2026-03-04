<?php
require 'db_connection.php';

// Получаем настройки авто-бекапа
$settings = [];
$stmt = $db->query("SELECT * FROM settings WHERE setting_key IN 
                   ('auto_backup_enabled', 'auto_backup_interval', 'auto_backup_time', 'auto_backup_max_files')");
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

// Проверяем, включены ли авто-бекапы
if (empty($settings['auto_backup_enabled']) {
    exit;
}

// Проверяем, нужно ли создавать бекап сейчас
$should_run = false;
$current_time = date('H:i');
$current_day = date('d');
$current_weekday = date('w'); // 0 (воскресенье) до 6 (суббота)

switch ($settings['auto_backup_interval']) {
    case 'daily':
        if ($current_time == $settings['auto_backup_time']) {
            $should_run = true;
        }
        break;
    case 'weekly':
        if ($current_time == $settings['auto_backup_time'] && $current_weekday == 0) { // Каждое воскресенье
            $should_run = true;
        }
        break;
    case 'monthly':
        if ($current_time == $settings['auto_backup_time'] && $current_day == 1) { // Первое число месяца
            $should_run = true;
        }
        break;
}

if (!$should_run) {
    exit;
}

// Создаем бекап
require 'admin_backup.php'; // Подключаем файл с функцией create_backup
$backup_file = create_backup($db);

if ($backup_file) {
    // Удаляем старые бекапы, если превышен лимит
    $backup_dir = 'backups/';
    $files = glob($backup_dir . 'backup_*.sql');
    
    if (count($files) > $settings['auto_backup_max_files']) {
        // Сортируем файлы по дате создания (старые сначала)
        usort($files, function($a, $b) {
            return filemtime($a) - filemtime($b);
        });
        
        // Удаляем лишние файлы
        $files_to_delete = count($files) - $settings['auto_backup_max_files'];
        for ($i = 0; $i < $files_to_delete; $i++) {
            unlink($files[$i]);
        }
    }
}
?>