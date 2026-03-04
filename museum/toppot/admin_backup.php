<?php
session_start();
require 'db_connection.php';

if (!isset($_SESSION['is_admin'])) {
    header("Location: login.php");
    exit();
}
$current_theme = $_COOKIE['admin_theme'] ?? 'light';
// Функция для логирования действий администратора
function log_action($user_id, $action, $details = '') {
    global $db;
    
    try {
        $stmt = $db->prepare("INSERT INTO admin_logs (user_id, action, details, ip_address) 
                             VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $user_id,
            $action,
            $details,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
        return true;
    } catch (Exception $e) {
        // В случае ошибки можно записать в PHP error log
        error_log("Ошибка при записи в лог: " . $e->getMessage());
        return false;
    }
}

// Функция создания резервной копии
function create_backup($db) {
    $backup_dir = 'backups/';
    if (!is_dir($backup_dir)) {
        mkdir($backup_dir, 0755, true);
    }
    
    $backup_file = $backup_dir . 'backup_' . date('Y-m-d_His') . '.sql';
    
    try {
        $tables = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);
        $output = "-- Резервная копия SQLite базы данных музея\n";
        $output .= "-- Дата: " . date('Y-m-d H:i:s') . "\n\n";
        
        foreach ($tables as $table) {
            $output .= "--\n-- Структура таблицы `$table`\n--\n";
            $create_sql = $db->query("SELECT sql FROM sqlite_master WHERE type='table' AND name='$table'")->fetchColumn();
            if ($create_sql) {
                $output .= "DROP TABLE IF EXISTS `$table`;\n";
                $output .= $create_sql . ";\n\n";
            }
            
            $rows = $db->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
            if (count($rows) > 0) {
                $output .= "--\n-- Дамп данных таблицы `$table`\n--\n";
                
                foreach ($rows as $row) {
                    $columns = array_map(function($col) use ($db) {
                        if ($col === null) return 'NULL';
                        return $db->quote($col);
                    }, array_values($row));
                    
                    $output .= "INSERT INTO `$table` VALUES (" . implode(', ', $columns) . ");\n";
                }
                $output .= "\n";
            }
        }
        
        file_put_contents($backup_file, $output);
        log_action($_SESSION['user_id'], 'Создание резервной копии', basename($backup_file));
        
        return $backup_file;
    } catch (Exception $e) {
        log_action($_SESSION['user_id'], 'Ошибка создания резервной копии', $e->getMessage());
        return false;
    }
}

// Создание резервной копии по запросу
if (isset($_POST['create_backup'])) {
    $backup_file = create_backup($db);
    
    if ($backup_file) {
        $_SESSION['success_message'] = "Резервная копия успешно создана: " . basename($backup_file);
    } else {
        $_SESSION['error_message'] = "Ошибка при создании резервной копии";
    }
    
    header("Location: admin_backup.php");
    exit();
}

// Сохранение настроек авто-бекапа
if (isset($_POST['save_auto_backup_settings'])) {
    $auto_backup_enabled = isset($_POST['auto_backup_enabled']) ? 1 : 0;
    $auto_backup_interval = $_POST['auto_backup_interval'];
    $auto_backup_time = $_POST['auto_backup_time'];
    $auto_backup_max_files = (int)$_POST['auto_backup_max_files'];
    
    // Валидация
    if ($auto_backup_max_files < 1) {
        $auto_backup_max_files = 1;
    }
    
    // Сохраняем настройки в БД
    $settings_to_save = [
        'auto_backup_enabled' => $auto_backup_enabled,
        'auto_backup_interval' => $auto_backup_interval,
        'auto_backup_time' => $auto_backup_time,
        'auto_backup_max_files' => $auto_backup_max_files
    ];
    $stmt = $db->prepare("INSERT OR REPLACE INTO settings (setting_key, setting_value) VALUES (?, ?)");
    foreach ($settings_to_save as $key => $value) {
        $stmt->execute([$key, $value]);
    }
    
    $_SESSION['success_message'] = "Настройки авто-бекапа успешно сохранены";
    header("Location: admin_backup.php");
    exit();
}

// Получаем текущие настройки авто-бекапа
$auto_backup_settings = [
    'enabled' => 0,
    'interval' => 'daily',
    'time' => '00:00',
    'max_files' => 5
];

$stmt = $db->query("SELECT * FROM settings WHERE setting_key IN 
                   ('auto_backup_enabled', 'auto_backup_interval', 'auto_backup_time', 'auto_backup_max_files')");
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    switch ($row['setting_key']) {
        case 'auto_backup_enabled':
            $auto_backup_settings['enabled'] = (int)$row['setting_value'];
            break;
        case 'auto_backup_interval':
            $auto_backup_settings['interval'] = $row['setting_value'];
            break;
        case 'auto_backup_time':
            $auto_backup_settings['time'] = $row['setting_value'];
            break;
        case 'auto_backup_max_files':
            $auto_backup_settings['max_files'] = (int)$row['setting_value'];
            break;
    }
}

// Получаем список резервных копий
$backups = [];
if (is_dir('backups')) {
    $files = glob('backups/backup_*.sql');
    
    foreach ($files as $file) {
        $backups[] = [
            'name' => basename($file),
            'path' => $file,
            'size' => filesize($file),
            'date' => date('d.m.Y H:i', filemtime($file)),
            'tables' => count(get_tables_from_backup($file))
        ];
    }
    
    usort($backups, function($a, $b) {
        return filemtime($b['path']) - filemtime($a['path']);
    });
}

function get_tables_from_backup($file) {
    $content = file_get_contents($file);
    preg_match_all('/-- Структура таблицы `(.+?)`/', $content, $matches);
    return $matches[1] ?? [];
}

function format_size($bytes) {
    if ($bytes === 0) return '0 Bytes';
    $k = 1024;
    $sizes = ['Bytes', 'KB', 'MB', 'GB'];
    $i = floor(log($bytes) / log($k));
    return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
}
?>

<!DOCTYPE html>
<html lang="ru" data-bs-theme="<?= $current_theme ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Резервные копии | Админ-панель</title>
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
        
        .backup-card {
            border-left: 4px solid var(--primary-color);
            transition: all 0.3s;
        }
        
        .backup-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .backup-size {
            font-family: monospace;
        }
        
        .action-btn {
            width: 35px;
            height: 35px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }

        .settings-card {
            border-left: 4px solid #6c757d;
        }
        
        .form-switch .form-check-input {
            width: 3em;
            height: 1.5em;
        }
        
        .time-input {
            max-width: 100px;
        }
        
        .number-input {
            max-width: 80px;
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
                    <a href="admin_backup.php" class="sidebar-link active">
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
                <h1 class="h3 mb-0 text-gray-800"><i class="bi bi-database me-2"></i>Управление резервными копиями</h1>
            </div>
            
            <!-- Уведомления -->
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle me-2"></i><?= $_SESSION['success_message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-triangle me-2"></i><?= $_SESSION['error_message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>
            
            <!-- Настройки авто-бекапов -->
            <div class="card mb-4 settings-card">
                <div class="card-header">
                    <h5 class="m-0"><i class="bi bi-clock me-2"></i>Настройки автоматического резервного копирования</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="autoBackupEnabled" 
                                           name="auto_backup_enabled" value="1" <?= $auto_backup_settings['enabled'] ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="autoBackupEnabled">Автоматическое создание резервных копий</label>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="autoBackupInterval" class="form-label">Интервал</label>
                                    <select class="form-select" id="autoBackupInterval" name="auto_backup_interval">
                                        <option value="daily" <?= $auto_backup_settings['interval'] == 'daily' ? 'selected' : '' ?>>Ежедневно</option>
                                        <option value="weekly" <?= $auto_backup_settings['interval'] == 'weekly' ? 'selected' : '' ?>>Еженедельно</option>
                                        <option value="monthly" <?= $auto_backup_settings['interval'] == 'monthly' ? 'selected' : '' ?>>Ежемесячно</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="autoBackupTime" class="form-label">Время создания (ЧЧ:ММ)</label>
                                    <input type="time" class="form-control time-input" id="autoBackupTime" 
                                           name="auto_backup_time" value="<?= $auto_backup_settings['time'] ?>" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="autoBackupMaxFiles" class="form-label">Максимальное количество копий</label>
                                    <input type="number" class="form-control number-input" id="autoBackupMaxFiles" 
                                           name="auto_backup_max_files" min="1" max="100" 
                                           value="<?= $auto_backup_settings['max_files'] ?>" required>
                                    <div class="form-text">При превышении лимита старые копии будут удаляться автоматически</div>
                                </div>
                                
                                <div class="alert alert-info mt-4">
                                    <h5><i class="bi bi-info-circle me-2"></i>Информация</h5>
                                    <ul class="mb-0">
                                        <li>Авто-бекапы выполняются через системный cron</li>
                                        <li>Для работы необходимо настроить задание cron</li>
                                        <li>Пример команды для cron: <code>0 * * * * php /path/to/backup_cron.php</code></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-3">
                            <button type="submit" name="save_auto_backup_settings" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i>Сохранить настройки
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <!-- Создание резервной копии -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="m-0"><i class="bi bi-plus-circle me-2"></i>Создать резервную копию вручную</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="alert alert-info">
                                <h5><i class="bi bi-info-circle me-2"></i>Информация</h5>
                                <ul class="mb-0">
                                    <li>Будет создана полная резервная копия всех таблиц</li>
                                    <li>Имя файла формируется автоматически</li>
                                    <li>Рекомендуется создавать резервные копии перед обновлениями</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-6 d-flex align-items-center">
                            <form method="POST" class="w-100">
                                <div class="d-grid">
                                    <button type="submit" name="create_backup" class="btn btn-primary btn-lg py-3">
                                        <i class="bi bi-database-add me-2"></i>Создать резервную копию
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Список резервных копий -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="m-0"><i class="bi bi-files me-2"></i>Доступные резервные копии</h5>
                    <div>
                        <button class="btn btn-sm btn-outline-danger" id="deleteAllBackupsBtn">
                            <i class="bi bi-trash me-1"></i>Удалить все
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($backups)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover" id="backupsTable">
                                <thead>
                                    <tr>
                                        <th>Имя файла</th>
                                        <th>Размер</th>
                                        <th>Таблиц</th>
                                        <th>Дата создания</th>
                                        <th>Действия</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($backups as $backup): ?>
                                    <tr class="backup-card">
                                        <td>
                                            <div class="fw-bold"><?= $backup['name'] ?></div>
                                            <small class="text-muted"><?= $backup['path'] ?></small>
                                        </td>
                                        <td class="backup-size">
                                            <?= format_size($backup['size']) ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary"><?= $backup['tables'] ?></span>
                                        </td>
                                        <td>
                                            <?= $backup['date'] ?>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="<?= $backup['path'] ?>" download 
                                                   class="action-btn btn btn-outline-primary"
                                                   title="Скачать">
                                                    <i class="bi bi-download"></i>
                                                </a>
                                                <a href="admin_restore.php?file=<?= urlencode($backup['name']) ?>" 
                                                   class="action-btn btn btn-outline-success"
                                                   title="Восстановить"
                                                   onclick="return confirm('Восстановить базу из этой копии? Текущие данные будут заменены!')">
                                                    <i class="bi bi-arrow-counterclockwise"></i>
                                                </a>
                                                <a href="admin_delete_backup.php?file=<?= urlencode($backup['name']) ?>" 
                                                   class="action-btn btn btn-outline-danger"
                                                   title="Удалить"
                                                   onclick="return confirm('Удалить эту резервную копию?')">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning text-center py-4">
                            <i class="bi bi-exclamation-triangle fs-1 mb-3 d-block"></i>
                            <h4>Нет доступных резервных копий</h4>
                            <p class="mb-0">Создайте первую резервную копию нажав кнопку выше</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Модальное окно подтверждения удаления -->
    <div class="modal fade" id="confirmDeleteAllModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="bi bi-exclamation-triangle me-2"></i>Подтверждение</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Вы уверены, что хотите удалить <strong>все резервные копии</strong>? Это действие нельзя отменить.</p>
                    <p class="fw-bold">Будет удалено <?= count($backups) ?> файлов.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <a href="admin_delete_all_backups.php" class="btn btn-danger">Удалить все</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#backupsTable').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/ru.json'
                },
                order: [[3, 'desc']],
                columnDefs: [
                    { orderable: false, targets: [4] }
                ]
            });
            
            $('#deleteAllBackupsBtn').click(function() {
                $('#confirmDeleteAllModal').modal('show');
            });
        });
    </script>
</body>
</html>