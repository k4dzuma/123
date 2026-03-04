<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
$errorMessage = "";
$successMessage = "";
$is_blocked = false;
$warning_count = 0;

// Подключение к базе данных
try {
    require_once 'db_connection.php';

    // Проверка блокировки
    if (isset($_SESSION['user_id'])) {
        $stmt = $db->prepare("SELECT COUNT(*) as warning_count FROM user_warnings WHERE user_id = ? AND expires_at > datetime('now','localtime')");
        $stmt->execute([$_SESSION['user_id']]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $warning_count = $result['warning_count'];
        $is_blocked = $warning_count >= 3;
    }

    // Обработка отправки комментария
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['commentText'])) {
        if (!isset($_SESSION['user_id'])) {
            throw new Exception("Необходимо войти в систему для отправки отзыва");
        }
        if ($is_blocked) {
            throw new Exception("Ваш аккаунт заблокирован для оставления отзывов");
        }

        $commentText = trim($_POST['commentText']);
        if (empty($commentText)) {
            throw new Exception("Текст комментария не может быть пустым");
        }

        $parent_id = $_POST['parent_id'] ?? null;
        $image_path = null;

        // Обработка загрузки изображения
        if (isset($_FILES['commentImage']) && $_FILES['commentImage']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $fileType = mime_content_type($_FILES['commentImage']['tmp_name']);
            
            if (!in_array($fileType, $allowedTypes)) {
                throw new Exception("Разрешены только изображения JPG, PNG или GIF");
            }

            $filename = uniqid() . '_' . basename($_FILES['commentImage']['name']);
            if (move_uploaded_file($_FILES['commentImage']['tmp_name'], $uploadDir . $filename)) {
                $image_path = $uploadDir . $filename;
            }
        }

        $stmt = $db->prepare("INSERT INTO Comments (user_id, comment, image_path, parent_id) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $commentText, $image_path, $parent_id]);
        
        $_SESSION['success_message'] = "Отзыв успешно опубликован!";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

    // Обработка редактирования комментария
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['editCommentId'])) {
        $comment_id = $_POST['editCommentId'];
        $commentText = trim($_POST['editCommentText']);
        
        $stmt = $db->prepare("SELECT user_id FROM Comments WHERE id = ?");
        $stmt->execute([$comment_id]);
        $comment = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($comment && ($comment['user_id'] == $_SESSION['user_id'] || (isset($_SESSION['is_admin']) && $_SESSION['is_admin']))) {
            $stmt = $db->prepare("UPDATE Comments SET comment = ? WHERE id = ?");
            $stmt->execute([$commentText, $comment_id]);
            
            $_SESSION['success_message'] = "Комментарий успешно обновлен!";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            throw new Exception("Вы не можете редактировать этот комментарий");
        }
    }

    // Обработка удаления комментария
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['deleteCommentId'])) {
        $comment_id = $_POST['deleteCommentId'];
        
        $stmt = $db->prepare("SELECT user_id FROM Comments WHERE id = ?");
        $stmt->execute([$comment_id]);
        $comment = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($comment && ($comment['user_id'] == $_SESSION['user_id'] || (isset($_SESSION['is_admin']) && $_SESSION['is_admin']))) {
            $stmt = $db->prepare("DELETE FROM Comments WHERE id = ?");
            $stmt->execute([$comment_id]);
            
            $_SESSION['success_message'] = "Комментарий успешно удален!";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            throw new Exception("Вы не можете удалить этот комментарий");
        }
    }

    // Обработка выдачи предупреждения
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['warn_user'])) {
        if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
            throw new Exception("Только администраторы могут выдавать предупреждения");
        }

        $user_id = $_POST['user_id'];
        $comment_id = $_POST['comment_id'];
        $reason = $_POST['reason'];

        $db->beginTransaction();

        try {
            // Добавляем предупреждение
            $expires_at = date('Y-m-d H:i:s', strtotime('+7 days'));
            $stmt = $db->prepare("INSERT INTO user_warnings (user_id, admin_id, reason, expires_at) VALUES (?, ?, ?, ?)");
            $stmt->execute([$user_id, $_SESSION['user_id'], $reason, $expires_at]);

            // Удаляем комментарий
            $stmt = $db->prepare("DELETE FROM Comments WHERE id = ?");
            $stmt->execute([$comment_id]);

            $db->commit();
            $_SESSION['success_message'] = "Предупреждение выдано и комментарий удален";
            header("Location: otziv.php");
            exit();
        } catch (Exception $e) {
            $db->rollBack();
            throw new Exception("Ошибка при выдаче предупреждения: " . $e->getMessage());
        }
    }

    // Получение комментариев
    $stmt = $db->query("
        SELECT c.*, r.Login, r.avatar,
               (SELECT COUNT(*) FROM Comments AS replies WHERE replies.parent_id = c.id) AS reply_count
        FROM Comments c 
        JOIN Registr r ON c.user_id = r.id
        WHERE c.parent_id IS NULL
        ORDER BY c.created_at DESC
    ");
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($comments as &$comment) {
        $stmt = $db->prepare("
            SELECT c.*, r.Login, r.avatar 
            FROM Comments c 
            JOIN Registr r ON c.user_id = r.id
            WHERE c.parent_id = :parent_id
            ORDER BY c.created_at ASC
        ");
        $stmt->execute([':parent_id' => $comment['id']]);
        $comment['replies'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

} catch (Exception $e) {
    $errorMessage = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <title>Отзывы - Виртуальный музей "Человек и Время"</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: rgba(74, 111, 165, 0.7);
            --secondary-color: #166088;
            --accent-color: #4fc3f7;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
            --error-color: #e74c3c;
            --success-color: #2ecc71;
            --action-color: #5cb85c;
            --warning-color: #ffc107;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            background-attachment: fixed;
            color: #333;
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }
        
        .main-content {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-top: 80px;
            margin-bottom: 30px;
            padding: 30px;
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
        
        .section-title {
            color: var(--secondary-color);
            font-weight: 700;
            margin-bottom: 2rem;
            text-align: center;
            position: relative;
            padding-bottom: 15px;
        }
        
        .section-title::after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 3px;
            background-color: var(--accent-color);
        }
        
        .comment-form {
            background-color: var(--light-color);
            border-radius: 10px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        }
        
        .form-control {
            border-radius: 8px;
            border: 1px solid var(--accent-color);
            padding: 12px 15px;
            margin-bottom: 15px;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.2rem rgba(22, 96, 136, 0.25);
        }
        
        .btn-primary {
            background-color: var(--accent-color);
            border: none;
            border-radius: 50px;
            padding: 0.8rem 2rem;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-color);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .btn-action {
            background-color: var(--action-color);
            border: none;
            border-radius: 50px;
            padding: 0.5rem 1.5rem;
            transition: all 0.3s;
            color: white;
        }
        
        .btn-action:hover {
            background-color: #4cae4c;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            color: white;
        }
        
        .btn-danger {
            background-color: var(--error-color);
            border: none;
            border-radius: 50px;
            padding: 0.5rem 1.5rem;
            transition: all 0.3s;
        }
        
        .btn-danger:hover {
            background-color: #c0392b;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .comment-card {
            background-color: var(--light-color);
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            transition: all 0.3s;
        }
        
        .comment-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        
        .comment-header {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 1rem;
            border: 2px solid var(--accent-color);
        }
        
        .user-name {
            font-weight: 600;
            color: var(--secondary-color);
        }
        
        .comment-date {
            font-size: 0.8rem;
            color: #777;
            margin-left: 0.5rem;
        }
        
        .comment-text {
            margin-bottom: 1rem;
            line-height: 1.6;
            color: #555;
        }
        
        .comment-image {
            max-width: 100%;
            max-height: 300px;
            border-radius: 8px;
            margin-bottom: 1rem;
            cursor: pointer;
            transition: transform 0.3s;
            border: 2px solid var(--accent-color);
        }
        
        .comment-image:hover {
            transform: scale(1.02);
        }
        
        .comment-actions {
            display: flex;
            gap: 10px;
            margin-top: 1rem;
        }
        
        .reply-form {
            margin-top: 1rem;
            padding-left: 2rem;
            border-left: 3px solid var(--accent-color);
            display: none;
        }
        
        .replies-section {
            margin-top: 1.5rem;
            padding-left: 2rem;
            border-left: 3px solid var(--accent-color);
        }
        
        .reply-card {
            background-color: rgba(79, 195, 247, 0.1);
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        
        .show-replies-btn {
            background: none;
            border: none;
            color: var(--accent-color);
            cursor: pointer;
            font-size: 0.9rem;
            padding: 0;
            margin-top: 0.5rem;
            font-weight: 600;
        }
        
        .show-replies-btn:hover {
            text-decoration: underline;
            color: var(--secondary-color);
        }
        
        .alert {
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .alert-success {
            background-color: rgba(46, 204, 113, 0.2);
            color: var(--success-color);
            border: 1px solid var(--success-color);
        }
        
        .alert-danger {
            background-color: rgba(231, 76, 60, 0.2);
            color: var(--error-color);
            border: 1px solid var(--error-color);
        }
        
        .alert-warning {
            background-color: rgba(255, 193, 7, 0.2);
            color: #856404;
            border: 1px solid var(--warning-color);
        }
        
        .image-preview {
            max-width: 150px;
            max-height: 150px;
            margin-top: 10px;
            margin-bottom: 15px;
            display: none;
            border-radius: 8px;
            border: 2px solid var(--accent-color);
        }
        
        .edit-form {
            display: none;
            margin-top: 1rem;
        }
        
        .login-prompt {
            text-align: center;
            padding: 2rem;
            background-color: var(--light-color);
            border-radius: 10px;
            margin-bottom: 2rem;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        }
        
        .footer {
            background-color: var(--dark-color);
            color: white;
            padding: 2rem 0;
            text-align: center;
        }
        
        .social-links a {
            color: white;
            margin: 0 0.5rem;
            font-size: 1.2rem;
            transition: color 0.3s;
        }
        
        .social-links a:hover {
            color: var(--accent-color);
        }
        
        .admin-warning-btn {
            background-color: var(--warning-color);
            color: black;
            border: none;
            border-radius: 50px;
            padding: 0.5rem 1.5rem;
            transition: all 0.3s;
        }
        
        .admin-warning-btn:hover {
            background-color: #e0a800;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .warning-badge {
            background-color: var(--warning-color);
            color: black;
            border-radius: 50px;
            padding: 0.2rem 0.8rem;
            font-size: 0.8rem;
            margin-left: 0.5rem;
        }
        
        @media (max-width: 992px) {
            .navbar-brand img {
                height: 50px;
            }
        }
        
        @media (max-width: 768px) {
            .main-content {
                margin-top: 70px;
                padding: 20px;
            }
            
            .comment-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .user-avatar {
                margin-bottom: 0.5rem;
            }
            
            .comment-actions {
                flex-wrap: wrap;
            }
            
            .replies-section, .reply-form {
                padding-left: 1rem;
            }
        }
        
        @media (max-width: 576px) {
            .main-content {
                margin-top: 60px;
                padding: 15px;
            }
            
            .section-title {
                font-size: 1.5rem;
            }
            
            .comment-form, .comment-card, .login-prompt {
                padding: 1rem;
            }
            
            .btn-primary, .btn-action, .btn-danger {
                width: 100%;
                margin-bottom: 0.5rem;
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

    <!-- Основное содержимое -->
    <div class="container main-content">
        <?php if ($errorMessage): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message']); ?></div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['user_id']) && $is_blocked): ?>
            <div class="alert alert-danger">
                <h4><i class="bi bi-exclamation-triangle-fill"></i> Ваш аккаунт заблокирован!</h4>
                <p>Вы получили 3 или более предупреждений и не можете оставлять отзывы.</p>
            </div>
        <?php elseif (isset($_SESSION['user_id']) && $warning_count > 0): ?>
            <div class="alert alert-warning">
                <h4><i class="bi bi-exclamation-triangle-fill"></i> У вас <?php echo $warning_count; ?> активных предупреждений</h4>
                <p>После 3 предупреждений ваш аккаунт будет заблокирован для оставления отзывов.</p>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['user_id']) && !$is_blocked): ?>
            <div class="comment-form mb-4">
                <h3 class="section-title">Оставьте свой отзыв</h3>
                <form method="POST" enctype="multipart/form-data" id="commentForm">
                    <div class="form-group mb-3">
                        <label for="commentText" class="form-label fw-bold">Ваш отзыв:</label>
                        <textarea class="form-control p-3" 
                                  id="commentText" 
                                  name="commentText" 
                                  rows="5" 
                                  placeholder="Напишите здесь ваш отзыв о музее..." 
                                  style="font-size: 1.1rem;"
                                  maxlength="1000"
                                  required></textarea>
                        <div class="form-text text-end"><span id="charCount">0</span>/1000 символов</div>
                    </div>
                    
                    <div class="form-group mb-4">
                        <label class="d-block mb-2 fw-bold">Дополнительно:</label>
                        <div class="d-flex align-items-center gap-3">
                            <label for="commentImage" class="btn btn-action flex-grow-0">
                                <i class="fas fa-image me-2"></i> Прикрепить фото
                                <input type="file" id="commentImage" name="commentImage" accept="image/*" style="display: none;">
                            </label>
                            <span id="fileName" class="text-muted small">Файл не выбран</span>
                        </div>
                        <img id="imagePreview" class="image-preview mt-2" src="#" alt="Предпросмотр изображения">
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-lg w-100">
                        <i class="bi bi-send me-2"></i>Опубликовать отзыв
                    </button>
                </form>
            </div>
        <?php elseif (!isset($_SESSION['user_id'])): ?>
            <div class="login-prompt">
                <h3 class="section-title">Хотите оставить отзыв?</h3>
                <p>Чтобы оставить отзыв, пожалуйста войдите в систему</p>
                <a href="login.php" class="btn btn-primary"><i class="bi bi-box-arrow-in-right me-2"></i>Войти</a>
            </div>
        <?php endif; ?>
        
        <section class="comments-section">
            <h2 class="section-title">Отзывы посетителей</h2>
            
            <?php if (empty($comments)): ?>
                <div class="alert alert-info">Пока нет отзывов. Будьте первым!</div>
            <?php else: ?>
                <?php foreach ($comments as $comment): ?>
                    <div class="comment-card" id="comment-<?php echo $comment['id']; ?>">
                        <div class="comment-header">
                            <?php if (!empty($comment['avatar'])): ?>
                                <img src="<?php echo htmlspecialchars($comment['avatar']); ?>" class="user-avatar" alt="Аватар пользователя">
                            <?php else: ?>
                                <div class="user-avatar" style="background-color: #4fc3f7; color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 1.2rem;">
                                    <?php echo strtoupper(substr($comment['Login'], 0, 1)); ?>
                                </div>
                            <?php endif; ?>
                            <div>
                                <span class="user-name"><?php echo htmlspecialchars($comment['Login']); ?></span>
                                <span class="comment-date"><?php echo date('d.m.Y H:i', strtotime($comment['created_at'])); ?></span>
                            </div>
                        </div>
                        
                        <div class="comment-text"><?php echo nl2br(htmlspecialchars($comment['comment'])); ?></div>
                        
                        <?php if ($comment['image_path']): ?>
                            <img src="<?php echo htmlspecialchars($comment['image_path']); ?>" class="comment-image" alt="Изображение к отзыву">
                        <?php endif; ?>
                        
                        <div class="comment-actions">
                            <button class="btn btn-action reply-btn" data-comment-id="<?php echo $comment['id']; ?>">
                                <i class="bi bi-reply me-1"></i> Ответить
                            </button>
                            
                            <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $comment['user_id']): ?>
                                <button class="btn btn-action edit-btn" data-comment-id="<?php echo $comment['id']; ?>">
                                    <i class="bi bi-pencil me-1"></i> Редактировать
                                </button>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="deleteCommentId" value="<?php echo $comment['id']; ?>">
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('Вы уверены, что хотите удалить этот отзыв?');">
                                        <i class="bi bi-trash me-1"></i> Удалить
                                    </button>
                                </form>
                            <?php endif; ?>
                            
                            <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                                <button class="btn admin-warning-btn warn-btn" 
                                        data-user-id="<?php echo $comment['user_id']; ?>"
                                        data-user-name="<?php echo htmlspecialchars($comment['Login']); ?>">
                                    <i class="bi bi-exclamation-triangle me-1"></i> Предупредить
                                </button>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Форма редактирования -->
                        <div class="edit-form" id="edit-form-<?php echo $comment['id']; ?>">
                            <form method="POST">
                                <div class="form-group">
                                    <textarea class="form-control" name="editCommentText" rows="3" required><?php echo htmlspecialchars($comment['comment']); ?></textarea>
                                </div>
                                <input type="hidden" name="editCommentId" value="<?php echo $comment['id']; ?>">
                                <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i>Сохранить</button>
                                <button type="button" class="btn btn-action cancel-edit-btn" data-comment-id="<?php echo $comment['id']; ?>">
                                    <i class="bi bi-x-circle me-1"></i>Отмена
                                </button>
                            </form>
                        </div>
                        
                        <!-- Форма ответа -->
                        <div class="reply-form" id="reply-form-<?php echo $comment['id']; ?>">
                            <form method="POST" enctype="multipart/form-data">
                                <div class="form-group">
                                    <textarea class="form-control" name="commentText" rows="2" placeholder="Напишите ваш ответ..." required></textarea>
                                </div>
                                <input type="hidden" name="parent_id" value="<?php echo $comment['id']; ?>">
                                <button type="submit" class="btn btn-primary"><i class="bi bi-send me-1"></i>Отправить ответ</button>
                                <button type="button" class="btn btn-action cancel-reply-btn" data-comment-id="<?php echo $comment['id']; ?>">
                                    <i class="bi bi-x-circle me-1"></i>Отмена
                                </button>
                            </form>
                        </div>
                        
                        <!-- Секция с ответами -->
                        <?php if ($comment['reply_count'] > 0): ?>
                            <button class="show-replies-btn" data-comment-id="<?php echo $comment['id']; ?>">
                                <i class="bi bi-chat-left-text me-1"></i> Показать ответы (<?php echo $comment['reply_count']; ?>)
                            </button>
                            
                            <div class="replies-section" id="replies-<?php echo $comment['id']; ?>" style="display: none;">
                                <?php foreach ($comment['replies'] as $reply): ?>
                                    <div class="reply-card">
                                        <div class="comment-header">
                                            <?php if (!empty($reply['avatar'])): ?>
                                                <img src="<?php echo htmlspecialchars($reply['avatar']); ?>" class="user-avatar" alt="Аватар пользователя">
                                            <?php else: ?>
                                                <div class="user-avatar" style="background-color: #4fc3f7; color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 1.2rem;">
                                                    <?php echo strtoupper(substr($reply['Login'], 0, 1)); ?>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <span class="user-name"><?php echo htmlspecialchars($reply['Login']); ?></span>
                                                <span class="comment-date"><?php echo date('d.m.Y H:i', strtotime($reply['created_at'])); ?></span>
                                            </div>
                                        </div>
                                        <div class="comment-text"><?php echo nl2br(htmlspecialchars($reply['comment'])); ?></div>
                                        <?php if ($reply['image_path']): ?>
                                            <img src="<?php echo htmlspecialchars($reply['image_path']); ?>" class="comment-image" alt="Изображение к ответу">
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
    </div>

    <!-- Подвал -->
    <footer class="footer">
        <div class="container text-center">
            <p>&copy; <?= date('Y'); ?> Виртуальный музей "Человек и Время". Все права защищены.</p>
            <div class="social-links">
                <a href="#"><i class="bi bi-facebook"></i></a>
                <a href="#"><i class="bi bi-twitter"></i></a>
                <a href="#"><i class="bi bi-instagram"></i></a>
                <a href="#"><i class="bi bi-youtube"></i></a>
            </div>
        </div>
    </footer>

    <!-- Модальное окно предупреждения -->
    <div class="modal fade" id="warnUserModal" tabindex="-1" aria-labelledby="warnUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="warnUserModalLabel">Выдать предупреждение</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="user_id" id="warn_user_id">
                        <input type="hidden" name="comment_id" id="warn_comment_id">
                        <p>Вы собираетесь выдать предупреждение пользователю <strong id="warn_user_name"></strong>.</p>
                        <p>Комментарий будет удален, а пользователь получит предупреждение.</p>
                        
                        <div class="mb-3">
                            <label for="reason" class="form-label">Причина предупреждения</label>
                            <textarea class="form-control" id="reason" name="reason" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                        <button type="submit" name="warn_user" class="btn btn-warning">Выдать предупреждение</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Просмотр изображения перед загрузкой
            $('#commentImage').change(function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('#imagePreview').attr('src', e.target.result).show();
                    }
                    reader.readAsDataURL(file);
                }
            });
            
            // Обработка кнопки "Ответить"
            $(document).on('click', '.reply-btn', function() {
                const commentId = $(this).data('comment-id');
                $('#reply-form-' + commentId).slideToggle();
                $('#edit-form-' + commentId).hide();
            });
            
            // Обработка кнопки "Отмена" для ответа
            $(document).on('click', '.cancel-reply-btn', function() {
                const commentId = $(this).data('comment-id');
                $('#reply-form-' + commentId).slideUp();
            });
            
            // Обработка кнопки "Редактировать"
            $(document).on('click', '.edit-btn', function() {
                const commentId = $(this).data('comment-id');
                $('#edit-form-' + commentId).slideToggle();
                $('#reply-form-' + commentId).hide();
            });
            
            // Обработка кнопки "Отмена" для редактирования
            $(document).on('click', '.cancel-edit-btn', function() {
                const commentId = $(this).data('comment-id');
                $('#edit-form-' + commentId).slideUp();
            });
            
            // Обработка кнопки "Показать ответы"
            $(document).on('click', '.show-replies-btn', function() {
                const commentId = $(this).data('comment-id');
                const repliesSection = $('#replies-' + commentId);
                const icon = $(this).find('i');
                
                repliesSection.slideToggle(function() {
                    if (repliesSection.is(':visible')) {
                        icon.removeClass('bi-chat-left-text').addClass('bi-chat-left-text-fill');
                        $(this).prev().html('<i class="bi bi-chat-left-text-fill me-1"></i> Скрыть ответы');
                    } else {
                        icon.removeClass('bi-chat-left-text-fill').addClass('bi-chat-left-text');
                        $(this).prev().html('<i class="bi bi-chat-left-text me-1"></i> Показать ответы (' + $(this).data('reply-count') + ')');
                    }
                });
            });
            
            // Обработка кнопки предупреждения
            $(document).on('click', '.warn-btn', function() {
                var userId = $(this).data('user-id');
                var userName = $(this).data('user-name');
                var commentId = $(this).closest('.comment-card').attr('id').replace('comment-', '');
                
                $('#warn_user_id').val(userId);
                $('#warn_user_name').text(userName);
                $('#warn_comment_id').val(commentId);
                
                $('#warnUserModal').modal('show');
            });
            
            // Подсчет символов в текстовом поле
            $('#commentText').on('input', function() {
                var length = $(this).val().length;
                $('#charCount').text(length);
            });
        });
    </script>
</body>
</html>