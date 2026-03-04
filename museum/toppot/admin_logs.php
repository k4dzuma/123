<?php
session_start();
require 'db_connection.php';

if (!isset($_SESSION['is_admin'])) {
    header("Location: login.php");
    exit();
}
$current_theme = $_COOKIE['admin_theme'] ?? 'light';
// Фильтрация логов
$action_filter = $_GET['action'] ?? '';
$user_filter = $_GET['user_id'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

// Базовый запрос
$query = "SELECT l.*, r.Login as username 
          FROM admin_logs l
          LEFT JOIN Registr r ON l.user_id = r.id
          WHERE 1=1";

$params = [];

// Применяем фильтры
if (!empty($action_filter)) {
    $query .= " AND l.action LIKE ?";
    $params[] = "%$action_filter%";
}

if (!empty($user_filter)) {
    $query .= " AND l.user_id = ?";
    $params[] = $user_filter;
}

if (!empty($date_from)) {
    $query .= " AND DATE(l.created_at) >= ?";
    $params[] = $date_from;
}

if (!empty($date_to)) {
    $query .= " AND DATE(l.created_at) <= ?";
    $params[] = $date_to;
}

$query .= " ORDER BY l.created_at DESC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Получаем список администраторов для фильтра
$admins = $db->query("SELECT r.id, r.Login 
                      FROM Registr r
                      JOIN admin_security a ON r.id = a.user_id
                      ORDER BY r.Login")->fetchAll(PDO::FETCH_ASSOC);

// Очистка логов
if (isset($_POST['clear_logs'])) {
    $db->query("TRUNCATE TABLE admin_logs");
    log_action($_SESSION['user_id'], 'Очистка логов', 'Все записи журнала удалены');
    $_SESSION['success_message'] = "Журнал событий очищен";
    header("Location: admin_logs.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="ru" data-bs-theme="<?= $current_theme ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Журнал событий | Админ-панель</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <style>
        :root {
            --sidebar-width: 300px;
            --topbar-height: 70px;
            --primary-color: #4e73df;
            --light-color: #f8f9fc;
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
        
        .log-critical { border-left: 4px solid #dc3545; }
        .log-warning { border-left: 4px solid #fd7e14; }
        .log-info { border-left: 4px solid #0dcaf0; }
        .log-success { border-left: 4px solid #198754; }
        
        .log-table {
            font-size: 0.9rem;
        }
        
        .log-details {
            max-width: 300px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .badge-system {
            background-color: #6c757d;
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
                    <a href="admin_content.php" class="sidebar-link">
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
                    <a href="admin_logs.php" class="sidebar-link active">
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
                <h1 class="h3 mb-0 text-gray-800"><i class="bi bi-journal-text me-2"></i>Журнал событий</h1>
            </div>
            
            <!-- Фильтры -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" name="action" class="form-control" placeholder="Действие" value="<?= htmlspecialchars($action_filter) ?>">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <select name="user_id" class="form-select">
                                <option value="">Все пользователи</option>
                                <?php foreach ($admins as $admin): ?>
                                    <option value="<?= $admin['id'] ?>" <?= $user_filter == $admin['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($admin['Login']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="date" name="date_from" class="form-control" placeholder="С даты" value="<?= htmlspecialchars($date_from) ?>">
                        </div>
                        <div class="col-md-2">
                            <input type="date" name="date_to" class="form-control" placeholder="По дату" value="<?= htmlspecialchars($date_to) ?>">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-funnel me-2"></i>Фильтровать
                            </button>
                        </div>
                        <div class="col-md-1">
                            <a href="admin_logs.php" class="btn btn-outline-secondary w-100" title="Сбросить">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Таблица логов -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="m-0"><i class="bi bi-table me-2"></i>Последние события (<?= count($logs) ?>)</h5>
                    <div>
                        <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#clearLogsModal">
                            <i class="bi bi-trash me-1"></i>Очистить журнал
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover log-table mb-0" id="logsTable">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Дата</th>
                                    <th>Пользователь</th>
                                    <th>Действие</th>
                                    <th>Детали</th>
                                    <th>IP</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($logs as $log): 
                                    $log_class = '';
                                    if (stripos($log['action'], 'ошибка') !== false) $log_class = 'log-critical';
                                    elseif (stripos($log['action'], 'удал') !== false) $log_class = 'log-warning';
                                    elseif (stripos($log['action'], 'добав') !== false) $log_class = 'log-success';
                                    else $log_class = 'log-info';
                                ?>
                                <tr class="<?= $log_class ?>">
                                    <td><?= $log['id'] ?></td>
                                    <td>
                                        <?= date('d.m.Y', strtotime($log['created_at'])) ?>
                                        <small class="d-block text-muted">
                                            <?= date('H:i:s', strtotime($log['created_at'])) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php if ($log['user_id']): ?>
                                            <span class="badge bg-primary"><?= $log['username'] ?? $log['user_id'] ?></span>
                                        <?php else: ?>
                                            <span class="badge badge-system">Система</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($log['action']) ?></td>
                                    <td class="log-details" title="<?= htmlspecialchars($log['details']) ?>">
                                        <?= htmlspecialchars($log['details']) ?>
                                    </td>
                                    <td>
                                        <small class="text-muted"><?= $log['ip_address'] ?? 'N/A' ?></small>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer text-muted">
                    Всего записей: <?= count($logs) ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Модальное окно подтверждения очистки -->
    <div class="modal fade" id="clearLogsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="bi bi-exclamation-triangle me-2"></i>Подтверждение</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <p>Вы уверены, что хотите полностью очистить журнал событий?</p>
                        <p class="fw-bold">Будет удалено <?= count($logs) ?> записей. Это действие нельзя отменить.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                        <button type="submit" name="clear_logs" class="btn btn-danger">Очистить журнал</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#logsTable').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/ru.json'
                },
                order: [[1, 'desc']],
                stateSave: true,
                lengthMenu: [ [10, 25, 50, 100, -1], [10, 25, 50, 100, "Все"] ],
                columnDefs: [
                    { targets: [5], orderable: false }
                ]
            });
            
            // Показываем полный текст при наведении на детали
            $('.log-details').tooltip({
                placement: 'top'
            });
        });
    </script>
</body>
</html>