<?php
/**
 * Страница управления работниками
 */

require_once __DIR__ . '/../config/functions.php';

// Только для администраторов
if (!hasRole('admin')) {
    header('Location: /pages/repairs.php');
    exit;
}

 $pageTitle = 'Работники';
 $error = '';
 $success = '';

 $db = getDB();

// Обработка действий
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $fullName = trim($_POST['full_name'] ?? '');
        $role = $_POST['role'] ?? 'manager';
        
        if (empty($username) || empty($password) || empty($fullName)) {
            $error = 'Заполните все обязательные поля';
        } else {
            $usernameEsc = escapeString($username);
            $result = $db->query("SELECT id FROM users WHERE username = '$usernameEsc'");
            
            if ($result->fetch_assoc()) {
                $error = 'Пользователь с таким логином уже существует';
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $fullNameEsc = escapeString($fullName);
                $roleEsc = escapeString($role);
                $hashedPasswordEsc = escapeString($hashedPassword);
                
                $db->query("INSERT INTO users (username, password, full_name, role, is_active) VALUES ('$usernameEsc', '$hashedPasswordEsc', '$fullNameEsc', '$roleEsc', 1)");
                $success = 'Работник успешно добавлен';
            }
        }
    } elseif ($action === 'edit') {
        $id = intval($_POST['id'] ?? 0);
        $fullName = trim($_POST['full_name'] ?? '');
        $role = $_POST['role'] ?? 'manager';
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        $password = trim($_POST['password'] ?? '');
        
        if ($id && !empty($fullName)) {
            $fullNameEsc = escapeString($fullName);
            $roleEsc = escapeString($role);
            
            if (!empty($password)) {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $hashedPasswordEsc = escapeString($hashedPassword);
                $db->query("UPDATE users SET full_name = '$fullNameEsc', role = '$roleEsc', is_active = $isActive, password = '$hashedPasswordEsc' WHERE id = $id");
            } else {
                $db->query("UPDATE users SET full_name = '$fullNameEsc', role = '$roleEsc', is_active = $isActive WHERE id = $id");
            }
            $success = 'Данные работника обновлены';
        }
    } elseif ($action === 'delete') {
        $id = intval($_POST['id'] ?? 0);
        if ($id) {
            $result = $db->query("SELECT role FROM users WHERE id = $id");
            $user = $result->fetch_assoc();
            
            if ($user && $user['role'] === 'admin') {
                $result = $db->query("SELECT COUNT(*) as cnt FROM users WHERE role = 'admin' AND is_active = 1");
                $row = $result->fetch_assoc();
                if ($row['cnt'] <= 1) {
                    $error = 'Нельзя удалить последнего администратора';
                } else {
                    $db->query("DELETE FROM users WHERE id = $id");
                    $success = 'Работник удален';
                }
            } else {
                $db->query("DELETE FROM users WHERE id = $id");
                $success = 'Работник удален';
            }
        }
    } elseif ($action === 'toggle') {
        $id = intval($_POST['id'] ?? 0);
        if ($id) {
            $db->query("UPDATE users SET is_active = NOT is_active WHERE id = $id");
            $success = 'Статус работника изменен';
        }
    }
}

// Получаем список работников
 $workers = [];
 $result = $db->query("SELECT * FROM users ORDER BY created_at DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $workers[] = $row;
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1>Работники</h1>
    </div>
    <button type="button" class="btn btn-primary" onclick="openModal('add')">+ Добавить</button>
</div>

<?php if ($error): ?>
    <div class="alert alert-error"><?php echo $error; ?></div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h3>Список работников (<?php echo count($workers); ?>)</h3>
    </div>
    <div class="card-body">
        <table>
            <thead>
                <tr>
                    <th>ФИО</th>
                    <th>Логин</th>
                    <th>Роль</th>
                    <th>Статус</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($workers as $worker): ?>
                    <?php $roles = ['admin' => 'Админ', 'manager' => 'Менеджер', 'technician' => 'Мастер']; ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($worker['full_name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($worker['username']); ?></td>
                        <td><?php echo $roles[$worker['role']] ?? $worker['role']; ?></td>
                        <td>
                            <?php if ($worker['is_active']): ?>
                                <span class="badge badge-success">Активен</span>
                            <?php else: ?>
                                <span class="badge badge-default">Неактивен</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-secondary" onclick='openModal("edit", <?php echo json_encode($worker); ?>)'>Ред.</button>
                            <form method="POST" style="display:inline">
                                <input type="hidden" name="action" value="toggle">
                                <input type="hidden" name="id" value="<?php echo $worker['id']; ?>">
                                <button class="btn btn-sm"><?php echo $worker['is_active'] ? 'Выкл' : 'Вкл'; ?></button>
                            </form>
                            <?php if ($worker['id'] != $_SESSION['user_id']): ?>
                            <form method="POST" style="display:inline" onsubmit="return confirm('Удалить?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $worker['id']; ?>">
                                <button class="btn btn-sm btn-error">Удл.</button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal -->
<div id="modal" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000;">
    <div style="background:white; padding:20px; border-radius:8px; max-width:400px; margin:50px auto;">
        <h3 id="modal-title">Добавить</h3>
        <form method="POST" id="worker-form">
            <input type="hidden" name="action" id="form-action" value="add">
            <input type="hidden" name="id" id="worker-id">
            
            <div class="form-group">
                <label>ФИО *</label>
                <input type="text" id="worker-full_name" name="full_name" required>
            </div>
            
            <div class="form-group" id="username-group">
                <label>Логин *</label>
                <input type="text" id="worker-username" name="username" required>
            </div>
            
            <div class="form-group">
                <label>Пароль <span id="password-hint">*</span></label>
                <input type="password" id="worker-password" name="password">
                <small id="password-note" style="display:none">Оставьте пустым, чтобы не менять</small>
            </div>
            
            <div class="form-group">
                <label>Роль</label>
                <select id="worker-role" name="role">
                    <option value="manager">Менеджер</option>
                    <option value="technician">Мастер</option>
                    <option value="admin">Администратор</option>
                </select>
            </div>
            
            <div id="is-active-group" style="display:none">
                <label><input type="checkbox" id="worker-is_active" name="is_active" value="1" checked> Активен</label>
            </div>
            
            <button type="button" class="btn btn-outline" onclick="closeModal()">Отмена</button>
            <button type="submit" class="btn btn-primary"><span id="submit-text">Добавить</span></button>
        </form>
    </div>
</div>

<style>
.badge-success { background:#27ae60; color:white; padding:4px 8px; border-radius:4px; }
.badge-default { background:#95a5a6; color:white; padding:4px 8px; border-radius:4px; }
</style>

<script>
function openModal(mode, worker) {
    worker = worker || null;
    document.getElementById('worker-form').reset();
    document.getElementById('worker-id').value = '';
    
    if (mode === 'add') {
        document.getElementById('modal-title').textContent = 'Добавить работника';
        document.getElementById('form-action').value = 'add';
        document.getElementById('submit-text').textContent = 'Добавить';
        document.getElementById('worker-password').required = true;
        document.getElementById('password-hint').style.display = 'inline';
        document.getElementById('password-note').style.display = 'none';
        document.getElementById('is-active-group').style.display = 'none';
        document.getElementById('username-group').style.display = 'block';
    } else {
        document.getElementById('modal-title').textContent = 'Редактировать';
        document.getElementById('form-action').value = 'edit';
        document.getElementById('submit-text').textContent = 'Сохранить';
        document.getElementById('worker-password').required = false;
        document.getElementById('password-hint').style.display = 'none';
        document.getElementById('password-note').style.display = 'inline';
        document.getElementById('is-active-group').style.display = 'block';
        document.getElementById('username-group').style.display = 'none';
        
        document.getElementById('worker-id').value = worker.id;
        document.getElementById('worker-full_name').value = worker.full_name;
        document.getElementById('worker-role').value = worker.role;
        document.getElementById('worker-is_active').checked = worker.is_active == 1;
    }
    
    document.getElementById('modal').style.display = 'block';
}

function closeModal() {
    document.getElementById('modal').style.display = 'none';
}

document.getElementById('modal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>