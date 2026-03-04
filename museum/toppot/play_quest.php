<?php
session_start();
require 'db_connection.php';
require 'functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$quest_id = filter_input(INPUT_GET, 'quest_id', FILTER_VALIDATE_INT);
if (!$quest_id) {
    header("Location: quests.php");
    exit();
}

// Получаем квест
$stmt = $db->prepare("SELECT * FROM quests WHERE quest_id = ? AND is_active = 1");
$stmt->execute([$quest_id]);
$quest = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$quest) {
    header("Location: quests.php");
    exit();
}

// Получаем все шаги квеста
$stmt = $db->prepare("SELECT * FROM quest_steps WHERE quest_id = ? ORDER BY step_order ASC");
$stmt->execute([$quest_id]);
$steps = $stmt->fetchAll(PDO::FETCH_ASSOC);
$total_steps = count($steps);

if ($total_steps === 0) {
    $_SESSION['error_message'] = "Квест пока не содержит этапов.";
    header("Location: quests.php");
    exit();
}

// Ищем или создаём сессию
$stmt = $db->prepare("SELECT * FROM player_sessions WHERE player_id = ? AND quest_id = ? AND status = 'in_progress' ORDER BY start_time DESC LIMIT 1");
$stmt->execute([$user_id, $quest_id]);
$session = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$session) {
    // Проверяем, нет ли уже пройденного квеста
    $stmt = $db->prepare("SELECT * FROM player_sessions WHERE player_id = ? AND quest_id = ? AND status = 'completed' ORDER BY start_time DESC LIMIT 1");
    $stmt->execute([$user_id, $quest_id]);
    $completed_session = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Создаём новую сессию
    $first_step = $steps[0];
    $stmt = $db->prepare("INSERT INTO player_sessions (player_id, quest_id, session_score, current_step_id, status) VALUES (?, ?, 0, ?, 'in_progress')");
    $stmt->execute([$user_id, $quest_id, $first_step['step_id']]);
    $session_id = $db->lastInsertId();
    
    // Записываем событие начала
    $stmt = $db->prepare("INSERT INTO session_events (player_session_id, event_type, related_step_id, event_data) VALUES (?, 'step_started', ?, 'Квест начат')");
    $stmt->execute([$session_id, $first_step['step_id']]);
    
    $session = [
        'player_session_id' => $session_id,
        'session_score' => 0,
        'current_step_id' => $first_step['step_id'],
        'status' => 'in_progress'
    ];
}

$session_id = $session['player_session_id'];

// Обработка отправки ответа
$feedback = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        $step_id = filter_input(INPUT_POST, 'step_id', FILTER_VALIDATE_INT);
        
        // Получаем текущий шаг
        $stmt = $db->prepare("SELECT * FROM quest_steps WHERE step_id = ?");
        $stmt->execute([$step_id]);
        $current_step = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($action === 'answer' && $current_step) {
            $answer = mb_strtolower(trim($_POST['answer'] ?? ''));
            
            // Считаем попытки на этом шаге
            $stmt = $db->prepare("SELECT COUNT(*) FROM session_events WHERE player_session_id = ? AND related_step_id = ? AND event_type = 'solution_attempt'");
            $stmt->execute([$session_id, $step_id]);
            $attempt_count = (int)$stmt->fetchColumn();
            
            // Проверяем лимит попыток
            if ($attempt_count >= $current_step['max_attempts']) {
                $feedback = ['type' => 'danger', 'message' => 'Попытки исчерпаны. Переход к следующему этапу.'];
                // Переходим к следующему шагу
                $next = null;
                foreach ($steps as $i => $s) {
                    if ($s['step_id'] == $step_id && isset($steps[$i + 1])) {
                        $next = $steps[$i + 1];
                        break;
                    }
                }
                if ($next) {
                    $stmt = $db->prepare("UPDATE player_sessions SET current_step_id = ? WHERE player_session_id = ?");
                    $stmt->execute([$next['step_id'], $session_id]);
                    $session['current_step_id'] = $next['step_id'];
                    $stmt = $db->prepare("INSERT INTO session_events (player_session_id, event_type, related_step_id) VALUES (?, 'step_started', ?)");
                    $stmt->execute([$session_id, $next['step_id']]);
                } else {
                    // Квест завершён
                    $stmt = $db->prepare("UPDATE player_sessions SET status = 'completed', end_time = datetime('now','localtime') WHERE player_session_id = ?");
                    $stmt->execute([$session_id]);
                    // Обновляем total_score
                    $stmt = $db->prepare("SELECT session_score FROM player_sessions WHERE player_session_id = ?");
                    $stmt->execute([$session_id]);
                    $final_score = (int)$stmt->fetchColumn();
                    $stmt = $db->prepare("UPDATE Registr SET total_score = COALESCE(total_score, 0) + ? WHERE id = ?");
                    $stmt->execute([$final_score, $user_id]);
                    header("Location: quest_result.php?session_id=" . $session_id);
                    exit();
                }
            } else {
                // Записываем попытку
                $stmt = $db->prepare("INSERT INTO session_events (player_session_id, event_type, related_step_id, event_data) VALUES (?, 'solution_attempt', ?, ?)");
                $stmt->execute([$session_id, $step_id, $answer]);
                
                // Проверяем ответ
                $is_correct = password_verify($answer, $current_step['solution_hash']);
                
                if ($is_correct) {
                    // Правильный ответ — начисляем баллы
                    $score = $current_step['step_score'];
                    if ($attempt_count > 0) {
                        $score = max(10, (int)($score * (1 - $attempt_count * 0.3))); // Меньше баллов за повторные попытки
                    }
                    
                    // Записываем событие и баллы
                    $stmt = $db->prepare("INSERT INTO session_events (player_session_id, event_type, related_step_id, score_delta, event_data) VALUES (?, 'step_completed', ?, ?, 'Правильный ответ')");
                    $stmt->execute([$session_id, $step_id, $score]);
                    
                    $stmt = $db->prepare("UPDATE player_sessions SET session_score = session_score + ? WHERE player_session_id = ?");
                    $stmt->execute([$score, $session_id]);
                    $session['session_score'] += $score;
                    
                    $feedback = ['type' => 'success', 'message' => "Правильно! +$score баллов"];
                    
                    // Переход к следующему шагу
                    $next = null;
                    foreach ($steps as $i => $s) {
                        if ($s['step_id'] == $step_id && isset($steps[$i + 1])) {
                            $next = $steps[$i + 1];
                            break;
                        }
                    }
                    if ($next) {
                        $stmt = $db->prepare("UPDATE player_sessions SET current_step_id = ? WHERE player_session_id = ?");
                        $stmt->execute([$next['step_id'], $session_id]);
                        $session['current_step_id'] = $next['step_id'];
                        $stmt = $db->prepare("INSERT INTO session_events (player_session_id, event_type, related_step_id) VALUES (?, 'step_started', ?)");
                        $stmt->execute([$session_id, $next['step_id']]);
                    } else {
                        // Квест завершён!
                        $stmt = $db->prepare("UPDATE player_sessions SET status = 'completed', end_time = datetime('now','localtime') WHERE player_session_id = ?");
                        $stmt->execute([$session_id]);
                        $stmt = $db->prepare("SELECT session_score FROM player_sessions WHERE player_session_id = ?");
                        $stmt->execute([$session_id]);
                        $final_score = (int)$stmt->fetchColumn();
                        $stmt = $db->prepare("UPDATE Registr SET total_score = COALESCE(total_score, 0) + ? WHERE id = ?");
                        $stmt->execute([$final_score, $user_id]);
                        header("Location: quest_result.php?session_id=" . $session_id);
                        exit();
                    }
                } else {
                    $remaining = $current_step['max_attempts'] - $attempt_count - 1;
                    $feedback = ['type' => 'warning', 'message' => "Неверный ответ. Осталось попыток: $remaining"];
                }
            }
        } elseif ($action === 'hint' && $current_step) {
            // Использование подсказки
            $stmt = $db->prepare("SELECT COUNT(*) FROM session_events WHERE player_session_id = ? AND related_step_id = ? AND event_type = 'hint_used'");
            $stmt->execute([$session_id, $step_id]);
            $hint_used = (int)$stmt->fetchColumn() > 0;
            
            if (!$hint_used && !empty($current_step['hint_text'])) {
                $penalty = -20;
                $stmt = $db->prepare("INSERT INTO session_events (player_session_id, event_type, related_step_id, score_delta, event_data) VALUES (?, 'hint_used', ?, ?, ?)");
                $stmt->execute([$session_id, $step_id, $penalty, $current_step['hint_text']]);
                $stmt = $db->prepare("UPDATE player_sessions SET session_score = session_score + ? WHERE player_session_id = ?");
                $stmt->execute([$penalty, $session_id]);
                $session['session_score'] += $penalty;
            }
            // Подсказку покажем в интерфейсе через JS
        }
    }
    
    // Перечитываем сессию
    $stmt = $db->prepare("SELECT * FROM player_sessions WHERE player_session_id = ?");
    $stmt->execute([$session_id]);
    $session = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Определяем текущий шаг
$current_step = null;
$current_step_index = 0;
foreach ($steps as $i => $s) {
    if ($s['step_id'] == $session['current_step_id']) {
        $current_step = $s;
        $current_step_index = $i;
        break;
    }
}

// Подсчёт попыток текущего шага
$attempts_on_step = 0;
$hint_shown = false;
$hint_text = '';
if ($current_step) {
    $stmt = $db->prepare("SELECT COUNT(*) FROM session_events WHERE player_session_id = ? AND related_step_id = ? AND event_type = 'solution_attempt'");
    $stmt->execute([$session_id, $current_step['step_id']]);
    $attempts_on_step = (int)$stmt->fetchColumn();
    
    $stmt = $db->prepare("SELECT event_data FROM session_events WHERE player_session_id = ? AND related_step_id = ? AND event_type = 'hint_used' LIMIT 1");
    $stmt->execute([$session_id, $current_step['step_id']]);
    $hint_row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($hint_row) {
        $hint_shown = true;
        $hint_text = $hint_row['event_data'];
    }
}

$progress = $total_steps > 0 ? round(($current_step_index / $total_steps) * 100) : 0;
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($quest['title']) ?> - Квест</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root { --primary-color: rgba(74, 111, 165, 0.7); --secondary-color: #166088; --accent-color: #4fc3f7; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .navbar-custom { background-color: var(--primary-color); backdrop-filter: blur(10px); padding: 10px 0; }
        .navbar-custom .nav-link { color: white; font-weight: 500; padding: 0.5rem 1rem; border-radius: 4px; transition: all 0.3s; }
        .navbar-custom .nav-link:hover { background-color: rgba(255,255,255,0.2); }
        .quest-container { max-width: 800px; margin: 30px auto; }
        .score-panel { background: rgba(255,255,255,0.15); backdrop-filter: blur(10px); border-radius: 15px; padding: 1.5rem; color: white; margin-bottom: 1.5rem; }
        .score-value { font-size: 2rem; font-weight: 700; }
        .progress-bar-custom { height: 8px; border-radius: 4px; background: rgba(255,255,255,0.2); overflow: hidden; }
        .progress-bar-fill { height: 100%; border-radius: 4px; background: linear-gradient(90deg, #4fc3f7, #00e676); transition: width 0.5s ease; }
        .step-card { background: white; border-radius: 20px; box-shadow: 0 15px 40px rgba(0,0,0,0.2); padding: 2.5rem; margin-bottom: 2rem; }
        .step-number { display: inline-flex; align-items: center; justify-content: center; width: 45px; height: 45px; border-radius: 50%; background: linear-gradient(135deg, var(--accent-color), var(--secondary-color)); color: white; font-weight: 700; font-size: 1.2rem; margin-right: 1rem; }
        .step-title { font-size: 1.5rem; font-weight: 700; color: #333; }
        .step-description { font-size: 1.1rem; color: #555; line-height: 1.8; margin: 1.5rem 0; padding: 1.5rem; background: #f8f9fa; border-radius: 12px; border-left: 4px solid var(--accent-color); }
        .answer-input { border: 2px solid #e0e0e0; border-radius: 12px; padding: 1rem 1.5rem; font-size: 1.1rem; transition: all 0.3s; }
        .answer-input:focus { border-color: var(--accent-color); box-shadow: 0 0 0 0.2rem rgba(79,195,247,0.25); }
        .btn-submit { background: linear-gradient(135deg, var(--accent-color), var(--secondary-color)); border: none; border-radius: 50px; padding: 0.8rem 2.5rem; font-weight: 600; color: white; font-size: 1.1rem; transition: all 0.3s; }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 5px 20px rgba(0,0,0,0.2); color: white; }
        .btn-hint { background: transparent; border: 2px solid #ffc107; border-radius: 50px; padding: 0.6rem 1.5rem; font-weight: 600; color: #ffc107; transition: all 0.3s; }
        .btn-hint:hover { background: #ffc107; color: #333; }
        .hint-box { background: #fff3cd; border-radius: 12px; padding: 1.2rem; margin-top: 1rem; border: 1px solid #ffc107; }
        .steps-nav { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 1.5rem; }
        .step-dot { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 0.85rem; transition: all 0.3s; }
        .step-dot.completed { background: #28a745; color: white; }
        .step-dot.current { background: var(--accent-color); color: white; box-shadow: 0 0 0 4px rgba(79,195,247,0.3); }
        .step-dot.pending { background: #e9ecef; color: #999; }
        .step-media { max-width: 100%; border-radius: 12px; margin: 1rem 0; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
    <?php include 'nav_header.php'; ?>

    <div class="quest-container px-3">
        <!-- Панель счёта -->
        <div class="score-panel">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <small class="d-block opacity-75">Квест</small>
                    <strong><?= htmlspecialchars($quest['title']) ?></strong>
                </div>
                <div class="text-end">
                    <small class="d-block opacity-75">Баллы сессии</small>
                    <span class="score-value"><?= max(0, (int)$session['session_score']) ?></span>
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-2">
                <small class="opacity-75">Этап <?= $current_step_index + 1 ?> из <?= $total_steps ?></small>
                <small class="opacity-75"><?= $progress ?>%</small>
            </div>
            <div class="progress-bar-custom">
                <div class="progress-bar-fill" style="width: <?= $progress ?>%"></div>
            </div>
        </div>

        <!-- Навигация по шагам -->
        <div class="steps-nav">
            <?php foreach ($steps as $i => $s): ?>
                <?php
                    $class = 'pending';
                    if ($i < $current_step_index) $class = 'completed';
                    elseif ($i === $current_step_index) $class = 'current';
                ?>
                <div class="step-dot <?= $class ?>"><?= $i + 1 ?></div>
            <?php endforeach; ?>
        </div>

        <!-- Текущий шаг -->
        <?php if ($current_step): ?>
            <div class="step-card">
                <?php if ($feedback): ?>
                    <div class="alert alert-<?= $feedback['type'] ?> alert-dismissible fade show" role="alert">
                        <?php if ($feedback['type'] === 'success'): ?>
                            <i class="bi bi-check-circle-fill me-2"></i>
                        <?php elseif ($feedback['type'] === 'warning'): ?>
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <?php else: ?>
                            <i class="bi bi-x-circle-fill me-2"></i>
                        <?php endif; ?>
                        <?= htmlspecialchars($feedback['message']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="d-flex align-items-center mb-3">
                    <span class="step-number"><?= $current_step_index + 1 ?></span>
                    <h3 class="step-title mb-0"><?= htmlspecialchars($current_step['title']) ?></h3>
                </div>
                
                <div class="step-description">
                    <?= nl2br(htmlspecialchars($current_step['description'])) ?>
                </div>
                
                <?php if (!empty($current_step['media_path']) && file_exists($current_step['media_path'])): ?>
                    <img src="<?= htmlspecialchars($current_step['media_path']) ?>" class="step-media" alt="Медиа к заданию">
                <?php endif; ?>
                
                <div class="mb-3">
                    <small class="text-muted">
                        Попытка <?= $attempts_on_step + 1 ?> из <?= $current_step['max_attempts'] ?>
                        | Баллы за этап: <?= $current_step['step_score'] ?>
                    </small>
                </div>
                
                <form method="POST" class="mb-3">
                    <input type="hidden" name="action" value="answer">
                    <input type="hidden" name="step_id" value="<?= $current_step['step_id'] ?>">
                    <div class="input-group mb-3">
                        <input type="text" class="form-control answer-input" name="answer" placeholder="Введите ваш ответ..." required autofocus>
                        <button type="submit" class="btn btn-submit"><i class="bi bi-send me-1"></i>Ответить</button>
                    </div>
                </form>
                
                <?php if (!empty($current_step['hint_text'])): ?>
                    <?php if ($hint_shown): ?>
                        <div class="hint-box">
                            <strong><i class="bi bi-lightbulb me-1"></i>Подсказка:</strong>
                            <p class="mb-0 mt-1"><?= htmlspecialchars($hint_text) ?></p>
                        </div>
                    <?php else: ?>
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="action" value="hint">
                            <input type="hidden" name="step_id" value="<?= $current_step['step_id'] ?>">
                            <button type="submit" class="btn btn-hint" onclick="return confirm('Использование подсказки: штраф -20 баллов. Продолжить?')">
                                <i class="bi bi-lightbulb me-1"></i>Подсказка (-20 б.)
                            </button>
                        </form>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <div class="text-center mb-4">
            <a href="quests.php" class="btn btn-outline-light"><i class="bi bi-arrow-left me-1"></i>Вернуться к квестам</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
