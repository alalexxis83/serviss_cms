<?php
/**
 * Общие функции для CMS (mysqli версия без mysqlnd)
 */

session_start();

require_once __DIR__ . '/database.php';

/**
 * Проверка авторизации
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Проверка роли пользователя
 */
function hasRole($role) {
    return isLoggedIn() && $_SESSION['user_role'] === $role;
}

/**
 * Перенаправление если не авторизован
 */
function requireAuth() {
    if (!isLoggedIn()) {
        header('Location: /pages/login.php');
        exit;
    }
}

/**
 * Генерация номера ремонта
 */
function generateRepairNumber() {
    $prefix = 'REM';
    $date = date('Ymd');
    $random = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 4));
    return $prefix . '-' . $date . '-' . $random;
}

/**
 * Получение списка статусов ремонта
 */
function getRepairStatuses() {
    return [
        'new' => ['label' => 'Новая заявка', 'class' => 'badge-new'],
        'diagnosed' => ['label' => 'Диагностика', 'class' => 'badge-diagnosed'],
        'in_progress' => ['label' => 'В работе', 'class' => 'badge-progress'],
        'waiting_parts' => ['label' => 'Ожидание запчастей', 'class' => 'badge-waiting'],
        'ready' => ['label' => 'Готов к выдаче', 'class' => 'badge-ready'],
        'completed' => ['label' => 'Выдан клиенту', 'class' => 'badge-completed'],
        'cancelled' => ['label' => 'Отменен', 'class' => 'badge-cancelled'],
    ];
}

/**
 * Получение названия статуса
 */
function getStatusLabel($status) {
    $statuses = getRepairStatuses();
    return isset($statuses[$status]) ? $statuses[$status]['label'] : $status;
}

/**
 * Получение CSS класса для статуса
 */
function getStatusClass($status) {
    $statuses = getRepairStatuses();
    return isset($statuses[$status]) ? $statuses[$status]['class'] : 'badge-default';
}

/**
 * Получение списка приоритетов
 */
function getPriorities() {
    return [
        'low' => ['label' => 'Низкий', 'class' => 'priority-low'],
        'normal' => ['label' => 'Обычный', 'class' => 'priority-normal'],
        'high' => ['label' => 'Высокий', 'class' => 'priority-high'],
        'urgent' => ['label' => 'Срочный', 'class' => 'priority-urgent'],
    ];
}

/**
 * Получение названия приоритета
 */
function getPriorityLabel($priority) {
    $priorities = getPriorities();
    return isset($priorities[$priority]) ? $priorities[$priority]['label'] : $priority;
}

/**
 * Санитизация строки
 */
function sanitize($string) {
    return htmlspecialchars(trim($string), ENT_QUOTES, 'UTF-8');
}

/**
 * Форматирование даты
 */
function formatDate($date, $format = 'd.m.Y H:i') {
    if (empty($date)) return '-';
    return date($format, strtotime($date));
}

/**
 * Форматирование цены
 */
function formatPrice($price) {
    if (empty($price)) return '-';
    return number_format($price, 2, ',', ' ') . ' ₽';
}

/**
 * Показать сообщение об ошибке
 */
function showError($message) {
    return '<div class="alert alert-error">' . sanitize($message) . '</div>';
}

/**
 * Показать сообщение об успехе
 */
function showSuccess($message) {
    return '<div class="alert alert-success">' . sanitize($message) . '</div>';
}

/**
 * Безопасное экранирование строки для SQL
 */
function escapeString($string) {
    $db = getDB();
    return $db->real_escape_string($string);
}

/**
 * Получить все устройства
 */
function getDevices() {
    $db = getDB();
    $result = $db->query("SELECT * FROM devices WHERE is_active = 1 ORDER BY category, name");
    $devices = [];
    while ($row = $result->fetch_assoc()) {
        $devices[] = $row;
    }
    return $devices;
}

/**
 * Получить устройство по ID
 */
function getDevice($id) {
    $db = getDB();
    $id = intval($id);
    $result = $db->query("SELECT * FROM devices WHERE id = $id");
    return $result->fetch_assoc();
}

/**
 * Получить клиента по ID
 */
function getClient($id) {
    $db = getDB();
    $id = intval($id);
    $result = $db->query("SELECT * FROM clients WHERE id = $id");
    return $result->fetch_assoc();
}

/**
 * Получить или создать клиента
 */
function getOrCreateClient($name, $phone, $email = '', $address = '') {
    $db = getDB();
    
    $phone = escapeString($phone);
    $result = $db->query("SELECT id FROM clients WHERE phone = '$phone'");
    $client = $result->fetch_assoc();
    
    if ($client) {
        return $client['id'];
    }
    
    $name = escapeString($name);
    $email = escapeString($email);
    $address = escapeString($address);
    
    $db->query("INSERT INTO clients (full_name, phone, email, address) VALUES ('$name', '$phone', '$email', '$address')");
    return $db->insert_id;
}

/**
 * Получить ремонт по ID
 */
function getRepair($id) {
    $db = getDB();
    $id = intval($id);
    $result = $db->query("
        SELECT r.*, 
               c.full_name as client_name, c.phone as client_phone, c.email as client_email,
               d.name as device_name, d.brand as device_brand, d.model as device_model,
               u1.full_name as manager_name,
               u2.full_name as technician_name
        FROM repairs r
        LEFT JOIN clients c ON r.client_id = c.id
        LEFT JOIN devices d ON r.device_id = d.id
        LEFT JOIN users u1 ON r.manager_id = u1.id
        LEFT JOIN users u2 ON r.technician_id = u2.id
        WHERE r.id = $id
    ");
    return $result->fetch_assoc();
}

/**
 * Получить историю ремонта
 */
function getRepairHistory($repairId) {
    $db = getDB();
    $repairId = intval($repairId);
    $result = $db->query("
        SELECT h.*, u.full_name as user_name
        FROM repair_history h
        LEFT JOIN users u ON h.user_id = u.id
        WHERE h.repair_id = $repairId
        ORDER BY h.created_at DESC
    ");
    $history = [];
    while ($row = $result->fetch_assoc()) {
        $history[] = $row;
    }
    return $history;
}

/**
 * Добавить запись в историю
 */
function addHistory($repairId, $statusFrom, $statusTo, $comment = '', $userId = null) {
    $db = getDB();
    if ($userId === null && isLoggedIn()) {
        $userId = $_SESSION['user_id'];
    }
    $repairId = intval($repairId);
    $statusFrom = $statusFrom ? "'" . escapeString($statusFrom) . "'" : 'NULL';
    $statusTo = escapeString($statusTo);
    $comment = escapeString($comment);
    $userId = intval($userId);
    
    $db->query("INSERT INTO repair_history (repair_id, status_from, status_to, comment, user_id) VALUES ($repairId, $statusFrom, '$statusTo', '$comment', $userId)");
}

/**
 * Получить список всех ремонтов с фильтрацией
 */
function getRepairs($filters = [], $limit = 50, $offset = 0) {
    $db = getDB();
    
    $where = [];
    
    if (!empty($filters['status'])) {
        $status = escapeString($filters['status']);
        $where[] = "r.status = '$status'";
    }
    
    if (!empty($filters['search'])) {
        $search = escapeString($filters['search']);
        $where[] = "(r.repair_number LIKE '%$search%' OR c.full_name LIKE '%$search%' OR c.phone LIKE '%$search%')";
    }
    
    $sql = "
        SELECT r.*, 
               c.full_name as client_name, c.phone as client_phone,
               d.name as device_name, d.brand as device_brand,
               u2.full_name as technician_name
        FROM repairs r
        LEFT JOIN clients c ON r.client_id = c.id
        LEFT JOIN devices d ON r.device_id = d.id
        LEFT JOIN users u2 ON r.technician_id = u2.id
    ";
    
    if (!empty($where)) {
        $sql .= " WHERE " . implode(" AND ", $where);
    }
    
    $limit = intval($limit);
    $offset = intval($offset);
    $sql .= " ORDER BY r.received_at DESC LIMIT $limit OFFSET $offset";
    
    $result = $db->query($sql);
    $repairs = [];
    while ($row = $result->fetch_assoc()) {
        $repairs[] = $row;
    }
    return $repairs;
}

/**
 * Получить список работников
 */
function getWorkers() {
    $db = getDB();
    $result = $db->query("SELECT id, full_name, role FROM users WHERE is_active = 1 ORDER BY full_name");
    $workers = [];
    while ($row = $result->fetch_assoc()) {
        $workers[] = $row;
    }
    return $workers;
}

/**
 * Подсчет количества ремонтов
 */
function countRepairs($filters = []) {
    $db = getDB();
    
    $where = [];
    
    if (!empty($filters['status'])) {
        $status = escapeString($filters['status']);
        $where[] = "r.status = '$status'";
    }
    
    if (!empty($filters['search'])) {
        $search = escapeString($filters['search']);
        $where[] = "(r.repair_number LIKE '%$search%' OR c.full_name LIKE '%$search%' OR c.phone LIKE '%$search%')";
    }
    
    $sql = "SELECT COUNT(*) as cnt FROM repairs r LEFT JOIN clients c ON r.client_id = c.id";
    
    if (!empty($where)) {
        $sql .= " WHERE " . implode(" AND ", $where);
    }
    
    $result = $db->query($sql);
    $row = $result->fetch_assoc();
    return $row['cnt'];
}

/**
 * Получить пользователя по логину (для авторизации)
 */
function getUserByUsername($username) {
    $db = getDB();
    $username = escapeString($username);
    $result = $db->query("SELECT * FROM users WHERE username = '$username' AND is_active = 1");
    return $result->fetch_assoc();
}