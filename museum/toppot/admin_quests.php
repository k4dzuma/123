<?php
session_start();
require 'db_connection.php';
require 'functions.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: login.php");
    exit();
}

$message = '';
$error = '';

// Обработка действий
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // Создание квеста
    if ($action === 'create') {
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $duration = (int)($_POST['duration_minutes'] ?? 30);
        $difficulty = $_POST['difficulty_level'] ?? 'medium';
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        if (empty($title)) {
            $error = 'Название квеста обязательно';
        } else {
            $stmt = $db->prepare("INSERT INTO quests (title, description, duration_minutes, difficulty_level, is_active, created_by) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $description, $duration, $difficulty, $is_active, $_SESSION['user_id']]);
            log_action($_SESSION['user_id'], 'Создание квеста', $title);
            $message = 'Квест успешно создан!';
        }
    }
    
    // Редактирование квеста
    if ($action === 'update') {
        $quest_id = (int)($_POST['quest_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $duration = (int)($_POST['duration_minutes'] ?? 30);
        $difficulty = $_POST['difficulty_level'] ?? 'medium';
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        if ($quest_id > 0 && !empty($title)) {
            $stmt = $db->prepare("UPDATE quests SET title = ?, description = ?, duration_minutes = ?, difficulty_level = ?, is_active = ? WHERE quest_id = ?");
            $stmt->execute([$title, $description, $duration, $difficulty, $is_active, $quest_id]);
            log_action($_SESSION['user_id'], 'Редактирование квеста', "ID: $quest_id, $title");
            $message = 'Квест обновлён!';
        }
    }
    
    // Удаление квеста
    if ($action === 'delete') {
        $quest_id = (int)($_POST['quest_id'] ?? 0);
        if ($quest_id > 0) {
            $stmt = $db->prepare("SELECT title FROM quests WHERE quest_id = ?");
            $stmt->execute([$quest_id]);
            $title = $stmt->fetchColumn();
            $stmt = $db->prepare("DELETE FROM quests WHERE quest_id = ?");
            $stmt->execute([$quest_id]);
            log_action($_SESSION['user_id'], 'Удаление квеста', "ID: $quest_id, $title");
            $message = 'Квест удалён!';
        }
    }
    
    // Переключение активности
    if ($action === 'toggle') {
        $quest_id = (int)($_POST['quest_id'] ?? 0);
        if ($quest_id > 0) {
            $stmt = $db->prepare("UPDATE quests SET is_active = NOT is_active WHERE quest_id = ?");
            $stmt->execute([$quest_id]);
            $message = 'Статус квеста изменён!';
        }
    }
}

// Получаем квесты
$stmt = $db->query("SELECT q.*, 
    (SELECT COUNT(*) FROM quest_steps WHERE quest_id = q.quest_id) as step_count,
    (SELECT COUNT(*) FROM player_sessions WHERE quest_id = q.quest_id) as session_count,
    (SELECT COUNT(*) FROM player_sessions WHERE quest_id = q.quest_id AND status = 'completed') as completed_count
    FROM quests q ORDER BY q.created_at DESC");
$quests = $stmt->fetchAll(PDO::FETCH_ASSOC);

$current_theme = $_COOKIE['admin_theme'] ?? 'light';
?>
<!DOCTYPE html>
<html lang="ru" data-bs-theme="<?= $current_theme ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Квесты | Админ-панель</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root { --sidebar-width: 300px; --topbar-height: 70px; --primary-color: #4e73df; --light-color: #f8f9fc; }
        body { font-family: 'Nunito', -apple-system, sans-serif; background-color: var(--light-color); }
        #sidebar { width: var(--sidebar-width); height: 100vh; position: fixed; left: 0; top: 0; padding-top: var(--topbar-height); background: linear-gradient(180deg, #4e73df 0%, #224abe 100%); z-index: 100; transition: all 0.3s ease; color: white; }
        #topbar { height: var(--topbar-height); position: fixed; top: 0; left: 0; right: 0; background: white; box-shadow: 0 0.15rem 1.75rem 0 rgba(58,59,69,0.15); z-index: 110; padding: 0 20px; }
        #content { margin-left: var(--sidebar-width); margin-top: var(--topbar-height); padding: 30px; transition: all 0.3s ease; min-height: calc(100vh - var(--topbar-height)); }
        .sidebar-link { color: rgba(255,255,255,0.8); padding: 15px 25px; margin: 5px 10px; border-radius: 5px; transition: all 0.3s; display: flex; align-items: center; text-decoration: none; }
        .sidebar-link:hover, .sidebar-link.active { color: white; background: rgba(255,255,255,0.2); text-decoration: none; }
        .sidebar-link i { margin-right: 10px; font-size: 1.1rem; }
        .sidebar-divider { border-top: 1px solid rgba(255,255,255,0.2); margin: 15px 20px; }
        .sidebar-heading { padding: 0 25px; margin-top: 20px; font-size: 0.8rem; color: rgba(255,255,255,0.6); text-transform: uppercase; letter-spacing: 1px; }
        .card { border: none; border-radius: 10px; box-shadow: 0 0.15rem 1.75rem 0 rgba(58,59,69,0.1); }
        .quest-row:hover { background-color: rgba(78,115,223,0.05); }
        .badge-easy { background-color: #1cc88a; }
        .badge-medium { background-color: #f6c23e; color: #333; }
        .badge-hard { background-color: #e74a3b; }
    </style>
</head>
<body>
    <!-- Topbar -->
    <nav id="topbar" class="navbar navbar-expand navbar-light bg-white">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php"><i class="bi bi-house-door me-2"></i>На главную</a>
            <div class="d-flex align-items-center ms-auto">
                <span class="fw-bold me-3"><?= htmlspecialchars($_SESSION['login']) ?></span>
                <a href="logout.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-box-arrow-right"></i></a>
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
                <li><a href="admin_panel.php" class="sidebar-link"><i class="bi bi-speedometer2"></i>Главная</a></li>
                <div class="sidebar-divider"></div>
                <div class="sidebar-heading">Управление</div>
                <li><a href="admin_users.php" class="sidebar-link"><i class="bi bi-people"></i>Пользователи</a></li>
                <li><a href="admin_content.php" class="sidebar-link"><i class="bi bi-collection"></i>Контент</a></li>
                <li><a href="admin_quests.php" class="sidebar-link active"><i class="bi bi-trophy"></i>Квесты</a></li>
                <div class="sidebar-divider"></div>
                <div class="sidebar-heading">Система</div>
                <li><a href="admin_backup.php" class="sidebar-link"><i class="bi bi-database"></i>Резервные копии</a></li>
                <li><a href="admin_logs.php" class="sidebar-link"><i class="bi bi-journal-text"></i>Логи</a></li>
            </ul>
        </div>
    </div>

    <!-- Content -->
    <div id="content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0"><i class="bi bi-trophy me-2"></i>Управление квестами</h1>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createQuestModal">
                    <i class="bi bi-plus-circle me-1"></i>Новый квест
                </button>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($message) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show"><i class="bi bi-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            <?php endif; ?>

            <!-- Статистика -->
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="card p-3 border-start border-primary border-4">
                        <div class="text-muted small">Всего квестов</div>
                        <div class="fw-bold fs-4"><?= count($quests) ?></div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card p-3 border-start border-success border-4">
                        <div class="text-muted small">Активных</div>
                        <div class="fw-bold fs-4"><?= count(array_filter($quests, fn($q) => $q['is_active'])) ?></div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card p-3 border-start border-info border-4">
                        <div class="text-muted small">Всего сессий</div>
                        <div class="fw-bold fs-4"><?= array_sum(array_column($quests, 'session_count')) ?></div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card p-3 border-start border-warning border-4">
                        <div class="text-muted small">Завершено</div>
                        <div class="fw-bold fs-4"><?= array_sum(array_column($quests, 'completed_count')) ?></div>
                    </div>
                </div>
            </div>

            <!-- Таблица квестов -->
            <div class="card">
                <div class="card-header bg-white py-3"><h6 class="m-0 fw-bold"><i class="bi bi-list-ul me-2"></i>Список квестов</h6></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Название</th>
                                    <th>Сложность</th>
                                    <th>Длительность</th>
                                    <th>Этапы</th>
                                    <th>Прохождения</th>
                                    <th>Статус</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($quests)): ?>
                                    <tr><td colspan="8" class="text-center py-4 text-muted">Квесты не созданы</td></tr>
                                <?php else: ?>
                                    <?php foreach ($quests as $q): ?>
                                        <tr class="quest-row">
                                            <td><?= $q['quest_id'] ?></td>
                                            <td><strong><?= htmlspecialchars($q['title']) ?></strong></td>
                                            <td><span class="badge badge-<?= $q['difficulty_level'] ?> rounded-pill"><?= ['easy'=>'Легкий','medium'=>'Средний','hard'=>'Сложный'][$q['difficulty_level']] ?></span></td>
                                            <td><?= $q['duration_minutes'] ?> мин</td>
                                            <td>
                                                <a href="admin_quest_steps.php?quest_id=<?= $q['quest_id'] ?>" class="text-decoration-none">
                                                    <?= $q['step_count'] ?> <i class="bi bi-arrow-right-short"></i>
                                                </a>
                                            </td>
                                            <td><?= $q['completed_count'] ?>/<?= $q['session_count'] ?></td>
                                            <td>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="action" value="toggle">
                                                    <input type="hidden" name="quest_id" value="<?= $q['quest_id'] ?>">
                                                    <button type="submit" class="btn btn-sm <?= $q['is_active'] ? 'btn-success' : 'btn-secondary' ?>">
                                                        <?= $q['is_active'] ? 'Активен' : 'Скрыт' ?>
                                                    </button>
                                                </form>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="admin_quest_steps.php?quest_id=<?= $q['quest_id'] ?>" class="btn btn-outline-info" title="Этапы"><i class="bi bi-list-ol"></i></a>
                                                    <button class="btn btn-outline-primary edit-quest-btn" title="Редактировать"
                                                        data-id="<?= $q['quest_id'] ?>"
                                                        data-title="<?= htmlspecialchars($q['title']) ?>"
                                                        data-description="<?= htmlspecialchars($q['description']) ?>"
                                                        data-duration="<?= $q['duration_minutes'] ?>"
                                                        data-difficulty="<?= $q['difficulty_level'] ?>"
                                                        data-active="<?= $q['is_active'] ?>"
                                                        data-bs-toggle="modal" data-bs-target="#editQuestModal">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <form method="POST" class="d-inline" onsubmit="return confirm('Удалить квест и все его этапы?')">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="quest_id" value="<?= $q['quest_id'] ?>">
                                                        <button type="submit" class="btn btn-outline-danger" title="Удалить"><i class="bi bi-trash"></i></button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Модальное окно: Создание квеста -->
    <div class="modal fade" id="createQuestModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="action" value="create">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Новый квест</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Название *</label>
                            <input type="text" name="title" class="form-control" required maxlength="255">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Описание</label>
                            <textarea name="description" class="form-control" rows="4"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Продолжительность (мин)</label>
                                <input type="number" name="duration_minutes" class="form-control" value="30" min="5" max="180">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Сложность</label>
                                <select name="difficulty_level" class="form-select">
                                    <option value="easy">Легкий</option>
                                    <option value="medium" selected>Средний</option>
                                    <option value="hard">Сложный</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" name="is_active" class="form-check-input" id="createActive" checked>
                            <label class="form-check-label" for="createActive">Активен (виден участникам)</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check me-1"></i>Создать</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Модальное окно: Редактирование квеста -->
    <div class="modal fade" id="editQuestModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="quest_id" id="editQuestId">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Редактирование квеста</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Название *</label>
                            <input type="text" name="title" id="editTitle" class="form-control" required maxlength="255">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Описание</label>
                            <textarea name="description" id="editDescription" class="form-control" rows="4"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Продолжительность (мин)</label>
                                <input type="number" name="duration_minutes" id="editDuration" class="form-control" min="5" max="180">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Сложность</label>
                                <select name="difficulty_level" id="editDifficulty" class="form-select">
                                    <option value="easy">Легкий</option>
                                    <option value="medium">Средний</option>
                                    <option value="hard">Сложный</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" name="is_active" class="form-check-input" id="editActive">
                            <label class="form-check-label" for="editActive">Активен</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check me-1"></i>Сохранить</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Заполнение модального окна редактирования
        document.querySelectorAll('.edit-quest-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('editQuestId').value = this.dataset.id;
                document.getElementById('editTitle').value = this.dataset.title;
                document.getElementById('editDescription').value = this.dataset.description;
                document.getElementById('editDuration').value = this.dataset.duration;
                document.getElementById('editDifficulty').value = this.dataset.difficulty;
                document.getElementById('editActive').checked = this.dataset.active === '1';
            });
        });
    </script>
</body>
</html>
