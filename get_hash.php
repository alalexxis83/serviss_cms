<?php
// get_hash.php - Получить MD5 своего IP
// Загрузите этот файл, узнайте хеш, затем УДАЛИТЕ его!

$ip = $_SERVER['REMOTE_ADDR'];
$hash = md5($ip);

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Получение MD5 хеша IP</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .box {
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        .hash {
            background: #2c3e50;
            color: #2ecc71;
            padding: 15px;
            border-radius: 4px;
            font-family: monospace;
            font-size: 18px;
            margin: 20px 0;
        }
        .warning {
            color: #e74c3c;
            font-weight: bold;
            margin-top: 20px;
        }
        code {
            background: #f0f0f0;
            padding: 2px 6px;
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <div class="box">
        <h2>Ваш IP: <?= $ip ?></h2>
        <p>MD5 хеш:</p>
        <div class="hash"><?= $hash ?></div>
        <p>Скопируйте этот хеш в <code>config.php</code> в массив <code>$allowed_ips</code></p>
        <p class="warning">⚠️ ВАЖНО: Удалите этот файл после использования!</p>
    </div>
</body>
</html>
