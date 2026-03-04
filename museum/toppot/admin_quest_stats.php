<?php
session_start();
require 'db_connection.php';
require 'functions.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: login.php");
    exit();
}

$quest_id = filter_input(INPUT_GET, 'quest_id', FILTER_VALIDATE_INT);

// Общая статистика по всем квестам
$stmt = $db->query("SELECT q.quest_id, q.title, q.difficulty_level,
    (SELECT COUNT(*) FROM player_sessions ps WHERE ps.quest_id = q.quest_id) as total_sessions,
    (SELECT COUNT(*) FROM player_sessions ps WHERE ps.quest_id = q.quest_id AND ps.status = 'completed') as completed,
    (SELECT AVG(ps.session_score) FROM player_sessions ps WHERE ps.quest_id = q.quest_id AND ps.status = 'completed') as avg_score,
    (SELECT AVG(CAST((julianday(ps.end_time) - julianday(ps.start_time)) * 1440 AS INTEGER)) FROM player_sessions ps WHERE ps.quest_id = q.quest_id AND ps.status = 'completed' AND ps.end_time IS NOT NULL) as avg_time
    FROM quests q ORDER BY q.created_at DESC");
$quests_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Детальная статистика по конкретному квесту
$quest_detail = null;
$step_stats = [];
$recent_sessions = [];
if ($quest_id) {
    $stmt = $db->prepare("SELECT * FROM quests WHERE quest_id = ?");
    $stmt->execute([$quest_id]);
    $quest_detail = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($quest_detail) {
        // Статистика по шагам
        $stmt = $db->prepare("SELECT qs.step_id, qs.title, qs.step_order, qs.step_score,
            (SELECT COUNT(*) FROM session_events se JOIN player_sessions ps ON se.player_session_id = ps.player_session_id WHERE se.related_step_id = qs.step_id AND se.event_type = 'step_completed') as completions,
            (SELECT COUNT(*) FROM session_events se JOIN player_sessions ps ON se.player_session_id = ps.player_session_id WHERE se.related_step_id = qs.step_id AND se.event_type = 'solution_attempt') as total_attempts,
            (SELECT COUNT(*) FROM session_events se WHERE se.related_step_id = qs.step_id AND se.event_type = 'hint_used') as hints_used
            FROM quest_steps qs WHERE qs.quest_id = ? ORDER BY qs.step_order");
        $stmt->execute([$quest_id]);
        $step_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Последние сессии
        $stmt = $db->prepare("SELECT ps.*, r.Login 
            FROM player_sessions ps 
            JOIN Registr r ON ps.player_id = r.id 
            WHERE ps.quest_id = ? 
            ORDER BY ps.start_time DESC LIMIT 20");
        $stmt->execute([$quest_id]);
        $recent_sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Статистика квестов | Админ-панель</title>
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
        .sidebar-link i { margin-right: 10px; }
        .sidebar-divider { border-top: 1px solid rgba(255,255,255,0.2); margin: 15px 20px; }
        .sidebar-heading { padding: 0 25px; margin-top: 20px; font-size: 0.8rem; color: rgba(255,255,255,0.6); text-transform: uppercase; letter-spacing: 1px; }
        .card { border: none; border-radius: 10px; box-shadow: 0 0.15rem 1.75rem 0 rgba(58,59,69,0.1); }
        .step-difficulty-bar { height: 8px; border-radius: 4px; background: #e9ecef; overflow: hidden; }
        .step-difficulty-fill { height: 100%; border-radius: 4px; transition: width 0.5s; }
    </style>
</head>
<body>
    <nav id="topbar" class="navbar navbar-expand navbar-light bg-white">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php"><i class="bi bi-house-door me-2"></i>На главную</a>
            <div class="d-flex align-items-center ms-auto">
                <a href="logout.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-box-arrow-right"></i></a>
            </div>
        </div>
    </nav>

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
                <li><a href="admin_quests.php" class="sidebar-link"><i class="bi bi-trophy"></i>Квесты</a></li>
                <li><a href="admin_quest_stats.php" class="sidebar-link active"><i class="bi bi-graph-up"></i>Статистика</a></li>
                <div class="sidebar-divider"></div>
                <div class="sidebar-heading">Система</div>
                <li><a href="admin_backup.php" class="sidebar-link"><i class="bi bi-database"></i>Резервные копии</a></li>
                <li><a href="admin_logs.php" class="sidebar-link"><i class="bi bi-journal-text"></i>Логи</a></li>
            </ul>
        </div>
    </div>

    <div id="content">
        <div class="container-fluid">
            <h1 class="h3 mb-4"><i class="bi bi-graph-up me-2"></i>Статистика квестов</h1>
            
            <!-- Общая таблица -->
            <div class="card mb-4">
                <div class="card-header bg-white py-3"><h6 class="m-0 fw-bold">Обзор всех квестов</h6></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Квест</th>
                                    <th>Сложность</th>
                                    <th>Сессий</th>
                                    <th>Завершено</th>
                                    <th>% завершения</th>
                                    <th>Ср. балл</th>
                                    <th>Ср. время</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($quests_stats as $qs): ?>
                                    <?php $rate = $qs['total_sessions'] > 0 ? round($qs['completed'] / $qs['total_sessions'] * 100) : 0; ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($qs['title']) ?></strong></td>
                                        <td><span class="badge bg-<?= ['easy'=>'success','medium'=>'warning','hard'=>'danger'][$qs['difficulty_level']] ?>"><?= ['easy'=>'Лёгкий','medium'=>'Средний','hard'=>'Сложный'][$qs['difficulty_level']] ?></span></td>
                                        <td><?= $qs['total_sessions'] ?></td>
                                        <td><?= $qs['completed'] ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="step-difficulty-bar flex-grow-1 me-2" style="width:80px">
                                                    <div class="step-difficulty-fill bg-<?= $rate >= 70 ? 'success' : ($rate >= 40 ? 'warning' : 'danger') ?>" style="width:<?= $rate ?>%"></div>
                                                </div>
                                                <small><?= $rate ?>%</small>
                                            </div>
                                        </td>
                                        <td><?= $qs['avg_score'] ? round($qs['avg_score']) : '—' ?></td>
                                        <td><?= $qs['avg_time'] ? round($qs['avg_time']) . ' мин' : '—' ?></td>
                                        <td><a href="?quest_id=<?= $qs['quest_id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <?php if ($quest_detail): ?>
                <h2 class="h4 mb-3">Детали: <?= htmlspecialchars($quest_detail['title']) ?></h2>
                
                <!-- Статистика по шагам -->
                <div class="card mb-4">
                    <div class="card-header bg-white py-3"><h6 class="m-0 fw-bold">Статистика по этапам</h6></div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Этап</th>
                                        <th>Баллы</th>
                                        <th>Прохождений</th>
                                        <th>Попыток</th>
                                        <th>Подсказок</th>
                                        <th>Ср. попыток</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($step_stats as $ss): ?>
                                        <tr>
                                            <td><?= $ss['step_order'] ?></td>
                                            <td><?= htmlspecialchars($ss['title']) ?></td>
                                            <td><?= $ss['step_score'] ?></td>
                                            <td><?= $ss['completions'] ?></td>
                                            <td><?= $ss['total_attempts'] ?></td>
                                            <td><?= $ss['hints_used'] ?></td>
                                            <td><?= $ss['completions'] > 0 ? round($ss['total_attempts'] / $ss['completions'], 1) : '—' ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Последние сессии -->
                <div class="card mb-4">
                    <div class="card-header bg-white py-3"><h6 class="m-0 fw-bold">Последние прохождения</h6></div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Участник</th>
                                        <th>Статус</th>
                                        <th>Баллы</th>
                                        <th>Начало</th>
                                        <th>Конец</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_sessions as $rs): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($rs['Login']) ?></td>
                                            <td><span class="badge bg-<?= $rs['status'] === 'completed' ? 'success' : ($rs['status'] === 'in_progress' ? 'primary' : 'secondary') ?>"><?= ['completed'=>'Завершён','in_progress'=>'В процессе','abandoned'=>'Прерван'][$rs['status']] ?></span></td>
                                            <td><?= $rs['session_score'] ?></td>
                                            <td><?= date('d.m.Y H:i', strtotime($rs['start_time'])) ?></td>
                                            <td><?= $rs['end_time'] ? date('d.m.Y H:i', strtotime($rs['end_time'])) : '—' ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
