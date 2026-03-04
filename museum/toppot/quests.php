<?php
session_start();
require 'db_connection.php';

try {
    $stmt = $db->query("SELECT q.*, 
        (SELECT COUNT(*) FROM quest_steps WHERE quest_id = q.quest_id) as step_count,
        (SELECT COUNT(*) FROM player_sessions WHERE quest_id = q.quest_id AND status = 'completed') as completions
        FROM quests q WHERE q.is_active = 1 ORDER BY q.created_at DESC");
    $quests = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $quests = [];
}

$user_sessions = [];
if (isset($_SESSION['user_id'])) {
    try {
        $stmt = $db->prepare("SELECT quest_id, status, session_score FROM player_sessions WHERE player_id = ? ORDER BY start_time DESC");
        $stmt->execute([$_SESSION['user_id']]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $s) {
            if (!isset($user_sessions[$s['quest_id']])) {
                $user_sessions[$s['quest_id']] = $s;
            }
        }
    } catch (PDOException $e) {}
}

try {
    $stmt = $db->query("SELECT r.Login, r.avatar, COALESCE(r.total_score, 0) as total_score 
        FROM Registr r WHERE COALESCE(r.total_score, 0) > 0 ORDER BY r.total_score DESC LIMIT 5");
    $leaderboard = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $leaderboard = [];
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Квесты - Виртуальный музей</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .quest-card {
            position: relative;
            overflow: hidden;
        }
        
        .quest-card::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--gradient-dark);
            opacity: 0;
            transition: opacity 0.3s ease;
            pointer-events: none;
        }
        
        .quest-card:hover::after {
            opacity: 0.1;
        }
        
        .difficulty-bar {
            height: 4px;
            border-radius: 2px;
            margin-top: 1rem;
            overflow: hidden;
        }
        
        .difficulty-bar-easy { background: var(--gradient-green); }
        .difficulty-bar-medium { background: var(--gradient-gold); }
        .difficulty-bar-hard { background: var(--gradient-pink); }
        
        .quest-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .leaderboard-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            overflow: hidden;
        }
        
        .leaderboard-header {
            background: var(--gradient-dark);
            padding: 1.5rem;
            border-bottom: 1px solid var(--border-color);
        }
        
        .leaderboard-item {
            display: flex;
            align-items: center;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--border-color);
            transition: all 0.3s ease;
        }
        
        .leaderboard-item:hover {
            background: rgba(139, 0, 255, 0.1);
        }
        
        .leaderboard-item:last-child {
            border-bottom: none;
        }
        
        .rank-badge {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            margin-right: 1rem;
        }
        
        .rank-1 { background: var(--gradient-gold); transform: scale(1.1); }
        .rank-2 { background: linear-gradient(135deg, #C0C0C0, #A0A0A0); transform: scale(1.05); }
        .rank-3 { background: linear-gradient(135deg, #CD7F32, #A0522D); }
        .rank-other { background: rgba(139, 0, 255, 0.2); }
    </style>
</head>
<body>
    <?php include 'nav_header.php'; ?>

    <section class="hero-section" style="padding: 6rem 0 4rem;">
        <div class="container hero-content text-center">
            <h1 class="display-4 fw-bold mb-3">
                <i class="bi bi-trophy-fill text-warning me-3"></i>
                <span class="text-gradient">Интерактивные Квесты</span>
            </h1>
            <p class="lead text-white opacity-75 mb-0">
                Проверьте свои знания об истории колледжа в увлекательном игровом формате
            </p>
        </div>
    </section>

    <div class="container py-5">
        <div class="row">
            <!-- Квесты -->
            <div class="col-lg-8 mb-4">
                <?php if (empty($quests)): ?>
                    <div class="alert alert-info text-center rounded-4">
                        <i class="bi bi-inbox display-4 d-block mb-3"></i>
                        <h5 class="mb-2">Квесты пока не добавлены</h5>
                        <p class="mb-0">Загляните позже или предложите свой квест!</p>
                    </div>
                <?php else: ?>
                    <div class="row g-4">
                        <?php foreach ($quests as $quest): 
                            $difficulty_class = 'difficulty-' . $quest['difficulty_level'];
                            $difficulty_label = ['easy' => 'Легкий', 'medium' => 'Средний', 'hard' => 'Сложный'][$quest['difficulty_level']];
                            $difficulty_color = ['easy' => '#00E676', 'medium' => '#FFD700', 'hard' => '#FF4081'][$quest['difficulty_level']];
                            $difficulty_icon = ['easy' => 'emoji-smile', 'medium' => 'emoji-neutral', 'hard' => 'emoji-angry'][$quest['difficulty_level']];
                            $session = $user_sessions[$quest['quest_id']] ?? null;
                        ?>
                        <div class="col-md-6">
                            <div class="card quest-card <?= $difficulty_class ?>">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div class="quest-icon" style="background: linear-gradient(135deg, <?= $difficulty_color ?>, <?= $difficulty_color ?>88);">
                                            <i class="bi bi-puzzle text-white"></i>
                                        </div>
                                        <div>
                                            <span class="badge badge-<?= $quest['difficulty_level'] ?> rounded-pill">
                                                <?= $difficulty_label ?>
                                            </span>
                                        </div>
                                    </div>
                                    <h5 class="card-title fw-bold text-white"><?= htmlspecialchars($quest['title']) ?></h5>
                                    <p class="card-text text-secondary"><?= htmlspecialchars(mb_substr($quest['description'], 0, 120)) ?>...</p>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <small class="text-muted">
                                            <i class="bi bi-clock me-1"></i><?= (int)$quest['duration_minutes'] ?> мин
                                        </small>
                                        <small class="text-muted">
                                            <i class="bi bi-list-ol me-1"></i><?= (int)$quest['step_count'] ?> этапов
                                        </small>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <small class="text-muted">
                                            <i class="bi bi-people me-1"></i><?= (int)$quest['completions'] ?> прохождений
                                        </small>
                                    </div>
                                    <div class="difficulty-bar difficulty-bar-<?= $quest['difficulty_level'] ?>"></div>
                                    <div class="mt-3">
                                        <?php if (!isset($_SESSION['user_id'])): ?>
                                            <a href="login.php" class="btn btn-primary w-100">
                                                <i class="bi bi-box-arrow-in-right me-2"></i>Войти для участия
                                            </a>
                                        <?php elseif ($session && $session['status'] === 'completed'): ?>
                                            <button class="btn btn-success w-100" disabled>
                                                <i class="bi bi-check-circle me-2"></i>Пройден (<?= $session['session_score'] ?>)
                                            </button>
                                        <?php elseif ($session && $session['status'] === 'in_progress'): ?>
                                            <a href="play_quest.php?quest_id=<?= $quest['quest_id'] ?>" class="btn btn-warning w-100">
                                                <i class="bi bi-play-fill me-2"></i>Продолжить
                                            </a>
                                        <?php else: ?>
                                            <a href="play_quest.php?quest_id=<?= $quest['quest_id'] ?>" class="btn btn-primary w-100">
                                                <i class="bi bi-play-fill me-2"></i>Начать квест
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Рейтинг -->
            <div class="col-lg-4">
                <div class="leaderboard-card mb-4">
                    <div class="leaderboard-header">
                        <h5 class="fw-bold mb-0 text-white">
                            <i class="bi bi-star-fill text-warning me-2"></i>ТОП-5 Участников
                        </h5>
                    </div>
                    <?php if (empty($leaderboard)): ?>
                        <div class="p-5 text-center">
                            <i class="bi bi-trophy display-4 text-secondary"></i>
                            <p class="text-secondary mt-3">Пока нет результатов</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($leaderboard as $i => $player): 
                            $rank_class = $i < 3 ? 'rank-'.($i+1) : 'rank-other';
                        ?>
                        <div class="leaderboard-item">
                            <div class="rank-badge <?= $rank_class ?>"><?= $i + 1 ?></div>
                            <?php if (!empty($player['avatar']) && $player['avatar'] !== 'default_avatar.png'): ?>
                                <img src="<?= htmlspecialchars($player['avatar']) ?>" class="avatar avatar-sm" alt="">
                            <?php else: ?>
                                <div class="avatar avatar-sm d-flex align-items-center justify-content-center" style="background:var(--gradient-purple);color:white;font-weight:700;">
                                    <?= mb_strtoupper(mb_substr($player['Login'], 0, 1)) ?>
                                </div>
                            <?php endif; ?>
                            <div class="flex-grow-1 ms-2">
                                <div class="fw-bold text-white"><?= htmlspecialchars($player['Login']) ?></div>
                            </div>
                            <span class="fw-bold" style="color:var(--accent-cyan);">
                                <?= number_format($player['total_score']) ?> <i class="bi bi-star-fill text-warning"></i>
                            </span>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <a href="leaderboard.php" class="btn btn-secondary w-100 mb-4">
                    <i class="bi bi-list-ol me-2"></i>Полный рейтинг
                </a>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php
                        try {
                            $stmt = $db->prepare("SELECT COALESCE(total_score, 0) as score FROM Registr WHERE id = ?");
                            $stmt->execute([$_SESSION['user_id']]);
                            $my_score = $stmt->fetchColumn();
                            
                            $stmt = $db->prepare("SELECT COUNT(*) FROM player_sessions WHERE player_id = ? AND status = 'completed'");
                            $stmt->execute([$_SESSION['user_id']]);
                            $my_completed = $stmt->fetchColumn();
                        } catch (PDOException $e) { $my_score = 0; $my_completed = 0; }
                    ?>
                    <div class="card border-0 mb-4">
                        <div class="card-body">
                            <h5 class="fw-bold mb-4 text-white">
                                <i class="bi bi-person-badge me-2 text-gradient"></i>Ваша статистика
                            </h5>
                            <div class="d-flex align-items-center mb-4">
                                <div style="width:50px;height:50px;border-radius:12px;display:flex;align-items:center;justify-content:center;background:var(--gradient-purple);">
                                    <i class="bi bi-star text-white fs-4"></i>
                                </div>
                                <div class="ms-3">
                                    <div class="text-secondary small">Общий рейтинг</div>
                                    <div class="fw-bold fs-5 text-white"><?= number_format($my_score) ?> баллов</div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center">
                                <div style="width:50px;height:50px;border-radius:12px;display:flex;align-items:center;justify-content:center;background:var(--gradient-green);">
                                    <i class="bi bi-check-circle text-white fs-4"></i>
                                </div>
                                <div class="ms-3">
                                    <div class="text-secondary small">Пройдено квестов</div>
                                    <div class="fw-bold fs-5 text-white"><?= $my_completed ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
