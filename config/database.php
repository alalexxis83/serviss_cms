<?php
/**
 * Конфигурация базы данных (mysqli версия)
 */

define('DB_HOST', 'mysql');
define('DB_NAME', 'texmobile_serviss_test');
define('DB_USER', 'Serviss_t');
define('DB_PASS', 'j1t1?Xe74');
define('DB_CHARSET', 'utf8mb4');

/**
 * Получение подключения к БД через mysqli
 */
function getDB() {
    static $mysqli = null;
    
    if ($mysqli === null) {
        $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($mysqli->connect_error) {
            die("Ошибка подключения к базе данных: " . $mysqli->connect_error);
        }
        
        $mysqli->set_charset(DB_CHARSET);
    }
    
    return $mysqli;
}

/**
 * Инициализация базы данных (создание таблиц)
 */
function initDatabase() {
    $db = getDB();
    
    // Таблица пользователей
    $db->query("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        full_name VARCHAR(100) NOT NULL,
        role ENUM('admin', 'manager', 'technician') DEFAULT 'manager',
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    // Таблица клиентов
    $db->query("CREATE TABLE IF NOT EXISTS clients (
        id INT AUTO_INCREMENT PRIMARY KEY,
        full_name VARCHAR(100) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        email VARCHAR(100),
        address TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    // Таблица устройств/товаров
    $db->query("CREATE TABLE IF NOT EXISTS devices (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        category VARCHAR(50) NOT NULL,
        brand VARCHAR(50),
        model VARCHAR(50),
        description TEXT,
        is_active TINYINT(1) DEFAULT 1
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    // Таблица ремонтов/заявок
    $db->query("CREATE TABLE IF NOT EXISTS repairs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        repair_number VARCHAR(20) NOT NULL UNIQUE,
        client_id INT NOT NULL,
        device_id INT NOT NULL,
        device_serial VARCHAR(50),
        security_code VARCHAR(50),
        battery TINYINT(1) DEFAULT 0,
        charger TINYINT(1) DEFAULT 0,
        problem_description TEXT NOT NULL,
        diagnosis TEXT,
        solution TEXT,
        status ENUM('new', 'diagnosed', 'in_progress', 'waiting_parts', 'ready', 'completed', 'cancelled') DEFAULT 'new',
        priority ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
        estimated_cost DECIMAL(10,2),
        final_cost DECIMAL(10,2),
        received_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        completed_at TIMESTAMP NULL,
        manager_id INT,
        technician_id INT,
        notes TEXT,
        invoice_number VARCHAR(50),
        invoice_issuer VARCHAR(100),
        invoice_date DATE,
        estimated_repair_date DATE,
        repair_technician VARCHAR(100),
        warranty_period VARCHAR(10),
        repair_price DECIMAL(10,2),
        diagnostic_price DECIMAL(10,2),
        client_paid DECIMAL(10,2),
        FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
        FOREIGN KEY (device_id) REFERENCES devices(id),
        FOREIGN KEY (manager_id) REFERENCES users(id),
        FOREIGN KEY (technician_id) REFERENCES users(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    // Таблица истории статусов
    $db->query("CREATE TABLE IF NOT EXISTS repair_history (
        id INT AUTO_INCREMENT PRIMARY KEY,
        repair_id INT NOT NULL,
        status_from VARCHAR(50),
        status_to VARCHAR(50) NOT NULL,
        comment TEXT,
        user_id INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (repair_id) REFERENCES repairs(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    // Создание дефолтного администратора (логин: admin, пароль: admin)
    $stmt = $db->prepare("SELECT id FROM users WHERE username = 'admin'");
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows === 0) {
        $stmt->close();
        $hashedPassword = password_hash('admin', PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO users (username, password, full_name, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('ssss', $username, $hashedPassword, $fullName, $role);
        $username = 'admin';
        $hashedPassword = password_hash('admin', PASSWORD_DEFAULT);
        $fullName = 'Администратор';
        $role = 'admin';
        $stmt->execute();
    }
    $stmt->close();
    
    // Добавление тестовых устройств
    $defaultDevices = [
        ['Смартфон', 'phone', 'Apple', 'iPhone', 'Мобильный телефон'],
        ['Смартфон', 'phone', 'Samsung', 'Galaxy', 'Мобильный телефон'],
        ['Ноутбук', 'laptop', 'Lenovo', 'ThinkPad', 'Портативный компьютер'],
        ['Ноутбук', 'laptop', 'HP', 'Pavilion', 'Портативный компьютер'],
        ['Планшет', 'tablet', 'Apple', 'iPad', 'Планшетный компьютер'],
        ['Телевизор', 'tv', 'Samsung', 'Smart TV', 'Телевизор'],
        ['Стиральная машина', 'appliance', 'LG', 'Direct Drive', 'Бытовая техника'],
        ['Холодильник', 'appliance', 'Bosch', 'NoFrost', 'Бытовая техника'],
    ];
    
    foreach ($defaultDevices as $device) {
        $stmt = $db->prepare("SELECT id FROM devices WHERE name = ? AND brand = ? AND model = ?");
        $stmt->bind_param('sss', $device[0], $device[2], $device[3]);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows === 0) {
            $stmt->close();
            $stmt = $db->prepare("INSERT INTO devices (name, category, brand, model, description) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param('sssss', $device[0], $device[1], $device[2], $device[3], $device[4]);
            $stmt->execute();
        }
        $stmt->close();
    }
}

/**
 * Миграция базы данных (добавление новых полей)
 */
function migrateDatabase() {
    $db = getDB();
    
    // Проверяем и добавляем новые поля в таблицу repairs
    $newColumns = [
        'security_code' => 'VARCHAR(50)',
        'battery' => 'TINYINT(1) DEFAULT 0',
        'charger' => 'TINYINT(1) DEFAULT 0',
        'invoice_number' => 'VARCHAR(50)',
        'invoice_issuer' => 'VARCHAR(100)',
        'invoice_date' => 'DATE',
        'estimated_repair_date' => 'DATE',
        'repair_technician' => 'VARCHAR(100)',
        'warranty_period' => 'VARCHAR(10)',
        'repair_price' => 'DECIMAL(10,2)',
        'diagnostic_price' => 'DECIMAL(10,2)',
        'client_paid' => 'DECIMAL(10,2)',
    ];
    
    foreach ($newColumns as $column => $definition) {
        // Проверяем, существует ли колонка
        $result = $db->query("SHOW COLUMNS FROM repairs LIKE '$column'");
        if ($result->num_rows === 0) {
            $db->query("ALTER TABLE repairs ADD COLUMN {$column} {$definition}");
        }
    }
    
    return true;
}
