--
-- Структура таблицы `admin_logs`
--
DROP TABLE IF EXISTS `admin_logs`;
CREATE TABLE `admin_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `details` text,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `admin_logs`
--
INSERT INTO `admin_logs` VALUES ('1', '8', 'Создание резервной копии', 'backup_2025-04-13_224633.sql', '127.0.0.1', '2025-04-13 22:46:33');
INSERT INTO `admin_logs` VALUES ('2', '8', 'Создание резервной копии', 'backup_2025-04-13_224926.sql', '127.0.0.1', '2025-04-13 22:49:26');
INSERT INTO `admin_logs` VALUES ('3', '8', 'Создание резервной копии', 'backup_2025-04-13_224928.sql', '127.0.0.1', '2025-04-13 22:49:28');
INSERT INTO `admin_logs` VALUES ('4', '8', 'Создание резервной копии', 'backup_2025-04-13_224930.sql', '127.0.0.1', '2025-04-13 22:49:30');
INSERT INTO `admin_logs` VALUES ('5', '8', 'Создание резервной копии', 'backup_2025-04-13_225030.sql', '127.0.0.1', '2025-04-13 22:50:30');
INSERT INTO `admin_logs` VALUES ('6', '8', 'Удаление всех резервных копий', 'Удалено копий: 2', '127.0.0.1', '2025-04-13 22:50:53');
INSERT INTO `admin_logs` VALUES ('7', '8', 'Создание резервной копии', 'backup_2025-04-13_225054.sql', '127.0.0.1', '2025-04-13 22:50:54');

--
-- Структура таблицы `admin_security`
--
DROP TABLE IF EXISTS `admin_security`;
CREATE TABLE `admin_security` (
  `user_id` int NOT NULL,
  `pin_hash` varchar(255) NOT NULL,
  `attempts` tinyint DEFAULT '0',
  `last_attempt` datetime DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  CONSTRAINT `admin_security_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Registr` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `admin_security`
--
INSERT INTO `admin_security` VALUES ('8', '$2y$10$gmBkBQZwj39Ov4c1xgJ.geBpby8hbobfA6UA3UdGaEufxSROtARQG', '0', '2025-04-13 21:32:24');

--
-- Структура таблицы `col_istorya`
--
DROP TABLE IF EXISTS `col_istorya`;
CREATE TABLE `col_istorya` (
  `id` int NOT NULL AUTO_INCREMENT,
  `Nazvanie` text NOT NULL,
  `Img` text NOT NULL,
  `Text` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `col_istorya`
--
INSERT INTO `col_istorya` VALUES ('1', 'История колледжа', 'Содержимое_подразделов\\01_Первые_страницы_истории_текстильного_техникума\\01_05.png', 'Текст');

--
-- Структура таблицы `col_leg_promish`
--
DROP TABLE IF EXISTS `col_leg_promish`;
CREATE TABLE `col_leg_promish` (
  `id` int NOT NULL AUTO_INCREMENT,
  `Nazvanie` text NOT NULL,
  `Img` text NOT NULL,
  `Text` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `col_leg_promish`
--
INSERT INTO `col_leg_promish` VALUES ('1', 'История техникума легкой промышленности', 'Содержимое_подразделов\\01_Первые_страницы_истории_текстильного_техникума\\01_03.jpg', 'gwesecxercr');

--
-- Структура таблицы `col_obyvn`
--
DROP TABLE IF EXISTS `col_obyvn`;
CREATE TABLE `col_obyvn` (
  `id` int NOT NULL AUTO_INCREMENT,
  `Nazvanie` text NOT NULL,
  `Img` text NOT NULL,
  `Text` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `col_obyvn`
--
INSERT INTO `col_obyvn` VALUES ('1', 'История обувного техникума', 'Содержимое_подразделов\\01_Первые_страницы_истории_текстильного_техникума\\01_02.png', 'На основании постановления Совета Народных Комиссаров РСФСР от 17 января 1944 года No 54 и приказа Народного Комиссариата легкой промышленности РСФСР от 6 апреля 1944 года No 134 в городе Ярославле был открыт обувной техникум с целью подготовки техников-технологов и техников-механиков обувного производства. Директором техникума был назначен Щубин Сергей Данилович, которому было поручено произвести набор учащихся и начать занятия с 1-го сентября 1944 года.');
INSERT INTO `col_obyvn` VALUES ('2', '', 'Содержимое_подразделов\\02_История_обувного_техникума\\01_02_02.png', '');
INSERT INTO `col_obyvn` VALUES ('3', '', 'Содержимое_подразделов\\02_История_обувного_техникума\\01_02_03.png', '');

--
-- Структура таблицы `col_py14`
--
DROP TABLE IF EXISTS `col_py14`;
CREATE TABLE `col_py14` (
  `id` int NOT NULL AUTO_INCREMENT,
  `Nazvanie` text NOT NULL,
  `Img` text NOT NULL,
  `Text` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `col_py14`
--
INSERT INTO `col_py14` VALUES ('1', 'История ПУ №14', 'Содержимое_подразделов\\01_Первые_страницы_истории_текстильного_техникума\\01_04.png', 'gdfsgdfgdgdfgdf');
INSERT INTO `col_py14` VALUES ('2', 'tet', 'kyt4klyl', 'tykety');

--
-- Структура таблицы `col_text`
--
DROP TABLE IF EXISTS `col_text`;
CREATE TABLE `col_text` (
  `id` int NOT NULL AUTO_INCREMENT,
  `Nazvanie` text NOT NULL,
  `Img` text NOT NULL,
  `Text` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `col_text`
--
INSERT INTO `col_text` VALUES ('1', 'История обувного техникумакаиравыр', 'uploads/уф.png', 'На основании постановления Совета Народных Комиссаров РСФСР от 17 января 1944 года No 54 и приказа Народного Комиссариата легкой промышленности РСФСР от 6 апреля 1944 года No 134 в городе Ярославле был открыт обувной техникум с целью подготовки техников-технологов и техников-механиков обувного производства. Директором техникума был назначен Щубин Сергей Данилович, которому было поручено произвести набор учащихся и начать занятия с 1-го сентября 1944 года.');
INSERT INTO `col_text` VALUES ('2', 'Обувь', 'Содержимое_подразделов\\01_Первые_страницы_истории_текстильного_техникума\\01_01_01.png', 'Очень качественная обувь');

--
-- Структура таблицы `Comments`
--
DROP TABLE IF EXISTS `Comments`;
CREATE TABLE `Comments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `comment` text NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `parent_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `parent_id` (`parent_id`),
  CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Registr` (`id`),
  CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `Comments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `Comments`
--
INSERT INTO `Comments` VALUES ('35', '9', 'kkk', 'uploads/comments/67f8ee7f5cdb6.png', '', '2025-04-11 13:27:11');
INSERT INTO `Comments` VALUES ('36', '9', '123', '', '35', '2025-04-11 13:27:14');
INSERT INTO `Comments` VALUES ('37', '9', '321', '', '', '2025-04-11 13:27:23');
INSERT INTO `Comments` VALUES ('38', '8', 'khj', '', '', '2025-04-11 13:45:12');
INSERT INTO `Comments` VALUES ('39', '9', 'dawds', '', '', '2025-04-13 13:59:19');
INSERT INTO `Comments` VALUES ('40', '8', 'ао', '', '', '2025-04-13 16:26:27');

--
-- Структура таблицы `Eksponat`
--
DROP TABLE IF EXISTS `Eksponat`;
CREATE TABLE `Eksponat` (
  `id_exponata` int NOT NULL AUTO_INCREMENT,
  `Nazvanie` text NOT NULL,
  `Img` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  PRIMARY KEY (`id_exponata`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `Eksponat`
--
INSERT INTO `Eksponat` VALUES ('1', 'Печатная машинка', 'Экспонаты\\00_00_01.png');
INSERT INTO `Eksponat` VALUES ('2', 'Ручной трудъ', 'Экспонаты\\00_00_02.png');
INSERT INTO `Eksponat` VALUES ('3', 'Производственный альбом', 'Экспонаты\\00_00_03.png');
INSERT INTO `Eksponat` VALUES ('4', 'Паспорт техникума', 'Экспонаты\\00_00_04.png');
INSERT INTO `Eksponat` VALUES ('5', 'Чернила', 'Экспонаты\\00_00_05.png');
INSERT INTO `Eksponat` VALUES ('6', 'Книга Михайлов', 'Экспонаты\\00_00_06.png');
INSERT INTO `Eksponat` VALUES ('7', 'Подстаканники', 'Экспонаты\\00_00_07.png');
INSERT INTO `Eksponat` VALUES ('8', 'Плакат', 'Экспонаты\\00_00_08.png');
INSERT INTO `Eksponat` VALUES ('10', 'Смешные истории', 'uploads/67facc78439f5_67faa32889b50_67f8edecba98f.png');

--
-- Структура таблицы `index_znam`
--
DROP TABLE IF EXISTS `index_znam`;
CREATE TABLE `index_znam` (
  `id` int NOT NULL AUTO_INCREMENT,
  `Nazvanie` text NOT NULL,
  `Img` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `index_znam`
--
INSERT INTO `index_znam` VALUES ('1', 'Валентина Владимировна Терешкова ', 'Фоны\\tereshkova.png');
INSERT INTO `index_znam` VALUES ('2', 'Алексей Власов', 'Фоны\\vlasov.png');

--
-- Структура таблицы `meropriyatiya`
--
DROP TABLE IF EXISTS `meropriyatiya`;
CREATE TABLE `meropriyatiya` (
  `id` int NOT NULL AUTO_INCREMENT,
  `Nazvanie` text NOT NULL,
  `Img` text NOT NULL,
  `Text` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Структура таблицы `Razdel_meropriat`
--
DROP TABLE IF EXISTS `Razdel_meropriat`;
CREATE TABLE `Razdel_meropriat` (
  `id` int NOT NULL AUTO_INCREMENT,
  `Nazvanie` text NOT NULL,
  `Img` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Структура таблицы `Razdel_znam`
--
DROP TABLE IF EXISTS `Razdel_znam`;
CREATE TABLE `Razdel_znam` (
  `id_znam` int NOT NULL AUTO_INCREMENT,
  `Nazvanie` text NOT NULL,
  `Img` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  PRIMARY KEY (`id_znam`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `Razdel_znam`
--
INSERT INTO `Razdel_znam` VALUES ('1', 'Валентина Терешкова', 'Содержимое_подразделов\\19_Знаменитые выпускники\\08_19_01.png');

--
-- Структура таблицы `Registr`
--
DROP TABLE IF EXISTS `Registr`;
CREATE TABLE `Registr` (
  `id` int NOT NULL AUTO_INCREMENT,
  `Login` text NOT NULL,
  `password` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `Email` text NOT NULL,
  `avatar` varchar(255) DEFAULT 'default_avatar.png',
  `date` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `Registr`
--
INSERT INTO `Registr` VALUES ('8', 'admin', '$2y$10$A6WLaaRvh9e5smN4GPSP2.TFIm9ZpOvA8d6tGLhKw0qUPDzNw8Bjq', 'admin@gmail.com', 'uploads/avatars/avatar_8_1744554711.png', '2025-04-13 21:23:02');
INSERT INTO `Registr` VALUES ('9', 'kamisik', '$2y$10$HIBU5n7RufV/mkJHNzYcIekoUSvjCTQC3wdw/sEh.LS330MBnYs.y', 'kamisik@gmail.com', 'uploads/avatars/avatar_9_1744541950.png', '2025-04-13 21:23:02');
INSERT INTO `Registr` VALUES ('10', 'user1', '$2y$10$elgxf9uCEAE45PJ7NtkQPupKJOOCoQfSrqrsgmZKT1dJg/4fCWgPy', 'user1@gmail.com', 'default_avatar.png', '2025-04-13 21:41:34');
INSERT INTO `Registr` VALUES ('11', 'nolik', '$2y$10$sjjAPADIi1QdzFn.57hFQOHtLqg.4Q78icUIe5ByGELmA5CMtOc16', 'noliik@gmail.com', 'uploads/avatars/avatar_11_1744571320.jpg', '2025-04-13 22:05:08');

--
-- Структура таблицы `Section`
--
DROP TABLE IF EXISTS `Section`;
CREATE TABLE `Section` (
  `id_razdela` int NOT NULL AUTO_INCREMENT,
  `Nazvanie` text NOT NULL,
  `Img` text NOT NULL,
  `url` text NOT NULL,
  PRIMARY KEY (`id_razdela`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `Section`
--
INSERT INTO `Section` VALUES ('1', 'Исторические этапы развития учебного заведения', 'Разделы\\01.png', 'coll_tema.php');
INSERT INTO `Section` VALUES ('2', 'Спортивная жизнь колледжа', 'Разделы\\02.png', 'Sport_tema.php');
INSERT INTO `Section` VALUES ('3', 'История Театра моды', 'Разделы\\03.png', 'Teatr_tema.php');
INSERT INTO `Section` VALUES ('4', 'История студенческого совета', 'Разделы\\04.png', 'Stud_tema.php');
INSERT INTO `Section` VALUES ('5', 'Страницы архива, опаленные войной', 'Разделы\\05.png', 'Voina_tema.php');
INSERT INTO `Section` VALUES ('6', 'Мероприятия и выставки музея', 'Разделы\\06.png', 'meropriyatiya.php');
INSERT INTO `Section` VALUES ('7', 'Строительство новых корпусов на Тутаевском шоссе', 'Разделы\\07.png', 'Korpus_tema.php');
INSERT INTO `Section` VALUES ('8', 'Знаменитые выпускники', 'Разделы\\08.png', 'Znam.php');

--
-- Структура таблицы `settings`
--
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `settings`
--
INSERT INTO `settings` VALUES ('auto_backup_enabled', '1');
INSERT INTO `settings` VALUES ('auto_backup_interval', 'daily');
INSERT INTO `settings` VALUES ('auto_backup_max_files', '5');
INSERT INTO `settings` VALUES ('auto_backup_time', '22:25');

--
-- Структура таблицы `sport_izvest`
--
DROP TABLE IF EXISTS `sport_izvest`;
CREATE TABLE `sport_izvest` (
  `id` int NOT NULL AUTO_INCREMENT,
  `Nazvanie` text NOT NULL,
  `Img` text NOT NULL,
  `Text` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `sport_izvest`
--
INSERT INTO `sport_izvest` VALUES ('1', 'tyt', 'Содержимое_подразделов\\01_Первые_страницы_истории_текстильного_техникума\\01_02.png', 'ef');
INSERT INTO `sport_izvest` VALUES ('2', '', 'Содержимое_подразделов\\01_Первые_страницы_истории_текстильного_техникума\\01_02.png', '');

--
-- Структура таблицы `sport_str_istor`
--
DROP TABLE IF EXISTS `sport_str_istor`;
CREATE TABLE `sport_str_istor` (
  `id` int NOT NULL AUTO_INCREMENT,
  `Nazvanie` text NOT NULL,
  `Img` text NOT NULL,
  `Text` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `sport_str_istor`
--
INSERT INTO `sport_str_istor` VALUES ('1', 'gsg', 'Содержимое_подразделов\\01_Первые_страницы_истории_текстильного_техникума\\01_02.png', 'ddg');

--
-- Структура таблицы `sport_yspex`
--
DROP TABLE IF EXISTS `sport_yspex`;
CREATE TABLE `sport_yspex` (
  `id` int NOT NULL AUTO_INCREMENT,
  `Nazvanie` text NOT NULL,
  `Img` text NOT NULL,
  `Text` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Структура таблицы `stroit_akt`
--
DROP TABLE IF EXISTS `stroit_akt`;
CREATE TABLE `stroit_akt` (
  `id` int NOT NULL AUTO_INCREMENT,
  `Nazvanie` text NOT NULL,
  `Img` text NOT NULL,
  `Text` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Структура таблицы `stroit_foto`
--
DROP TABLE IF EXISTS `stroit_foto`;
CREATE TABLE `stroit_foto` (
  `id` int NOT NULL AUTO_INCREMENT,
  `Nazvanie` text NOT NULL,
  `Img` text NOT NULL,
  `Text` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Структура таблицы `stroit_liter`
--
DROP TABLE IF EXISTS `stroit_liter`;
CREATE TABLE `stroit_liter` (
  `id` int NOT NULL AUTO_INCREMENT,
  `Nazvanie` text NOT NULL,
  `Img` text NOT NULL,
  `Text` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Структура таблицы `studsovet_dostijenya`
--
DROP TABLE IF EXISTS `studsovet_dostijenya`;
CREATE TABLE `studsovet_dostijenya` (
  `id` int NOT NULL AUTO_INCREMENT,
  `Nazvanie` text NOT NULL,
  `Img` text NOT NULL,
  `Text` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `studsovet_dostijenya`
--
INSERT INTO `studsovet_dostijenya` VALUES ('1', 'аф', 'Содержимое_подразделов\\01_Первые_страницы_истории_текстильного_техникума\\01_02.png', 'аы');

--
-- Структура таблицы `studsovet_lideri`
--
DROP TABLE IF EXISTS `studsovet_lideri`;
CREATE TABLE `studsovet_lideri` (
  `id` int NOT NULL AUTO_INCREMENT,
  `Nazvanie` text NOT NULL,
  `Img` text NOT NULL,
  `Text` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `studsovet_lideri`
--
INSERT INTO `studsovet_lideri` VALUES ('1', 'аавы', 'Содержимое_подразделов\\01_Первые_страницы_истории_текстильного_техникума\\01_02.png', 'авыа');

--
-- Структура таблицы `teatr_kollekz`
--
DROP TABLE IF EXISTS `teatr_kollekz`;
CREATE TABLE `teatr_kollekz` (
  `id` int NOT NULL AUTO_INCREMENT,
  `Nazvanie` text NOT NULL,
  `Img` text NOT NULL,
  `Text` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `teatr_kollekz`
--
INSERT INTO `teatr_kollekz` VALUES ('1', 'аааы', 'Содержимое_подразделов\\01_Первые_страницы_истории_текстильного_техникума\\01_02.png', 'аыв');

--
-- Структура таблицы `teatr_nashi_vipusk`
--
DROP TABLE IF EXISTS `teatr_nashi_vipusk`;
CREATE TABLE `teatr_nashi_vipusk` (
  `id` int NOT NULL AUTO_INCREMENT,
  `Nazvanie` text NOT NULL,
  `Img` text NOT NULL,
  `Text` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Структура таблицы `voina_istor`
--
DROP TABLE IF EXISTS `voina_istor`;
CREATE TABLE `voina_istor` (
  `id` int NOT NULL AUTO_INCREMENT,
  `Nazvanie` text NOT NULL,
  `Img` text NOT NULL,
  `Text` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `voina_istor`
--
INSERT INTO `voina_istor` VALUES ('1', 'wde', 'Содержимое_подразделов\\15_История_учебного_заведения_в_годы_войны\\voina.jpg', 'das');

--
-- Структура таблицы `voina_sotrudniki`
--
DROP TABLE IF EXISTS `voina_sotrudniki`;
CREATE TABLE `voina_sotrudniki` (
  `id` int NOT NULL AUTO_INCREMENT,
  `Nazvanie` text NOT NULL,
  `Img` text NOT NULL,
  `Text` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Структура таблицы `voina_SVO`
--
DROP TABLE IF EXISTS `voina_SVO`;
CREATE TABLE `voina_SVO` (
  `id` int NOT NULL AUTO_INCREMENT,
  `Nazvanie` text NOT NULL,
  `Img` text NOT NULL,
  `Text` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Структура таблицы `vystavki`
--
DROP TABLE IF EXISTS `vystavki`;
CREATE TABLE `vystavki` (
  `id` int NOT NULL AUTO_INCREMENT,
  `Nazvanie` text NOT NULL,
  `Img` text NOT NULL,
  `Text` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `vystavki`
--
INSERT INTO `vystavki` VALUES ('1', 'dsdas', 'Содержимое_подразделов\\01_Первые_страницы_истории_текстильного_техникума\\01_02.png', 'fsdfs');

--
-- Структура таблицы `znamenitosti`
--
DROP TABLE IF EXISTS `znamenitosti`;
CREATE TABLE `znamenitosti` (
  `id` int NOT NULL AUTO_INCREMENT,
  `Nazvanie` text NOT NULL,
  `Img` text NOT NULL,
  `Text` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `znamenitosti`
--
INSERT INTO `znamenitosti` VALUES ('1', 'авыа', 'Содержимое_подразделов\\01_Первые_страницы_истории_текстильного_техникума\\01_02.png', 'ав');

