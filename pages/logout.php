<?php
/**
 * Выход из системы
 */

require_once __DIR__ . '/../config/functions.php';

// Очищаем сессию
$_SESSION = [];

// Удаляем cookie сессии
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Уничтожаем сессию
session_destroy();

// Перенаправляем на страницу входа
header('Location: /pages/login.php');
exit;
