<?php
/**
 * Страница просмотра ремонта (read-only)
 */

require_once __DIR__ . '/../config/functions.php';

 $pageTitle = 'Просмотр ремонта';

 $repairId = intval($_GET['id'] ?? 0);
if (!$repairId) {
    header('Location: /pages/repairs.php');
    exit;
}

 $repair = getRepair($repairId);
if (!$repair) {
    header('Location: /pages/repairs.php');
    exit;
}

 $history = getRepairHistory($repairId);

include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1>Просмотр ремонта</h1>
        <p>Заявка <?php echo htmlspecialchars($repair['repair_number']); ?></p>
    </div>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <a href="/pages/print.php?id=<?php echo $repairId; ?>" class="btn btn-primary" target="_blank">
            Печать
        </a>
        <a href="/pages/repair-edit.php?id=<?php echo $repairId; ?>" class="btn btn-secondary">
            Редактировать
        </a>
        <a href="/pages/repairs.php" class="btn btn-outline">
            Назад к списку
        </a>
    </div>
</div>

<div class="repair-detail">
    <div class="left-column">
        <div class="card">
            <div class="card-header">
                <h3>Информация о ремонте</h3>
                <span class="badge <?php echo getStatusClass($repair['status']); ?>">
                    <?php echo getStatusLabel($repair['status']); ?>
                </span>
            </div>
            <div class="card-body">
                <div class="detail-section">
                    <h4>Клиент</h4>
                    <div class="detail-row">
                        <span class="detail-label">ФИО:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($repair['client_name']); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Телефон:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($repair['client_phone']); ?></span>
                    </div>
                </div>

                <div class="detail-section">
                    <h4>Устройство</h4>
                    <div class="detail-row">
                        <span class="detail-label">Устройство:</span>
                        <span class="detail-value">
                            <?php echo htmlspecialchars($repair['device_brand'] . ' ' . $repair['device_model'] . ' - ' . $repair['device_name']); ?>
                        </span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Серийный номер:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($repair['device_serial'] ?? '-'); ?></span>
                    </div>
                </div>

                <div class="detail-section">
                    <h4>Описание проблемы</h4>
                    <p style="background: var(--gray-100); padding: 15px; border-radius: var(--radius);">
                        <?php echo nl2br(htmlspecialchars($repair['problem_description'])); ?>
                    </p>
                </div>

                <div class="detail-section">
                    <h4>Дополнительно</h4>
                    <?php if ($repair['invoice_issuer']): ?>
                    <div class="detail-row">
                        <span class="detail-label">Кто выписал счет:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($repair['invoice_issuer']); ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ($repair['repair_technician']): ?>
                    <div class="detail-row">
                        <span class="detail-label">Выполнил ремонт:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($repair['repair_technician']); ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ($repair['repair_price']): ?>
                    <div class="detail-row">
                        <span class="detail-label">Цена ремонта:</span>
                        <span class="detail-value"><?php echo formatPrice($repair['repair_price']); ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ($repair['diagnostic_price']): ?>
                    <div class="detail-row">
                        <span class="detail-label">Цена диагностики:</span>
                        <span class="detail-value"><?php echo formatPrice($repair['diagnostic_price']); ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ($repair['client_paid']): ?>
                    <div class="detail-row">
                        <span class="detail-label">Клиент оплатил:</span>
                        <span class="detail-value"><?php echo formatPrice($repair['client_paid']); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="right-column">
        <div class="card">
            <div class="card-header">
                <h3>Информация</h3>
            </div>
            <div class="card-body">
                <div class="detail-row">
                    <span class="detail-label">Номер:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($repair['repair_number']); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Принято:</span>
                    <span class="detail-value"><?php echo formatDate($repair['received_at']); ?></span>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3>История изменений</h3>
            </div>
            <div class="card-body">
                <?php if (empty($history)): ?>
                    <p style="color: var(--gray-500); text-align: center;">История пуста</p>
                <?php else: ?>
                    <div class="timeline">
                        <?php foreach ($history as $item): ?>
                            <div class="timeline-item">
                                <div class="timeline-date"><?php echo formatDate($item['created_at']); ?></div>
                                <div class="timeline-content">
                                    <?php if ($item['status_from']): ?>
                                        Статус: <strong><?php echo getStatusLabel($item['status_from']); ?></strong> → <strong><?php echo getStatusLabel($item['status_to']); ?></strong>
                                    <?php else: ?>
                                        <strong><?php echo getStatusLabel($item['status_to']); ?></strong>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>