<?php
// config.php - Конфигурация безопасности

/**
 * Проверка доступа по IP (MD5-хеш)
 * @return bool
 */
function checkIpAccess() {
    // Разрешенные IP (MD5-хеш => комментарий для себя)
    // Чтобы получить хеш: echo md5('192.168.1.1');
    $allowed_ips = [
        '202cb962ac59075b964b07152d234b70' => 'офис-главный',
        '5f4dcc3b5aa765d61d8327deb882cf99' => 'localhost',
        '837ec5754f503cfaaee0929fd48974e7' => 'домашний-ip',
        '9302508136a7c4c64d2597063387cbb2' => 'субдомен-test',
		'306d4f3bf17f5f0bb12fde1093f341c2'=> 'office',
         'a97762c366a979162b904a0c9e54522a' => 'AL home',
	     '793f70aaa70c5dee618dd8d6d2e50f7b' => 'Snajja home'
    ];

    // Получаем реальный IP (учитывая прокси)
    $client_ip = $_SERVER['HTTP_X_FORWARDED_FOR'] 
        ?? $_SERVER['REMOTE_ADDR'] 
        ?? '0.0.0.0';

    // Если несколько IP в заголовке (через запятую), берем первый
    if (strpos($client_ip, ',') !== false) {
        $ips = explode(',', $client_ip);
        $client_ip = trim($ips[0]);
    }

    $ip_hash = md5($client_ip);

    // Проверяем доступ
    if (!isset($allowed_ips[$ip_hash])) {
        // Логируем попытку несанкционированного доступа
        $log_file = __DIR__ . '/logs/security.log';
        $log_dir = dirname($log_file);

        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0755, true);
        }

        $log_entry = sprintf(
            "[%s] ДОСТУП ЗАПРЕЩЕН | IP: %s | Хеш: %s | User-Agent: %s | URL: %s\n",
            date('Y-m-d H:i:s'),
            $client_ip,
            $ip_hash,
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            $_SERVER['REQUEST_URI'] ?? '/'
        );

        file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);

        // Отправляем 403
        http_response_code(403);
        header('Content-Type: text/html; charset=utf-8');

        // Подключаем страницу ошибки или показываем сообщение
        if (file_exists(__DIR__ . '/403.php')) {
            include __DIR__ . '/403.php';
        } else {
            echo '<h1>403 - Доступ запрещен</h1>';
            echo '<p>Ваш IP-адрес не имеет доступа к этой системе.</p>';
        }
        exit;
    }

    // Доступ разрешен - можно записать в сессию
    return true;
}

/**
 * Получить MD5-хеш IP (вспомогательная функция)
 */
function getIpHash($ip = null) {
    $ip = $ip ?? ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
    return md5($ip);
}
?>
