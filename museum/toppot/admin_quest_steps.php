<?php
session_start();
require 'db_connection.php';
require 'functions.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: login.php");
    exit();
}

$quest_id = filter_input(INPUT_GET, 'quest_id', FILTER_VALIDATE_INT);
if (!$quest_id) {
    header("Location: admin_quests.php");
    exit();
}

// Получаем квест
$stmt = $db->prepare("SELECT * FROM quests WHERE quest_id = ?");
$stmt->execute([$quest_id]);
$quest = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$quest) {
    header("Location: admin_quests.php");
    exit();
}

$message = '';
$error = '';

// Обработка действий
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // Создание шага
    if ($action === 'create') {
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $solution = trim($_POST['solution'] ?? '');
        $hint_text = trim($_POST['hint_text'] ?? '');
        $step_score = max(10, (int)($_POST['step_score'] ?? 100));
        $max_attempts = max(1, (int)($_POST['max_attempts'] ?? 3));
        
        if (empty($title) || empty($description) || empty($solution)) {
            $error = 'Название, описание и правильный ответ обязательны';
        } else {
            // Определяем следующий порядковый номер
            $stmt = $db->prepare("SELECT COALESCE(MAX(step_order), 0) + 1 FROM quest_steps WHERE quest_id = ?");
            $stmt->execute([$quest_id]);
            $next_order = $stmt->fetchColumn();
            
            // Хешируем ответ
            $solution_hash = password_hash(mb_strtolower(trim($solution)), PASSWORD_DEFAULT);
            
            // Обработка медиа
            $media_path = null;
            if (isset($_FILES['media']) && $_FILES['media']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = 'uploads/quests/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
                $ext = strtolower(pathinfo($_FILES['media']['name'], PATHINFO_EXTENSION));
                $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                if (in_array($ext, $allowed_ext)) {
                    $filename = 'step_' . time() . '_' . uniqid() . '.' . $ext;
                    if (move_uploaded_file($_FILES['media']['tmp_name'], $upload_dir . $filename)) {
                        $media_path = $upload_dir . $filename;
                    }
                }
            }
            
            $stmt = $db->prepare("INSERT INTO quest_steps (quest_id, step_order, title, description, solution_hash, hint_text, step_score, max_attempts, media_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$quest_id, $next_order, $title, $description, $solution_hash, $hint_text, $step_score, $max_attempts, $media_path]);
            log_action($_SESSION['user_id'], 'Создание этапа квеста', "Квест ID: $quest_id, Этап: $title");
            $message = 'Этап успешно добавлен!';
        }
    }
    
    // Обновление шага
    if ($action === 'update') {
        $step_id = (int)($_POST['step_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $solution = trim($_POST['solution'] ?? '');
        $hint_text = trim($_POST['hint_text'] ?? '');
        $step_score = max(10, (int)($_POST['step_score'] ?? 100));
        $max_attempts = max(1, (int)($_POST['max_attempts'] ?? 3));
        $step_order = max(1, (int)($_POST['step_order'] ?? 1));
        
        if ($step_id > 0 && !empty($title) && !empty($description)) {
            $update_sql = "UPDATE quest_steps SET title = ?, description = ?, hint_text = ?, step_score = ?, max_attempts = ?, step_order = ?";
            $params = [$title, $description, $hint_text, $step_score, $max_attempts, $step_order];
            
            // Обновляем хэш только если ввели новый ответ
            if (!empty($solution)) {
                $update_sql .= ", solution_hash = ?";
                $params[] = password_hash(mb_strtolower(trim($solution)), PASSWORD_DEFAULT);
            }
            
            // Медиа
            if (isset($_FILES['media']) && $_FILES['media']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = 'uploads/quests/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
                $ext = strtolower(pathinfo($_FILES['media']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
                    $filename = 'step_' . time() . '_' . uniqid() . '.' . $ext;
                    if (move_uploaded_file($_FILES['media']['tmp_name'], $upload_dir . $filename)) {
                        $update_sql .= ", media_path = ?";
                        $params[] = $upload_dir . $filename;
                    }
                }
            }
            
            $update_sql .= " WHERE step_id = ? AND quest_id = ?";
            $params[] = $step_id;
            $params[] = $quest_id;
            
            $stmt = $db->prepare($update_sql);
            $stmt->execute($params);
            log_action($_SESSION['user_id'], 'Редактирование этапа квеста', "Этап ID: $step_id, $title");
            $message = 'Этап обновлён!';
        }
    }
    
    // Удаление шага
    if ($action === 'delete') {
        $step_id = (int)($_POST['step_id'] ?? 0);
        if ($step_id > 0) {
            $stmt = $db->prepare("DELETE FROM quest_steps WHERE step_id = ? AND quest_id = ?");
            $stmt->execute([$step_id, $quest_id]);
            // Перенумеровываем
            $stmt = $db->prepare("SELECT step_id FROM quest_steps WHERE quest_id = ? ORDER BY step_order ASC");
            $stmt->execute([$quest_id]);
            $order = 1;
            while ($row = $stmt->fetch()) {
                $db->prepare("UPDATE quest_steps SET step_order = ? WHERE step_id = ?")->execute([$order++, $row['step_id']]);
            }
            log_action($_SESSION['user_id'], 'Удаление этапа квеста', "Этап ID: $step_id");
            $message = 'Этап удалён!';
        }
    }
    
    // Перемещение шага
    if ($action === 'move_up' || $action === 'move_down') {
        $step_id = (int)($_POST['step_id'] ?? 0);
        $stmt = $db->prepare("SELECT step_id, step_order FROM quest_steps WHERE quest_id = ? ORDER BY step_order ASC");
        $stmt->execute([$quest_id]);
        $all_steps = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($all_steps as $i => $s) {
            if ($s['step_id'] == $step_id) {
                $swap_index = $action === 'move_up' ? $i - 1 : $i + 1;
                if (isset($all_steps[$swap_index])) {
                    $db->prepare("UPDATE quest_steps SET step_order = ? WHERE step_id = ?")->execute([$all_steps[$swap_index]['step_order'], $step_id]);
                    $db->prepare("UPDATE quest_steps SET step_order = ? WHERE step_id = ?")->execute([$s['step_order'], $all_steps[$swap_index]['step_id']]);
                    $message = 'Порядок изменён!';
                }
                break;
            }
        }
    }
}

// Получаем шаги
$stmt = $db->prepare("SELECT * FROM quest_steps WHERE quest_id = ? ORDER BY step_order ASC");
$stmt->execute([$quest_id]);
$steps = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Этапы: <?= htmlspecialchars($quest['title']) ?> | Админ-панель</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root { --sidebar-width: 300px; --topbar-height: 70px; }
        body { font-family: 'Nunito', -apple-system, sans-serif; background-color: #f8f9fc; }
        #sidebar { width: var(--sidebar-width); height: 100vh; position: fixed; left: 0; top: 0; padding-top: var(--topbar-height); background: linear-gradient(180deg, #4e73df 0%, #224abe 100%); z-index: 100; color: white; }
        #topbar { height: var(--topbar-height); position: fixed; top: 0; left: 0; right: 0; background: white; box-shadow: 0 0.15rem 1.75rem 0 rgba(58,59,69,0.15); z-index: 110; padding: 0 20px; }
        #content { margin-left: var(--sidebar-width); margin-top: var(--topbar-height); padding: 30px; min-height: calc(100vh - var(--topbar-height)); }
        .sidebar-link { color: rgba(255,255,255,0.8); padding: 15px 25px; margin: 5px 10px; border-radius: 5px; transition: all 0.3s; display: flex; align-items: center; text-decoration: none; }
        .sidebar-link:hover, .sidebar-link.active { color: white; background: rgba(255,255,255,0.2); text-decoration: none; }
        .sidebar-link i { margin-right: 10px; font-size: 1.1rem; }
        .sidebar-divider { border-top: 1px solid rgba(255,255,255,0.2); margin: 15px 20px; }
        .sidebar-heading { padding: 0 25px; margin-top: 20px; font-size: 0.8rem; color: rgba(255,255,255,0.6); text-transform: uppercase; letter-spacing: 1px; }
        .card { border: none; border-radius: 10px; box-shadow: 0 0.15rem 1.75rem 0 rgba(58,59,69,0.1); }
        .step-card { border-left: 4px solid #4e73df; transition: all 0.3s; }
        .step-card:hover { box-shadow: 0 0.5rem 2rem rgba(58,59,69,0.15); }
        .step-number-badge { width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #4fc3f7, #166088); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; }
    </style>
</head>
<body>
    <!-- Topbar -->
    <nav id="topbar" class="navbar navbar-expand navbar-light bg-white">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php"><i class="bi bi-house-door me-2"></i>На главную</a>
            <div class="d-flex align-items-center ms-auto">
                <span class="fw-bold me-3"><?= htmlspecialchars($_SESSION['login'] ?? '') ?></span>
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
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="admin_quests.php">Квесты</a></li>
                    <li class="breadcrumb-item active"><?= htmlspecialchars($quest['title']) ?></li>
                </ol>
            </nav>
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0"><i class="bi bi-list-ol me-2"></i>Этапы: <?= htmlspecialchars($quest['title']) ?></h1>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createStepModal">
                    <i class="bi bi-plus-circle me-1"></i>Добавить этап
                </button>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($message) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show"><?= htmlspecialchars($error) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            <?php endif; ?>

            <!-- Список этапов -->
            <?php if (empty($steps)): ?>
                <div class="card p-5 text-center">
                    <i class="bi bi-inbox display-1 text-muted mb-3"></i>
                    <h4>Этапы не созданы</h4>
                    <p class="text-muted">Добавьте первый этап квеста</p>
                </div>
            <?php else: ?>
                <?php foreach ($steps as $i => $step): ?>
                    <div class="card step-card mb-3">
                        <div class="card-body">
                            <div class="d-flex align-items-start">
                                <div class="step-number-badge me-3 flex-shrink-0"><?= $step['step_order'] ?></div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h5 class="fw-bold mb-1"><?= htmlspecialchars($step['title']) ?></h5>
                                            <p class="text-muted mb-2"><?= htmlspecialchars(mb_substr($step['description'], 0, 150)) ?>...</p>
                                            <div class="d-flex gap-3 flex-wrap">
                                                <small class="text-primary"><i class="bi bi-star me-1"></i><?= $step['step_score'] ?> баллов</small>
                                                <small class="text-info"><i class="bi bi-arrow-repeat me-1"></i>Попыток: <?= $step['max_attempts'] ?></small>
                                                <?php if (!empty($step['hint_text'])): ?>
                                                    <small class="text-warning"><i class="bi bi-lightbulb me-1"></i>Подсказка есть</small>
                                                <?php endif; ?>
                                                <?php if (!empty($step['media_path'])): ?>
                                                    <small class="text-success"><i class="bi bi-image me-1"></i>Медиа</small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="btn-group btn-group-sm ms-3">
                                            <!-- Кнопки перемещения -->
                                            <?php if ($i > 0): ?>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="action" value="move_up">
                                                    <input type="hidden" name="step_id" value="<?= $step['step_id'] ?>">
                                                    <button type="submit" class="btn btn-outline-secondary" title="Вверх"><i class="bi bi-arrow-up"></i></button>
                                                </form>
                                            <?php endif; ?>
                                            <?php if ($i < count($steps) - 1): ?>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="action" value="move_down">
                                                    <input type="hidden" name="step_id" value="<?= $step['step_id'] ?>">
                                                    <button type="submit" class="btn btn-outline-secondary" title="Вниз"><i class="bi bi-arrow-down"></i></button>
                                                </form>
                                            <?php endif; ?>
                                            <button class="btn btn-outline-primary edit-step-btn"
                                                data-id="<?= $step['step_id'] ?>"
                                                data-title="<?= htmlspecialchars($step['title']) ?>"
                                                data-description="<?= htmlspecialchars($step['description']) ?>"
                                                data-hint="<?= htmlspecialchars($step['hint_text'] ?? '') ?>"
                                                data-score="<?= $step['step_score'] ?>"
                                                data-attempts="<?= $step['max_attempts'] ?>"
                                                data-order="<?= $step['step_order'] ?>"
                                                data-bs-toggle="modal" data-bs-target="#editStepModal" title="Редактировать">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <form method="POST" class="d-inline" onsubmit="return confirm('Удалить этот этап?')">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="step_id" value="<?= $step['step_id'] ?>">
                                                <button type="submit" class="btn btn-outline-danger" title="Удалить"><i class="bi bi-trash"></i></button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Модальное окно: Создание этапа -->
    <div class="modal fade" id="createStepModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="create">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Новый этап</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Название этапа *</label>
                            <input type="text" name="title" class="form-control" required maxlength="255">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Текст задания *</label>
                            <textarea name="description" class="form-control" rows="4" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Правильный ответ * <small class="text-muted">(будет захэширован)</small></label>
                            <input type="text" name="solution" class="form-control" required>
                            <small class="text-muted">Ответ сравнивается без учёта регистра</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Подсказка</label>
                            <textarea name="hint_text" class="form-control" rows="2"></textarea>
                            <small class="text-muted">Участник получает штраф -20 баллов за использование подсказки</small>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Баллы за этап</label>
                                <input type="number" name="step_score" class="form-control" value="100" min="10" max="1000">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Макс. попыток</label>
                                <input type="number" name="max_attempts" class="form-control" value="3" min="1" max="10">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Изображение к заданию</label>
                            <input type="file" name="media" class="form-control" accept="image/*">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check me-1"></i>Добавить</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Модальное окно: Редактирование этапа -->
    <div class="modal fade" id="editStepModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="step_id" id="editStepId">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Редактирование этапа</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-9 mb-3">
                                <label class="form-label">Название этапа *</label>
                                <input type="text" name="title" id="editStepTitle" class="form-control" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Порядок</label>
                                <input type="number" name="step_order" id="editStepOrder" class="form-control" min="1">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Текст задания *</label>
                            <textarea name="description" id="editStepDescription" class="form-control" rows="4" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Новый ответ <small class="text-muted">(оставьте пустым, чтобы не менять)</small></label>
                            <input type="text" name="solution" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Подсказка</label>
                            <textarea name="hint_text" id="editStepHint" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Баллы</label>
                                <input type="number" name="step_score" id="editStepScore" class="form-control" min="10">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Макс. попыток</label>
                                <input type="number" name="max_attempts" id="editStepAttempts" class="form-control" min="1">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Обновить изображение</label>
                            <input type="file" name="media" class="form-control" accept="image/*">
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
        document.querySelectorAll('.edit-step-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('editStepId').value = this.dataset.id;
                document.getElementById('editStepTitle').value = this.dataset.title;
                document.getElementById('editStepDescription').value = this.dataset.description;
                document.getElementById('editStepHint').value = this.dataset.hint;
                document.getElementById('editStepScore').value = this.dataset.score;
                document.getElementById('editStepAttempts').value = this.dataset.attempts;
                document.getElementById('editStepOrder').value = this.dataset.order;
            });
        });
    </script>
</body>
</html>
