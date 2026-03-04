<?php
// Получаем текущую тему из куки или настроек
$current_theme = $_COOKIE['admin_theme'] ?? 'light';

// Если тема "system" - определяем предпочтения системы
if ($current_theme === 'system') {
    $current_theme = isset($_SERVER['HTTP_SEC_CH_PREFERS_COLOR_SCHEME']) && 
                     $_SERVER['HTTP_SEC_CH_PREFERS_COLOR_SCHEME'] === 'dark' ? 'dark' : 'light';
}
?>