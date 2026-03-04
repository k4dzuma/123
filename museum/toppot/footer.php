<!-- Подвал (общий для всех страниц) -->
<footer class="footer">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="d-flex align-items-center mb-3">
                    <img src="logo.png" alt="Логотип музея" style="height:50px;margin-right:15px;">
                    <div>
                        <h5 class="mb-0 text-gradient">Человек и Время</h5>
                        <small class="text-secondary">Виртуальный музей</small>
                    </div>
                </div>
                <p class="text-secondary mb-4">
                    Интерактивный музей истории колледжа. Узнайте о знаменитых выпускниках, пройдите квесты и оставьте свой отзыв.
                </p>
                <div class="social-links d-flex gap-3">
                    <a href="#" class="btn rounded-circle d-flex align-items-center justify-content-center" style="width:45px;height:45px;background:var(--gradient-purple);">
                        <i class="bi bi-vk"></i>
                    </a>
                    <a href="#" class="btn rounded-circle d-flex align-items-center justify-content-center" style="width:45px;height:45px;background:var(--gradient-cyan);">
                        <i class="bi bi-telegram"></i>
                    </a>
                    <a href="#" class="btn rounded-circle d-flex align-items-center justify-content-center" style="width:45px;height:45px;background:var(--gradient-pink);">
                        <i class="bi bi-youtube"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-2 col-md-6">
                <h5 class="mb-3"><i class="bi bi-grid me-2"></i>Разделы</h5>
                <a href="index.php">Главная</a>
                <a href="razdel.php">Экспозиция</a>
                <a href="eksp.php">Экспонаты</a>
                <a href="meropriyatiya.php">Мероприятия</a>
            </div>
            <div class="col-lg-2 col-md-6">
                <h5 class="mb-3"><i class="bi bi-trophy me-2"></i>Квесты</h5>
                <a href="quests.php">Все квесты</a>
                <a href="leaderboard.php">Рейтинг</a>
                <a href="play_quest.php">Начать</a>
            </div>
            <div class="col-lg-2 col-md-6">
                <h5 class="mb-3"><i class="bi bi-chat-square-text me-2"></i>Сообщество</h5>
                <a href="otziv.php">Отзывы</a>
                <a href="#">FAQ</a>
                <a href="#">Контакты</a>
            </div>
            <div class="col-lg-2 col-md-6">
                <h5 class="mb-3"><i class="bi bi-shield-lock me-2"></i>Аккаунт</h5>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="dashboard.php">Профиль</a>
                    <a href="logout.php">Выйти</a>
                <?php else: ?>
                    <a href="login.php">Войти</a>
                    <a href="register.php">Регистрация</a>
                <?php endif; ?>
            </div>
        </div>
        <hr class="my-4" style="border-color:var(--border-color);">
        <div class="row align-items-center">
            <div class="col-md-6 text-center text-md-start">
                <p class="mb-0 text-secondary">&copy; <?= date('Y'); ?> Виртуальный музей "Человек и Время". Все права защищены.</p>
            </div>
            <div class="col-md-6 text-center text-md-end">
                <small class="text-secondary">Сделано с <i class="bi bi-heart-fill text-danger"></i> для колледжа</small>
            </div>
        </div>
    </div>
</footer>
