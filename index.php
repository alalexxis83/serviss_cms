<?php
/**
 * Главная страница - перенаправление
 */

require_once __DIR__ . '/config/functions.php';

require_once '_config.php';

// Проверяем IP перед загрузкой страницы
checkIpAccess();


if (isLoggedIn()) {
    header('Location: /pages/repairs.php');
} else {
    header('Location: /pages/login.php');
}
exit;
