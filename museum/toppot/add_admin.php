<?php
session_start();
require 'db_connection.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
    $pin = trim($_POST['pin'] ?? '');

    if (!$user_id) {
        die("Некорректный ID пользователя");
    }

    if (!preg_match('/^\d{6}$/', $pin)) {
        die("PIN-код должен содержать 6 цифр");
    }
    
    // Проверяем, что пользователь существует
    $stmt = $db->prepare("SELECT id FROM Registr WHERE id = ?");
    $stmt->execute([$user_id]);
    if (!$stmt->fetch()) {
        die("Пользователь не найден");
    }
    
    // Хешируем PIN
    $pin_hash = password_hash($pin, PASSWORD_DEFAULT);
    
    // Добавляем в admin_security
    $stmt = $db->prepare("INSERT INTO admin_security (user_id, pin) VALUES (?, ?)");
    $stmt->execute([$user_id, $pin_hash]);
    
    echo "Администратор успешно добавлен!";
    exit;
}
?>

<form method="post">
    <label>ID пользователя: <input type="number" name="user_id" required></label><br>
    <label>PIN-код (6 цифр): <input type="password" name="pin" pattern="\d{6}" required></label><br>
    <button type="submit">Добавить администратора</button>
</form>