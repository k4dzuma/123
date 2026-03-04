<?php
session_start();
require 'db_connection.php';

header('Content-Type: application/json');

if (!isset($_SESSION['is_admin'])) {
    die(json_encode(['success' => false, 'message' => 'Доступ запрещен']));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];
    
    try {
        $stmt = $db->prepare("SELECT id FROM Comments WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
        $stmt->execute([$user_id]);
        $comment = $stmt->fetch();
        
        echo json_encode([
            'success' => !!$comment,
            'comment_id' => $comment['id'] ?? null
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Неверный запрос']);
}
?>