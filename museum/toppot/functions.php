<?php
// functions.php

function log_action($user_id, $action, $details = '') {
    global $db;
    
    try {
        $stmt = $db->prepare("INSERT INTO admin_logs (user_id, action, details, ip_address) 
                             VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $user_id,
            $action,
            $details,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
        return true;
    } catch (Exception $e) {
        error_log("Ошибка при записи в лог: " . $e->getMessage());
        return false;
    }
}