<?php
/**
 * Отладка ошибок
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h2>Step 1: Start session</h2>";
session_start();
echo "Session ID: " . session_id() . "<br>";
echo "Session data: <pre>" . print_r($_SESSION, true) . "</pre>";

echo "<h2>Step 2: Load database</h2>";
require_once __DIR__ . '/../config/database.php';
echo "Database loaded OK<br>";

echo "<h2>Step 3: Test DB connection</h2>";
 $db = getDB();
echo "DB connection OK<br>";
echo "Server info: " . $db->server_info . "<br>";

echo "<h2>Step 4: Test simple query</h2>";
 $result = $db->query("SELECT COUNT(*) as cnt FROM users");
 $row = $result->fetch_assoc();
echo "Users count: " . $row['cnt'] . "<br>";

echo "<h2>Step 5: Test login query</h2>";
 $username = 'admin';
 $usernameEsc = $db->real_escape_string($username);
 $result = $db->query("SELECT * FROM users WHERE username = '$usernameEsc' AND is_active = 1");
 $user = $result->fetch_assoc();
echo "User found: <pre>" . print_r($user, true) . "</pre>";

if ($user) {
    echo "<h2>Step 6: Test password verify</h2>";
    $testPassword = 'admin';
    
    if ($user['password'] === md5($testPassword)) {
        echo "Password is MD5! Updating to bcrypt...<br>";
        $newHash = password_hash($testPassword, PASSWORD_DEFAULT);
        $newHashEsc = $db->real_escape_string($newHash);
        $db->query("UPDATE users SET password = '$newHashEsc' WHERE username = 'admin'");
        echo "Password updated! New hash: $newHash<br>";
        echo '<a href="/pages/debug.php">Refresh page</a>';
    } elseif (password_verify($testPassword, $user['password'])) {
        echo "Password OK!<br>";
        
        echo "<h2>Step 7: Set session</h2>";
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_username'] = $user['username'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['user_role'] = $user['role'];
        echo "Session set OK<br>";
        echo "<pre>" . print_r($_SESSION, true) . "</pre>";
        
        echo '<br><a href="/pages/repairs.php">Go to repairs.php</a>';
    } else {
        echo "Password WRONG!<br>";
        echo "Stored hash: " . $user['password'] . "<br>";
    }
}

echo "<hr>";
echo "<h2>Test repairs.php components</h2>";
require_once __DIR__ . '/../config/functions.php';
echo "Functions loaded OK<br>";

echo "Testing getRepairs(): ";
 $repairs = getRepairs([], 5, 0);
echo count($repairs) . " repairs found<br>";

echo "Testing countRepairs(): ";
 $count = countRepairs([]);
echo $count . " total<br>";

echo '<br><a href="/pages/repairs.php">Try repairs.php</a>';

echo "<hr>";
echo "<h2>PHP Info</h2>";
echo "PHP Version: " . PHP_VERSION . "<br>";
echo "mysqli: " . (extension_loaded('mysqli') ? 'YES' : 'NO') . "<br>";