<?php
// Запускаем сессию если она еще не запущена
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Подключаем CSRF-защиту
require_once __DIR__ . '/csrf.php';

// Инициализируем CSRF-токен для текущей сессии
generateCsrfToken();

// Подключение к SQLite базе данных
// Файл БД хранится в папке проекта — не нужен MySQL/OpenServer
$db_path = __DIR__ . '/database/museum.db';
$db_dir = __DIR__ . '/database';

// Создаём папку для БД если её нет
if (!is_dir($db_dir)) {
    mkdir($db_dir, 0755, true);
}

try {
    $db = new PDO("sqlite:" . $db_path);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Включаем поддержку внешних ключей (по умолчанию выключена в SQLite)
    $db->exec("PRAGMA foreign_keys = ON");
    // Улучшаем производительность
    $db->exec("PRAGMA journal_mode = WAL");
    
    // Если база пустая — автоматически инициализируем
    $tables = $db->query("SELECT count(*) as cnt FROM sqlite_master WHERE type='table' AND name='Registr'")->fetch();
    if ($tables['cnt'] == 0) {
        require_once __DIR__ . '/init_database.php';
    }
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}
?>
