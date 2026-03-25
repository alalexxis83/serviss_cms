<?php
/**
 * Установка системы - создание таблиц базы данных (Texmobile style)
 */

require_once __DIR__ . '/config/database.php';

$message = '';
$error = '';
$migrationMessage = '';

try {
    // Проверяем подключение
    $db = getDB();
    
    // Создаем таблицы
    initDatabase();
    
    // Запускаем миграцию для добавления новых полей
    migrateDatabase();
    
    $message = 'Установка успешно завершена! База данных и таблицы созданы/обновлены.';
    
} catch (PDOException $e) {
    $error = 'Ошибка установки: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Установка RepairCMS</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: 
                linear-gradient(135deg, rgba(230, 126, 34, 0.9) 0%, rgba(211, 84, 0, 0.9) 100%),
                url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.1'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            padding: 20px;
        }
        .install-box {
            background: white;
            border-radius: 8px;
            padding: 40px;
            max-width: 450px;
            width: 100%;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        .install-logo {
            text-align: center;
            margin-bottom: 25px;
        }
        .install-logo-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #e67e22 0%, #d35400 100%);
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: white;
            box-shadow: 0 4px 12px rgba(230, 126, 34, 0.4);
        }
        .install-logo-icon svg {
            width: 35px;
            height: 35px;
        }
        .install-box h1 {
            text-align: center;
            margin-bottom: 10px;
            font-size: 1.5rem;
            color: #333;
        }
        .install-box h1 span {
            color: #e67e22;
        }
        .success-icon {
            text-align: center;
            margin-bottom: 20px;
        }
        .success-icon svg {
            width: 70px;
            height: 70px;
            color: #27ae60;
        }
        .credentials {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 6px;
            margin: 20px 0;
            border-left: 4px solid #e67e22;
        }
        .credentials h3 {
            margin-bottom: 12px;
            color: #333;
        }
        .credentials p {
            margin: 6px 0;
            color: #666;
        }
        .credentials strong {
            color: #e67e22;
        }
        .btn-container {
            text-align: center;
            margin-top: 25px;
        }
        .alert {
            padding: 15px 20px;
            border-radius: 6px;
            margin-bottom: 20px;
            border-left: 4px solid;
        }
        .alert-success {
            background: #e8f5e9;
            color: #2e7d32;
            border-color: #27ae60;
        }
        .alert-error {
            background: #ffebee;
            color: #c62828;
            border-color: #e74c3c;
        }
        code {
            background: #f1f1f1;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <div class="install-box">
        <div class="install-logo">
            <div class="install-logo-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
            </div>
        </div>
        <h1>Repair<span>CMS</span></h1>
        
        <?php if ($error): ?>
            <div class="alert alert-error">
                <strong>Ошибка!</strong><br>
                <?php echo $error; ?>
            </div>
            <p style="color: #666; margin-top: 15px; text-align: center;">
                Проверьте настройки подключения к базе данных в файле <code>config/database.php</code>
            </p>
        <?php else: ?>
            <div class="success-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            
            <div class="alert alert-success" style="text-align: center;">
                <?php echo $message; ?>
            </div>
            
            <div class="credentials">
                <h3>Данные для входа:</h3>
                <p><strong>Логин:</strong> admin</p>
                <p><strong>Пароль:</strong> admin</p>
                <p style="color: #e74c3c; font-size: 0.85rem; margin-top: 10px;">
                    ⚠️ Обязательно смените пароль после первого входа!
                </p>
            </div>
            
            <div class="btn-container">
                <a href="/pages/login.php" style="display: inline-flex; align-items: center; gap: 8px; padding: 12px 30px; background: linear-gradient(135deg, #e67e22 0%, #d35400 100%); color: white; text-decoration: none; border-radius: 4px; font-weight: 500;">
                    Перейти к входу
                </a>
            </div>
        <?php endif; ?>
        
        <div style="margin-top: 25px; padding-top: 20px; border-top: 1px solid #e9ecef; text-align: center; font-size: 0.8rem; color: #999;">
            RepairCMS v1.0
        </div>
    </div>
</body>
</html>
