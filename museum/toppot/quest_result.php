<?php
session_start();
require 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$session_id = filter_input(INPUT_GET, 'session_id', FILTER_VALIDATE_INT);
if (!$session_id) {
    header("Location: quests.php");
    exit();
}

// Получаем сессию
$stmt = $db->prepare("SELECT ps.*, q.title as quest_title, q.description as quest_description, 
    (SELECT COUNT(*) FROM quest_steps WHERE quest_id = q.quest_id) as total_steps,
    (SELECT SUM(step_score) FROM quest_steps WHERE quest_id = q.quest_id) as max_possible_score
    FROM player_sessions ps 
    JOIN quests q ON ps.quest_id = q.quest_id 
    WHERE ps.player_session_id = ? AND ps.player_id = ?");
$stmt->execute([$session_id, $_SESSION['user_id']]);
$session = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$session) {
    header("Location: quests.php");
    exit();
}

// Получаем события
$stmt = $db->prepare("SELECT se.*, qs.title as step_title, qs.step_order 
    FROM session_events se 
    JOIN quest_steps qs ON se.related_step_id = qs.step_id 
    WHERE se.player_session_id = ? 
    ORDER BY se.created_at ASC");
$stmt->execute([$session_id]);
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Считаем статистику
$correct_answers = 0;
$hints_used = 0;
$total_attempts = 0;
foreach ($events as $e) {
    if ($e['event_type'] === 'step_completed') $correct_answers++;
    if ($e['event_type'] === 'hint_used') $hints_used++;
    if ($e['event_type'] === 'solution_attempt') $total_attempts++;
}

$duration = '';
if ($session['start_time'] && $session['end_time']) {
    $start = new DateTime($session['start_time']);
    $end = new DateTime($session['end_time']);
    $diff = $start->diff($end);
    $duration = $diff->format('%i мин %s сек');
}

$score_percentage = $session['max_possible_score'] > 0 
    ? round(($session['session_score'] / $session['max_possible_score']) * 100) 
    : 0;
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Результат квеста</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root { --accent-color: #4fc3f7; --secondary-color: #166088; }
        body { font-family: 'Segoe UI', Tahoma, sans-serif; background: linear-gradient(135deg, #1a237e, #0d47a1); min-height: 100vh; display: flex; flex-direction: column; }
        .navbar-custom { background-color: rgba(74, 111, 165, 0.7); backdrop-filter: blur(10px); padding: 10px 0; }
        .navbar-custom .nav-link { color: white; font-weight: 500; padding: 0.5rem 1rem; border-radius: 4px; transition: all 0.3s; }
        .navbar-custom .nav-link:hover { background-color: rgba(255,255,255,0.2); }
        .result-container { max-width: 700px; margin: 40px auto; flex: 1; }
        .result-card { background: white; border-radius: 20px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); padding: 3rem; text-align: center; }
        .trophy-icon { font-size: 5rem; margin-bottom: 1rem; }
        .score-big { font-size: 3.5rem; font-weight: 700; background: linear-gradient(135deg, var(--accent-color), var(--secondary-color)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .stat-card { background: #f8f9fa; border-radius: 12px; padding: 1.2rem; text-align: center; }
        .stat-value { font-size: 1.5rem; font-weight: 700; color: var(--secondary-color); }
        .stat-label { font-size: 0.85rem; color: #888; }
        .btn-back { background: linear-gradient(135deg, var(--accent-color), var(--secondary-color)); border: none; border-radius: 50px; padding: 0.8rem 2.5rem; font-weight: 600; color: white; transition: all 0.3s; }
        .btn-back:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.2); color: white; }
        .footer { background-color: #343a40; margin-top: auto; }
    </style>
</head>
<body>
    <?php include 'nav_header.php'; ?>

    <div class="result-container px-3">
        <div class="result-card">
            <?php if ($score_percentage >= 80): ?>
                <div class="trophy-icon text-warning"><i class="bi bi-trophy-fill"></i></div>
                <h2 class="fw-bold mb-2">Отлично!</h2>
            <?php elseif ($score_percentage >= 50): ?>
                <div class="trophy-icon text-primary"><i class="bi bi-award-fill"></i></div>
                <h2 class="fw-bold mb-2">Хороший результат!</h2>
            <?php else: ?>
                <div class="trophy-icon text-secondary"><i class="bi bi-emoji-smile"></i></div>
                <h2 class="fw-bold mb-2">Квест завершён!</h2>
            <?php endif; ?>

            <p class="text-muted mb-3"><?= htmlspecialchars($session['quest_title']) ?></p>
            
            <div class="score-big mb-4"><?= max(0, (int)$session['session_score']) ?> баллов</div>
            
            <div class="row g-3 mb-4">
                <div class="col-4">
                    <div class="stat-card">
                        <div class="stat-value"><?= $correct_answers ?>/<?= (int)$session['total_steps'] ?></div>
                        <div class="stat-label">Правильных</div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="stat-card">
                        <div class="stat-value"><?= $hints_used ?></div>
                        <div class="stat-label">Подсказок</div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="stat-card">
                        <div class="stat-value"><?= $duration ?: '—' ?></div>
                        <div class="stat-label">Время</div>
                    </div>
                </div>
            </div>
            
            <div class="mb-4">
                <div class="progress" style="height: 10px; border-radius: 5px;">
                    <div class="progress-bar bg-success" style="width: <?= $score_percentage ?>%; border-radius: 5px;"></div>
                </div>
                <small class="text-muted"><?= $score_percentage ?>% от максимального балла</small>
            </div>

            <div class="d-flex justify-content-center gap-3">
                <a href="quests.php" class="btn btn-back"><i class="bi bi-arrow-left me-1"></i>К квестам</a>
                <a href="dashboard.php" class="btn btn-outline-secondary rounded-pill px-4"><i class="bi bi-person me-1"></i>Профиль</a>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
