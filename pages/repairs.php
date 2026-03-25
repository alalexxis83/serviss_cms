<?php
/**
 * Страница списка всех ремонтов
 */

require_once __DIR__ . '/../config/functions.php';

 $pageTitle = 'Список ремонтов';

// Параметры фильтрации
 $filters = [];
if (!empty($_GET['status'])) {
    $filters['status'] = $_GET['status'];
}
if (!empty($_GET['search'])) {
    $filters['search'] = $_GET['search'];
}

// Пагинация
 $page = max(1, intval($_GET['page'] ?? 1));
 $perPage = 20;
 $offset = ($page - 1) * $perPage;

// Получаем данные
 $repairs = getRepairs($filters, $perPage, $offset);
 $totalRepairs = countRepairs($filters);
 $totalPages = ceil($totalRepairs / $perPage);

// Статистика
 $db = getDB();
 $stats = [
    'new' => 0,
    'in_progress' => 0,
    'ready' => 0,
    'completed' => 0,
];

 $result = $db->query("SELECT COUNT(*) as cnt FROM repairs WHERE status = 'new'");
if ($result) {
    $row = $result->fetch_assoc();
    $stats['new'] = $row['cnt'];
}

 $result = $db->query("SELECT COUNT(*) as cnt FROM repairs WHERE status IN ('diagnosed', 'in_progress', 'waiting_parts')");
if ($result) {
    $row = $result->fetch_assoc();
    $stats['in_progress'] = $row['cnt'];
}

 $result = $db->query("SELECT COUNT(*) as cnt FROM repairs WHERE status = 'ready'");
if ($result) {
    $row = $result->fetch_assoc();
    $stats['ready'] = $row['cnt'];
}

 $result = $db->query("SELECT COUNT(*) as cnt FROM repairs WHERE status = 'completed'");
if ($result) {
    $row = $result->fetch_assoc();
    $stats['completed'] = $row['cnt'];
}

include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1>Список ремонтов</h1>
        <p>Управление заявками на ремонт техники</p>
    </div>
    <a href="/pages/repair-add.php" class="btn btn-primary">+ Новый ремонт</a>
</div>

<!-- Статистика -->
<div class="stats-grid">
    <div class="stat-card info">
        <div class="stat-info">
            <div class="value"><?php echo $stats['new']; ?></div>
            <div class="label">Новые</div>
        </div>
    </div>
    <div class="stat-card warning">
        <div class="stat-info">
            <div class="value"><?php echo $stats['in_progress']; ?></div>
            <div class="label">В работе</div>
        </div>
    </div>
    <div class="stat-card success">
        <div class="stat-info">
            <div class="value"><?php echo $stats['ready']; ?></div>
            <div class="label">Готовы</div>
        </div>
    </div>
    <div class="stat-card accent">
        <div class="stat-info">
            <div class="value"><?php echo $stats['completed']; ?></div>
            <div class="label">Выполнено</div>
        </div>
    </div>
</div>

<!-- Список ремонтов -->
<div class="card">
    <div class="card-header">
        <h3>Ремонты (<?php echo $totalRepairs; ?>)</h3>
    </div>
    <div class="card-body">
        <?php if (empty($repairs)): ?>
            <div class="empty-state">
                <h3>Ремонты не найдены</h3>
                <p>Начните с создания первой заявки на ремонт</p>
                <a href="/pages/repair-add.php" class="btn btn-primary">Создать заявку</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>№ заявки</th>
                            <th>Дата</th>
                            <th>Клиент</th>
                            <th>Устройство</th>
                            <th>Статус</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($repairs as $repair): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($repair['repair_number']); ?></strong></td>
                                <td><?php echo formatDate($repair['received_at'], 'd.m.Y'); ?></td>
                                <td>
                                    <?php echo htmlspecialchars($repair['client_name']); ?><br>
                                    <small><?php echo htmlspecialchars($repair['client_phone']); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($repair['device_name']); ?></td>
                                <td>
                                    <span class="badge <?php echo getStatusClass($repair['status']); ?>">
                                        <?php echo getStatusLabel($repair['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="/pages/repair-edit.php?id=<?php echo $repair['id']; ?>" class="btn btn-sm btn-primary">Редактировать</a>
                                    <a href="/pages/repair-view.php?id=<?php echo $repair['id']; ?>" class="btn btn-sm btn-secondary">Просмотр</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>