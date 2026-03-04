<!-- Навигационная панель (общая для всех страниц) -->
<nav class="navbar navbar-expand-lg navbar-dark navbar-custom sticky-top">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <img src="logo.png" alt="Логотип музея" style="height:50px;">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="index.php"><i class="bi bi-house-door me-1"></i>Главная</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="razdel.php"><i class="bi bi-collection me-1"></i>Разделы</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="quests.php"><i class="bi bi-trophy me-1"></i>Квесты</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="leaderboard.php"><i class="bi bi-star me-1"></i>Рейтинг</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="otziv.php"><i class="bi bi-chat-square-text me-1"></i>Отзывы</a>
                </li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php"><i class="bi bi-person me-1"></i>Профиль</a>
                    </li>
                    <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true): ?>
                        <li class="nav-item">
                            <a class="nav-link text-warning" href="admin_panel.php">
                                <i class="bi bi-shield-lock me-1"></i>Админ-панель
                            </a>
                        </li>
                    <?php endif; ?>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php"><i class="bi bi-box-arrow-in-right me-1"></i>Войти</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
