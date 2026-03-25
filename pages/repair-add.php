<?php
require_once __DIR__ . '/../config/functions.php';
$pageTitle = 'Новый ремонт';
$error = '';
$success = '';
$devices = getDevices();
$workers = getWorkers();
$autoInvoiceNumber = 'R-' . date('Ymd') . '-' . rand(1000, 9999);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $clientName = trim($_POST['client_name'] ?? '');
    $clientPhone = trim($_POST['client_phone'] ?? '');
    $clientEmail = trim($_POST['client_email'] ?? '');
    $clientAddress = trim($_POST['client_address'] ?? '');
    $deviceId = intval($_POST['device_id'] ?? 0);
    $deviceSerial = trim($_POST['device_serial'] ?? '');
    $securityCode = trim($_POST['security_code'] ?? '');
    $battery = isset($_POST['battery']) ? 1 : 0;
    $charger = isset($_POST['charger']) ? 1 : 0;
    $problemDescription = trim($_POST['problem_description'] ?? '');
    $priority = $_POST['priority'] ?? 'normal';
    $estimatedCost = floatval($_POST['estimated_cost'] ?? 0);
    $notes = trim($_POST['notes'] ?? '');
    $invoiceNumber = trim($_POST['invoice_number'] ?? '');
    $invoiceIssuer = trim($_POST['invoice_issuer'] ?? '');
    $invoiceDate = $_POST['invoice_date'] ?? null;
    $estimatedRepairDate = $_POST['estimated_repair_date'] ?? null;
    $repairTechnician = trim($_POST['repair_technician'] ?? '');
    $warrantyPeriod = trim($_POST['warranty_period'] ?? '');
    $repairPrice = floatval($_POST['repair_price'] ?? 0);
    $diagnosticPrice = floatval($_POST['diagnostic_price'] ?? 0);
    $clientPaid = floatval($_POST['client_paid'] ?? 0);
    
    if (empty($clientName)) {
        $error = 'Укажите имя клиента';
    } elseif (empty($clientPhone)) {
        $error = 'Укажите телефон клиента';
    } elseif ($deviceId === 0) {
        $error = 'Выберите устройство';
    } elseif (empty($problemDescription)) {
        $error = 'Опишите проблему';
    } else {
        try {
            $db = getDB();
            $clientId = getOrCreateClient($clientName, $clientPhone, $clientEmail, $clientAddress);
            $repairNumber = generateRepairNumber();
            
            $estimatedCostVal = $estimatedCost > 0 ? $estimatedCost : null;
            $invoiceDateVal = $invoiceDate ?: null;
            $estimatedRepairDateVal = $estimatedRepairDate ?: null;
            $repairPriceVal = $repairPrice > 0 ? $repairPrice : null;
            $diagnosticPriceVal = $diagnosticPrice > 0 ? $diagnosticPrice : null;
            $clientPaidVal = $clientPaid > 0 ? $clientPaid : null;
            $managerId = $_SESSION['user_id'];
            
            $stmt = $db->prepare("
                INSERT INTO repairs (repair_number, client_id, device_id, device_serial, security_code, 
                 battery, charger, problem_description, priority, estimated_cost, manager_id, notes, status, 
                 invoice_number, invoice_issuer, invoice_date, estimated_repair_date, repair_technician, warranty_period,
                 repair_price, diagnostic_price, client_paid)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'new', ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param('siissiissdisssssssddd', 
                $repairNumber, $clientId, $deviceId, $deviceSerial, $securityCode, $battery, $charger,
                $problemDescription, $priority, $estimatedCostVal, $managerId, $notes, $invoiceNumber, 
                $invoiceIssuer, $invoiceDateVal, $estimatedRepairDateVal, $repairTechnician, $warrantyPeriod,
                $repairPriceVal, $diagnosticPriceVal, $clientPaidVal
            );
            $stmt->execute();
            $repairId = $db->insert_id;
            addHistory($repairId, null, 'new', 'Создана новая заявка на ремонт');
            $success = 'Заявка на ремонт успешно создана! Номер: ' . $repairNumber;
            $_POST = [];
        } catch (Exception $e) {
            $error = 'Ошибка при сохранении: ' . $e->getMessage();
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1>Новый ремонт</h1>
        <p>Создание заявки на ремонт техники</p>
    </div>
</div>

<?php if ($error): ?>
    <?php echo showError($error); ?>
<?php endif; ?>

<?php if ($success): ?>
    <?php echo showSuccess($success); ?>
<?php endif; ?>

<form method="POST" action="">
    <div class="card">
        <div class="card-header">
            <h3>Информация о клиенте</h3>
        </div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-group">
                    <label for="client_name">ФИО клиента <span class="required">*</span></label>
                    <input type="text" id="client_name" name="client_name" required
                           value="<?php echo htmlspecialchars($_POST['client_name'] ?? ''); ?>"
                           placeholder="Иванов Иван Иванович">
                </div>
                <div class="form-group">
                    <label for="client_phone">Телефон <span class="required">*</span></label>
                    <input type="tel" id="client_phone" name="client_phone" required
                           value="<?php echo htmlspecialchars($_POST['client_phone'] ?? ''); ?>"
                           placeholder="+7 (999) 123-45-67">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="client_email">Email</label>
                    <input type="email" id="client_email" name="client_email"
                           value="<?php echo htmlspecialchars($_POST['client_email'] ?? ''); ?>"
                           placeholder="client@example.com">
                </div>
                <div class="form-group">
                    <label for="client_address">Адрес</label>
                    <input type="text" id="client_address" name="client_address"
                           value="<?php echo htmlspecialchars($_POST['client_address'] ?? ''); ?>"
                           placeholder="г. Москва, ул. Примерная, д. 1">
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>Информация об устройстве</h3>
        </div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-group">
                    <label for="device_id">Устройство <span class="required">*</span></label>
                    <select id="device_id" name="device_id" required>
                        <option value="">-- Выберите устройство --</option>
                        <?php 
                        $currentCategory = '';
                        foreach ($devices as $device): 
                            if ($currentCategory !== $device['category']):
                                if ($currentCategory !== '') echo '</optgroup>';
                                $currentCategory = $device['category'];
                                $categoryLabels = [
                                    'phone' => 'Телефоны',
                                    'laptop' => 'Ноутбуки',
                                    'tablet' => 'Планшеты',
                                    'tv' => 'Телевизоры',
                                    'appliance' => 'Бытовая техника'
                                ];
                                echo '<optgroup label="' . ($categoryLabels[$device['category']] ?? $device['category']) . '">';
                            endif;
                        ?>
                            <option value="<?php echo $device['id']; ?>" 
                                <?php echo ($_POST['device_id'] ?? '') == $device['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($device['brand'] . ' ' . $device['model'] . ' - ' . $device['name']); ?>
                            </option>
                        <?php endforeach; 
                        if ($currentCategory !== '') echo '</optgroup>';
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="device_serial">Серийный номер</label>
                    <input type="text" id="device_serial" name="device_serial"
                           value="<?php echo htmlspecialchars($_POST['device_serial'] ?? ''); ?>"
                           placeholder="SN123456789">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="security_code">Код безопасности</label>
                    <input type="text" id="security_code" name="security_code"
                           value="<?php echo htmlspecialchars($_POST['security_code'] ?? ''); ?>"
                           placeholder="PIN или пароль устройства">
                </div>
                <div class="form-group">
                    <label>Комплектация</label>
                    <div class="checkbox-group" style="display: flex; gap: 20px; margin-top: 10px;">
                        <label class="checkbox-label" style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                            <input type="checkbox" name="battery" value="1" <?php echo isset($_POST['battery']) ? 'checked' : ''; ?>>
                            <span>Батарея</span>
                        </label>
                        <label class="checkbox-label" style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                            <input type="checkbox" name="charger" value="1" <?php echo isset($_POST['charger']) ? 'checked' : ''; ?>>
                            <span>Зарядка</span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="priority">Приоритет</label>
                    <select id="priority" name="priority">
                        <?php foreach (getPriorities() as $key => $priority): ?>
                            <option value="<?php echo $key; ?>" <?php echo ($_POST['priority'] ?? 'normal') === $key ? 'selected' : ''; ?>>
                                <?php echo $priority['label']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="estimated_cost">Предварительная стоимость (₽)</label>
                    <input type="number" id="estimated_cost" name="estimated_cost" min="0" step="0.01"
                           value="<?php echo htmlspecialchars($_POST['estimated_cost'] ?? ''); ?>"
                           placeholder="0.00">
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>Информация о ремонте</h3>
        </div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-group">
                    <label for="invoice_number">Номер счета</label>
                    <input type="text" id="invoice_number" name="invoice_number" readonly
                           value="<?php echo htmlspecialchars($_POST['invoice_number'] ?? $autoInvoiceNumber); ?>">
                </div>
                <div class="form-group">
                    <label for="invoice_issuer">Кто выписал счет</label>
                    <select id="invoice_issuer" name="invoice_issuer">
                        <option value="">-- Выберите работника --</option>
                        <?php foreach ($workers as $worker): ?>
                            <option value="<?php echo htmlspecialchars($worker['full_name']); ?>" 
                                <?php echo ($_POST['invoice_issuer'] ?? '') === $worker['full_name'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($worker['full_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="invoice_date">Дата счета</label>
                    <input type="date" id="invoice_date" name="invoice_date"
                           value="<?php echo htmlspecialchars($_POST['invoice_date'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="estimated_repair_date">Дата примерного ремонта</label>
                    <input type="date" id="estimated_repair_date" name="estimated_repair_date"
                           value="<?php echo htmlspecialchars($_POST['estimated_repair_date'] ?? ''); ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="repair_technician">Выполнил ремонт</label>
                    <select id="repair_technician" name="repair_technician">
                        <option value="">-- Выберите работника --</option>
                        <?php foreach ($workers as $worker): ?>
                            <option value="<?php echo htmlspecialchars($worker['full_name']); ?>" 
                                <?php echo ($_POST['repair_technician'] ?? '') === $worker['full_name'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($worker['full_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="warranty_period">Период гарантии</label>
                    <select id="warranty_period" name="warranty_period">
                        <option value="">-- Выберите период --</option>
                        <option value="14" <?php echo ($_POST['warranty_period'] ?? '') === '14' ? 'selected' : ''; ?>>14 дней</option>
                        <option value="30" <?php echo ($_POST['warranty_period'] ?? '') === '30' ? 'selected' : ''; ?>>30 дней</option>
                        <option value="60" <?php echo ($_POST['warranty_period'] ?? '') === '60' ? 'selected' : ''; ?>>60 дней</option>
                        <option value="90" <?php echo ($_POST['warranty_period'] ?? '') === '90' ? 'selected' : ''; ?>>90 дней</option>
                        <option value="180" <?php echo ($_POST['warranty_period'] ?? '') === '180' ? 'selected' : ''; ?>>6 месяцев</option>
                        <option value="365" <?php echo ($_POST['warranty_period'] ?? '') === '365' ? 'selected' : ''; ?>>1 год</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>Цена</h3>
        </div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-group">
                    <label for="repair_price">Цена ремонта (₽)</label>
                    <input type="number" id="repair_price" name="repair_price" min="0" step="0.01"
                           value="<?php echo htmlspecialchars($_POST['repair_price'] ?? ''); ?>"
                           placeholder="0.00">
                </div>
                <div class="form-group">
                    <label for="diagnostic_price">Цена диагностики (₽)</label>
                    <input type="number" id="diagnostic_price" name="diagnostic_price" min="0" step="0.01"
                           value="<?php echo htmlspecialchars($_POST['diagnostic_price'] ?? ''); ?>"
                           placeholder="0.00">
                </div>
                <div class="form-group">
                    <label for="client_paid">Клиент оплатил (₽)</label>
                    <input type="number" id="client_paid" name="client_paid" min="0" step="0.01"
                           value="<?php echo htmlspecialchars($_POST['client_paid'] ?? ''); ?>"
                           placeholder="0.00">
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>Описание проблемы</h3>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label for="problem_description">Описание неисправности <span class="required">*</span></label>
                <textarea id="problem_description" name="problem_description" rows="4" required
                          placeholder="Опишите, что случилось с устройством, симптомы неисправности..."><?php echo htmlspecialchars($_POST['problem_description'] ?? ''); ?></textarea>
            </div>
            <div class="form-group">
                <label for="notes">Дополнительные заметки</label>
                <textarea id="notes" name="notes" rows="2"
                          placeholder="Внешний вид, особые пожелания клиента..."><?php echo htmlspecialchars($_POST['notes'] ?? ''); ?></textarea>
            </div>
        </div>
    </div>

    <div style="display: flex; gap: 15px; margin-top: 20px;">
        <button type="submit" class="btn btn-primary btn-lg">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            Создать заявку
        </button>
        <a href="/pages/repairs.php" class="btn btn-outline btn-lg">Отмена</a>
    </div>
</form>

<?php include __DIR__ . '/../includes/footer.php'; ?>
