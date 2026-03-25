<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Доступ запрещен</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .error-box {
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 500px;
        }
        .error-code {
            font-size: 72px;
            color: #e74c3c;
            font-weight: bold;
        }
        .error-text {
            font-size: 24px;
            color: #333;
            margin: 20px 0;
        }
        .error-desc {
            color: #666;
            margin-bottom: 20px;
        }
        .ip-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            font-family: monospace;
            color: #555;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="error-box">
        <div class="error-code">403</div>
        <div class="error-text">Доступ запрещен</div>
        <div class="error-desc">
            У вас нет прав для доступа к этой странице.<br>
            Обратитесь к администратору системы.
        </div>
        <div class="ip-info">
            Ваш IP: <?= htmlspecialchars($_SERVER['REMOTE_ADDR'] ?? 'unknown') ?><br>
            Время: <?= date('d.m.Y H:i:s') ?>
        </div>
    </div>
</body>
</html>
