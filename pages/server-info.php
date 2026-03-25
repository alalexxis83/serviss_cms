<?php
/**
 * Информация о сервере
 */

echo '<h2>Информация о сервере</h2>';
echo '<table border="1" cellpadding="10" style="border-collapse: collapse;">';

$info = [
    'Операционная система' => php_uname(),
    'ОС (кратко)' => PHP_OS,
    'Версия PHP' => phpversion(),
    'Сервер' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown',
    'PDO драйверы' => implode(', ', PDO::getAvailableDrivers()) ?: 'НЕТ!',
    'Расширение mysqli' => extension_loaded('mysqli') ? 'Да' : 'Нет',
    'Расширение mysqlnd' => extension_loaded('mysqlnd') ? 'Да' : 'Нет',
];

foreach ($info as $key => $value) {
    echo '<tr>';
    echo '<td><strong>' . $key . '</strong></td>';
    echo '<td>' . $value . '</td>';
    echo '</tr>';
}

echo '</table>';

echo '<h3>Важно: PDO драйверы</h3>';
$drivers = PDO::getAvailableDrivers();
if (empty($drivers)) {
    echo '<p style="color: red; font-weight: bold;">PDO драйверы НЕ установлены!</p>';
    echo '<p>Выполните на сервере:</p>';
    echo '<pre style="background: #f0f0f0; padding: 10px;">
# Ubuntu/Debian:
sudo apt install php-mysql
sudo systemctl restart apache2

# CentOS:
sudo yum install php-mysqlnd
sudo systemctl restart httpd
</pre>';
} elseif (!in_array('mysql', $drivers)) {
    echo '<p style="color: red;">MySQL драйвер не установлен!</p>';
} else {
    echo '<p style="color: green;">MySQL драйвер установлен ✓</p>';
}
