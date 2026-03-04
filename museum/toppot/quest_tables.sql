-- ============================================
-- Таблицы системы квестов для интернет-музея
-- «Человек и время»
-- ============================================

-- 1. Таблица квестов
CREATE TABLE IF NOT EXISTS `quests` (
  `quest_id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text,
  `duration_minutes` int DEFAULT 30,
  `difficulty_level` enum('easy','medium','hard') DEFAULT 'medium',
  `is_active` tinyint(1) DEFAULT 1,
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`quest_id`),
  KEY `idx_is_active` (`is_active`),
  KEY `idx_created_by` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- 2. Таблица шагов квеста
CREATE TABLE IF NOT EXISTS `quest_steps` (
  `step_id` int NOT NULL AUTO_INCREMENT,
  `quest_id` int NOT NULL,
  `step_order` int NOT NULL DEFAULT 1,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `solution_hash` varchar(255) NOT NULL,
  `hint_text` text DEFAULT NULL,
  `step_score` int DEFAULT 100,
  `max_attempts` int DEFAULT 3,
  `media_path` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`step_id`),
  KEY `idx_quest_id` (`quest_id`),
  KEY `idx_step_order` (`quest_id`, `step_order`),
  CONSTRAINT `fk_steps_quest` FOREIGN KEY (`quest_id`) REFERENCES `quests` (`quest_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- 3. Таблица сессий участников
CREATE TABLE IF NOT EXISTS `player_sessions` (
  `player_session_id` int NOT NULL AUTO_INCREMENT,
  `player_id` int NOT NULL,
  `quest_id` int NOT NULL,
  `session_score` int DEFAULT 0,
  `start_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `end_time` datetime DEFAULT NULL,
  `current_step_id` int DEFAULT NULL,
  `status` enum('in_progress','completed','abandoned') DEFAULT 'in_progress',
  PRIMARY KEY (`player_session_id`),
  KEY `idx_player_id` (`player_id`),
  KEY `idx_quest_id` (`quest_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_session_player` FOREIGN KEY (`player_id`) REFERENCES `Registr` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_session_quest` FOREIGN KEY (`quest_id`) REFERENCES `quests` (`quest_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_session_step` FOREIGN KEY (`current_step_id`) REFERENCES `quest_steps` (`step_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- 4. Таблица событий сессии
CREATE TABLE IF NOT EXISTS `session_events` (
  `event_id` int NOT NULL AUTO_INCREMENT,
  `player_session_id` int NOT NULL,
  `event_type` enum('step_started','solution_attempt','step_completed','hint_used') NOT NULL,
  `related_step_id` int NOT NULL,
  `event_data` text DEFAULT NULL,
  `score_delta` int DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`event_id`),
  KEY `idx_session_id` (`player_session_id`),
  KEY `idx_step_id` (`related_step_id`),
  CONSTRAINT `fk_event_session` FOREIGN KEY (`player_session_id`) REFERENCES `player_sessions` (`player_session_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_event_step` FOREIGN KEY (`related_step_id`) REFERENCES `quest_steps` (`step_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- 5. Таблица статистики квестов
CREATE TABLE IF NOT EXISTS `quest_statistics` (
  `stat_id` int NOT NULL AUTO_INCREMENT,
  `quest_id` int NOT NULL,
  `total_attempts` int DEFAULT 0,
  `successful_completions` int DEFAULT 0,
  `average_completion_time` int DEFAULT 0,
  `completion_rate` decimal(5,2) DEFAULT 0.00,
  `most_failed_step_id` int DEFAULT NULL,
  `period_start` date DEFAULT NULL,
  `period_end` date DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`stat_id`),
  KEY `idx_quest_id` (`quest_id`),
  CONSTRAINT `fk_stat_quest` FOREIGN KEY (`quest_id`) REFERENCES `quests` (`quest_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- 6. Добавляем столбец total_score к таблице Registr (если нет)
ALTER TABLE `Registr` ADD COLUMN IF NOT EXISTS `total_score` int DEFAULT 0;

-- ============================================
-- Демонстрационный квест
-- ============================================
INSERT INTO `quests` (`title`, `description`, `duration_minutes`, `difficulty_level`, `is_active`) VALUES
('История колледжа', 'Проверьте свои знания об истории Ярославского колледжа управления и профессиональных технологий. Пройдите все этапы и узнайте интересные факты!', 15, 'easy', 1),
('Знаменитые выпускники', 'Квест о знаменитых выпускниках нашего колледжа. Узнайте об их достижениях и вкладе в историю.', 20, 'medium', 1);

INSERT INTO `quest_steps` (`quest_id`, `step_order`, `title`, `description`, `solution_hash`, `hint_text`, `step_score`, `max_attempts`) VALUES
(1, 1, 'Основание', 'В каком году были организованы фабрично-заводские курсы при фабрике «Красный перекоп», положившие начало нашему учебному заведению?', '', 'Это было в начале 1930-х годов', 100, 3),
(1, 2, 'Текстильный техникум', 'В каком году было открыто дневное отделение Ярославского Текстильного Техникума?', '', 'Через 3 года после основания курсов', 100, 3),
(1, 3, 'Обувной техникум', 'На основании какого документа в 1944 году был открыт обувной техникум в Ярославле? Назовите тип документа (одно слово).', '', 'Это документ высшего государственного органа', 150, 3),
(2, 1, 'Первая женщина-космонавт', 'Назовите фамилию первой женщины в мире, совершившей космический полёт, которая является выпускницей нашего колледжа.', '', 'Она совершила полёт 16 июня 1963 года', 100, 3),
(2, 2, 'Космический полёт', 'Как назывался космический корабль, на котором она совершила свой полёт?', '', 'Это шестой корабль в серии', 150, 3);
