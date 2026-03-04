<?php
session_start();
require 'db_connection.php';
?>
<?php
try {
    $query = $db->query("SELECT * FROM Section");
    $info = $query->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Ошибка подключения к базе данных: " . $e->getMessage();
    $info = [];
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <title>Разделы музея</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: rgba(74, 111, 165, 0.7);
            --secondary-color: #166088;
            --accent-color: #4fc3f7;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            color: #333;
            line-height: 1.6;
        }
        
        .navbar-custom {
            background-color: var(--primary-color);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 10px 0;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }
        
        .navbar-brand img {
            height: 50px;
            transition: transform 0.3s;
        }
        
        .navbar-brand img:hover {
            transform: scale(1.05);
        }
        
        .navbar-custom .nav-link {
            color: white;
            font-weight: 500;
            padding: 0.5rem 1rem;
            margin: 0 0.2rem;
            border-radius: 4px;
            transition: all 0.3s;
        }
        
        .navbar-custom .nav-link:hover {
            background-color: rgba(255,255,255,0.2);
            color: white;
        }
        
        .sections-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 3rem 0;
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .page-title {
            font-weight: 700;
            font-size: 2.5rem;
        }
        
        .section-card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            height: 100%;
            background-color: white;
        }
        
        .section-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.2);
        }
        
        .card-img-container {
            overflow: hidden;
            height: 220px;
        }
        
        .section-card img {
            height: 100%;
            width: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .section-card:hover img {
            transform: scale(1.1);
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        .card-title {
            color: var(--secondary-color);
            font-weight: 600;
            margin-bottom: 0.75rem;
        }
        
        .btn-explore {
            background-color: var(--accent-color);
            color: white;
            border: none;
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-explore:hover {
            background-color: var(--secondary-color);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .footer {
            background-color: var(--dark-color);
            color: white;
            padding: 2rem 0;
            margin-top: 4rem;
        }
        
        .animate-fade {
            animation: fadeIn 0.6s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Адаптивные стили */
        @media (max-width: 992px) {
            .page-title {
                font-size: 2.2rem;
            }
            
            .card-img-container {
                height: 180px;
            }
        }
        
        @media (max-width: 768px) {
            .page-title {
                font-size: 2rem;
            }
            
            .sections-header {
                padding: 2rem 0;
            }
            
            .card-img-container {
                height: 160px;
            }
        }
        
        @media (max-width: 576px) {
            .page-title {
                font-size: 1.8rem;
            }
            
            .sections-header {
                padding: 1.5rem 0;
            }
            
            .card-img-container {
                height: 140px;
            }
        }
    </style>
</head>

<body>
    <!-- Навигационная панель -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="logo.png" alt="Логотип музея">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php"><i class="bi bi-house-door me-1"></i>Главная</a></li>
                    <li class="nav-item"><a class="nav-link active" href="razdel.php"><i class="bi bi-collection me-1"></i>Разделы</a></li>
                    <li class="nav-item"><a class="nav-link" href="quests.php"><i class="bi bi-trophy me-1"></i>Квесты</a></li>
                    <li class="nav-item"><a class="nav-link" href="otziv.php"><i class="bi bi-chat-square-text me-1"></i>Отзывы</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="bi bi-person me-1"></i>Профиль</a></li>
                        <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true): ?>
                            <li class="nav-item">
                                <a class="nav-link text-warning" href="admin_panel.php">
                                    <i class="bi bi-shield-lock me-1"></i>Админ-панель
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="login.php"><i class="bi bi-box-arrow-in-right me-1"></i>Войти</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Упрощенная шапка без фонового изображения -->
    <header class="sections-header">
        <div class="container">
            <h1 class="page-title">Разделы музея</h1>
            <p class="lead">Исследуйте наши коллекции</p>
        </div>
    </header>

    <!-- Основное содержимое -->
    <main class="container mb-5">
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger animate-fade">
                <?= $error_message ?>
            </div>
        <?php endif; ?>
        
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php foreach ($info as $data): ?>
                <div class="col animate-fade">
                    <div class="section-card h-100">
                        <a href="<?= htmlspecialchars($data['url']); ?>" class="text-decoration-none text-dark">
                            <div class="card-img-container">
                                <img src="<?= htmlspecialchars($data['Img']); ?>" class="w-100" alt="<?= htmlspecialchars($data['Nazvanie']); ?>">
                            </div>
                            <div class="card-body text-center">
                                <h5 class="card-title"><?= htmlspecialchars($data['Nazvanie']); ?></h5>
                                <p class="card-text text-muted mb-3"><?= htmlspecialchars($data['Opisanie'] ?? ''); ?></p>
                            </div>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <!-- Подвал -->
    <footer class="footer">
        <div class="container text-center">
            <p>&copy; <?= date('Y'); ?> Виртуальный музей. Все права защищены.</p>
            <div class="social-links">
                <a href="#" class="text-white me-3"><i class="bi bi-facebook"></i></a>
                <a href="#" class="text-white me-3"><i class="bi bi-twitter"></i></a>
                <a href="#" class="text-white me-3"><i class="bi bi-instagram"></i></a>
                <a href="#" class="text-white"><i class="bi bi-youtube"></i></a>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.animate-fade');
            cards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
            });
            
            // Плавное появление карточек при скролле
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, { threshold: 0.1 });
            
            cards.forEach(card => {
                observer.observe(card);
            });
        });
    </script>
</body>
</html>