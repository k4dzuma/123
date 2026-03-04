<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('MAX_PIN_ATTEMPTS', 3);
define('PIN_LOCKOUT_TIME', 15 * 60); // 15 минут в секундах

try {
    require 'db_connection.php';
    
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST['email']) && isset($_POST['password'])) {
            handleRegularLogin($db);
        } elseif (isset($_POST['pin']) && isset($_SESSION['temp_admin_id'])) {
            handlePinVerification($db);
        }
    }
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}

function handleRegularLogin($db) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $db->prepare("SELECT * FROM Registr WHERE Email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['login'] = $user['Login'];
        $_SESSION['email'] = $user['Email'];
        
        // Проверяем, является ли пользователь администратором
        $stmt = $db->prepare("SELECT * FROM admin_security WHERE user_id = :id");
        $stmt->bindParam(':id', $user['id']);
        $stmt->execute();
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($admin) {
            $_SESSION['awaiting_pin'] = true;
            $_SESSION['temp_admin_id'] = $user['id'];
            header("Location: login.php?pin=1");
            exit();
        } else {
            $_SESSION['is_admin'] = false;
            header("Location: dashboard.php");
            exit();
        }
    } else {
        $_SESSION['login_error'] = "Неверный email или пароль";
        header("Location: login.php");
        exit();
    }
}

function handlePinVerification($db) {
    $pin = trim($_POST['pin']);
    $admin_id = $_SESSION['temp_admin_id'];
    
    // Получаем данные администратора
    $stmt = $db->prepare("SELECT * FROM admin_security WHERE user_id = :id");
    $stmt->bindParam(':id', $admin_id);
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$admin) {
        $_SESSION['login_error'] = "Ошибка доступа к учетной записи администратора";
        header("Location: login.php");
        exit();
    }
    
    // Проверяем блокировку
    if ($admin['attempts'] >= MAX_PIN_ATTEMPTS && $admin['last_attempt']) {
        $last_attempt = strtotime($admin['last_attempt']);
        $remaining_time = PIN_LOCKOUT_TIME - (time() - $last_attempt);
        
        if ($remaining_time > 0) {
            $_SESSION['pin_error'] = "Превышено количество попыток. Попробуйте через " 
                                   . ceil($remaining_time/60) . " минут.";
            header("Location: login.php?pin=1");
            exit();
        } else {
            // Сбрасываем блокировку если время истекло
            $db->prepare("UPDATE admin_security SET attempts = 0 WHERE user_id = :id")
               ->execute([':id' => $admin_id]);
        }
    }
    
    // Проверяем PIN-код
    if (password_verify($pin, $admin['pin'])) {
        // Успешный вход
        $db->prepare("UPDATE admin_security SET attempts = 0 WHERE user_id = :id")
           ->execute([':id' => $admin_id]);
        
        $_SESSION['is_admin'] = true;
        $_SESSION['user_id'] = $admin_id;
        unset($_SESSION['awaiting_pin']);
        unset($_SESSION['temp_admin_id']);
        header("Location: admin_panel.php");
        exit();
    } else {
        // Неверный PIN
        $db->prepare("UPDATE admin_security SET attempts = attempts + 1, last_attempt = datetime('now','localtime') WHERE user_id = :id")
           ->execute([':id' => $admin_id]);
        
        $_SESSION['pin_error'] = "Неверный PIN-код";
        header("Location: login.php?pin=1");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход - Виртуальный музей</title>
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
        
        .login-section {
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
        
        .login-section::before {
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
        
        .login-container {
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
        
        .login-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            color: white;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }
        
        .login-subtitle {
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

        .register-subtitle {
                 font-size: 1.2rem;
            margin-bottom: 2rem;
            color: var(--accent-color);
            }
        
        .alert-success {
            background-color: rgba(40, 167, 69, 0.9);
            color: white;
        }
        
        .alert-danger {
            background-color: rgba(220, 53, 69, 0.9);
            color: white;
        }
        
        .register-link {
            color: rgba(255, 255, 255, 0.8);
            margin-top: 1.5rem;
            display: block;
        }
        
        .register-link a {
            color: var(--accent-color);
            font-weight: 500;
        }
        
        .register-link a:hover {
            color: white;
            text-decoration: none;
        }
        
        @media (max-width: 768px) {
            .login-container {
                padding: 1.5rem;
                margin: 0 1rem;
            }
            
            .login-title {
                font-size: 2rem;
            }
            
            .login-subtitle {
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
                    <li class="nav-item"><a class="nav-link" href="register.php"><i class="bi bi-person-plus me-1"></i>Регистрация</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <section class="login-section">
        <div class="container">
            <div class="login-container">
                <?php if (isset($_GET['pin']) && isset($_SESSION['temp_admin_id'])): ?>
                    <!-- Форма ввода PIN-кода для администратора -->
                    <h1 class="login-title"><i class="bi bi-shield-lock me-2"></i>Введите PIN-код</h1>
                    <p class="login-subtitle">Для доступа к админ-панели требуется подтверждение</p>
                    
                    <?php if (!empty($_SESSION['pin_error'])): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['pin_error']); unset($_SESSION['pin_error']); ?></div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="mb-4">
                            <label for="pin" class="form-label">6-значный PIN-код</label>
                            <input type="password" class="form-control" name="pin" id="pin" 
                                   pattern="\d{6}" maxlength="6" required
                                   placeholder="Введите 6 цифр">
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-arrow-right me-2"></i>Подтвердить
                            </button>
                        </div>
                    </form>
                <?php else: ?>
                    <!-- Обычная форма входа -->
                    <h1 class="login-title"><i class="bi bi-box-arrow-in-right me-2"></i>Вход</h1>
                    <p class="login-subtitle">Введите свои учетные данные</p>
                    
                    <?php if (!empty($_SESSION['login_error'])): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['login_error']); unset($_SESSION['login_error']); ?></div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="mb-4">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" id="email" required>
                        </div>
                        
                        <div class="mb-4">
                            <label for="password" class="form-label">Пароль</label>
                            <input type="password" class="form-control" name="password" id="password" required>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" name="login" class="btn btn-primary">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Войти
                            </button>
                        </div>
                    </form>

                    <p class="register-link">Нет аккаунта? <a href="register.php">Зарегистрируйтесь</a></p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Валидация PIN-кода (только цифры)
        document.getElementById('pin')?.addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '');
        });
    </script>
</body>
</html>