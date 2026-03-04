<?php
session_start();
require 'db_connection.php';

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;
$filter = $_GET['filter'] ?? 'all';

$filter_sql = '';
$filter_title = 'За всё время';
if ($filter === 'week') {
    $filter_sql = "AND date >= date('now', '-7 days')";
    $filter_title = 'За неделю';
} elseif ($filter === 'month') {
    $filter_sql = "AND date >= date('now', '-30 days')";
    $filter_title = 'За месяц';
}

try {
    $total_players = $db->query("SELECT COUNT(*) FROM Registr WHERE COALESCE(total_score, 0) > 0 $filter_sql")->fetchColumn();
    
    $stmt = $db->prepare("SELECT r.id, r.Login, r.avatar, COALESCE(r.total_score, 0) as total_score, r.date,
        (SELECT COUNT(*) FROM player_sessions WHERE player_id = r.id AND status = 'completed') as completed_quests
        FROM Registr r 
        WHERE COALESCE(r.total_score, 0) > 0 $filter_sql
        ORDER BY r.total_score DESC, r.date ASC
        LIMIT ? OFFSET ?");
    $stmt->execute([$per_page, $offset]);
    $players = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $total_pages = ceil($total_players / $per_page);
    
    $stats = $db->query("SELECT 
        COUNT(*) as total_users,
        SUM(COALESCE(total_score, 0)) as total_points,
        AVG(COALESCE(total_score, 0)) as avg_points,
        MAX(COALESCE(total_score, 0)) as max_points,
        (SELECT COUNT(*) FROM quests WHERE is_active = 1) as total_quests,
        (SELECT COUNT(*) FROM player_sessions WHERE status = 'completed') as total_completions
        FROM Registr")->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $players = [];
    $total_players = 0;
    $total_pages = 0;
    $stats = ['total_users' => 0, 'total_points' => 0, 'avg_points' => 0, 'max_points' => 0, 'total_quests' => 0, 'total_completions' => 0];
}

$my_rank = 0;
if (isset($_SESSION['user_id'])) {
    try {
        $stmt = $db->prepare("SELECT COUNT(*) + 1 as rank FROM Registr 
            WHERE COALESCE(total_score, 0) > (SELECT COALESCE(total_score, 0) FROM Registr WHERE id = ?)");
        $stmt->execute([$_SESSION['user_id']]);
        $my_rank = $stmt->fetchColumn();
    } catch (PDOException $e) {}
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Лидерборд - Виртуальный музей</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --bg-primary: #0f0f1a;
            --bg-secondary: #1a1a2e;
            --bg-card: #16162a;
            --accent-purple: #8B00FF;
            --accent-purple-light: #7C4DFF;
            --accent-cyan: #00d4ff;
            --text-primary: #ffffff;
            --text-secondary: #a0a0b0;
            --gradient-dark: linear-gradient(135deg, #191919 0%, #423189 100%);
            --gradient-purple: linear-gradient(135deg, #8B00FF 0%, #7C4DFF 100%);
            --gradient-cyan: linear-gradient(135deg, #00d4ff 0%, #0099cc 100%);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
        }
        .navbar-custom {
            background: rgba(15, 15, 26, 0.95);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(139, 0, 255, 0.2);
        }
        .hero-section {
            background: var(--gradient-dark);
            padding: 6rem 0 4rem;
            position: relative;
            overflow: hidden;
        }
        .hero-section::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 800px;
            height: 800px;
            background: radial-gradient(circle, rgba(139, 0, 255, 0.3) 0%, transparent 70%);
            border-radius: 50%;
        }
        .hero-section::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -10%;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(124, 77, 255, 0.2) 0%, transparent 70%);
            border-radius: 50%;
        }
        .hero-content { position: relative; z-index: 1; }
        .title-gradient {
            background: var(--gradient-purple);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .stats-card {
            background: var(--bg-card);
            border: 1px solid rgba(139, 0, 255, 0.2);
            border-radius: 20px;
            padding: 1.5rem;
            transition: all 0.3s ease;
        }
        .stats-card:hover {
            transform: translateY(-5px);
            border-color: var(--accent-purple);
            box-shadow: 0 10px 40px rgba(139, 0, 255, 0.3);
        }
        .stats-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            background: var(--gradient-purple);
        }
        .leaderboard-card {
            background: var(--bg-card);
            border: 1px solid rgba(139, 0, 255, 0.2);
            border-radius: 20px;
            overflow: hidden;
        }
        .leaderboard-header {
            background: var(--gradient-dark);
            padding: 2rem;
            border-bottom: 1px solid rgba(139, 0, 255, 0.2);
        }
        .filter-btn {
            background: transparent;
            border: 2px solid rgba(139, 0, 255, 0.3);
            color: var(--text-primary);
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            transition: all 0.3s;
            font-weight: 500;
        }
        .filter-btn:hover, .filter-btn.active {
            background: var(--gradient-purple);
            border-color: var(--accent-purple);
            color: white;
        }
        .player-row {
            display: flex;
            align-items: center;
            padding: 1.2rem 1.5rem;
            border-bottom: 1px solid rgba(139, 0, 255, 0.1);
            transition: all 0.3s;
        }
        .player-row:hover {
            background: rgba(139, 0, 255, 0.1);
        }
        .player-row:last-child { border-bottom: none; }
        .rank-badge {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.2rem;
            margin-right: 1rem;
        }
        .rank-1 { background: var(--gradient-purple); color: white; transform: scale(1.2); }
        .rank-2 { background: linear-gradient(135deg, #C0C0C0, #A0A0A0); color: white; transform: scale(1.1); }
        .rank-3 { background: linear-gradient(135deg, #CD7F32, #A0522D); color: white; transform: scale(1.05); }
        .rank-other { background: rgba(139, 0, 255, 0.2); color: var(--accent-purple); }
        .player-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 1rem;
            border: 3px solid var(--accent-purple);
        }
        .player-name { font-weight: 600; font-size: 1.1rem; color: var(--text-primary); }
        .player-info { color: var(--text-secondary); font-size: 0.9rem; }
        .score-badge {
            background: var(--gradient-purple);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.1rem;
        }
        .my-rank-card {
            background: var(--gradient-dark);
            border-radius: 20px;
            padding: 1.5rem;
            border: 2px solid var(--accent-purple);
        }
        .page-link {
            background: transparent;
            border: 1px solid rgba(139, 0, 255, 0.3);
            color: var(--text-primary);
            padding: 0.5rem 1rem;
            border-radius: 10px;
            margin: 0 0.2rem;
        }
        .page-link:hover, .page-link.active {
            background: var(--gradient-purple);
            border-color: var(--accent-purple);
            color: white;
        }
        .footer {
            background: var(--bg-secondary);
            border-top: 1px solid rgba(139, 0, 255, 0.2);
            padding: 3rem 0;
            margin-top: 4rem;
        }
    </style>
</head>
<body>
    <?php include 'nav_header.php'; ?>

    <section class="hero-section">
        <div class="container hero-content">
            <div class="text-center">
                <h1 class="display-3 fw-bold mb-3">
                    <i class="bi bi-trophy-fill text-warning me-3"></i>
                    <span class="title-gradient">Лидерборд</span>
                </h1>
                <p class="lead text-white opacity-75 mb-0">Лучшие участники музея по результатам прохождения квестов</p>
            </div>
        </div>
    </section>

    <div class="container py-5">
        <?php if (isset($_SESSION['user_id'])): ?>
            <div class="my-rank-card mb-5">
                <div class="row align-items-center">
                    <div class="col-md-3 text-center">
                        <div class="rank-badge rank-1 d-inline-flex" style="width:70px;height:70px;font-size:1.5rem;">
                            #<?= $my_rank ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h4 class="text-white mb-2">Ваше место: #<?= $my_rank ?></h4>
                        <p class="text-white opacity-75 mb-0">Продолжайте участвовать в квестах, чтобы подняться выше!</p>
                    </div>
                    <div class="col-md-3 text-center">
                        <a href="quests.php" class="btn btn-lg" style="background:var(--gradient-purple);color:white;border:none;border-radius:50px;padding:1rem 2rem;">
                            <i class="bi bi-play-fill me-2"></i>Начать квест
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="row g-4 mb-5">
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon me-3"><i class="bi bi-people"></i></div>
                        <div>
                            <div class="text-secondary small">Участников</div>
                            <div class="fs-4 fw-bold text-white"><?= (int)$stats['total_users'] ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon me-3"><i class="bi bi-star"></i></div>
                        <div>
                            <div class="text-secondary small">Всего баллов</div>
                            <div class="fs-4 fw-bold text-white"><?= number_format($stats['total_points']) ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon me-3"><i class="bi bi-check-circle"></i></div>
                        <div>
                            <div class="text-secondary small">Квестов пройдено</div>
                            <div class="fs-4 fw-bold text-white"><?= (int)$stats['total_completions'] ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon me-3"><i class="bi bi-trophy"></i></div>
                        <div>
                            <div class="text-secondary small">Рекорд</div>
                            <div class="fs-4 fw-bold text-white"><?= number_format($stats['max_points']) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="leaderboard-card mb-4">
            <div class="leaderboard-header">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <h3 class="text-white fw-bold mb-0"><i class="bi bi-list-ol me-2"></i>Рейтинг - <?= $filter_title ?></h3>
                    <div class="btn-group">
                        <a href="?filter=all" class="filter-btn <?= $filter === 'all' ? 'active' : '' ?>">За всё время</a>
                        <a href="?filter=month" class="filter-btn <?= $filter === 'month' ? 'active' : '' ?>">За месяц</a>
                        <a href="?filter=week" class="filter-btn <?= $filter === 'week' ? 'active' : '' ?>">За неделю</a>
                    </div>
                </div>
            </div>
            
            <?php if (empty($players)): ?>
                <div class="p-5 text-center">
                    <i class="bi bi-inbox display-4 text-secondary"></i>
                    <p class="text-secondary mt-3">Пока нет результатов. Станьте первым!</p>
                </div>
            <?php else: ?>
                <?php foreach ($players as $i => $player): 
                    $rank = $offset + $i + 1;
                    $rank_class = $rank <= 3 ? 'rank-'.$rank : 'rank-other';
                ?>
                <div class="player-row">
                    <div class="rank-badge <?= $rank_class ?>"><?= $rank ?></div>
                    <?php if (!empty($player['avatar']) && $player['avatar'] !== 'default_avatar.png'): ?>
                        <img src="<?= htmlspecialchars($player['avatar']) ?>" class="player-avatar" alt="">
                    <?php else: ?>
                        <div class="player-avatar d-flex align-items-center justify-content-center" style="background:var(--gradient-purple);color:white;font-weight:700;font-size:1.5rem;">
                            <?= mb_strtoupper(mb_substr($player['Login'], 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                    <div class="flex-grow-1">
                        <div class="player-name"><?= htmlspecialchars($player['Login']) ?></div>
                        <div class="player-info">
                            <i class="bi bi-check-circle me-1"></i><?= (int)$player['completed_quests'] ?> квестов
                        </div>
                    </div>
                    <div class="score-badge">
                        <?= number_format($player['total_score']) ?> <i class="bi bi-star-fill ms-1"></i>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php if ($total_pages > 1): ?>
        <nav>
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page - 1 ?>&filter=<?= $filter ?>">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                    </li>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <?php if ($i == 1 || $i == $total_pages || ($i >= $page - 2 && $i <= $page + 2)): ?>
                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>&filter=<?= $filter ?>"><?= $i ?></a>
                        </li>
                    <?php elseif ($i == $page - 3 || $i == $page + 3): ?>
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page + 1 ?>&filter=<?= $filter ?>">
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php endif; ?>
    </div>

    <?php include 'footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
