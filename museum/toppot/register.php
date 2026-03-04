<?php  
session_start();   
$errorMessage = "";   
$successMessage = "";  

try {  
    require 'db_connection.php';  

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {  
        $login = trim($_POST['login']);  
        $email = trim($_POST['email']);  
        $password = $_POST['password'];  
        $confirmPassword = $_POST['confirm_password'];  

        if ($password !== $confirmPassword) {  
            $errorMessage = "Пароли не совпадают.";  
        } else { 
            $stmt = $db->prepare("SELECT * FROM Registr WHERE Email = ?");  
            $stmt->execute([$email]);  

            if ($stmt->fetch()) { 
                $errorMessage = "Пользователь с таким Email уже существует."; 
            } else { 
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);  
                $stmt = $db->prepare("INSERT INTO Registr (Login, password, Email) VALUES (?, ?, ?)");  

                if ($stmt->execute([$login, $hashedPassword, $email])) { 
                    $successMessage = "Регистрация успешна! Теперь вы можете войти."; 
                } else { 
                    $errorMessage = "Ошибка при регистрации."; 
                } 
            } 
        } 
    } 
} catch (PDOException $e) {  
    $errorMessage = "Ошибка подключения к базе данных: " . htmlspecialchars($e->getMessage());  
    error_log($e->getMessage());
} 
?>  

<!DOCTYPE html>  
<html lang="ru">  
<head>  
    <meta charset="UTF-8">  
    <meta name="viewport" content="width=device-width, initial-scale=1.0">  
    <title>Регистрация - Виртуальный музей</title>  
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
        
        .register-section {
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.85)), url('museum-bg.jpg') no-repeat center center;
            background-size: cover;
            color: white;
            padding: 6rem 0 4rem;
            text-align: center;
            margin-top: 0;
            position: relative;
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        
        .register-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: inherit;
            filter: blur(5px) brightness(0.7);
            z-index: -1;
        }
        
        .register-container {
            max-width: 500px;
            width: 100%;
            margin: 0 auto;
            padding: 2rem;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }
        
        .register-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            color: white;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }
        
        .register-subtitle {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            color: var(--accent-color);
        }
        
        .form-label {
            color: white;
            font-weight: 500;
        }
        
        .form-control {
            background-color: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            padding: 0.75rem 1rem;
        }
        
        .form-control:focus {
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
            border-color: var(--accent-color);
            box-shadow: 0 0 0 0.25rem rgba(79, 195, 247, 0.25);
        }
        
        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }
        
        .btn-primary {
            background-color: var(--accent-color);
            border: none;
            padding: 0.75rem 2rem;
            font-weight: 600;
            border-radius: 50px;
            transition: all 0.3s;
            margin-top: 1rem;
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-color);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
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
            height: 60px;
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
        
        .alert {
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 2rem;
            border: none;
        }
        
        .alert-success {
            background-color: rgba(40, 167, 69, 0.9);
            color: white;
        }
        
        .alert-danger {
            background-color: rgba(220, 53, 69, 0.9);
            color: white;
        }
        
        .login-link {
            color: rgba(255, 255, 255, 0.8);
            margin-top: 1.5rem;
            display: block;
        }
        
        .login-link a {
            color: var(--accent-color);
            font-weight: 500;
        }
        
        .login-link a:hover {
            color: white;
            text-decoration: none;
        }
        
        @media (max-width: 768px) {
            .register-container {
                padding: 1.5rem;
                margin: 0 1rem;
            }
            
            .register-title {
                font-size: 2rem;
            }
            
            .register-subtitle {
                font-size: 1rem;
            }
        }
    </style>
</head>  
<body>  
    <!-- Навигационная панель -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="logo.png" alt="Логотип музея" class="logo">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php"><i class="bi bi-house-door me-1"></i>Главная</a></li>
                    <li class="nav-item"><a class="nav-link" href="razdel.php"><i class="bi bi-collection me-1"></i>Разделы</a></li>
                    <li class="nav-item"><a class="nav-link" href="quests.php"><i class="bi bi-trophy me-1"></i>Квесты</a></li>
                    <li class="nav-item"><a class="nav-link" href="otziv.php"><i class="bi bi-chat-square-text me-1"></i>Отзывы</a></li>
                    <li class="nav-item"><a class="nav-link" href="login.php"><i class="bi bi-box-arrow-in-right me-1"></i>Войти</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Форма регистрации -->
    <section class="register-section">
        <div class="container">
            <div class="register-container">
                <h1 class="register-title"><i class="bi bi-person-plus me-2"></i>Регистрация</h1>
                <p class="register-subtitle">Создайте новую учетную запись</p>
                
                <?php if (!empty($errorMessage)): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($errorMessage); ?></div>
                <?php endif; ?>
                
                <?php if (!empty($successMessage)): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($successMessage); ?></div>
                <?php endif; ?>
                
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                    <div class="mb-4">
                        <label for="login" class="form-label">Имя пользователя</label>
                        <input type="text" class="form-control" name="login" id="login" required>
                    </div>
                    
                    <div class="mb-4">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" id="email" required>
                    </div>
                    
                    <div class="mb-4">
                        <label for="password" class="form-label">Пароль</label>
                        <input type="password" class="form-control" name="password" id="password" required>
                    </div>
                    
                    <div class="mb-4">
                        <label for="confirm_password" class="form-label">Подтвердите пароль</label>
                        <input type="password" class="form-control" name="confirm_password" id="confirm_password" required>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" name="register" class="btn btn-primary">
                            <i class="bi bi-person-plus me-2"></i>Зарегистрироваться
                        </button>
                    </div>
                    
                    <div class="login-link">
                        Уже есть аккаунт? <a href="login.php">Войдите</a>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const successMessage = document.querySelector('.alert-success');
        if (successMessage) {
            setTimeout(() => {
                window.location.href = 'login.php';
            }, 1500);
        }
    </script>
</body>  
</html>