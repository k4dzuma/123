<?php
session_start();
require 'db_connection.php';

if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header("Location: login.php");
    exit();
}

// Обработка смены PIN
if (isset($_POST['change_pin'])) {
    $new_pin = $_POST['new_pin'];
    $confirm_pin = $_POST['confirm_pin'];
    
    if ($new_pin !== $confirm_pin) {
        $pin_error = "PIN-коды не совпадают";
    } elseif (!preg_match('/^\d{4,6}$/', $new_pin)) {
        $pin_error = "PIN должен содержать 4-6 цифр";
    } else {
        try {
            $pin_hash = password_hash($new_pin, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE admin_security SET pin = :pin WHERE user_id = :user_id");
            $stmt->execute([
                ':pin' => $pin_hash,
                ':user_id' => $_SESSION['user_id']
            ]);
            $pin_success = "PIN успешно изменен!";
        } catch (PDOException $e) {
            $pin_error = "Ошибка базы данных: " . $e->getMessage();
        }
    }
}

$current_theme = $_COOKIE['admin_theme'] ?? 'light';
// Получаем статистику
$stats = [];
try {
    $stats['tables'] = $db->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE()")->fetchColumn();
    $stats['users'] = $db->query("SELECT COUNT(*) FROM Registr")->fetchColumn();
    $stats['admins'] = $db->query("SELECT COUNT(*) FROM admin_security")->fetchColumn();
    $stats['backups'] = count(glob('backups/backup_*.sql'));
    $stats['last_backup'] = file_exists('backups/') ? date('d.m.Y H:i', filemtime('backups/')) : 'Нет данных';
    
    $stmt = $db->query("SELECT * FROM admin_logs ORDER BY created_at DESC LIMIT 5");
    $stats['recent_activity'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Информация о сервере
    $stats['php_version'] = phpversion();
    $stats['server_software'] = $_SERVER['SERVER_SOFTWARE'];
    $stats['db_size'] = $db->query("SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) as size FROM information_schema.TABLES WHERE table_schema = DATABASE()")->fetchColumn();
    
} catch (PDOException $e) {
    error_log("Ошибка получения статистики: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ru" data-bs-theme="<?= $current_theme ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Главная | Админ-панель</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --sidebar-width: 300px;
            --topbar-height: 70px;
            --primary-color: #4e73df;
            --secondary-color: #1cc88a;
            --warning-color: #f6c23e;
            --danger-color: #e74a3b;
            --dark-color: #5a5c69;
            --light-color: #f8f9fc;
            --transition: all 0.3s ease;
        }
        
        body {
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--light-color);
            min-height: 100vh;
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
            transition: var(--transition);
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
            transition: var(--transition);
            min-height: calc(100vh - var(--topbar-height));
            background-color: var(--light-color);
        }
        
        .sidebar-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 15px 25px;
            margin: 5px 10px;
            border-radius: 5px;
            transition: var(--transition);
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
        
        .stat-card {
            border-radius: 10px;
            overflow: hidden;
            transition: var(--transition);
            border: none;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
            border-left: 5px solid;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1.5rem 0 rgba(58, 59, 69, 0.2);
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
            transition: var(--transition);
        }
        
        .card:hover {
            box-shadow: 0 0.5rem 1.5rem 0 rgba(58, 59, 69, 0.2);
        }
        
        .card-header {
            background: white;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            font-weight: 600;
            padding: 20px;
        }
        
        .activity-item {
            border-left: 3px solid var(--primary-color);
            transition: var(--transition);
            padding: 15px;
            margin-bottom: 10px;
            background: white;
            border-radius: 5px;
        }
        
        .system-info-item {
            padding: 10px 15px;
            margin-bottom: 10px;
            background: white;
            border-radius: 5px;
            border-left: 3px solid var(--secondary-color);
        }
        
        .info-label {
            font-weight: 600;
            color: var(--dark-color);
        }
        
        .info-value {
            font-family: monospace;
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
        </style>
</head>
<body>
    <!-- Topbar -->
    <nav id="topbar" class="navbar navbar-expand navbar-light bg-white">
        <div class="container-fluid">
            <button class="btn btn-link d-md-none" type="button" id="sidebarToggle">
                <i class="bi bi-list"></i>
            </button>
            
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-house-door me-2"></i>На главную
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
                    <a href="admin_panel.php" class="sidebar-link active">
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
                    <a href="admin_content.php" class="sidebar-link">
                        <i class="bi bi-collection"></i>Контент
                    </a>
                </li>
                <li>
                    <a href="admin_quests.php" class="sidebar-link">
                        <i class="bi bi-trophy"></i>Квесты
                    </a>
                </li>
                <li>
                    <a href="admin_quest_stats.php" class="sidebar-link">
                        <i class="bi bi-graph-up"></i>Статистика квестов
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
                <h1 class="h3 mb-0 text-gray-800"><i class="bi bi-speedometer2 me-2"></i>Главная панель</h1>
            </div>
            
            <!-- Статистика -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="stat-card bg-white p-4" style="border-left-color: var(--primary-color);">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="fw-bold"><?= $stats['tables'] ?? 0 ?></h5>
                                <span class="text-muted">Таблиц в БД</span>
                            </div>
                            <i class="bi bi-database fs-1 text-primary opacity-23"></i>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="stat-card bg-white p-4" style="border-left-color: var(--secondary-color);">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="fw-bold"><?= $stats['users'] ?? 0 ?></h5>
                                <span class="text-muted">Пользователей</span>
                            </div>
                            <i class="bi bi-people fs-1 text-success opacity-25"></i>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="stat-card bg-white p-4" style="border-left-color: var(--danger-color);">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="fw-bold"><?= $stats['backups'] ?? 0 ?></h5>
                                <span class="text-muted">Резервных копий</span>
                            </div>
                            <i class="bi bi-archive fs-1 text-danger opacity-25"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Основной контент -->
            <div class="row">
                <!-- Последние действия -->
                <div class="col-lg-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header py-3 d-flex justify-content-between align-items-center">
                            <h6 class="m-0 fw-bold"><i class="bi bi-clock-history me-2"></i>Последние действия</h6>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($stats['recent_activity'])): ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach ($stats['recent_activity'] as $log): ?>
                                        <div class="list-group-item border-0 activity-item mb-2">
                                            <div class="d-flex justify-content-between">
                                                <div>
                                                    <h6 class="mb-1"><?= htmlspecialchars($log['action']) ?></h6>
                                                    <small class="text-muted"><?= htmlspecialchars($log['details']) ?></small>
                                                </div>
                                                <small class="text-muted">
                                                    <?= date('d.m.Y H:i', strtotime($log['created_at'])) ?>
                                                </small>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="bi bi-info-circle fs-1 text-muted mb-3"></i>
                                    <p class="text-muted">Нет данных о последних действиях</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Информация о системе -->
                <div class="col-lg-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header py-3 d-flex justify-content-between align-items-center">
                            <h6 class="m-0 fw-bold"><i class="bi bi-info-circle me-2"></i>Информация о системе</h6>
                        </div>
                        <div class="card-body">
                            <div class="system-info-item mb-3">
                                <div class="info-label">Версия PHP</div>
                                <div class="info-value"><?= $stats['php_version'] ?></div>
                            </div>
                            
                            <div class="system-info-item mb-3">
                                <div class="info-label">Сервер</div>
                                <div class="info-value"><?= htmlspecialchars($stats['server_software']) ?></div>
                            </div>
                            
                            <div class="system-info-item mb-3">
                                <div class="info-label">Размер базы данных</div>
                                <div class="info-value"><?= $stats['db_size'] ?> MB</div>
                            </div>
                            
                            <div class="system-info-item mb-3">
                                <div class="info-label">Последняя резервная копия</div>
                                <div class="info-value"><?= $stats['last_backup'] ?></div>
                            </div>
                            
                            <div class="system-info-item">
                                <div class="info-label">Пользователь системы</div>
                                <div class="info-value"><?= htmlspecialchars($_SESSION['login']) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Модальное окно смены PIN -->
    <div class="modal fade" id="changePinModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-shield-lock me-2"></i>Смена PIN-кода</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="admin_panel.php">
                    <div class="modal-body">
                        <?php if (isset($pin_error)): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($pin_error) ?></div>
                        <?php elseif (isset($pin_success)): ?>
                            <div class="alert alert-success"><?= htmlspecialchars($pin_success) ?></div>
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <label class="form-label">Новый PIN-код (4-6 цифр)</label>
                            <input type="password" class="form-control" name="new_pin" 
                                   pattern="\d{4,6}" maxlength="6" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Подтвердите PIN-код</label>
                            <input type="password" class="form-control" name="confirm_pin" 
                                   pattern="\d{4,6}" maxlength="6" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                        <button type="submit" name="change_pin" class="btn btn-primary">Сохранить</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
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
        
        // Валидация PIN-кода
        $('input[name="new_pin"], input[name="confirm_pin"]').on('input', function() {
            this.value = this.value.replace(/\D/g, '');
        });
    </script>
</body>
</html>