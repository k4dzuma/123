<?php
session_start();
require 'db_connection.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: login.php");
    exit();
}

$current_theme = $_COOKIE['admin_theme'] ?? 'light';

$content_tables = [
    'Основные' => ['Разделы' => 'Section', 'Экспонаты' => 'Eksponat'],
    'Коллекции' => ['Тексты коллекций' => 'col_text', 'Обывательские коллекции' => 'col_obyvn', 'Легкая промышленность' => 'col_leg_promish', 'Промышленность 14 века' => 'col_py14', 'Исторические коллекции' => 'col_istorya'],
    'Театр' => ['Театральные коллекции' => 'teatr_kollekz', 'Наши выпускники' => 'teatr_nashi_vipusk'],
    'Спорт' => ['Известные спортсмены' => 'sport_izvest', 'История спорта' => 'sport_str_istor', 'Спортивные успехи' => 'sport_yspex'],
    'Военная история' => ['Военная история' => 'voina_istor', 'Сотрудники военных лет' => 'voina_sotrudniki', 'СВО' => 'voina_SVO'],
    'Строительство' => ['Акты строительства' => 'stroit_akt', 'Фото строительства' => 'stroit_foto', 'Литература по строительству' => 'stroit_liter'],
    'Знаменитости' => ['Знаменитости' => 'znamenitosti'],
    'Мероприятия' => ['Мероприятия' => 'meropriyatiya', 'Выставки' => 'vystavki'],
    'Студсовет' => ['Достижения студсовета' => 'studsovet_dostijenya', 'Лидеры студсовета' => 'studsovet_lideri']
];

// Цвета для категорий
$category_colors = [
    'Основные' => 'primary',
    'Коллекции' => 'info',
    'Театр' => 'purple',
    'Спорт' => 'success',
    'Военная история' => 'danger',
    'Строительство' => 'warning',
    'Знаменитости' => 'pink',
    'Мероприятия' => 'teal',
    'Студсовет' => 'indigo'
];
?>

<!DOCTYPE html>
<html lang="ru" data-bs-theme="<?= $current_theme ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Контент | Админ-панель</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --sidebar-width: 300px;
            --topbar-height: 70px;
            --primary-color: #4e73df;
            --light-color: #f8f9fc;
            --purple: #6f42c1;
            --pink: #e83e8c;
            --teal: #20c997;
            --indigo: #6610f2;
        }
        
        body {
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--light-color);
        }
        
        #sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            padding-top: var(--topbar-height);
            background: linear-gradient(180deg, #4e73df 0%, #224abe 100%);
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            z-index: 100;
            transition: all 0.3s ease;
            color: white;
        }
        
        #topbar {
            height: var(--topbar-height);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: white;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            z-index: 110;
            padding: 0 20px;
        }
        
        #content {
            margin-left: var(--sidebar-width);
            margin-top: var(--topbar-height);
            padding: 30px;
            transition: all 0.3s ease;
            min-height: calc(100vh - var(--topbar-height));
            background-color: var(--light-color);
        }
        
        .sidebar-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 15px 25px;
            margin: 5px 10px;
            border-radius: 5px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            text-decoration: none;
        }
        
        .sidebar-link:hover, .sidebar-link.active {
            color: white;
            background: rgba(255, 255, 255, 0.2);
            text-decoration: none;
        }
        
        .sidebar-link i {
            margin-right: 10px;
            font-size: 1.1rem;
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
        }
        
        .card-header {
            background: white;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            font-weight: 600;
            padding: 20px;
        }
        
        .quick-link {
            padding: 10px 15px;
            margin: 5px;
            border-radius: 8px;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        
        .quick-link:hover {
            transform: translateY(-2px);
        }
        
        .sidebar-divider {
            border-top: 1px solid rgba(255,255,255,0.2);
            margin: 15px 20px;
        }
        
        .sidebar-heading {
            padding: 0 25px;
            margin-top: 20px;
            font-size: 0.8rem;
            color: rgba(255,255,255,0.6);
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        /* Кастомные цвета */
        .text-purple { color: var(--purple); }
        .bg-purple { background-color: var(--purple); }
        .border-purple { border-color: var(--purple); }
        
        .text-pink { color: var(--pink); }
        .bg-pink { background-color: var(--pink); }
        .border-pink { border-color: var(--pink); }
        
        .text-teal { color: var(--teal); }
        .bg-teal { background-color: var(--teal); }
        .border-teal { border-color: var(--teal); }
        
        .text-indigo { color: var(--indigo); }
        .bg-indigo { background-color: var(--indigo); }
        .border-indigo { border-color: var(--indigo); }
        
        /* Стили для кнопок категорий */
        .btn-outline-purple {
            color: var(--purple);
            border-color: var(--purple);
        }
        .btn-outline-purple:hover {
            color: #fff;
            background-color: var(--purple);
            border-color: var(--purple);
        }
        
        .btn-outline-pink {
            color: var(--pink);
            border-color: var(--pink);
        }
        .btn-outline-pink:hover {
            color: #fff;
            background-color: var(--pink);
            border-color: var(--pink);
        }
        
        .btn-outline-teal {
            color: var(--teal);
            border-color: var(--teal);
        }
        .btn-outline-teal:hover {
            color: #fff;
            background-color: var(--teal);
            border-color: var(--teal);
        }
        
        .btn-outline-indigo {
            color: var(--indigo);
            border-color: var(--indigo);
        }
        .btn-outline-indigo:hover {
            color: #fff;
            background-color: var(--indigo);
            border-color: var(--indigo);
        }
        
        /* Цвета заголовков категорий */
        .category-primary { color: #4e73df; }
        .category-info { color: #36b9cc; }
        .category-purple { color: var(--purple); }
        .category-success { color: #1cc88a; }
        .category-danger { color: #e74a3b; }
        .category-warning { color: #f6c23e; }
        .category-pink { color: var(--pink); }
        .category-teal { color: var(--teal); }
        .category-indigo { color: var(--indigo); }
    </style>
</head>
<body>
    <!-- Topbar -->
    <nav id="topbar" class="navbar navbar-expand navbar-light bg-white">
        <div class="container-fluid">
            <button class="btn btn-link d-md-none" type="button" id="sidebarToggle">
                <i class="bi bi-list"></i>
            </button>
            
            <a class="navbar-brand" href="admin_panel.php">
                <i class="bi bi-arrow-left me-2"></i>Назад
            </a>
            
            <div class="d-flex align-items-center ms-auto">
                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" 
                       id="userDropdown" data-bs-toggle="dropdown">
                        <div class="me-2 d-none d-lg-inline text-end">
                            <span class="fw-bold"><?= htmlspecialchars($_SESSION['login']) ?></span>
                            <small class="d-block text-muted">Администратор</small>
                        </div>
                        <i class="bi bi-person-circle fs-3"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow">
                        <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#changePinModal">
                            <i class="bi bi-shield-lock me-2"></i>Сменить PIN</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php">
                            <i class="bi bi-box-arrow-right me-2"></i>Выйти</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Sidebar -->
    <div id="sidebar">
        <div class="px-3 py-4">
            <div class="text-center mb-4">
                <img src="logo.png" alt="Логотип" style="height: 50px;">
                <div class="mt-2 fw-bold">Админ-панель</div>
            </div>
            
            <ul class="list-unstyled">
                <li>
                    <a href="admin_panel.php" class="sidebar-link">
                        <i class="bi bi-speedometer2"></i>Главная
                    </a>
                </li>
                
                <div class="sidebar-divider"></div>
                <div class="sidebar-heading">Управление</div>
                
                <li>
                    <a href="admin_users.php" class="sidebar-link">
                        <i class="bi bi-people"></i>Пользователи
                    </a>
                </li>
                <li>
                    <a href="admin_content.php" class="sidebar-link active">
                        <i class="bi bi-collection"></i>Контент
                    </a>
                </li>
                
                <div class="sidebar-divider"></div>
                <div class="sidebar-heading">Система</div>
                
                <li>
                    <a href="admin_backup.php" class="sidebar-link">
                        <i class="bi bi-database"></i>Резервные копии
                    </a>
                </li>
                <li>
                    <a href="admin_logs.php" class="sidebar-link">
                        <i class="bi bi-journal-text"></i>Логи
                    </a>
                </li>
               
            </ul>
        </div>
    </div>

    <!-- Main Content -->
    <div id="content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800"><i class="bi bi-collection me-2"></i>Управление контентом</h1>
            </div>
            
            <!-- Категории контента -->
            <div class="card">
                <div class="card-header">
                    <h5 class="m-0"><i class="bi bi-folder me-2"></i>Категории контента</h5>
                </div>
                <div class="card-body">
                    <?php foreach ($content_tables as $category => $tables): ?>
                        <?php $color = $category_colors[$category]; ?>
                        <div class="mb-4">
                            <h5 class="fw-bold mb-3 category-<?= $color ?>">
                                <i class="bi bi-folder-fill me-2"></i><?= $category ?>
                            </h5>
                            <div class="d-flex flex-wrap">
                                <?php foreach ($tables as $name => $table): ?>
                                    <a href="admin_edit.php?table=<?= $table ?>" 
                                       class="quick-link btn btn-outline-<?= $color ?> me-3 mb-3">
                                        <i class="bi bi-table me-2"></i><?= $name ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php if ($category !== array_key_last($content_tables)): ?>
                            <hr class="my-4">
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Переключение сайдбара
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            const content = document.getElementById('content');
            
            if (sidebar.style.left === '0px') {
                sidebar.style.left = `-${sidebar.offsetWidth}px`;
                content.style.marginLeft = '0';
            } else {
                sidebar.style.left = '0';
                content.style.marginLeft = `${sidebar.offsetWidth}px`;
            }
        });
    </script>
</body>
</html>