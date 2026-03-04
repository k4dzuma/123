<?php
session_start();

if (!function_exists('generateCsrfToken')) {
    function generateCsrfToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('verifyCsrfToken')) {
    function verifyCsrfToken($token) {
        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }
}

if (!function_exists('requireCsrfToken')) {
    function requireCsrfToken($token) {
        if (!verifyCsrfToken($token)) {
            die('Security error: Invalid CSRF token. Please refresh the page and try again.');
        }
    }
}
