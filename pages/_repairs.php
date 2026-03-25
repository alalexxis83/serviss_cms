<?php
/**
 * Страница списка всех ремонтов (Texmobile style)
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
    'new' => $db->query("SELECT COUNT(*) FROM repairs WHERE status = 'new'")->fetchColumn(),
    'in_progress' => $db->query("SELECT COUNT(*) FROM repairs WHERE status IN ('diagnosed', 'in_progress', 'waiting_parts')")->fetchColumn(),
    'ready' => $db->query("SELECT COUNT(*) FROM repairs WHERE status = 'ready'")->fetchColumn(),
    'completed' => $db->query("SELECT COUNT(*) FROM repairs WHERE status = 'completed'")->fetchColumn(),
];

include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1>Список ремонтов</h1>
        <p>Управление заявками на ремонт техники</p>
    </div>
    <a href="/pages/repair-add.php" class="btn btn-primary">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Новый ремонт
    </a>
</div>

<!-- Статистика -->
<div class="stats-grid">
    <div class="stat-card info">
        <div class="stat-icon">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
        </div>
        <div class="stat-info">
            <div class="value"><?php echo $stats['new']; ?></div>
            <div class="label">Новые заявки</div>
        </div>
    </div>
    <div class="stat-card warning">
        <div class="stat-icon">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <div class="stat-info">
            <div class="value"><?php echo $stats['in_progress']; ?></div>
            <div class="label">В работе</div>
        </div>
    </div>
    <div class="stat-card success">
        <div class="stat-icon">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
        </div>
        <div class="stat-info">
            <div class="value"><?php echo $stats['ready']; ?></div>
            <div class="label">Готовы к выдаче</div>
        </div>
    </div>
    <div class="stat-card accent">
        <div class="stat-icon">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
        </div>
        <div class="stat-info">
            <div class="value"><?php echo $stats['completed']; ?></div>
            <div class="label">Выполнено</div>
        </div>
    </div>
</div>

<!-- Фильтры -->
<div class="card">
    <div class="card-body">
        <form method="GET" action="" class="filters">
            <div class="form-group">
                <div class="search-box">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="text" name="search" placeholder="Поиск по номеру, клиенту, телефону..." 
                           value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                </div>
            </div>
            <div class="form-group">
                <select name="status">
                    <option value="">Все статусы</option>
                    <?php foreach (getRepairStatuses() as $key => $status): ?>
                        <option value="<?php echo $key; ?>" <?php echo ($_GET['status'] ?? '') === $key ? 'selected' : ''; ?>>
                            <?php echo $status['label']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                Найти
            </button>
            <a href="/pages/repairs.php" class="btn btn-outline">Сбросить</a>
        </form>
    </div>
</div>

<!-- Таблица ремонтов -->
<div class="card">
    <div class="card-header">
        <h3>Ремонты (<?php echo $totalRepairs; ?>)</h3>
    </div>
    <div class="card-body">
        <?php if (empty($repairs)): ?>
            <div class="empty-state">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
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
                            <th>Мастер</th>
                            <th>Статус</th>
                            <th>Стоимость</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($repairs as $repair): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($repair['repair_number']); ?></strong>
                                </td>
                                <td><?php echo formatDate($repair['received_at'], 'd.m.Y'); ?></td>
                                <td>
                                    <?php echo htmlspecialchars($repair['client_name']); ?><br>
                                    <small style="color: var(--gray-500);"><?php echo htmlspecialchars($repair['client_phone']); ?></small>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($repair['device_name']); ?><br>
                                    <small style="color: var(--gray-500);">
                                        <?php echo htmlspecialchars($repair['device_brand']); ?>
                                    </small>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($repair['repair_technician'] ?? $repair['technician_name'] ?? '-'); ?>
                                </td>
                                <td>
                                    <span class="badge <?php echo getStatusClass($repair['status']); ?>">
                                        <?php echo getStatusLabel($repair['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo formatPrice($repair['final_cost'] ?: $repair['estimated_cost']); ?></td>
                                <td>
                                    <div class="actions">
                                        <a href="/pages/repair-edit.php?id=<?php echo $repair['id']; ?>" class="btn btn-sm btn-primary" title="Редактировать">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>
                                        <a href="/pages/repair-view.php?id=<?php echo $repair['id']; ?>" class="btn btn-sm btn-secondary" title="Просмотр">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Пагинация -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>&status=<?php echo $_GET['status'] ?? ''; ?>&search=<?php echo urlencode($_GET['search'] ?? ''); ?>">← Назад</a>
                    <?php else: ?>
                        <span class="disabled">← Назад</span>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <?php if ($i == $page): ?>
                            <span class="active"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?page=<?php echo $i; ?>&status=<?php echo $_GET['status'] ?? ''; ?>&search=<?php echo urlencode($_GET['search'] ?? ''); ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?php echo $page + 1; ?>&status=<?php echo $_GET['status'] ?? ''; ?>&search=<?php echo urlencode($_GET['search'] ?? ''); ?>">Вперед →</a>
                    <?php else: ?>
                        <span class="disabled">Вперед →</span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
