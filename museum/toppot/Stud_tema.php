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
    $studsovet_dostijenya = $db->query("SELECT * FROM studsovet_dostijenya")->fetchAll(PDO::FETCH_ASSOC);
    $studsovet_lideri = $db->query("SELECT * FROM studsovet_lideri")->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error_message = "Ошибка подключения к базе данных: " . $e->getMessage();
    $all_data = [];
}

$all_data = [
    'studsovet_dostijenya' => $studsovet_dostijenya,
    'studsovet_lideri' => $studsovet_lideri
];
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <title>Студенческий совет - Виртуальный музей</title>
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
            display: flex;
            flex-direction: column;
            min-height: 100vh;
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
            max-width: 100%;
            object-fit: contain;
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
        
        .page-header {
            padding: 3rem 0;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            margin-bottom: 2rem;
            text-align: center;
        }
        
        .collection-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            margin: 30px auto;
            width: 90%;
            max-width: 1200px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .collection-container:hover {
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
            transform: translateY(-5px);
        }
        
        .collection-image, .collection-text {
            flex: 1;
            min-width: 300px;
            padding: 25px;
        }
        
        .collection-image {
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f0f0f0;
        }
        
        .collection-image img {
            width: 100%;
            height: auto;
            max-height: 400px;
            object-fit: cover;
            border-radius: 5px;
            transition: transform 0.5s;
            cursor: pointer;
        }
        
        .collection-image:hover img {
            transform: scale(1.03);
        }
        
        .collection-image::after {
            content: "Нажмите для просмотра галереи";
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0,0,0,0.7);
            color: white;
            padding: 10px;
            text-align: center;
            font-size: 14px;
            opacity: 0;
            transition: opacity 0.3s;
        }
        
        .collection-image:hover::after {
            opacity: 1;
        }
        
        .collection-text h2 {
            color: var(--secondary-color);
            font-weight: 600;
            margin-bottom: 20px;
            transition: color 0.3s;
        }
        
        .collection-text h2 a {
            color: inherit;
            text-decoration: none;
        }
        
        .collection-text h2:hover {
            color: var(--primary-color);
        }
        
        .collection-text p {
            color: #555;
            line-height: 1.7;
            margin-bottom: 1.5rem;
        }
        
        .gallery-icon {
            position: absolute;
            top: 15px;
            right: 15px;
            background: rgba(255,255,255,0.9);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-color);
            font-size: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            z-index: 2;
        }
        
        .gallery-btn {
            background-color: var(--secondary-color);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
        }
        
        .gallery-btn:hover {
            background-color: var(--primary-color);
            color: white;
        }
        
        .gallery-btn i {
            margin-right: 5px;
        }
        
        /* Модальное окно галереи */
        .gallery-modal .modal-content {
            border-radius: 10px;
            border: none;
        }
        
        .gallery-modal .modal-header {
            border-bottom: none;
            background-color: var(--primary-color);
            color: white;
        }
        
        .gallery-modal .modal-body {
            padding: 0;
        }
        
        .gallery-modal .carousel-item img {
            max-height: 70vh;
            object-fit: contain;
        }
        
        .gallery-modal .carousel-caption {
            background: rgba(0,0,0,0.6);
            padding: 10px;
            left: 0;
            right: 0;
            bottom: 0;
        }
        
        .footer {
            background-color: var(--dark-color);
            color: white;
            padding: 1.5rem 0;
            margin-top: auto;
        }
        
        @media (max-width: 768px) {
            .collection-container {
                flex-direction: column;
                width: 95%;
            }
            
            .collection-image, .collection-text {
                min-width: 100%;
            }
            
            .collection-image {
                order: 1;
            }
            
            .collection-text {
                order: 2;
            }
            
            .navbar-brand img {
                height: 50px;
            }
        }
        
        @media (max-width: 576px) {
            .page-header {
                padding: 1.5rem 0;
            }
            
            .collection-image::after {
                font-size: 12px;
                padding: 8px;
            }
            
            .gallery-icon {
                width: 30px;
                height: 30px;
                font-size: 16px;
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
    <!-- Заголовок страницы -->
    <header class="page-header">
        <div class="container">
            <h1>Студенческий совет</h1>
            <p class="lead">Достижения и лидеры студенческого самоуправления</p>
        </div>
    </header>

    <!-- Основное содержимое -->
    <main class="container mb-5">
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger">
                <?= $error_message ?>
            </div>
        <?php endif; ?>
        
        <?php
        $carousel_index = 0; 
        foreach ($all_data as $tableName => $data):
            if (!empty($data)): ?>
                <!-- Модальное окно галереи -->
                <div class="modal fade gallery-modal" id="carouselModal-<?= $carousel_index; ?>" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-xl">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title"><?= htmlspecialchars($data[0]['Nazvanie']); ?></h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div id="carousel-<?= $carousel_index; ?>" class="carousel slide" data-bs-ride="carousel">
                                    <div class="carousel-inner">
                                        <?php foreach ($data as $key => $item): ?>
                                            <div class="carousel-item <?= $key === 0 ? 'active' : ''; ?>">
                                                <img src="<?= htmlspecialchars($item['Img']); ?>" class="d-block w-100" alt="<?= htmlspecialchars($item['Nazvanie']); ?>">
                                                <div class="carousel-caption d-none d-md-block">
                                                    <p><?= htmlspecialchars($item['Nazvanie']); ?></p>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <button class="carousel-control-prev" type="button" data-bs-target="#carousel-<?= $carousel_index; ?>" data-bs-slide="prev">
                                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                        <span class="visually-hidden">Предыдущий</span>
                                    </button>
                                    <button class="carousel-control-next" type="button" data-bs-target="#carousel-<?= $carousel_index; ?>" data-bs-slide="next">
                                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                        <span class="visually-hidden">Следующий</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Блок коллекции -->
                <div class="collection-container">
                    <?php if ($carousel_index % 2 == 0): ?>
                        <div class="collection-image">
                            <div class="gallery-icon">
                                <i class="bi bi-grid-3x3-gap"></i>
                            </div>
                            <a href="#" data-bs-toggle="modal" data-bs-target="#carouselModal-<?= $carousel_index; ?>">
                                <img src="<?= htmlspecialchars($data[0]['Img']); ?>" alt="<?= htmlspecialchars($data[0]['Nazvanie']); ?>">
                            </a>
                        </div>
                        <div class="collection-text">
                            <h2>
                                <a href="#" data-bs-toggle="modal" data-bs-target="#carouselModal-<?= $carousel_index; ?>">
                                    <?= htmlspecialchars($data[0]['Nazvanie']); ?>
                                </a>
                            </h2>
                            <p><?= htmlspecialchars($data[0]['Text']); ?></p>
                            <button class="gallery-btn" data-bs-toggle="modal" data-bs-target="#carouselModal-<?= $carousel_index; ?>">
                                <i class="bi bi-images"></i> Смотреть галерею
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="collection-text">
                            <h2>
                                <a href="#" data-bs-toggle="modal" data-bs-target="#carouselModal-<?= $carousel_index; ?>">
                                    <?= htmlspecialchars($data[0]['Nazvanie']); ?>
                                </a>
                            </h2>
                            <p><?= htmlspecialchars($data[0]['Text']); ?></p>
                            <button class="gallery-btn" data-bs-toggle="modal" data-bs-target="#carouselModal-<?= $carousel_index; ?>">
                                <i class="bi bi-images"></i> Смотреть галерею
                            </button>
                        </div>
                        <div class="collection-image">
                            <div class="gallery-icon">
                                <i class="bi bi-grid-3x3-gap"></i>
                            </div>
                            <a href="#" data-bs-toggle="modal" data-bs-target="#carouselModal-<?= $carousel_index; ?>">
                                <img src="<?= htmlspecialchars($data[0]['Img']); ?>" alt="<?= htmlspecialchars($data[0]['Nazvanie']); ?>">
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
                
                <?php 
                $carousel_index++; 
            endif; 
        endforeach; 
        ?>
    </main>

    <!-- Подвал -->
    <footer class="footer">
        <div class="container text-center">
            <p>&copy; <?= date('Y'); ?> Виртуальный музей "Человек и Время". Все права защищены.</p>
            <div class="social-links">
                <a href="#" class="text-white me-2"><i class="bi bi-facebook"></i></a>
                <a href="#" class="text-white me-2"><i class="bi bi-twitter"></i></a>
                <a href="#" class="text-white me-2"><i class="bi bi-instagram"></i></a>
                <a href="#" class="text-white"><i class="bi bi-youtube"></i></a>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Добавляем анимацию при наведении на изображения
        document.addEventListener('DOMContentLoaded', function() {
            const images = document.querySelectorAll('.collection-image img');
            images.forEach(img => {
                img.addEventListener('mouseenter', function() {
                    this.style.transform = 'scale(1.03)';
                });
                img.addEventListener('mouseleave', function() {
                    this.style.transform = 'scale(1)';
                });
            });
        });
    </script>
</body>
</html>