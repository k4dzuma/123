<?php 
session_start(); 
require 'db_connection.php'; 

if (!isset($_SESSION['user_id'])) { 
    header("Location: login.php");
    exit(); 
} 

$userId = $_SESSION['user_id']; 

// Получаем текущие данные пользователя 
$stmt = $db->prepare("SELECT * FROM Registr WHERE id = ?"); 
$stmt->execute([$userId]); 
$user = $stmt->fetch(PDO::FETCH_ASSOC); 

if (!$user) { 
    $_SESSION['error_message'] = "Пользователь не найден.";
    header("Location: dashboard.php");
    exit(); 
} 

// Обработка данных формы 
if ($_SERVER['REQUEST_METHOD'] === 'POST') { 
    $login = $_POST['login'] ?? $user['Login'];
    $email = $_POST['email'] ?? $user['Email'];
    $oldPassword = $_POST['old_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $avatarPath = $user['avatar'] ?? 'images/default_avatar.png';

    // Проверка пароля только если он меняется
    if (!empty($newPassword)) {
        if (!password_verify($oldPassword, $user['password'])) { 
            $_SESSION['error_message'] = "Неправильный старый пароль."; 
            header("Location: dashboard.php");
            exit();
        } 
        
        if ($newPassword !== $confirmPassword) { 
            $_SESSION['error_message'] = "Новый пароль и подтверждение не совпадают."; 
            header("Location: dashboard.php");
            exit();
        }
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    }

    // Обработка загрузки аватарки
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/avatars/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        // Валидация изображения
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
        $detectedType = finfo_file($fileInfo, $_FILES['avatar']['tmp_name']);
        finfo_close($fileInfo);
        
        if (!in_array($detectedType, $allowedTypes)) {
            $_SESSION['error_message'] = "Допустимы только JPG, PNG и GIF.";
            header("Location: dashboard.php");
            exit();
        }
        
        if ($_FILES['avatar']['size'] > 2097152) {
            $_SESSION['error_message'] = "Максимальный размер файла - 2MB.";
            header("Location: dashboard.php");
            exit();
        }
        
        $extension = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
        $avatarName = 'avatar_'.$userId.'_'.time().'.'.$extension;
        $avatarPath = $uploadDir.$avatarName;
        
        if (!move_uploaded_file($_FILES['avatar']['tmp_name'], $avatarPath)) {
            $_SESSION['error_message'] = "Ошибка загрузки файла.";
            header("Location: dashboard.php");
            exit();
        }
        
        // Удаляем старую аватарку, если она не дефолтная
        if (!empty($user['avatar']) && $user['avatar'] !== 'images/default_avatar.png' && file_exists($user['avatar'])) {
            unlink($user['avatar']);
        }
    }

    try {
        if (!empty($newPassword)) {
            $query = "UPDATE Registr SET Login = ?, Email = ?, password = ?, avatar = ? WHERE id = ?";
            $params = [$login, $email, $hashedPassword, $avatarPath, $userId];
        } else {
            $query = "UPDATE Registr SET Login = ?, Email = ?, avatar = ? WHERE id = ?";
            $params = [$login, $email, $avatarPath, $userId];
        }

        $stmt = $db->prepare($query);
        $stmt->execute($params);
        
        $_SESSION['success_message'] = "Данные успешно обновлены!";
        $_SESSION['user_login'] = $login;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_avatar'] = $avatarPath;
        
        header("Location: dashboard.php");
        exit();
        
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Ошибка базы данных: ".$e->getMessage();
        header("Location: dashboard.php");
        exit();
    }
}
?>