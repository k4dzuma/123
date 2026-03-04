<?php
session_start();
require 'db_connection.php';

try {
    $total_users = $db->query("SELECT COUNT(*) FROM Registr")->fetchColumn();
    $total_quests = $db->query("SELECT COUNT(*) FROM quests WHERE is_active = 1")->fetchColumn();
    $total_exhibits = $db->query("SELECT COUNT(*) FROM Eksponat")->fetchColumn();
} catch (PDOException $e) {
    $total_users = 0;
    $total_quests = 0;
    $total_exhibits = 0;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Виртуальный музей - Человек и Время</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .hero-animation {
            animation: fadeInUp 1s ease;
        }
        
        .feature-card {
            position: relative;
            overflow: hidden;
        }
        
        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-purple);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }
        
        .feature-card:hover::before {
            transform: scaleX(1);
        }
        
        .feature-icon {
            width: 80px;
            height: 80px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            background: var(--gradient-purple);
            box-shadow: var(--shadow-md);
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
        }
        
        .feature-card:hover .feature-icon {
            transform: scale(1.1) rotate(5deg);
            box-shadow: var(--shadow-lg);
        }
        
        .graduate-card {
            position: relative;
            overflow: hidden;
        }
        
        .graduate-card img {
            transition: all 0.5s ease;
        }
        
        .graduate-card:hover img {
            transform: scale(1.05);
        }
        
        .graduate-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(15, 15, 26, 0.95), transparent);
            padding: 2rem 1.5rem 1.5rem;
        }
        
        .cta-section {
            background: var(--gradient-dark);
            position: relative;
            overflow: hidden;
        }
        
        .cta-section::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(139, 0, 255, 0.2) 0%, transparent 70%);
            border-radius: 50%;
        }
        
        .contact-icon {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
        }
        
        .contact-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .contact-card:hover {
            transform: translateY(-10px);
            border-color: var(--accent-purple);
            box-shadow: var(--shadow-lg);
        }
        
        .contact-card:hover .contact-icon {
            transform: scale(1.1);
        }
        
        .theme-item {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 15px;
            padding: 1.2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: all 0.3s ease;
            margin-bottom: 1rem;
        }
        
        .theme-item:hover {
            border-color: var(--accent-purple);
            background: var(--bg-card-hover);
            transform: translateX(10px);
        }
        
        .theme-icon {
            width: 45px;
            height: 45px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--gradient-purple);
            flex-shrink: 0;
        }
        
        @media (max-width: 768px) {
            .hero-section {
                padding: 6rem 0 4rem;
            }
            
            .feature-icon {
                width: 60px;
                height: 60px;
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
    <?php include 'nav_header.php'; ?>

    <!-- Модальное окно для видео -->
    <div class="modal fade video-modal" id="videoModal" tabindex="-1" aria-labelledby="videoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="modal-body p-0">
                    <video id="videoFrame" controls autoplay style="width:100%;height:100%;object-fit:contain;">
                        <source src="obzor.mp4" type="video/mp4">
                    </video>
                </div>
            </div>
        </div>
    </div>

    <!-- Герой-секция -->
    <section class="hero-section">
        <div class="container hero-content">
            <div class="row justify-content-center">
                <div class="col-lg-10 text-center hero-animation">
                    <h1 class="museum">ВИРТУАЛЬНЫЙ МУЗЕЙ</h1>
                    <p class="hero-subtitle">Человек и Время — Путешествие сквозь эпоху</p>
                    <p class="lead text-white opacity-75 mb-4">
                        Погрузитесь в богатую историю нашего колледжа: от первых дней до наших времен, 
                        от знаменитых выпускников до современных достижений
                    </p>
                    <div class="buttons-container">
                        <a href="razdel.php" class="btn btn-primary">
                            <i class="bi bi-collection-play me-2"></i>Экспозиция
                        </a>
                        <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#videoModal">
                            <i class="bi bi-play-circle me-2"></i>Видео-тур
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Статистика -->
    <section class="py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="stats-card text-center">
                        <div class="stats-icon d-inline-flex">
                            <i class="bi bi-people-fill text-white"></i>
                        </div>
                        <h3 class="display-4 fw-bold text-white mt-3"><?= number_format($total_users) ?></h3>
                        <p class="text-secondary mb-0">Участников</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stats-card text-center">
                        <div class="stats-icon d-inline-flex" style="background:var(--gradient-cyan);">
                            <i class="bi bi-image-fill text-white"></i>
                        </div>
                        <h3 class="display-4 fw-bold text-white mt-3"><?= number_format($total_exhibits) ?></h3>
                        <p class="text-secondary mb-0">Экспонатов</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stats-card text-center">
                        <div class="stats-icon d-inline-flex" style="background:var(--gradient-pink);">
                            <i class="bi bi-trophy-fill text-white"></i>
                        </div>
                        <h3 class="display-4 fw-bold text-white mt-3"><?= number_format($total_quests) ?></h3>
                        <p class="text-secondary mb-0">Квестов</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- О музее -->
    <section class="section-container">
        <div class="container">
            <h2 class="section-title">О нашем музее</h2>
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="image">
                        <img src="img.jpg" alt="Интерьер музея">
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="ps-lg-4">
                        <h3 class="text-gradient mb-4">История длиной в 90 лет</h3>
                        <p class="text-secondary mb-4">
                            История нашего колледжа начинается в 1930 году, когда были организованы 
                            фабрично-заводские курсы при фабрике «Красный перекоп». Занятия шли четыре 
                            раза в неделю, в две смены.
                        </p>
                        <p class="text-secondary mb-4">
                            Затем курсы преобразовались в вечернее отделение текстильного техникума, а в 1933 
                            году было открыто и дневное отделение Ярославского Текстильного Техникума.
                        </p>
                        <div class="theme-item">
                            <div class="theme-icon"><i class="bi bi-calendar3 text-white"></i></div>
                            <div>
                                <h5 class="text-white mb-1">Основан в 1930</h5>
                                <p class="text-secondary mb-0 small">Более 90 лет истории</p>
                            </div>
                        </div>
                        <div class="theme-item">
                            <div class="theme-icon"><i class="bi bi-mortarboard-fill text-white"></i></div>
                            <div>
                                <h5 class="text-white mb-1">Тысячи выпускников</h5>
                                <p class="text-secondary mb-0 small">Многие стали знаменитыми</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Особенности -->
    <section class="py-5" style="background:var(--bg-secondary);">
        <div class="container">
            <h2 class="section-title">Что мы предлагаем</h2>
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="card feature-card h-100">
                        <div class="card-body">
                            <div class="feature-icon"><i class="bi bi-collection text-white"></i></div>
                            <h4 class="text-white mb-3">Экспозиция</h4>
                            <p class="text-secondary">
                                Ознакомьтесь с богатой коллекцией экспонатов, рассказывающих о истории и развитии нашего учебного заведения.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="card feature-card h-100">
                        <div class="card-body">
                            <div class="feature-icon" style="background:var(--gradient-cyan);"><i class="bi bi-trophy text-white"></i></div>
                            <h4 class="text-white mb-3">Интерактивные квесты</h4>
                            <p class="text-secondary">
                                Пройдите увлекательные квесты и проверьте свои знания об истории колледжа в игровой форме.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="card feature-card h-100">
                        <div class="card-body">
                            <div class="feature-icon" style="background:var(--gradient-pink);"><i class="bi bi-chat-square-quote text-white"></i></div>
                            <h4 class="text-white mb-3">Обсуждения</h4>
                            <p class="text-secondary">
                                Делитесь впечатлениями, оставляйте отзывы и общайтесь с другими посетителями музея.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Тематики -->
    <section class="section-container">
        <div class="container">
            <h2 class="section-title">Наши тематики</h2>
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="pe-lg-4">
                        <div class="theme-item">
                            <div class="theme-icon"><i class="bi bi-person-hearts text-white"></i></div>
                            <div>
                                <h5 class="text-white mb-1">История Театра моды</h5>
                                <p class="text-secondary mb-0 small">Уникальные коллекции и показы</p>
                            </div>
                        </div>
                        <div class="theme-item">
                            <div class="theme-icon"><i class="bi bi-trophy text-white"></i></div>
                            <div>
                                <h5 class="text-white mb-1">Спортивная жизнь</h5>
                                <p class="text-secondary mb-0 small">Достижения и победы</p>
                            </div>
                        </div>
                        <div class="theme-item">
                            <div class="theme-icon"><i class="bi bi-shield-exclamation text-white"></i></div>
                            <div>
                                <h5 class="text-white mb-1">Страницы архива, опаленные войной</h5>
                                <p class="text-secondary mb-0 small">История в лицах и событиях</p>
                            </div>
                        </div>
                        <div class="theme-item">
                            <div class="theme-icon"><i class="bi bi-buildings text-white"></i></div>
                            <div>
                                <h5 class="text-white mb-1">Исторические этапы</h5>
                                <p class="text-secondary mb-0 small">Развитие учебного заведения</p>
                            </div>
                        </div>
                        <div class="theme-item">
                            <div class="theme-icon"><i class="bi bi-stars text-white"></i></div>
                            <div>
                                <h5 class="text-white mb-1">Знаменитые выпускники</h5>
                                <p class="text-secondary mb-0 small">Люди, прославившие колледж</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="image2">
                        <img src="natalia.png" alt="Экспозиция">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Знаменитые выпускники -->
    <section class="py-5" style="background:var(--bg-secondary);">
        <div class="container">
            <h2 class="section-title">Знаменитые выпускники</h2>
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="card graduate-card">
                        <img src="tereshkova.png" alt="Валентина Терешкова" style="width:100%;height:400px;object-fit:cover;">
                        <div class="graduate-overlay">
                            <h4 class="text-white mb-2">Валентина Владимировна Терешкова</h4>
                            <p class="text-white opacity-75 mb-3">
                                Первая женщина в мире, совершившая космический полет, который состоялся 
                                16 июня 1963 года на корабле "Восток-6".
                            </p>
                            <span class="badge badge-success">Космонавт</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card graduate-card">
                        <img src="vlasov.png" alt="Алексей Власов" style="width:100%;height:400px;object-fit:cover;">
                        <div class="graduate-overlay">
                            <h4 class="text-white mb-2">Алексей Власов</h4>
                            <p class="text-white opacity-75 mb-3">
                                Талантливый дизайнер из Ярославля, известный своими креативными и 
                                уникальными решениями в области дизайна.
                            </p>
                            <span class="badge badge-info">Дизайнер</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Контакты -->
    <section class="section-container">
        <div class="container">
            <h2 class="section-title">Контакты</h2>
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="contact-card">
                        <img src="director.jpg" alt="Директор" class="avatar avatar-lg mx-auto mb-3">
                        <div class="contact-icon" style="background:var(--gradient-purple);">
                            <i class="bi bi-person-badge text-white"></i>
                        </div>
                        <h4 class="text-white mb-2">Директор колледжа</h4>
                        <p class="text-secondary">Цветаева Марина Владимировна</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="contact-card">
                        <img src="nat.jpg" alt="Ответственное лицо" class="avatar avatar-lg mx-auto mb-3">
                        <div class="contact-icon" style="background:var(--gradient-cyan);">
                            <i class="bi bi-person-gear text-white"></i>
                        </div>
                        <h4 class="text-white mb-2">Ответственное лицо</h4>
                        <p class="text-secondary">Румянцева Наталья Васильевна</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="contact-card" style="cursor:pointer;" onclick="document.getElementById('map-location').scrollIntoView({behavior:'smooth'})">
                        <div class="contact-icon" style="background:var(--gradient-pink);">
                            <i class="bi bi-geo-alt text-white"></i>
                        </div>
                        <h4 class="text-white mb-2">Расположение</h4>
                        <p class="text-secondary">Тутаевское шоссе, 31А</p>
                        <button class="btn btn-primary btn-sm mt-2">
                            <i class="bi bi-map me-2"></i>На карте
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA секция -->
    <section class="cta-section py-5">
        <div class="container text-center">
            <div style="position:relative;z-index:1;">
                <h2 class="display-5 fw-bold text-white mb-3">Готовы начать путешествие?</h2>
                <p class="lead text-white opacity-75 mb-4">
                    Присоединяйтесь к тысячам посетителей и узнайте больше о нашем музее
                </p>
                <div class="buttons-container justify-content-center">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="quests.php" class="btn btn-primary">
                            <i class="bi bi-trophy me-2"></i>Начать квест
                        </a>
                    <?php else: ?>
                        <a href="register.php" class="btn btn-primary">
                            <i class="bi bi-person-plus me-2"></i>Регистрация
                        </a>
                    <?php endif; ?>
                    <a href="razdel.php" class="btn btn-secondary">
                        <i class="bi bi-collection me-2"></i>Экспозиция
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Карта -->
    <section class="py-5" id="map-location">
        <div class="container">
            <h2 class="section-title">Как нас найти</h2>
            <div style="border-radius:20px;overflow:hidden;box-shadow:var(--shadow-lg);border:2px solid var(--border-color);">
                <script type="text/javascript" charset="utf-8" async src="https://api-maps.yandex.ru/services/constructor/1.0/js/?um=constructor%3Ac61d5f47b808f7ac1646443b776fe91c31303dfc2eb4cbf5e0d166ba2354bd42&amp;width=100%&amp;height=500&amp;lang=ru_RU&amp;scroll=true"></script>
            </div>
        </div>
    </section>

    <?php include 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('videoModal').addEventListener('hidden.bs.modal', function () {
            const video = document.getElementById('videoFrame');
            video.pause();
            video.currentTime = 0;
        });
    </script>
</body>
</html>
