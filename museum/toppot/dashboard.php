<?php
session_start();
require 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$warning_count = 0;

try {
    $stmt = $db->prepare("SELECT * FROM Registr WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        die("Пользователь не найден");
    }
    
    // Получаем количество активных предупреждений
    $stmt = $db->prepare("SELECT COUNT(*) as warning_count FROM user_warnings WHERE user_id = ? AND expires_at > datetime('now','localtime')");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $warning_count = $result['warning_count'];
    
} catch (PDOException $e) {
    die("Ошибка базы данных: " . $e->getMessage());
}    
?>    
<!DOCTYPE html>    
<html lang="ru">    
<head>    
    <meta charset="UTF-8">    
    <meta name="viewport" content="width=device-width, initial-scale=1.0">    
    <title>Личный кабинет</title>    
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
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.85)), url('museum-bg.jpg') no-repeat center center fixed;
            background-size: cover;
            color: white;
            min-height: 100vh;
        }
        
        .profile-container {
            max-width: 800px;
            margin: 100px auto 50px;
            padding: 2rem;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }
        
        .profile-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .profile-avatar {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid var(--accent-color);
            margin-bottom: 1rem;
        }
        
        .profile-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: white;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }
        
        .profile-subtitle {
            font-size: 1.2rem;
            color: var(--accent-color);
            margin-bottom: 2rem;
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
        
        .form-container {
            max-width: 600px;
            margin: 0 auto;
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
            margin-bottom: 1rem;
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
            color: var(--dark-color);
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-color);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            color: white;
        }
        
        .btn-logout {
            background-color: transparent;
            border: 1px solid var(--accent-color);
            color: var(--accent-color);
            padding: 0.75rem 2rem;
            font-weight: 600;
            border-radius: 50px;
            transition: all 0.3s;
            margin-top: 1rem;
        }
        
        .btn-logout:hover {
            background-color: rgba(220, 53, 69, 0.2);
            border-color: #dc3545;
            color: #dc3545;
        }
        
        .alert {
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 2rem;
        }
        
        .alert-success {
            background-color: rgba(40, 167, 69, 0.9);
            color: white;
            border: none;
        }
        
        .alert-danger {
            background-color: rgba(220, 53, 69, 0.9);
            color: white;
            border: none;
        }
        
        .avatar-upload {
            position: relative;
            display: inline-block;
            margin: 0 auto;
        }
        
        .avatar-upload-label {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background: var(--accent-color);
            color: var(--dark-color);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0,0,0,0.3);
        }
        
        .avatar-upload-label:hover {
            background: var(--secondary-color);
            color: white;
        }
        
        .avatar-upload-input {
            display: none;
        }
        
        @media (max-width: 768px) {
            .profile-container {
                padding: 1.5rem;
                margin: 80px auto 30px;
            }
            
            .profile-title {
                font-size: 2rem;
            }
            
            .profile-subtitle {
                font-size: 1rem;
            }
            
            .profile-avatar {
                width: 120px;
                height: 120px;
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
                    <li class="nav-item"><a class="nav-link" href="razdel.php"><i class="bi bi-collection me-1"></i>Разделы</a></li>
                    <li class="nav-item"><a class="nav-link" href="quests.php"><i class="bi bi-trophy me-1"></i>Квесты</a></li>
                    <li class="nav-item"><a class="nav-link" href="otziv.php"><i class="bi bi-chat-square-text me-1"></i>Отзывы</a></li>
                    <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true): ?>
                        <li class="nav-item">
                            <a class="nav-link text-warning" href="admin_panel.php">
                                <i class="bi bi-shield-lock me-1"></i>Админ-панель
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Профиль -->
    <div class="profile-container">
        <div class="profile-header">
            <div class="avatar-upload">
                <img src="<?php echo !empty($user['avatar']) ? htmlspecialchars($user['avatar']) : 'images/default_avatar.png'; ?>" 
                     class="profile-avatar" 
                     id="avatar-preview"
                     alt="Аватар пользователя"
                     onerror="this.src='images/default_avatar.png'">
                <label for="avatar-input" class="avatar-upload-label" title="Изменить аватар">
                    <i class="bi bi-camera"></i>
                </label>
            </div>
            <h1 class="profile-title"><?php echo htmlspecialchars($user['Login']); ?></h1>
            <p class="profile-subtitle">Личный кабинет</p>
        </div>
        <?php if ($warning_count > 0): ?>
            <div class="alert alert-warning">
                <h4><i class="bi bi-exclamation-triangle-fill"></i> У вас <?php echo $warning_count; ?> активных предупреждений</h4>
                <p>После 3 предупреждений ваш аккаунт будет заблокирован для оставления отзывов.</p>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($_SESSION['success_message']); ?>
                <?php unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($_SESSION['error_message']); ?>
                <?php unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>
        
        <div class="form-container">
            <ul class="nav nav-pills mb-4 justify-content-center" id="profile-tabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="profile-tab" data-bs-toggle="pill" 
                            data-bs-target="#profile" type="button" role="tab" 
                            aria-controls="profile" aria-selected="true">
                        <i class="bi bi-person me-1"></i>Профиль
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="quests-tab" data-bs-toggle="pill" 
                            data-bs-target="#quests" type="button" role="tab" 
                            aria-controls="quests" aria-selected="false">
                        <i class="bi bi-trophy me-1"></i>Мои квесты
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="security-tab" data-bs-toggle="pill" 
                            data-bs-target="#security" type="button" role="tab" 
                            aria-controls="security" aria-selected="false">
                        <i class="bi bi-shield-lock me-1"></i>Безопасность
                    </button>
                </li>
            </ul>
            
            <div class="tab-content" id="profile-tabs-content">
                <!-- Вкладка профиля -->
                <div class="tab-pane fade show active" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                    <form method="POST" action="update_profile.php" enctype="multipart/form-data" id="profile-form">
                        <input type="file" id="avatar-input" name="avatar" class="avatar-upload-input" accept="image/*">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="login" class="form-label">Никнейм</label>
                                    <input type="text" class="form-control" name="login" id="login" 
                                           value="<?php echo htmlspecialchars($user['Login']); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" name="email" id="email" 
                                           value="<?php echo htmlspecialchars($user['Email']); ?>" required>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Скрытые поля для пароля (чтобы не требовалось вводить при изменении аватарки) -->
                        <input type="hidden" name="old_password" value="<?php echo isset($_SESSION['temp_password']) ? $_SESSION['temp_password'] : ''; ?>">
                        <input type="hidden" name="new_password" value="">
                        <input type="hidden" name="confirm_password" value="">
                        
                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i>Сохранить изменения
                            </button>
                            <a href="logout.php" class="btn btn-logout ms-2">
                                <i class="bi bi-box-arrow-right me-2"></i>Выйти
                            </a>
                        </div>
                    </form>
                </div>
                
                <!-- Вкладка квестов -->
                <div class="tab-pane fade" id="quests" role="tabpanel" aria-labelledby="quests-tab">
                    <?php
                    try {
                        $total_score = $user['total_score'] ?? 0;
                        $stmt = $db->prepare("SELECT ps.*, q.title as quest_title, q.difficulty_level,
                            (SELECT SUM(step_score) FROM quest_steps WHERE quest_id = q.quest_id) as max_score
                            FROM player_sessions ps 
                            JOIN quests q ON ps.quest_id = q.quest_id 
                            WHERE ps.player_id = ? ORDER BY ps.start_time DESC");
                        $stmt->execute([$user_id]);
                        $my_sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    } catch (PDOException $e) {
                        $my_sessions = [];
                        $total_score = 0;
                    }
                    ?>
                    <div class="text-center mb-4">
                        <div style="font-size:3rem;font-weight:700;background:linear-gradient(135deg,#4fc3f7,#166088);-webkit-background-clip:text;-webkit-text-fill-color:transparent;">
                            <?= (int)$total_score ?> баллов
                        </div>
                        <p class="opacity-75">Ваш общий рейтинг</p>
                    </div>
                    <?php if (empty($my_sessions)): ?>
                        <div class="text-center py-4">
                            <p class="opacity-75">Вы ещё не проходили квесты</p>
                            <a href="quests.php" class="btn btn-primary"><i class="bi bi-play me-1"></i>Перейти к квестам</a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($my_sessions as $ms): ?>
                            <div style="background:rgba(255,255,255,0.1);border-radius:12px;padding:1rem;margin-bottom:0.75rem;border:1px solid rgba(255,255,255,0.15);">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?= htmlspecialchars($ms['quest_title']) ?></strong>
                                        <br>
                                        <small class="opacity-75"><?= date('d.m.Y H:i', strtotime($ms['start_time'])) ?></small>
                                    </div>
                                    <div class="text-end">
                                        <?php if ($ms['status'] === 'completed'): ?>
                                            <span class="badge bg-success"><?= $ms['session_score'] ?> / <?= (int)$ms['max_score'] ?></span>
                                        <?php elseif ($ms['status'] === 'in_progress'): ?>
                                            <a href="play_quest.php?quest_id=<?= $ms['quest_id'] ?>" class="badge bg-warning text-dark text-decoration-none">Продолжить</a>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Прерван</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <!-- Вкладка безопасности -->
                <div class="tab-pane fade" id="security" role="tabpanel" aria-labelledby="security-tab">
                    <form method="POST" action="update_profile.php" enctype="multipart/form-data" id="security-form">
                        <div class="mb-3">
                            <label for="old_password_sec" class="form-label">Текущий пароль</label>
                            <input type="password" class="form-control" name="old_password" id="old_password_sec" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="new_password_sec" class="form-label">Новый пароль</label>
                            <input type="password" class="form-control" name="new_password" id="new_password_sec" 
                                   placeholder="Оставьте пустым, если не хотите менять">
                            <small class="text-muted">Минимум 8 символов</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirm_password_sec" class="form-label">Подтвердите новый пароль</label>
                            <input type="password" class="form-control" name="confirm_password" id="confirm_password_sec" 
                                   placeholder="Повторите новый пароль">
                        </div>
                        
                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-shield-check me-2"></i>Обновить пароль
                            </button>
                            <a href="logout.php" class="btn btn-logout ms-2">
                                <i class="bi bi-box-arrow-right me-2"></i>Выйти
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Просмотр загружаемой аватарки перед отправкой
        document.getElementById('avatar-input').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    document.getElementById('avatar-preview').src = event.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
        
        // Валидация формы безопасности
        document.getElementById('security-form').addEventListener('submit', function(e) {
            const newPassword = document.getElementById('new_password_sec');
            const confirmPassword = document.getElementById('confirm_password_sec');
            
            if (newPassword.value !== confirmPassword.value) {
                e.preventDefault();
                alert('Новый пароль и подтверждение пароля не совпадают.');
            }
        });
        
        // Обработка ошибки загрузки аватарки
        document.getElementById('avatar-preview').addEventListener('error', function() {
            this.src = 'images/default_avatar.png';
        });
    </script>
</body>
</html>