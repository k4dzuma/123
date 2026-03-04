<?php
session_start();
require 'db_connection.php'; // Подключение к базе данных

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['edit'])) {
        $commentId = $_POST['comment_id'];
        
        // Получение текущего текста комментария
        $stmt = $db->prepare("SELECT * FROM Comments WHERE id = :id AND user_id = :user_id");
        $stmt->bindParam(':id', $commentId);
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        $stmt->execute();
        $comment = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$comment) {
            die("Комментарий не найден или у вас нет прав на его редактирование.");
        }
    }

    if (isset($_POST['update'])) {
        $updatedCommentText = filter_input(INPUT_POST, 'updated_comment', FILTER_SANITIZE_STRING);
        
        // Обновление комментария
        $stmt = $db->prepare("UPDATE Comments SET comment = :comment WHERE id = :id AND user_id = :user_id");
        $stmt->bindParam(':comment', $updatedCommentText);
        $stmt->bindParam(':id', $_POST['comment_id']);
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        $stmt->execute();

        header("Location: index.php"); // Перенаправление на главную страницу после обновления
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Редактировать комментарий</title>
</head>
<body>
    <form method="POST" action="">
        <input type="hidden" name="comment_id" value="<?php echo htmlspecialchars($comment['id']); ?>">
        <textarea name="updated_comment"><?php echo htmlspecialchars($comment['comment']); ?></textarea>
        <button type="submit" name="update">Обновить</button>
    </form>
</body>
</html>
