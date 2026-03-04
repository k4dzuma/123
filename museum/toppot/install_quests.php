<?php
/**
 * Установка/переустановка таблиц квестов
 * Используется для повторной инициализации квестов в существующей БД
 */
session_start();
require 'db_connection.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    die('Доступ запрещён. Войдите как администратор.');
}

try {
    // Проверяем, существует ли уже таблица quests
    $check = $db->query("SELECT count(*) as cnt FROM sqlite_master WHERE type='table' AND name='quests'")->fetch();
    
    if ($check['cnt'] > 0) {
        // Таблицы уже есть — проверяем наличие данных
        $quest_count = $db->query("SELECT COUNT(*) FROM quests")->fetchColumn();
        if ($quest_count > 0) {
            echo "<h2>Таблицы квестов уже существуют!</h2>";
            echo "<p>Найдено квестов: <b>$quest_count</b></p>";
            echo "<p><a href='admin_quests.php'>Перейти к управлению квестами</a> | <a href='quests.php'>Каталог квестов</a></p>";
            exit;
        }
    }

    // Создаём таблицы (если их нет)
    $db->exec("CREATE TABLE IF NOT EXISTS quests (
        quest_id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL,
        description TEXT,
        duration_minutes INTEGER DEFAULT 30,
        difficulty_level TEXT DEFAULT 'medium' CHECK(difficulty_level IN ('easy','medium','hard')),
        is_active INTEGER DEFAULT 1,
        created_by INTEGER DEFAULT NULL,
        created_at TEXT DEFAULT (datetime('now','localtime')),
        last_modified TEXT DEFAULT (datetime('now','localtime'))
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS quest_steps (
        step_id INTEGER PRIMARY KEY AUTOINCREMENT,
        quest_id INTEGER NOT NULL,
        step_order INTEGER NOT NULL DEFAULT 1,
        title TEXT NOT NULL,
        description TEXT NOT NULL,
        solution_hash TEXT NOT NULL,
        hint_text TEXT DEFAULT NULL,
        step_score INTEGER DEFAULT 100,
        max_attempts INTEGER DEFAULT 3,
        media_path TEXT DEFAULT NULL,
        FOREIGN KEY (quest_id) REFERENCES quests(quest_id) ON DELETE CASCADE
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS player_sessions (
        player_session_id INTEGER PRIMARY KEY AUTOINCREMENT,
        player_id INTEGER NOT NULL,
        quest_id INTEGER NOT NULL,
        session_score INTEGER DEFAULT 0,
        start_time TEXT DEFAULT (datetime('now','localtime')),
        end_time TEXT DEFAULT NULL,
        current_step_id INTEGER DEFAULT NULL,
        status TEXT DEFAULT 'in_progress' CHECK(status IN ('in_progress','completed','abandoned')),
        FOREIGN KEY (player_id) REFERENCES Registr(id) ON DELETE CASCADE,
        FOREIGN KEY (quest_id) REFERENCES quests(quest_id) ON DELETE CASCADE,
        FOREIGN KEY (current_step_id) REFERENCES quest_steps(step_id) ON DELETE SET NULL
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS session_events (
        event_id INTEGER PRIMARY KEY AUTOINCREMENT,
        player_session_id INTEGER NOT NULL,
        event_type TEXT NOT NULL CHECK(event_type IN ('step_started','solution_attempt','step_completed','hint_used')),
        related_step_id INTEGER NOT NULL,
        event_data TEXT DEFAULT NULL,
        score_delta INTEGER DEFAULT 0,
        created_at TEXT DEFAULT (datetime('now','localtime')),
        FOREIGN KEY (player_session_id) REFERENCES player_sessions(player_session_id) ON DELETE CASCADE,
        FOREIGN KEY (related_step_id) REFERENCES quest_steps(step_id) ON DELETE CASCADE
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS quest_statistics (
        stat_id INTEGER PRIMARY KEY AUTOINCREMENT,
        quest_id INTEGER NOT NULL,
        total_attempts INTEGER DEFAULT 0,
        successful_completions INTEGER DEFAULT 0,
        average_completion_time INTEGER DEFAULT 0,
        completion_rate REAL DEFAULT 0.00,
        most_failed_step_id INTEGER DEFAULT NULL,
        period_start TEXT DEFAULT NULL,
        period_end TEXT DEFAULT NULL,
        updated_at TEXT DEFAULT (datetime('now','localtime')),
        FOREIGN KEY (quest_id) REFERENCES quests(quest_id) ON DELETE CASCADE
    )");

    // Демо-квесты
    $db->exec("INSERT INTO quests (title, description, duration_minutes, difficulty_level, is_active) VALUES 
        ('История колледжа', 'Проверьте свои знания об истории Ярославского колледжа управления и профессиональных технологий.', 15, 'easy', 1),
        ('Знаменитые выпускники', 'Квест о знаменитых выпускниках нашего колледжа.', 20, 'medium', 1)");

    $db->exec("INSERT INTO quest_steps (quest_id, step_order, title, description, solution_hash, hint_text, step_score, max_attempts) VALUES 
        (1, 1, 'Основание', 'В каком году были организованы фабрично-заводские курсы при фабрике «Красный перекоп»?', '', 'Это было в начале 1930-х годов', 100, 3),
        (1, 2, 'Текстильный техникум', 'В каком году было открыто дневное отделение Ярославского Текстильного Техникума?', '', 'Через 3 года после основания курсов', 100, 3),
        (1, 3, 'Обувной техникум', 'На основании какого документа в 1944 году был открыт обувной техникум? Назовите тип документа (одно слово).', '', 'Это документ высшего государственного органа', 150, 3),
        (2, 1, 'Первая женщина-космонавт', 'Назовите фамилию первой женщины-космонавта, выпускницы колледжа.', '', 'Она совершила полёт 16 июня 1963 года', 100, 3),
        (2, 2, 'Космический полёт', 'Как назывался космический корабль?', '', 'Это шестой корабль в серии', 150, 3)");

    // Хешируем ответы
    $answers = [1 => '1930', 2 => '1933', 3 => 'постановление', 4 => 'терешкова', 5 => 'восток-6'];
    $stmt = $db->prepare("UPDATE quest_steps SET solution_hash = ? WHERE step_id = ?");
    foreach ($answers as $stepId => $answer) {
        $hash = password_hash(mb_strtolower(trim($answer)), PASSWORD_DEFAULT);
        $stmt->execute([$hash, $stepId]);
    }

    echo "<h2 style='color:green'>Таблицы квестов успешно созданы!</h2>";
    echo "<p>Демонстрационные квесты добавлены.</p>";
    echo "<p><a href='admin_quests.php'>Управление квестами</a> | <a href='quests.php'>Каталог квестов</a></p>";

} catch (Exception $e) {
    echo "<h2 style='color:red'>Ошибка:</h2><p>" . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
