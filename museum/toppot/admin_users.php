<?php
session_start();
require 'db_connection.php';
require 'admin_theme.php';

if (!isset($_SESSION['is_admin'])) {
    header("Location: login.php");
    exit();
}

// Обработка выдачи предупреждения
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['warn_user'])) {
    $user_id = $_POST['user_id'];
    $comment_id = $_POST['comment_id'] ?? null;
    $reason = $_POST['reason'];
    
    try {
        $db->beginTransaction();
        
        // Добавление предупреждения
        $expires_at = date('Y-m-d H:i:s', strtotime('+7 days'));
        $stmt = $db->prepare("INSERT INTO user_warnings (user_id, admin_id, reason, expires_at) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $_SESSION['user_id'], $reason, $expires_at]);
        
        // Удаление комментария, если выбрано
        if ($comment_id) {
            $stmt = $db->prepare("DELETE FROM Comments WHERE id = ?");
            $stmt->execute([$comment_id]);
            if ($stmt->rowCount() === 0) {
                throw new Exception("Комментарий не найден");
            }
        }
        
        $db->commit();
        $_SESSION['success_message'] = "Действие выполнено: " . ($comment_id ? "комментарий удален + " : "") . "предупреждение выдано";
    } catch (Exception $e) {
        $db->rollBack();
        $_SESSION['error_message'] = "Ошибка: " . $e->getMessage();
    }
    header("Location: admin_users.php");
    exit();
}

// Поиск и фильтрация
$search = $_GET['search'] ?? '';
$role_filter = $_GET['role'] ?? 'all';

// Базовый запрос
$query = "SELECT r.*, 
          CASE WHEN a.user_id IS NOT NULL THEN 1 ELSE 0 END as is_admin,
          a.attempts as login_attempts,
          (SELECT COUNT(*) FROM user_warnings uw WHERE uw.user_id = r.id AND uw.expires_at > datetime('now','localtime')) as warning_count
          FROM Registr r
          LEFT JOIN admin_security a ON r.id = a.user_id
          WHERE (r.Login LIKE :search OR r.Email LIKE :search)";

// Фильтр по роли
if ($role_filter === 'admin') {
    $query .= " AND a.user_id IS NOT NULL";
} elseif ($role_filter === 'user') {
    $query .= " AND a.user_id IS NULL";
}

$query .= " ORDER BY r.id DESC";

$stmt = $db->prepare($query);
$stmt->bindValue(':search', "%$search%");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru" data-bs-theme="<?= $current_theme ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Пользователи | Админ-панель</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
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
        
        [data-bs-theme="dark"] {
            --bs-body-bg: #212529;
            --bs-body-color: #f8f9fa;
            --light-color: #212529;
            --dark-color: #f8f9fa;
            --bs-border-color: #495057;
        }
        
        body {
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--light-color);
            color: var(--dark-color);
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
        
        .user-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(0,0,0,0.1);
        }
        
        .badge-admin {
            background-color: #6f42c1;
        }
        
        .badge-user {
            background-color: #6c757d;
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
                    <a href="admin_users.php" class="sidebar-link active">
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
                <h1 class="h3 mb-0 text-gray-800"><i class="bi bi-people me-2"></i>Управление пользователями</h1>
            </div>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= $_SESSION['success_message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= $_SESSION['error_message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>

            <!-- Статистика -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="stat-card bg-white p-4" style="border-left-color: var(--primary-color);">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="fw-bold"><?= count($users) ?></h5>
                                <span class="text-muted">Всего пользователей</span>
                            </div>
                            <i class="bi bi-people fs-1 text-primary opacity-23"></i>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="stat-card bg-white p-4" style="border-left-color: var(--secondary-color);">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="fw-bold"><?= array_reduce($users, function($carry, $user) { return $carry + ($user['is_admin'] ? 1 : 0); }, 0) ?></h5>
                                <span class="text-muted">Администраторов</span>
                            </div>
                            <i class="bi bi-shield-lock fs-1 text-success opacity-25"></i>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="stat-card bg-white p-4" style="border-left-color: var(--warning-color);">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="fw-bold"><?= array_reduce($users, function($carry, $user) { return $carry + ($user['warning_count'] > 0 ? 1 : 0); }, 0) ?></h5>
                                <span class="text-muted">С предупреждениями</span>
                            </div>
                            <i class="bi bi-exclamation-triangle fs-1 text-warning opacity-25"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Фильтры и поиск -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-5">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" name="search" class="form-control" placeholder="Поиск по логину или email" value="<?= htmlspecialchars($search) ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select name="role" class="form-select">
                                <option value="all" <?= $role_filter === 'all' ? 'selected' : '' ?>>Все пользователи</option>
                                <option value="admin" <?= $role_filter === 'admin' ? 'selected' : '' ?>>Только администраторы</option>
                                <option value="user" <?= $role_filter === 'user' ? 'selected' : '' ?>>Только обычные пользователи</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-funnel me-2"></i>Фильтровать
                            </button>
                        </div>
                        <div class="col-md-2">
                            <a href="admin_users.php" class="btn btn-outline-secondary w-100">
                                <i class="bi bi-arrow-counterclockwise me-2"></i>Сбросить
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Таблица пользователей -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="m-0"><i class="bi bi-table me-2"></i>Список пользователей (<?= count($users) ?>)</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="usersTable">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Пользователь</th>
                                    <th>Email</th>
                                    <th>Регистрация</th>
                                    <th>Статус</th>
                                    <th>Предупреждения</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?= $user['id'] ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if (!empty($user['avatar'])): ?>
                                                <img src="<?= htmlspecialchars($user['avatar']) ?>" class="user-avatar me-3" alt="Аватар">
                                            <?php else: ?>
                                                <div class="user-avatar me-3" style="background-color: #4e73df; color: white; display: flex; align-items: center; justify-content: center; font-weight: bold;">
                                                    <?= strtoupper(substr($user['Login'], 0, 1)) ?>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <div class="fw-bold"><?= htmlspecialchars($user['Login']) ?></div>
                                                <small class="text-muted">ID: <?= $user['id'] ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($user['Email']) ?></td>
                                    <td>
                                        <?= date('d.m.Y', strtotime($user['date'] ?? 'now')) ?>
                                        <small class="d-block text-muted">
                                            <?= date('H:i', strtotime($user['date'] ?? 'now')) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge rounded-pill <?= $user['is_admin'] ? 'badge-admin' : 'badge-user' ?>">
                                            <?= $user['is_admin'] ? 'Администратор' : 'Пользователь' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($user['warning_count'] > 0): ?>
                                            <span class="badge bg-warning text-dark">
                                                <?= $user['warning_count'] ?> активных
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-success">Нет</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-warning" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#warnUserModal"
                                                data-user-id="<?= $user['id'] ?>"
                                                data-user-name="<?= htmlspecialchars($user['Login']) ?>">
                                            <i class="bi bi-exclamation-triangle"></i> Предупредить
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Модальное окно выдачи предупреждения -->
    <div class="modal fade" id="warnUserModal" tabindex="-1" aria-labelledby="warnUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="warnUserModalLabel">Выдать предупреждение</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="user_id" id="warn_user_id">
                        <input type="hidden" name="comment_id" id="warn_comment_id">
                        <p>Вы собираетесь выдать предупреждение пользователю <strong id="warn_user_name"></strong>.</p>
                        <p>После 3 предупреждений пользователь будет заблокирован.</p>
                        
                        <div class="mb-3">
                            <label for="reason" class="form-label">Причина предупреждения</label>
                            <textarea class="form-control" id="reason" name="reason" rows="3" required></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Дополнительные действия</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="deleteLastComment">
                                <label class="form-check-label" for="deleteLastComment">
                                    Удалить последний комментарий пользователя
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                        <button type="submit" name="warn_user" class="btn btn-warning">Выдать предупреждение</button>
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
            $('#usersTable').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/ru.json'
                },
                order: [[0, 'desc']]
            });
            
            // Обработка модального окна предупреждения
            $('#warnUserModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var userId = button.data('user-id');
                var userName = button.data('user-name');
                
                var modal = $(this);
                modal.find('#warn_user_id').val(userId);
                modal.find('#warn_user_name').text(userName);
                modal.find('#warn_comment_id').val('');
                modal.find('#deleteLastComment').prop('checked', false);
            });
            
            // Обработка изменения чекбокса удаления комментария
            $('#deleteLastComment').change(function() {
                if ($(this).is(':checked')) {
                    var userId = $('#warn_user_id').val();
                    $.ajax({
                        url: 'get_last_comment.php',
                        method: 'POST',
                        data: { user_id: userId },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success && response.comment_id) {
                                $('#warn_comment_id').val(response.comment_id);
                            } else {
                                alert('Не удалось найти последний комментарий пользователя');
                                $('#deleteLastComment').prop('checked', false);
                            }
                        },
                        error: function() {
                            alert('Ошибка при поиске последнего комментария');
                            $('#deleteLastComment').prop('checked', false);
                        }
                    });
                } else {
                    $('#warn_comment_id').val('');
                }
            });
        });
    </script>
</body>
</html>