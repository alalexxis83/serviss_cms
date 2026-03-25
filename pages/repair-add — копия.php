<?php
/**
 * Страница добавления нового ремонта (Texmobile style)
 */

require_once __DIR__ . '/../config/functions.php';

$pageTitle = 'Новый ремонт';

$error = '';
$success = '';

// Получаем список устройств
$devices = getDevices();

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Валидация
    $clientName = trim($_POST['client_name'] ?? '');
    $clientPhone = trim($_POST['client_phone'] ?? '');
    $clientEmail = trim($_POST['client_email'] ?? '');
    $clientAddress = trim($_POST['client_address'] ?? '');
    $deviceId = intval($_POST['device_id'] ?? 0);
    $deviceSerial = trim($_POST['device_serial'] ?? '');
    $problemDescription = trim($_POST['problem_description'] ?? '');
    $priority = $_POST['priority'] ?? 'normal';
    $estimatedCost = floatval($_POST['estimated_cost'] ?? 0);
    $notes = trim($_POST['notes'] ?? '');
    
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
            
            // Получаем или создаем клиента
            $clientId = getOrCreateClient($clientName, $clientPhone, $clientEmail, $clientAddress);
            
            // Генерируем номер ремонта
            $repairNumber = generateRepairNumber();
            
            // Создаем заявку на ремонт
            $stmt = $db->prepare("
                INSERT INTO repairs 
                (repair_number, client_id, device_id, device_serial, problem_description, 
                 priority, estimated_cost, manager_id, notes, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'new')
            ");
            
            $stmt->execute([
                $repairNumber,
                $clientId,
                $deviceId,
                $deviceSerial,
                $problemDescription,
                $priority,
                $estimatedCost > 0 ? $estimatedCost : null,
                $_SESSION['user_id'],
                $notes
            ]);
            
            $repairId = $db->lastInsertId();
            
            // Добавляем запись в историю
            addHistory($repairId, null, 'new', 'Создана новая заявка на ремонт');
            
            $success = 'Заявка на ремонт успешно создана! Номер: ' . $repairNumber;
            
            // Очищаем форму
            $_POST = [];
            
        } catch (PDOException $e) {
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
                          placeholder="Комплектность, внешний вид, особые пожелания клиента..."><?php echo htmlspecialchars($_POST['notes'] ?? ''); ?></textarea>
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
