-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1:3306
-- Время создания: Апр 15 2025 г., 00:52
-- Версия сервера: 8.0.30
-- Версия PHP: 8.0.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `Museum2`
--

-- --------------------------------------------------------

--
-- Структура таблицы `admin_logs`
--

CREATE TABLE `admin_logs` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `details` text,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `admin_logs`
--

INSERT INTO `admin_logs` (`id`, `user_id`, `action`, `details`, `ip_address`, `created_at`) VALUES
(1, 8, 'Создание резервной копии', 'backup_2025-04-13_224633.sql', '127.0.0.1', '2025-04-13 19:46:33'),
(2, 8, 'Создание резервной копии', 'backup_2025-04-13_224926.sql', '127.0.0.1', '2025-04-13 19:49:26'),
(3, 8, 'Создание резервной копии', 'backup_2025-04-13_224928.sql', '127.0.0.1', '2025-04-13 19:49:28'),
(4, 8, 'Создание резервной копии', 'backup_2025-04-13_224930.sql', '127.0.0.1', '2025-04-13 19:49:30'),
(5, 8, 'Создание резервной копии', 'backup_2025-04-13_225030.sql', '127.0.0.1', '2025-04-13 19:50:30'),
(6, 8, 'Удаление всех резервных копий', 'Удалено копий: 2', '127.0.0.1', '2025-04-13 19:50:53'),
(7, 8, 'Создание резервной копии', 'backup_2025-04-13_225054.sql', '127.0.0.1', '2025-04-13 19:50:54'),
(8, 8, 'Создание резервной копии', 'backup_2025-04-13_225132.sql', '127.0.0.1', '2025-04-13 19:51:32'),
(9, 8, 'Создание резервной копии', 'backup_2025-04-13_225133.sql', '127.0.0.1', '2025-04-13 19:51:33'),
(10, 8, 'Создание резервной копии', 'backup_2025-04-13_225134.sql', '127.0.0.1', '2025-04-13 19:51:34'),
(11, 8, 'Удаление резервной копии', 'backup_2025-04-13_225134.sql', '127.0.0.1', '2025-04-13 19:51:46'),
(12, 8, 'Изменение настроек системы', 'Обновлены основные настройки', '127.0.0.1', '2025-04-13 20:02:58'),
(13, 8, 'Изменение настроек системы', 'Обновлены основные настройки', '127.0.0.1', '2025-04-13 20:29:59'),
(14, 8, 'Изменение настроек системы', 'Обновлены основные настройки', '127.0.0.1', '2025-04-13 20:35:09'),
(15, 8, 'Создание резервной копии', 'backup_2025-04-14_192954.sql', '127.0.0.1', '2025-04-14 16:29:54'),
(16, 8, 'Создание резервной копии', 'backup_2025-04-15_003257.sql', '127.0.0.1', '2025-04-14 21:32:57');

-- --------------------------------------------------------

--
-- Структура таблицы `admin_security`
--

CREATE TABLE `admin_security` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `pin` varchar(255) NOT NULL,
  `attempts` int NOT NULL DEFAULT '0',
  `last_attempt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `admin_security`
--

INSERT INTO `admin_security` (`id`, `user_id`, `pin`, `attempts`, `last_attempt`) VALUES
(1, 8, '$2y$10$oNLkXdvhOatKsiX3WhWaVO7r/MCzmrabP/qCU1ntUaF4Nnllbp4my', 0, NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `col_istorya`
--

CREATE TABLE `col_istorya` (
  `id` int NOT NULL,
  `Nazvanie` text NOT NULL,
  `Img` text NOT NULL,
  `Text` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `col_istorya`
--

INSERT INTO `col_istorya` (`id`, `Nazvanie`, `Img`, `Text`) VALUES
(1, 'История колледжа', 'Содержимое_подразделов\\01_Первые_страницы_истории_текстильного_техникума\\01_05.png', 'Текст');

-- --------------------------------------------------------

--
-- Структура таблицы `col_leg_promish`
--

CREATE TABLE `col_leg_promish` (
  `id` int NOT NULL,
  `Nazvanie` text NOT NULL,
  `Img` text NOT NULL,
  `Text` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `col_leg_promish`
--

INSERT INTO `col_leg_promish` (`id`, `Nazvanie`, `Img`, `Text`) VALUES
(1, 'История техникума легкой промышленности', 'Содержимое_подразделов\\01_Первые_страницы_истории_текстильного_техникума\\01_03.jpg', 'gwesecxercr');

-- --------------------------------------------------------

--
-- Структура таблицы `col_obyvn`
--

CREATE TABLE `col_obyvn` (
  `id` int NOT NULL,
  `Nazvanie` text NOT NULL,
  `Img` text NOT NULL,
  `Text` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `col_obyvn`
--

INSERT INTO `col_obyvn` (`id`, `Nazvanie`, `Img`, `Text`) VALUES
(1, 'История обувного техникума', 'Содержимое_подразделов\\01_Первые_страницы_истории_текстильного_техникума\\01_02.png', 'На основании постановления Совета Народных Комиссаров РСФСР от 17 января 1944 года No 54 и приказа Народного Комиссариата легкой промышленности РСФСР от 6 апреля 1944 года No 134 в городе Ярославле был открыт обувной техникум с целью подготовки техников-технологов и техников-механиков обувного производства. Директором техникума был назначен Щубин Сергей Данилович, которому было поручено произвести набор учащихся и начать занятия с 1-го сентября 1944 года.'),
(2, '', 'Содержимое_подразделов\\02_История_обувного_техникума\\01_02_02.png', ''),
(3, '', 'Содержимое_подразделов\\02_История_обувного_техникума\\01_02_03.png', '');

-- --------------------------------------------------------

--
-- Структура таблицы `col_py14`
--

CREATE TABLE `col_py14` (
  `id` int NOT NULL,
  `Nazvanie` text NOT NULL,
  `Img` text NOT NULL,
  `Text` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `col_py14`
--

INSERT INTO `col_py14` (`id`, `Nazvanie`, `Img`, `Text`) VALUES
(1, 'История ПУ №14', 'Содержимое_подразделов\\01_Первые_страницы_истории_текстильного_техникума\\01_04.png', 'gdfsgdfgdgdfgdf'),
(2, 'tet', 'kyt4klyl', 'tykety');

-- --------------------------------------------------------

--
-- Структура таблицы `col_text`
--

CREATE TABLE `col_text` (
  `id` int NOT NULL,
  `Nazvanie` text NOT NULL,
  `Img` text NOT NULL,
  `Text` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `col_text`
--

INSERT INTO `col_text` (`id`, `Nazvanie`, `Img`, `Text`) VALUES
(1, 'История обувного техникумакаиравыр', 'uploads/уф.png', 'На основании постановления Совета Народных Комиссаров РСФСР от 17 января 1944 года No 54 и приказа Народного Комиссариата легкой промышленности РСФСР от 6 апреля 1944 года No 134 в городе Ярославле был открыт обувной техникум с целью подготовки техников-технологов и техников-механиков обувного производства. Директором техникума был назначен Щубин Сергей Данилович, которому было поручено произвести набор учащихся и начать занятия с 1-го сентября 1944 года.'),
(2, 'Обувь', 'Содержимое_подразделов\\01_Первые_страницы_истории_текстильного_техникума\\01_01_01.png', 'Очень качественная обувь');

-- --------------------------------------------------------

--
-- Структура таблицы `Comments`
--

CREATE TABLE `Comments` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `comment` text NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `parent_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `Comments`
--

INSERT INTO `Comments` (`id`, `user_id`, `comment`, `image_path`, `parent_id`, `created_at`) VALUES
(35, 9, 'kkk', 'uploads/comments/67f8ee7f5cdb6.png', NULL, '2025-04-11 10:27:11'),
(36, 9, '123', NULL, 35, '2025-04-11 10:27:14'),
(37, 9, '321', NULL, NULL, '2025-04-11 10:27:23'),
(38, 8, 'khj', NULL, NULL, '2025-04-11 10:45:12'),
(39, 9, 'dawds', NULL, NULL, '2025-04-13 10:59:19'),
(40, 8, 'ао', NULL, NULL, '2025-04-13 13:26:27'),
(41, 8, 'топ', 'uploads/comments/67fd17fd89fa5.png', NULL, '2025-04-14 14:13:17'),
(42, 12, 'плохой сайт', NULL, NULL, '2025-04-14 14:25:43'),
(43, 12, 'не нравится', NULL, NULL, '2025-04-14 14:26:35'),
(44, 12, 'фу', NULL, NULL, '2025-04-14 14:26:38'),
(45, 13, 'апоп', NULL, 44, '2025-04-14 18:11:59'),
(46, 13, 'плохо', NULL, NULL, '2025-04-14 18:23:29'),
(47, 13, 'не очень', NULL, NULL, '2025-04-14 18:23:34'),
(48, 13, 'топ', 'uploads/67fd52ae465ba_natalia.png', NULL, '2025-04-14 18:23:42');

-- --------------------------------------------------------

--
-- Структура таблицы `Eksponat`
--

CREATE TABLE `Eksponat` (
  `id_exponata` int NOT NULL,
  `Nazvanie` text NOT NULL,
  `Img` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `Eksponat`
--

INSERT INTO `Eksponat` (`id_exponata`, `Nazvanie`, `Img`) VALUES
(1, 'Печатная машинка', 'Экспонаты\\00_00_01.png'),
(2, 'Ручной трудъ', 'Экспонаты\\00_00_02.png'),
(3, 'Производственный альбом', 'Экспонаты\\00_00_03.png'),
(4, 'Паспорт техникума', 'Экспонаты\\00_00_04.png'),
(5, 'Чернила', 'Экспонаты\\00_00_05.png'),
(6, 'Книга Михайлов', 'Экспонаты\\00_00_06.png'),
(7, 'Подстаканники', 'Экспонаты\\00_00_07.png'),
(8, 'Плакат', 'Экспонаты\\00_00_08.png'),
(10, 'Смешные истории', 'uploads/67facc78439f5_67faa32889b50_67f8edecba98f.png');

-- --------------------------------------------------------

--
-- Структура таблицы `index_znam`
--

CREATE TABLE `index_znam` (
  `id` int NOT NULL,
  `Nazvanie` text NOT NULL,
  `Img` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `index_znam`
--

INSERT INTO `index_znam` (`id`, `Nazvanie`, `Img`) VALUES
(1, 'Валентина Владимировна Терешкова ', 'Фоны\\tereshkova.png'),
(2, 'Алексей Власов', 'Фоны\\vlasov.png');

-- --------------------------------------------------------

--
-- Структура таблицы `meropriyatiya`
--

CREATE TABLE `meropriyatiya` (
  `id` int NOT NULL,
  `Nazvanie` text NOT NULL,
  `Img` text NOT NULL,
  `Text` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `Razdel_meropriat`
--

CREATE TABLE `Razdel_meropriat` (
  `id` int NOT NULL,
  `Nazvanie` text NOT NULL,
  `Img` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `Razdel_znam`
--

CREATE TABLE `Razdel_znam` (
  `id_znam` int NOT NULL,
  `Nazvanie` text NOT NULL,
  `Img` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `Razdel_znam`
--

INSERT INTO `Razdel_znam` (`id_znam`, `Nazvanie`, `Img`) VALUES
(1, 'Валентина Терешкова', 'Содержимое_подразделов\\19_Знаменитые выпускники\\08_19_01.png');

-- --------------------------------------------------------

--
-- Структура таблицы `Registr`
--

CREATE TABLE `Registr` (
  `id` int NOT NULL,
  `Login` text NOT NULL,
  `password` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `Email` text NOT NULL,
  `avatar` varchar(255) DEFAULT 'default_avatar.png',
  `date` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `Registr`
--

INSERT INTO `Registr` (`id`, `Login`, `password`, `Email`, `avatar`, `date`) VALUES
(8, 'admin', '$2y$10$A6WLaaRvh9e5smN4GPSP2.TFIm9ZpOvA8d6tGLhKw0qUPDzNw8Bjq', 'admin@gmail.com', 'uploads/avatars/avatar_8_1744554711.png', '2025-04-13 21:23:02'),
(9, 'kamisik', '$2y$10$HIBU5n7RufV/mkJHNzYcIekoUSvjCTQC3wdw/sEh.LS330MBnYs.y', 'kamisik@gmail.com', 'uploads/avatars/avatar_9_1744541950.png', '2025-04-13 21:23:02'),
(10, 'user1', '$2y$10$elgxf9uCEAE45PJ7NtkQPupKJOOCoQfSrqrsgmZKT1dJg/4fCWgPy', 'user1@gmail.com', 'default_avatar.png', '2025-04-13 21:41:34'),
(11, 'nolik', '$2y$10$sjjAPADIi1QdzFn.57hFQOHtLqg.4Q78icUIe5ByGELmA5CMtOc16', 'noliik@gmail.com', 'uploads/avatars/avatar_11_1744571320.jpg', '2025-04-13 22:05:08'),
(12, 'ban', '$2y$10$9X1oowKdBcISXRoY5BqZ8.ixnS0tcipCeWN2I5IVaWb0uSihl4cEq', 'ban@gmail.com', 'uploads/avatars/avatar_12_1744640721.jpg', '2025-04-14 17:24:55'),
(13, 'simka', '$2y$10$OTJx.oXSB7qL8wjhA7XU4.TrDJtVoVtn8bxM1eAVdRSYE8UwHOsJ2', 'simka@gmail.com', 'uploads/avatars/avatar_13_1744648329.jpg', '2025-04-14 19:31:27'),
(14, 'usert', '$2y$10$l9jDi6vnbidccFYrc61DluRGc2EIc.FdVLemIFZnHRHvG3y/wScnO', 'usert@gmail.com', 'default_avatar.png', '2025-04-14 23:24:15'),
(15, 'dedus', '$2y$10$dm0p6ytHkMpimVYEFsSEouI1KtaNmXCOEzdAHGD5/JvC4NgE8xpxu', 'dedus@gmail.com', 'uploads/avatars/avatar_15_1744666884.jpg', '2025-04-15 00:40:45');

-- --------------------------------------------------------

--
-- Структура таблицы `Section`
--

CREATE TABLE `Section` (
  `id_razdela` int NOT NULL,
  `Nazvanie` text NOT NULL,
  `Img` text NOT NULL,
  `url` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `Section`
--

INSERT INTO `Section` (`id_razdela`, `Nazvanie`, `Img`, `url`) VALUES
(1, 'Исторические этапы развития учебного заведения', 'Разделы\\01.png', 'coll_tema.php'),
(2, 'Спортивная жизнь колледжа', 'Разделы\\02.png', 'Sport_tema.php'),
(3, 'История Театра моды', 'Разделы\\03.png', 'Teatr_tema.php'),
(4, 'История студенческого совета', 'Разделы\\04.png', 'Stud_tema.php'),
(5, 'Страницы архива, опаленные войной', 'Разделы\\05.png', 'Voina_tema.php'),
(6, 'Мероприятия и выставки музея', 'Разделы\\06.png', 'meropriyatiya.php'),
(7, 'Строительство новых корпусов на Тутаевском шоссе', 'Разделы\\07.png', 'Korpus_tema.php'),
(8, 'Знаменитые выпускники', 'Разделы\\08.png', 'Znam.php');

-- --------------------------------------------------------

--
-- Структура таблицы `settings`
--

CREATE TABLE `settings` (
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `settings`
--

INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
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
('timezone', 'Europe/Moscow');

-- --------------------------------------------------------

--
-- Структура таблицы `sport_izvest`
--

CREATE TABLE `sport_izvest` (
  `id` int NOT NULL,
  `Nazvanie` text NOT NULL,
  `Img` text NOT NULL,
  `Text` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `sport_izvest`
--

INSERT INTO `sport_izvest` (`id`, `Nazvanie`, `Img`, `Text`) VALUES
(1, 'tyt', 'Содержимое_подразделов\\01_Первые_страницы_истории_текстильного_техникума\\01_02.png', 'ef'),
(2, '', 'Содержимое_подразделов\\01_Первые_страницы_истории_текстильного_техникума\\01_02.png', '');

-- --------------------------------------------------------

--
-- Структура таблицы `sport_str_istor`
--

CREATE TABLE `sport_str_istor` (
  `id` int NOT NULL,
  `Nazvanie` text NOT NULL,
  `Img` text NOT NULL,
  `Text` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `sport_str_istor`
--

INSERT INTO `sport_str_istor` (`id`, `Nazvanie`, `Img`, `Text`) VALUES
(1, 'gsg', 'Содержимое_подразделов\\01_Первые_страницы_истории_текстильного_техникума\\01_02.png', 'ddg');

-- --------------------------------------------------------

--
-- Структура таблицы `sport_yspex`
--

CREATE TABLE `sport_yspex` (
  `id` int NOT NULL,
  `Nazvanie` text NOT NULL,
  `Img` text NOT NULL,
  `Text` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `stroit_akt`
--

CREATE TABLE `stroit_akt` (
  `id` int NOT NULL,
  `Nazvanie` text NOT NULL,
  `Img` text NOT NULL,
  `Text` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `stroit_foto`
--

CREATE TABLE `stroit_foto` (
  `id` int NOT NULL,
  `Nazvanie` text NOT NULL,
  `Img` text NOT NULL,
  `Text` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `stroit_liter`
--

CREATE TABLE `stroit_liter` (
  `id` int NOT NULL,
  `Nazvanie` text NOT NULL,
  `Img` text NOT NULL,
  `Text` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `studsovet_dostijenya`
--

CREATE TABLE `studsovet_dostijenya` (
  `id` int NOT NULL,
  `Nazvanie` text NOT NULL,
  `Img` text NOT NULL,
  `Text` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `studsovet_dostijenya`
--

INSERT INTO `studsovet_dostijenya` (`id`, `Nazvanie`, `Img`, `Text`) VALUES
(1, 'аф', 'Содержимое_подразделов\\01_Первые_страницы_истории_текстильного_техникума\\01_02.png', 'аы');

-- --------------------------------------------------------

--
-- Структура таблицы `studsovet_lideri`
--

CREATE TABLE `studsovet_lideri` (
  `id` int NOT NULL,
  `Nazvanie` text NOT NULL,
  `Img` text NOT NULL,
  `Text` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `studsovet_lideri`
--

INSERT INTO `studsovet_lideri` (`id`, `Nazvanie`, `Img`, `Text`) VALUES
(1, 'аавы', 'Содержимое_подразделов\\01_Первые_страницы_истории_текстильного_техникума\\01_02.png', 'авыа');

-- --------------------------------------------------------

--
-- Структура таблицы `teatr_kollekz`
--

CREATE TABLE `teatr_kollekz` (
  `id` int NOT NULL,
  `Nazvanie` text NOT NULL,
  `Img` text NOT NULL,
  `Text` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `teatr_kollekz`
--

INSERT INTO `teatr_kollekz` (`id`, `Nazvanie`, `Img`, `Text`) VALUES
(1, 'аааы', 'Содержимое_подразделов\\01_Первые_страницы_истории_текстильного_техникума\\01_02.png', 'аыв');

-- --------------------------------------------------------

--
-- Структура таблицы `teatr_nashi_vipusk`
--

CREATE TABLE `teatr_nashi_vipusk` (
  `id` int NOT NULL,
  `Nazvanie` text NOT NULL,
  `Img` text NOT NULL,
  `Text` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `user_warnings`
--

CREATE TABLE `user_warnings` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `admin_id` int NOT NULL,
  `reason` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `user_warnings`
--

INSERT INTO `user_warnings` (`id`, `user_id`, `admin_id`, `reason`, `created_at`, `expires_at`) VALUES
(1, 13, 8, 'jytkyl', '2025-04-15 00:35:39', '2025-04-22 00:35:39'),
(2, 13, 8, 'ytdkyd', '2025-04-15 00:35:44', '2025-04-22 00:35:44'),
(3, 13, 8, 'kruy,uy', '2025-04-15 00:35:49', '2025-04-22 00:35:49'),
(4, 15, 8, 'нельзя так делать с моими чувствами а то будешь на месте моего парня он до сих пор не знает куда деть себя и заббыл про кое что важное', '2025-04-15 00:50:34', '2025-04-22 00:50:34'),
(5, 15, 8, 'плохо\r\n', '2025-04-15 00:51:19', '2025-04-22 00:51:19');

-- --------------------------------------------------------

--
-- Структура таблицы `voina_istor`
--

CREATE TABLE `voina_istor` (
  `id` int NOT NULL,
  `Nazvanie` text NOT NULL,
  `Img` text NOT NULL,
  `Text` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `voina_istor`
--

INSERT INTO `voina_istor` (`id`, `Nazvanie`, `Img`, `Text`) VALUES
(1, 'wde', 'Содержимое_подразделов\\15_История_учебного_заведения_в_годы_войны\\voina.jpg', 'das');

-- --------------------------------------------------------

--
-- Структура таблицы `voina_sotrudniki`
--

CREATE TABLE `voina_sotrudniki` (
  `id` int NOT NULL,
  `Nazvanie` text NOT NULL,
  `Img` text NOT NULL,
  `Text` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `voina_SVO`
--

CREATE TABLE `voina_SVO` (
  `id` int NOT NULL,
  `Nazvanie` text NOT NULL,
  `Img` text NOT NULL,
  `Text` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `vystavki`
--

CREATE TABLE `vystavki` (
  `id` int NOT NULL,
  `Nazvanie` text NOT NULL,
  `Img` text NOT NULL,
  `Text` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `vystavki`
--

INSERT INTO `vystavki` (`id`, `Nazvanie`, `Img`, `Text`) VALUES
(1, 'dsdas', 'Содержимое_подразделов\\01_Первые_страницы_истории_текстильного_техникума\\01_02.png', 'fsdfs');

-- --------------------------------------------------------

--
-- Структура таблицы `znamenitosti`
--

CREATE TABLE `znamenitosti` (
  `id` int NOT NULL,
  `Nazvanie` text NOT NULL,
  `Img` text NOT NULL,
  `Text` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `znamenitosti`
--

INSERT INTO `znamenitosti` (`id`, `Nazvanie`, `Img`, `Text`) VALUES
(1, 'авыа', 'Содержимое_подразделов\\01_Первые_страницы_истории_текстильного_техникума\\01_02.png', 'ав');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `admin_logs`
--
ALTER TABLE `admin_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `created_at` (`created_at`);

--
-- Индексы таблицы `admin_security`
--
ALTER TABLE `admin_security`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Индексы таблицы `col_istorya`
--
ALTER TABLE `col_istorya`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `col_leg_promish`
--
ALTER TABLE `col_leg_promish`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `col_obyvn`
--
ALTER TABLE `col_obyvn`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `col_py14`
--
ALTER TABLE `col_py14`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `col_text`
--
ALTER TABLE `col_text`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `Comments`
--
ALTER TABLE `Comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Индексы таблицы `Eksponat`
--
ALTER TABLE `Eksponat`
  ADD PRIMARY KEY (`id_exponata`);

--
-- Индексы таблицы `index_znam`
--
ALTER TABLE `index_znam`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `meropriyatiya`
--
ALTER TABLE `meropriyatiya`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `Razdel_meropriat`
--
ALTER TABLE `Razdel_meropriat`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `Razdel_znam`
--
ALTER TABLE `Razdel_znam`
  ADD PRIMARY KEY (`id_znam`);

--
-- Индексы таблицы `Registr`
--
ALTER TABLE `Registr`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `Section`
--
ALTER TABLE `Section`
  ADD PRIMARY KEY (`id_razdela`);

--
-- Индексы таблицы `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`setting_key`);

--
-- Индексы таблицы `sport_izvest`
--
ALTER TABLE `sport_izvest`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `sport_str_istor`
--
ALTER TABLE `sport_str_istor`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `sport_yspex`
--
ALTER TABLE `sport_yspex`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `stroit_akt`
--
ALTER TABLE `stroit_akt`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `stroit_foto`
--
ALTER TABLE `stroit_foto`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `stroit_liter`
--
ALTER TABLE `stroit_liter`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `studsovet_dostijenya`
--
ALTER TABLE `studsovet_dostijenya`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `studsovet_lideri`
--
ALTER TABLE `studsovet_lideri`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `teatr_kollekz`
--
ALTER TABLE `teatr_kollekz`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `teatr_nashi_vipusk`
--
ALTER TABLE `teatr_nashi_vipusk`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `user_warnings`
--
ALTER TABLE `user_warnings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Индексы таблицы `voina_istor`
--
ALTER TABLE `voina_istor`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `voina_sotrudniki`
--
ALTER TABLE `voina_sotrudniki`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `voina_SVO`
--
ALTER TABLE `voina_SVO`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `vystavki`
--
ALTER TABLE `vystavki`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `znamenitosti`
--
ALTER TABLE `znamenitosti`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `admin_logs`
--
ALTER TABLE `admin_logs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT для таблицы `admin_security`
--
ALTER TABLE `admin_security`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT для таблицы `col_istorya`
--
ALTER TABLE `col_istorya`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `col_leg_promish`
--
ALTER TABLE `col_leg_promish`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `col_obyvn`
--
ALTER TABLE `col_obyvn`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `col_py14`
--
ALTER TABLE `col_py14`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `col_text`
--
ALTER TABLE `col_text`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT для таблицы `Comments`
--
ALTER TABLE `Comments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT для таблицы `Eksponat`
--
ALTER TABLE `Eksponat`
  MODIFY `id_exponata` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT для таблицы `index_znam`
--
ALTER TABLE `index_znam`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `meropriyatiya`
--
ALTER TABLE `meropriyatiya`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `Razdel_meropriat`
--
ALTER TABLE `Razdel_meropriat`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `Razdel_znam`
--
ALTER TABLE `Razdel_znam`
  MODIFY `id_znam` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `Registr`
--
ALTER TABLE `Registr`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT для таблицы `Section`
--
ALTER TABLE `Section`
  MODIFY `id_razdela` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT для таблицы `sport_izvest`
--
ALTER TABLE `sport_izvest`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `sport_str_istor`
--
ALTER TABLE `sport_str_istor`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `sport_yspex`
--
ALTER TABLE `sport_yspex`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `stroit_akt`
--
ALTER TABLE `stroit_akt`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `stroit_foto`
--
ALTER TABLE `stroit_foto`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `stroit_liter`
--
ALTER TABLE `stroit_liter`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `studsovet_dostijenya`
--
ALTER TABLE `studsovet_dostijenya`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT для таблицы `studsovet_lideri`
--
ALTER TABLE `studsovet_lideri`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT для таблицы `teatr_kollekz`
--
ALTER TABLE `teatr_kollekz`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `teatr_nashi_vipusk`
--
ALTER TABLE `teatr_nashi_vipusk`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `user_warnings`
--
ALTER TABLE `user_warnings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT для таблицы `voina_istor`
--
ALTER TABLE `voina_istor`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `voina_sotrudniki`
--
ALTER TABLE `voina_sotrudniki`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `voina_SVO`
--
ALTER TABLE `voina_SVO`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `vystavki`
--
ALTER TABLE `vystavki`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `znamenitosti`
--
ALTER TABLE `znamenitosti`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `Comments`
--
ALTER TABLE `Comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Registr` (`id`),
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `Comments` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
