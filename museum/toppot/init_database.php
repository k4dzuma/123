<?php
/**
 * Инициализация SQLite базы данных музея
 * Автоматически вызывается из db_connection.php при первом запуске
 */

// $db уже определён в db_connection.php
if (!isset($db)) {
    die("Этот файл не предназначен для прямого запуска");
}

$db->exec("BEGIN TRANSACTION");

try {
    // =============================================
    // ОСНОВНЫЕ ТАБЛИЦЫ МУЗЕЯ
    // =============================================

    // Таблица пользователей
    $db->exec("CREATE TABLE IF NOT EXISTS Registr (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        Login TEXT NOT NULL,
        password TEXT NOT NULL,
        Email TEXT NOT NULL,
        avatar TEXT DEFAULT 'default_avatar.png',
        date TEXT DEFAULT (datetime('now','localtime')),
        total_score INTEGER DEFAULT 0
    )");

    // Таблица комментариев
    $db->exec("CREATE TABLE IF NOT EXISTS Comments (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        comment TEXT NOT NULL,
        image_path TEXT DEFAULT NULL,
        parent_id INTEGER DEFAULT NULL,
        created_at TEXT DEFAULT (datetime('now','localtime')),
        FOREIGN KEY (user_id) REFERENCES Registr(id),
        FOREIGN KEY (parent_id) REFERENCES Comments(id) ON DELETE CASCADE
    )");

    // Таблица разделов
    $db->exec("CREATE TABLE IF NOT EXISTS Section (
        id_razdela INTEGER PRIMARY KEY AUTOINCREMENT,
        Nazvanie TEXT NOT NULL,
        Img TEXT NOT NULL,
        url TEXT NOT NULL
    )");

    // Экспонаты
    $db->exec("CREATE TABLE IF NOT EXISTS Eksponat (
        id_exponata INTEGER PRIMARY KEY AUTOINCREMENT,
        Nazvanie TEXT NOT NULL,
        Img TEXT NOT NULL
    )");

    // Знаменитости на главной
    $db->exec("CREATE TABLE IF NOT EXISTS index_znam (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        Nazvanie TEXT NOT NULL,
        Img TEXT NOT NULL
    )");

    // Разделы знаменитостей
    $db->exec("CREATE TABLE IF NOT EXISTS Razdel_znam (
        id_znam INTEGER PRIMARY KEY AUTOINCREMENT,
        Nazvanie TEXT NOT NULL,
        Img TEXT NOT NULL
    )");

    // Раздел мероприятий
    $db->exec("CREATE TABLE IF NOT EXISTS Razdel_meropriat (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        Nazvanie TEXT NOT NULL,
        Img TEXT NOT NULL
    )");

    // Мероприятия
    $db->exec("CREATE TABLE IF NOT EXISTS meropriyatiya (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        Nazvanie TEXT NOT NULL,
        Img TEXT NOT NULL,
        Text TEXT NOT NULL
    )");

    // Выставки
    $db->exec("CREATE TABLE IF NOT EXISTS vystavki (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        Nazvanie TEXT NOT NULL,
        Img TEXT NOT NULL,
        Text TEXT NOT NULL
    )");

    // =============================================
    // КОНТЕНТ-ТАБЛИЦЫ (подразделы музея)
    // =============================================

    $content_tables = [
        'col_istorya', 'col_leg_promish', 'col_obyvn', 'col_py14', 'col_text',
        'sport_izvest', 'sport_str_istor', 'sport_yspex',
        'teatr_kollekz', 'teatr_nashi_vipusk',
        'studsovet_dostijenya', 'studsovet_lideri',
        'voina_istor', 'voina_sotrudniki', 'voina_SVO',
        'stroit_akt', 'stroit_foto', 'stroit_liter',
        'znamenitosti'
    ];

    foreach ($content_tables as $table) {
        $db->exec("CREATE TABLE IF NOT EXISTS $table (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            Nazvanie TEXT NOT NULL,
            Img TEXT NOT NULL,
            Text TEXT NOT NULL
        )");
    }

    // =============================================
    // АДМИНИСТРАТИВНЫЕ ТАБЛИЦЫ
    // =============================================

    // Логи администратора
    $db->exec("CREATE TABLE IF NOT EXISTS admin_logs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER DEFAULT NULL,
        action TEXT NOT NULL,
        details TEXT,
        ip_address TEXT DEFAULT NULL,
        created_at TEXT DEFAULT (datetime('now','localtime'))
    )");

    // Безопасность администратора (PIN)
    $db->exec("CREATE TABLE IF NOT EXISTS admin_security (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL UNIQUE,
        pin TEXT NOT NULL,
        attempts INTEGER NOT NULL DEFAULT 0,
        last_attempt TEXT DEFAULT NULL
    )");

    // Настройки системы
    $db->exec("CREATE TABLE IF NOT EXISTS settings (
        setting_key TEXT PRIMARY KEY,
        setting_value TEXT
    )");

    // Предупреждения пользователей
    $db->exec("CREATE TABLE IF NOT EXISTS user_warnings (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        admin_id INTEGER NOT NULL,
        reason TEXT NOT NULL,
        created_at TEXT DEFAULT (datetime('now','localtime')),
        expires_at TEXT NOT NULL
    )");

    // =============================================
    // ТАБЛИЦЫ СИСТЕМЫ КВЕСТОВ
    // =============================================

    // Квесты
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

    // Шаги квеста
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

    // Сессии игроков
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

    // События сессии
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

    // Статистика квестов
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

    // =============================================
    // НАЧАЛЬНЫЕ ДАННЫЕ
    // =============================================

    // Администратор (логин: admin, пароль: admin123)
    $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
    $db->exec("INSERT INTO Registr (id, Login, password, Email, avatar) VALUES 
        (1, 'admin', '$admin_password', 'admin@gmail.com', 'default_avatar.png')");

    // PIN администратора (1234)
    $admin_pin = password_hash('1234', PASSWORD_DEFAULT);
    $db->exec("INSERT INTO admin_security (user_id, pin) VALUES (1, '$admin_pin')");

    // Разделы музея
    $db->exec("INSERT INTO Section (Nazvanie, Img, url) VALUES 
        ('Исторические этапы развития учебного заведения', 'Разделы/01.png', 'coll_tema.php'),
        ('Спортивная жизнь колледжа', 'Разделы/02.png', 'Sport_tema.php'),
        ('История Театра моды', 'Разделы/03.png', 'Teatr_tema.php'),
        ('История студенческого совета', 'Разделы/04.png', 'Stud_tema.php'),
        ('Страницы архива, опаленные войной', 'Разделы/05.png', 'Voina_tema.php'),
        ('Мероприятия и выставки музея', 'Разделы/06.png', 'meropriyatiya.php'),
        ('Строительство новых корпусов на Тутаевском шоссе', 'Разделы/07.png', 'Korpus_tema.php'),
        ('Знаменитые выпускники', 'Разделы/08.png', 'Znam.php')");

    // Экспонаты
    $db->exec("INSERT INTO Eksponat (Nazvanie, Img) VALUES 
        ('Печатная машинка', 'Экспонаты/00_00_01.png'),
        ('Ручной трудъ', 'Экспонаты/00_00_02.png'),
        ('Производственный альбом', 'Экспонаты/00_00_03.png'),
        ('Паспорт техникума', 'Экспонаты/00_00_04.png'),
        ('Чернила', 'Экспонаты/00_00_05.png'),
        ('Книга Михайлов', 'Экспонаты/00_00_06.png'),
        ('Подстаканники', 'Экспонаты/00_00_07.png'),
        ('Плакат', 'Экспонаты/00_00_08.png')");

    // Знаменитости на главной
    $db->exec("INSERT INTO index_znam (Nazvanie, Img) VALUES 
        ('Валентина Владимировна Терешкова', 'Фоны/tereshkova.png'),
        ('Алексей Власов', 'Фоны/vlasov.png')");

    // Раздел знаменитостей
    $db->exec("INSERT INTO Razdel_znam (Nazvanie, Img) VALUES 
        ('Валентина Терешкова', 'Содержимое_подразделов/19_Знаменитые выпускники/08_19_01.png')");

    // Настройки
    $db->exec("INSERT INTO settings (setting_key, setting_value) VALUES 
        ('auto_backup_enabled', '0'),
        ('auto_backup_interval', 'daily'),
        ('auto_backup_max_files', '5'),
        ('auto_backup_time', '20:00'),
        ('backup_retention', '7'),
        ('date_format', 'd.m.Y'),
        ('debug_mode', '0'),
        ('log_level', 'error'),
        ('login_attempts', '5'),
        ('maintenance_mode', '0'),
        ('records_per_page', '10'),
        ('session_lifetime', '120'),
        ('show_breadcrumbs', '0'),
        ('site_email', ''),
        ('site_name', ''),
        ('timezone', 'Europe/Moscow')");

    // =============================================
    // ДЕМОНСТРАЦИОННЫЕ КВЕСТЫ
    // =============================================

    $db->exec("INSERT INTO quests (title, description, duration_minutes, difficulty_level, is_active) VALUES 
        ('История колледжа', 'Проверьте свои знания об истории Ярославского колледжа управления и профессиональных технологий. Пройдите все этапы и узнайте интересные факты!', 15, 'easy', 1),
        ('Знаменитые выпускники', 'Квест о знаменитых выпускниках нашего колледжа. Узнайте об их достижениях и вкладе в историю.', 20, 'medium', 1)");

    // Шаги квестов (solution_hash будет заполнен ниже)
    $db->exec("INSERT INTO quest_steps (quest_id, step_order, title, description, solution_hash, hint_text, step_score, max_attempts) VALUES 
        (1, 1, 'Основание', 'В каком году были организованы фабрично-заводские курсы при фабрике «Красный перекоп», положившие начало нашему учебному заведению?', '', 'Это было в начале 1930-х годов', 100, 3),
        (1, 2, 'Текстильный техникум', 'В каком году было открыто дневное отделение Ярославского Текстильного Техникума?', '', 'Через 3 года после основания курсов', 100, 3),
        (1, 3, 'Обувной техникум', 'На основании какого документа в 1944 году был открыт обувной техникум в Ярославле? Назовите тип документа (одно слово).', '', 'Это документ высшего государственного органа', 150, 3),
        (2, 1, 'Первая женщина-космонавт', 'Назовите фамилию первой женщины в мире, совершившей космический полёт, которая является выпускницей нашего колледжа.', '', 'Она совершила полёт 16 июня 1963 года', 100, 3),
        (2, 2, 'Космический полёт', 'Как назывался космический корабль, на котором она совершила свой полёт?', '', 'Это шестой корабль в серии', 150, 3)");

    // Хешируем ответы для шагов квестов
    $answers = [
        1 => '1930',
        2 => '1933',
        3 => 'постановление',
        4 => 'терешкова',
        5 => 'восток-6',
    ];
    $stmt = $db->prepare("UPDATE quest_steps SET solution_hash = ? WHERE step_id = ?");
    foreach ($answers as $stepId => $answer) {
        $hash = password_hash(mb_strtolower(trim($answer)), PASSWORD_DEFAULT);
        $stmt->execute([$hash, $stepId]);
    }

    // Контент подразделов (примеры)
    $db->exec("INSERT INTO col_text (Nazvanie, Img, Text) VALUES 
        ('История текстильного техникума', 'Содержимое_подразделов/01_Первые_страницы_истории_текстильного_техникума/01_01_01.png', 'В 1930 году при фабрике «Красный перекоп» были организованы фабрично-заводские курсы.')");

    $db->exec("INSERT INTO col_obyvn (Nazvanie, Img, Text) VALUES 
        ('История обувного техникума', 'Содержимое_подразделов/02_История_обувного_техникума/01_02_02.png', 'На основании постановления Совета Народных Комиссаров РСФСР от 17 января 1944 года в городе Ярославле был открыт обувной техникум.')");

    $db->exec("COMMIT");

    // Вывод сообщения только если скрипт вызван напрямую (не из db_connection)
    if (php_sapi_name() === 'cli' || (isset($_SERVER['SCRIPT_FILENAME']) && basename($_SERVER['SCRIPT_FILENAME']) === 'init_database.php')) {
        echo "<h2 style='color:green'>База данных успешно создана!</h2>";
        echo "<p>Администратор: <b>admin</b> / <b>admin123</b> (PIN: 1234)</p>";
        echo "<p><a href='index.php'>Перейти на сайт</a></p>";
    }

} catch (Exception $e) {
    $db->exec("ROLLBACK");
    die("Ошибка инициализации базы данных: " . $e->getMessage());
}
?>
