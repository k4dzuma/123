<?php
try {
    require 'db_connection.php';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Получаем данные из формы
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        $nazvanie = trim($_POST['nazvanie'] ?? '');
        $text = trim($_POST['text'] ?? '');

        if (!$id) {
            throw new Exception('Некорректный идентификатор записи.');
        }
        if ($nazvanie === '' || $text === '') {
            throw new Exception('Название и текст обязательны.');
        }

        // Проверяем, загружено ли новое изображение
        if (isset($_FILES['img']) && $_FILES['img']['error'] === UPLOAD_ERR_OK) {
            $target_dir = "uploads/"; // Папка для хранения изображений
            $target_file = $target_dir . basename($_FILES["img"]["name"]);
            
            // Проверяем, существует ли папка uploads, если нет - создаем
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0755, true);
            }

            // Перемещаем загруженный файл
            if (move_uploaded_file($_FILES["img"]["tmp_name"], $target_file)) {
                // Обновляем данные в базе с новым изображением
                $stmt = $db->prepare("UPDATE col_text SET Nazvanie = :nazvanie, Text = :text, Img = :img WHERE id = :id");
                $stmt->bindParam(':img', $target_file);
            } else {
                throw new Exception('Не удалось переместить загруженный файл.');
            }
        } else {
            // Обновляем данные в базе без изменения изображения
            $stmt = $db->prepare("UPDATE col_text SET Nazvanie = :nazvanie, Text = :text WHERE id = :id");
        }

        // Общие параметры
        $stmt->bindParam(':nazvanie', $nazvanie);
        $stmt->bindParam(':text', $text);
        $stmt->bindParam(':id', $id);
        
        // Выполняем запрос
        $stmt->execute();

        // Перенаправляем обратно на страницу
        header("Location: razdel.php");
        exit();
    }
} catch (PDOException $e) {
    echo "Ошибка: " . htmlspecialchars($e->getMessage());
} catch (Exception $e) {
    echo "Ошибка: " . htmlspecialchars($e->getMessage());
}
?>
