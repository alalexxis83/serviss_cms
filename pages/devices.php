<?php
/**
 * Страница управления устройствами (Texmobile style)
 */

require_once __DIR__ . '/../config/functions.php';

// Только для администраторов
if (!hasRole('admin')) {
    header('Location: /pages/repairs.php');
    exit;
}

$pageTitle = 'Устройства';

$error = '';
$success = '';

// Обработка добавления устройства
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $db = getDB();
    
    if ($_POST['action'] === 'add') {
        $name = trim($_POST['name'] ?? '');
        $category = $_POST['category'] ?? '';
        $brand = trim($_POST['brand'] ?? '');
        $model = trim($_POST['model'] ?? '');
        $description = trim($_POST['description'] ?? '');
        
        if (empty($name) || empty($category)) {
            $error = 'Укажите название и категорию устройства';
        } else {
            $stmt = $db->prepare("INSERT INTO devices (name, category, brand, model, description) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $category, $brand, $model, $description]);
            $success = 'Устройство успешно добавлено!';
        }
    } elseif ($_POST['action'] === 'delete' && !empty($_POST['device_id'])) {
        $deviceId = intval($_POST['device_id']);
        $stmt = $db->prepare("UPDATE devices SET is_active = 0 WHERE id = ?");
        $stmt->execute([$deviceId]);
        $success = 'Устройство удалено!';
    }
}

// Получаем список устройств
$devices = getDevices();

// Категории
$categories = [
    'phone' => 'Телефоны',
    'laptop' => 'Ноутбуки',
    'tablet' => 'Планшеты',
    'tv' => 'Телевизоры',
    'appliance' => 'Бытовая техника',
    'other' => 'Другое'
];

include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1>Устройства</h1>
        <p>Управление списком устройств для ремонта</p>
    </div>
</div>

<?php if ($error): ?>
    <?php echo showError($error); ?>
<?php endif; ?>

<?php if ($success): ?>
    <?php echo showSuccess($success); ?>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h3>Добавить новое устройство</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="">
            <input type="hidden" name="action" value="add">
            <div class="form-row">
                <div class="form-group">
                    <label>Название <span class="required">*</span></label>
                    <input type="text" name="name" required placeholder="Например: Смартфон">
                </div>
                <div class="form-group">
                    <label>Категория <span class="required">*</span></label>
                    <select name="category" required>
                        <option value="">-- Выберите --</option>
                        <?php foreach ($categories as $key => $label): ?>
                            <option value="<?php echo $key; ?>"><?php echo $label; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Бренд</label>
                    <input type="text" name="brand" placeholder="Apple, Samsung, etc.">
                </div>
                <div class="form-group">
                    <label>Модель</label>
                    <input type="text" name="model" placeholder="iPhone 14, Galaxy S23, etc.">
                </div>
            </div>
            <div class="form-group">
                <label>Описание</label>
                <input type="text" name="description" placeholder="Краткое описание устройства">
            </div>
            <button type="submit" class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Добавить устройство
            </button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>Список устройств</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Категория</th>
                        <th>Название</th>
                        <th>Бренд</th>
                        <th>Модель</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($devices as $device): ?>
                        <tr>
                            <td><?php echo $device['id']; ?></td>
                            <td><?php echo $categories[$device['category']] ?? $device['category']; ?></td>
                            <td><?php echo htmlspecialchars($device['name']); ?></td>
                            <td><?php echo htmlspecialchars($device['brand'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($device['model'] ?? '-'); ?></td>
                            <td>
                                <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Удалить это устройство?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="device_id" value="<?php echo $device['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
