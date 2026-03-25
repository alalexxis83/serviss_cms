<?php
/**
 * Страница авторизации
 */

// Включаем отображение ошибок
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Запускаем сессию ПЕРЕД любым выводом
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

 $error = '';

// Если уже авторизован
if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    header('Location: /pages/repairs.php');
    exit;
}

// Обработка формы входа
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Введите логин и пароль';
    } else {
        $db = getDB();
        $usernameEsc = $db->real_escape_string($username);
        $result = $db->query("SELECT * FROM users WHERE username = '$usernameEsc' AND is_active = 1");
        $user = $result->fetch_assoc();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_username'] = $user['username'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_role'] = $user['role'];
            
            header('Location: /pages/repairs.php');
            exit;
        } else {
            $error = 'Неверный логин или пароль';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход - RepairCMS</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        body { background: linear-gradient(135deg, #e67e22 0%, #d35400 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-box { background: white; padding: 40px; border-radius: 8px; width: 100%; max-width: 400px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); }
        .login-box h2 { text-align: center; margin-bottom: 20px; }
        .login-box h2 span { color: #e67e22; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 500; }
        .form-group input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        .btn { width: 100%; padding: 12px; background: #e67e22; color: white; border: none; border-radius: 4px; font-size: 16px; cursor: pointer; }
        .btn:hover { background: #d35400; }
        .alert { padding: 10px; margin-bottom: 20px; border-radius: 4px; background: #fee; color: #c00; border-left: 4px solid #c00; }
        .login-creds { text-align: center; margin-top: 20px; color: #666; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>Repair<span>CMS</span></h2>
        
        <?php if ($error): ?>
            <div class="alert"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label>Логин</label>
                <input type="text" name="username" required autofocus value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label>Пароль</label>
                <input type="password" name="password" required>
            </div>
            
            <button type="submit" class="btn">Войти</button>
        </form>
        
        <div class="login-creds">
            <p>Логин: <strong>admin</strong> / Пароль: <strong>admin</strong></p>
        </div>
    </div>
</body>
</html>