<?php
session_start();
require 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Для админ-страниц добавьте дополнительную проверку:
if (strpos($_SERVER['SCRIPT_NAME'], 'admin') !== false && !isset($_SESSION['is_admin'])) {
    header("Location: index.php");
    exit();
}
?>
<?php     
try {     
    $query = $db->query("SELECT * FROM Eksponat");     
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
    <title>Экспонаты музея</title>   
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
            background-color: #000;
            color: #333;
            line-height: 1.6;
            padding-top: 70px;
            margin: 0;
            height: 100vh;
            overflow: hidden;
        }
        
        .navbar-custom {
            background-color: var(--primary-color);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 10px 0;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1030;
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
        
        /* Стили для карусели */
        .hero-carousel {
            width: 100%;
            height: calc(100vh - 70px);
            margin: 0;
            border-radius: 0;
            overflow: hidden;
        }
        
        .carousel-inner {
            height: 100%;
            border-radius: 0;
        }
        
        .carousel-item {
            height: 100%;
        }
        
        .carousel-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .carousel-control-prev, 
        .carousel-control-next {
            width: 60px;
            height: 60px;
            background-color: rgba(0,0,0,0.3);
            border-radius: 50%;
            top: 50%;
            transform: translateY(-50%);
            margin: 0 30px;
            opacity: 0.8;
            transition: all 0.3s;
        }
        
        .carousel-control-prev:hover, 
        .carousel-control-next:hover {
            background-color: rgba(0,0,0,0.5);
            opacity: 1;
        }
        
        .carousel-indicators {
            bottom: 30px;
        }
        
        .carousel-indicators button {
            width: 14px;
            height: 14px;
            border-radius: 50%;
            margin: 0 10px;
            background-color: rgba(255,255,255,0.5);
            border: none;
            transition: all 0.3s;
        }
        
        .carousel-indicators .active {
            background-color: var(--accent-color);
            transform: scale(1.3);
        }
        
        .carousel-caption {
            background-color: rgba(0,0,0,0.7);
            border-radius: 10px;
            padding: 1.5rem;
            bottom: 80px;
            left: 50%;
            transform: translateX(-50%);
            width: 80%;
        }
        
        .carousel-caption h5 {
            font-size: 2rem;
            color: var(--accent-color);
            margin-bottom: 1rem;
        }
        
        .carousel-caption p {
            font-size: 1.2rem;
        }
        
        /* Анимации */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .animate-fade {
            animation: fadeIn 0.8s ease-in;
        }
        
        /* Адаптивные стили */
        @media (max-width: 992px) {
            .carousel-caption {
                width: 90%;
                padding: 1rem;
                bottom: 60px;
            }
            
            .carousel-caption h5 {
                font-size: 1.5rem;
            }
            
            .carousel-caption p {
                font-size: 1rem;
            }
        }
        
        @media (max-width: 768px) {
            .carousel-control-prev, 
            .carousel-control-next {
                width: 50px;
                height: 50px;
                margin: 0 20px;
            }
            
            .carousel-indicators {
                bottom: 20px;
            }
        }
        
        @media (max-width: 576px) {
            body {
                padding-top: 60px;
            }
            
            .hero-carousel {
                height: calc(100vh - 60px);
            }
            
            .carousel-caption {
                bottom: 40px;
            }
            
            .carousel-caption h5 {
                font-size: 1.2rem;
            }
            
            .carousel-caption p {
                font-size: 0.9rem;
            }
            
            .carousel-control-prev, 
            .carousel-control-next {
                width: 40px;
                height: 40px;
                margin: 0 15px;
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
    <li class="nav-item">
        <a class="nav-link" href="index.php"><i class="bi bi-house-door me-1"></i>Главная</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="razdel.php"><i class="bi bi-collection me-1"></i>Разделы</a>
    </li>
    <?php if (isset($_SESSION['user_id'])): ?>
        <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
            <li class="nav-item">
                <a class="nav-link text-warning" href="admin_panel.php">
                    <i class="bi bi-shield-lock me-1"></i>Админ-панель
                </a>
            </li>
        <?php endif; ?>
        <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="bi bi-person me-1"></i>Профиль</a></li>
    <?php else: ?>
        <li class="nav-item"><a class="nav-link" href="login.php"><i class="bi bi-box-arrow-in-right me-1"></i>Войти</a></li>
    <?php endif; ?>
</ul>
            </div>
        </div>
    </nav>

    <!-- Карусель с экспонатами на всю страницу -->
    <div id="exhibitsCarousel" class="carousel slide hero-carousel" data-bs-ride="carousel">
        <div class="carousel-indicators">
            <?php foreach ($info as $index => $data): ?>
                <button type="button" data-bs-target="#exhibitsCarousel" data-bs-slide-to="<?= $index; ?>" 
                        class="<?= $index === 0 ? 'active' : ''; ?>" 
                        aria-current="<?= $index === 0 ? 'true' : 'false'; ?>" 
                        aria-label="Экспонат <?= $index + 1; ?>"></button>
            <?php endforeach; ?>
        </div>
        
        <div class="carousel-inner">
            <?php if (empty($info)): ?>
                <div class="carousel-item active">
                    <img src="default-image.jpg" class="d-block w-100" alt="Нет изображений">
                    <div class="carousel-caption">
                        <h5>Нет доступных экспонатов</h5>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($info as $index => $data): ?>
                    <div class="carousel-item <?= $index === 0 ? 'active' : ''; ?>">
                        <img src="<?= htmlspecialchars($data['Img']); ?>" class="d-block w-100" alt="<?= htmlspecialchars($data['Nazvanie'] ?? 'Экспонат'); ?>">
                        <div class="carousel-caption">
                            <h5><?= htmlspecialchars($data['Nazvanie'] ?? ''); ?></h5>
                            <p><?= htmlspecialchars($data['Opisanie'] ?? ''); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <button class="carousel-control-prev" type="button" data-bs-target="#exhibitsCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Предыдущий</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#exhibitsCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Следующий</span>
        </button>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Инициализация карусели с автопрокруткой
        document.addEventListener('DOMContentLoaded', function() {
            const myCarousel = document.getElementById('exhibitsCarousel');
            const carousel = new bootstrap.Carousel(myCarousel, {
                interval: 5000, // 5 секунд между переключениями
                pause: 'hover', // Пауза при наведении
                wrap: true // Бесконечная прокрутка
            });
        });
    </script>
</body>   
</html>