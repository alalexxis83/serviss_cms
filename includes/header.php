<?php
/**
 * Шапка страницы с навигацией (Texmobile style)
 */

// Проверяем авторизацию
requireAuth();

// Текущая страница для активного пункта меню
 $currentPage = basename($_SERVER['PHP_SELF'], '.php');

// Получаем статистику для sidebar
 $db = getDB();
 $repairCounts = [
    'all' => 0,
    'new' => 0,
    'in_progress' => 0,
    'ready' => 0,
    'completed' => 0,
];

// Получаем статистику через mysqli
 $result = $db->query("SELECT COUNT(*) as cnt FROM repairs");
if ($result) {
    $row = $result->fetch_assoc();
    $repairCounts['all'] = $row['cnt'];
}

 $result = $db->query("SELECT COUNT(*) as cnt FROM repairs WHERE status = 'new'");
if ($result) {
    $row = $result->fetch_assoc();
    $repairCounts['new'] = $row['cnt'];
}

 $result = $db->query("SELECT COUNT(*) as cnt FROM repairs WHERE status IN ('diagnosed', 'in_progress', 'waiting_parts')");
if ($result) {
    $row = $result->fetch_assoc();
    $repairCounts['in_progress'] = $row['cnt'];
}

 $result = $db->query("SELECT COUNT(*) as cnt FROM repairs WHERE status = 'ready'");
if ($result) {
    $row = $result->fetch_assoc();
    $repairCounts['ready'] = $row['cnt'];
}

 $result = $db->query("SELECT COUNT(*) as cnt FROM repairs WHERE status = 'completed'");
if ($result) {
    $row = $result->fetch_assoc();
    $repairCounts['completed'] = $row['cnt'];
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Система учета ремонтов'; ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <!-- Top Bar -->
    <div class="top-bar">
        <div class="top-bar-content">
            <div>Добро пожаловать, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</div>
            <div>
                <a href="/pages/logout.php">Выход</a>
            </div>
        </div>
    </div>

    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <a href="/pages/repairs.php" class="logo">
                <div class="logo-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                </div>
                <div class="logo-text">Repair<span>CMS</span></div>
            </a>
            <div class="header-phone">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                </svg>
                +371 201-370-60
            </div>
        </div>
    </header>

    <!-- Navigation -->
    <nav class="navbar">
        <div class="navbar-content">
            <a href="/pages/repairs.php" class="<?php echo $currentPage === 'repairs' ? 'active' : ''; ?>">
                Все ремонты
            </a>
            <a href="/pages/repair-add.php" class="<?php echo $currentPage === 'repair-add' ? 'active' : ''; ?>">
                Новый ремонт
            </a>
            <?php if (hasRole('admin')): ?>
            <a href="/pages/devices.php" class="<?php echo $currentPage === 'devices' ? 'active' : ''; ?>">
                Устройства
            </a>
            <a href="/pages/workers.php" class="<?php echo $currentPage === 'workers' ? 'active' : ''; ?>">
                Работники
            </a>
            <?php endif; ?>
        </div>
    </nav>

    <!-- Main Container -->
    <div class="container">
        <div class="page-layout">
            <!-- Sidebar -->
            <aside class="sidebar">
                <div class="sidebar-menu">
                    <div class="sidebar-title">Меню</div>
                    <ul class="sidebar-nav">
                        <li>
                            <a href="/pages/repairs.php" class="<?php echo $currentPage === 'repairs' ? 'active' : ''; ?>">
                                Все ремонты
                                <span class="count"><?php echo $repairCounts['all']; ?></span>
                            </a>
                        </li>
                        <li>
                            <a href="/pages/repairs.php?status=new">
                                Новые заявки
                                <span class="count"><?php echo $repairCounts['new']; ?></span>
                            </a>
                        </li>
                        <li>
                            <a href="/pages/repairs.php?status=in_progress">
                                В работе
                                <span class="count"><?php echo $repairCounts['in_progress']; ?></span>
                            </a>
                        </li>
                        <li>
                            <a href="/pages/repairs.php?status=ready">
                                Готовы к выдаче
                                <span class="count"><?php echo $repairCounts['ready']; ?></span>
                            </a>
                        </li>
                        <li>
                            <a href="/pages/repairs.php?status=completed">
                                Выполненные
                                <span class="count"><?php echo $repairCounts['completed']; ?></span>
                            </a>
                        </li>
                        <li>
                            <a href="/pages/repair-add.php" class="<?php echo $currentPage === 'repair-add' ? 'active' : ''; ?>">
                                + Новый ремонт
                            </a>
                        </li>
                        <?php if (hasRole('admin')): ?>
                        <li>
                            <a href="/pages/devices.php" class="<?php echo $currentPage === 'devices' ? 'active' : ''; ?>">
                                Устройства
                            </a>
                        </li>
                        <li>
                            <a href="/pages/workers.php" class="<?php echo $currentPage === 'workers' ? 'active' : ''; ?>">
                                Работники
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </aside>

            <!-- Main Content -->
            <main class="main-content">