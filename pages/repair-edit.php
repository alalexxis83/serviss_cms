<?php
/**
 * Страница редактирования ремонта (Texmobile style)
 */

require_once __DIR__ . '/../config/functions.php';

$pageTitle = 'Редактирование ремонта';

$error = '';
$success = '';

// Получаем ID ремонта
$repairId = intval($_GET['id'] ?? 0);
if (!$repairId) {
    header('Location: /pages/repairs.php');
    exit;
}

// Получаем данные ремонта
$repair = getRepair($repairId);
if (!$repair) {
    header('Location: /pages/repairs.php');
    exit;
}

// Получаем список устройств
$devices = getDevices();

// Получаем список работников
$workers = getWorkers();

// Получаем историю
$history = getRepairHistory($repairId);

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'save';
    
    try {
        $db = getDB();
        
        if ($action === 'update_status') {
            // Обновление статуса
            $newStatus = $_POST['new_status'] ?? '';
            $statusComment = trim($_POST['status_comment'] ?? '');
            
            if (empty($newStatus)) {
                $error = 'Выберите новый статус';
            } else {
                $oldStatus = $repair['status'];
                
                $stmt = $db->prepare("UPDATE repairs SET status = ? WHERE id = ?");
                $stmt->bind_param('si', $newStatus, $repairId);
                $stmt->execute();
                
                // Если статус "готов" или "выдан", обновляем дату
                if (in_array($newStatus, ['ready', 'completed'])) {
                    $stmt = $db->prepare("UPDATE repairs SET completed_at = NOW() WHERE id = ?");
                    $stmt->bind_param('i', $repairId);
                    $stmt->execute();
                }
                
                // Добавляем в историю
                addHistory($repairId, $oldStatus, $newStatus, $statusComment);
                
                $success = 'Статус успешно обновлен!';
                
                // Обновляем данные
                $repair = getRepair($repairId);
                $history = getRepairHistory($repairId);
            }
        } else {
            // Сохранение изменений
            $problemDescription = trim($_POST['problem_description'] ?? '');
            $diagnosis = trim($_POST['diagnosis'] ?? '');
            $solution = trim($_POST['solution'] ?? '');
            $finalCost = floatval($_POST['final_cost'] ?? 0);
            $notes = trim($_POST['notes'] ?? '');
            $priority = $_POST['priority'] ?? 'normal';
            $technicianId = !empty($_POST['technician_id']) ? intval($_POST['technician_id']) : null;
            $invoiceIssuer = trim($_POST['invoice_issuer'] ?? '');
            $repairTechnician = trim($_POST['repair_technician'] ?? '');
            $estimatedRepairDate = $_POST['estimated_repair_date'] ?? null;
            $warrantyPeriod = trim($_POST['warranty_period'] ?? '');
            $repairPrice = floatval($_POST['repair_price'] ?? 0);
            $diagnosticPrice = floatval($_POST['diagnostic_price'] ?? 0);
            $clientPaid = floatval($_POST['client_paid'] ?? 0);
            
            if (empty($problemDescription)) {
                $error = 'Описание проблемы обязательно';
            } else {
                $finalCostVal = $finalCost > 0 ? $finalCost : null;
                $repairPriceVal = $repairPrice > 0 ? $repairPrice : null;
                $diagnosticPriceVal = $diagnosticPrice > 0 ? $diagnosticPrice : null;
                $clientPaidVal = $clientPaid > 0 ? $clientPaid : null;
                $invoiceIssuerVal = $invoiceIssuer ?: null;
                $repairTechnicianVal = $repairTechnician ?: null;
                $estimatedRepairDateVal = $estimatedRepairDate ?: null;
                $warrantyPeriodVal = $warrantyPeriod ?: null;
                
                $stmt = $db->prepare("
                    UPDATE repairs SET 
                        problem_description = ?,
                        diagnosis = ?,
                        solution = ?,
                        final_cost = ?,
                        notes = ?,
                        priority = ?,
                        technician_id = ?,
                        invoice_issuer = ?,
                        repair_technician = ?,
                        estimated_repair_date = ?,
                        warranty_period = ?,
                        repair_price = ?,
                        diagnostic_price = ?,
                        client_paid = ?
                    WHERE id = ?
                ");
                
                $stmt->bind_param('sssdssissssdddi', 
                    $problemDescription, $diagnosis, $solution, $finalCostVal,
                    $notes, $priority, $technicianId, $invoiceIssuerVal,
                    $repairTechnicianVal, $estimatedRepairDateVal, $warrantyPeriodVal,
                    $repairPriceVal, $diagnosticPriceVal, $clientPaidVal, $repairId
                );
                $stmt->execute();
                
                $success = 'Изменения успешно сохранены!';
                
                // Обновляем данные
                $repair = getRepair($repairId);
            }
        }
    } catch (Exception $e) {
        $error = 'Ошибка при сохранении: ' . $e->getMessage();
    }
}



include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1>Редактирование ремонта</h1>
        <p>Заявка <?php echo htmlspecialchars($repair['repair_number']); ?></p>
    </div>
    <span class="badge <?php echo getStatusClass($repair['status']); ?>" style="font-size: 1rem; padding: 8px 16px;">
        <?php echo getStatusLabel($repair['status']); ?>
    </span>
</div>

<?php if ($error): ?>
    <?php echo showError($error); ?>
<?php endif; ?>

<?php if ($success): ?>
    <?php echo showSuccess($success); ?>
<?php endif; ?>

<div class="repair-detail">
    <div class="left-column">
        <!-- Основная информация -->
        <div class="card">
            <div class="card-header">
                <h3>Информация о ремонте</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <input type="hidden" name="action" value="save">
                    
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
                        <?php if ($repair['client_email']): ?>
                        <div class="detail-row">
                            <span class="detail-label">Email:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($repair['client_email']); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="detail-section">
                        <h4>Устройство</h4>
                        <div class="detail-row">
                            <span class="detail-label">Устройство:</span>
                            <span class="detail-value">
                                <?php echo htmlspecialchars($repair['device_brand'] . ' ' . $repair['device_model'] . ' - ' . $repair['device_name']); ?>
                            </span>
                        </div>
                        <?php if ($repair['device_serial']): ?>
                        <div class="detail-row">
                            <span class="detail-label">Серийный номер:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($repair['device_serial']); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="detail-section">
                        <h4>Описание проблемы</h4>
                        <div class="form-group">
                            <textarea name="problem_description" rows="3" required><?php echo htmlspecialchars($repair['problem_description']); ?></textarea>
                        </div>
                    </div>

                    <div class="detail-section">
                        <h4>Диагностика</h4>
                        <div class="form-group">
                            <textarea name="diagnosis" rows="3" placeholder="Результаты диагностики..."><?php echo htmlspecialchars($repair['diagnosis'] ?? ''); ?></textarea>
                        </div>
                    </div>

                    <div class="detail-section">
                        <h4>Выполненные работы</h4>
                        <div class="form-group">
                            <textarea name="solution" rows="3" placeholder="Что было сделано..."><?php echo htmlspecialchars($repair['solution'] ?? ''); ?></textarea>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Приоритет</label>
                            <select name="priority">
                                <?php foreach (getPriorities() as $key => $priority): ?>
                                    <option value="<?php echo $key; ?>" <?php echo $repair['priority'] === $key ? 'selected' : ''; ?>>
                                        <?php echo $priority['label']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Исполнитель</label>
                            <select name="technician_id">
                                <option value="">-- Не назначен --</option>
                                <?php foreach ($workers as $worker): ?>
                                    <option value="<?php echo $worker['id']; ?>" <?php echo $repair['technician_id'] == $worker['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($worker['full_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="detail-section">
                        <h4>Информация о ремонте</h4>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Кто выписал счет</label>
                                <select name="invoice_issuer">
                                    <option value="">-- Выберите работника --</option>
                                    <?php foreach ($workers as $worker): ?>
                                        <option value="<?php echo htmlspecialchars($worker['full_name']); ?>" <?php echo ($repair['invoice_issuer'] ?? '') === $worker['full_name'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($worker['full_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Выполнил ремонт</label>
                                <select name="repair_technician">
                                    <option value="">-- Выберите работника --</option>
                                    <?php foreach ($workers as $worker): ?>
                                        <option value="<?php echo htmlspecialchars($worker['full_name']); ?>" <?php echo ($repair['repair_technician'] ?? '') === $worker['full_name'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($worker['full_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Дата примерного ремонта</label>
                                <input type="date" name="estimated_repair_date" value="<?php echo $repair['estimated_repair_date'] ?? ''; ?>">
                            </div>
                            <div class="form-group">
                                <label>Период гарантии</label>
                                <select name="warranty_period">
                                    <option value="">-- Выберите период --</option>
                                    <option value="14" <?php echo ($repair['warranty_period'] ?? '') === '14' ? 'selected' : ''; ?>>14 дней</option>
                                    <option value="30" <?php echo ($repair['warranty_period'] ?? '') === '30' ? 'selected' : ''; ?>>30 дней</option>
                                    <option value="60" <?php echo ($repair['warranty_period'] ?? '') === '60' ? 'selected' : ''; ?>>60 дней</option>
                                    <option value="90" <?php echo ($repair['warranty_period'] ?? '') === '90' ? 'selected' : ''; ?>>90 дней</option>
                                    <option value="180" <?php echo ($repair['warranty_period'] ?? '') === '180' ? 'selected' : ''; ?>>6 месяцев</option>
                                    <option value="365" <?php echo ($repair['warranty_period'] ?? '') === '365' ? 'selected' : ''; ?>>1 год</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="detail-section">
                        <h4>Цена</h4>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Цена ремонта (₽)</label>
                                <input type="number" name="repair_price" step="0.01" min="0"
                                       value="<?php echo $repair['repair_price'] ?? ''; ?>" placeholder="0.00">
                            </div>
                            <div class="form-group">
                                <label>Цена диагностики (₽)</label>
                                <input type="number" name="diagnostic_price" step="0.01" min="0"
                                       value="<?php echo $repair['diagnostic_price'] ?? ''; ?>" placeholder="0.00">
                            </div>
                            <div class="form-group">
                                <label>Клиент оплатил (₽)</label>
                                <input type="number" name="client_paid" step="0.01" min="0"
                                       value="<?php echo $repair['client_paid'] ?? ''; ?>" placeholder="0.00">
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Предварительная стоимость</label>
                            <input type="number" name="estimated_cost" step="0.01" min="0"
                                   value="<?php echo $repair['estimated_cost'] ?? ''; ?>" placeholder="0.00">
                        </div>
                        <div class="form-group">
                            <label>Итоговая стоимость</label>
                            <input type="number" name="final_cost" step="0.01" min="0"
                                   value="<?php echo $repair['final_cost'] ?? ''; ?>" placeholder="0.00">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Заметки</label>
                        <textarea name="notes" rows="2"><?php echo htmlspecialchars($repair['notes'] ?? ''); ?></textarea>
                    </div>

                    <div style="display: flex; gap: 15px; margin-top: 20px;">
                        <button type="submit" class="btn btn-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Сохранить изменения
                        </button>
                        <a href="/pages/repairs.php" class="btn btn-outline">Назад к списку</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="right-column">
        <!-- Смена статуса -->
        <div class="card">
            <div class="card-header">
                <h3>Изменить статус</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <input type="hidden" name="action" value="update_status">
                    
                    <div class="form-group">
                        <label>Новый статус</label>
                        <select name="new_status" required>
                            <option value="">-- Выберите статус --</option>
                            <?php foreach (getRepairStatuses() as $key => $status): ?>
                                <option value="<?php echo $key; ?>" <?php echo $repair['status'] === $key ? 'disabled' : ''; ?>>
                                    <?php echo $status['label']; ?> <?php echo $repair['status'] === $key ? '(текущий)' : ''; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Комментарий к смене статуса</label>
                        <textarea name="status_comment" rows="2" placeholder="Необязательно..."></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-success" style="width: 100%;">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Обновить статус
                    </button>
                </form>
            </div>
        </div>

        <!-- Информация -->
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
                <div class="detail-row">
                    <span class="detail-label">Менеджер:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($repair['manager_name'] ?? 'Не указан'); ?></span>
                </div>
                <?php if ($repair['completed_at']): ?>
                <div class="detail-row">
                    <span class="detail-label">Завершено:</span>
                    <span class="detail-value"><?php echo formatDate($repair['completed_at']); ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- История -->
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
                                    <?php if ($item['comment']): ?>
                                        <p style="margin-top: 4px; font-size: 0.85rem;"><?php echo htmlspecialchars($item['comment']); ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="timeline-user"><?php echo htmlspecialchars($item['user_name'] ?? 'Система'); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
