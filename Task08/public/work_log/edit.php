<?php
header('Content-Type: text/html; charset=utf-8');
require_once __DIR__ . '/../src/db.php';

$id = (int)($_GET['id'] ?? 0);
$worker_id = (int)($_GET['worker_id'] ?? 0);
if (!$id || !$worker_id) {
    die('ID или worker_id не указаны');
}

$stmt = $pdo->prepare("
    SELECT cw.*, s.name AS service_name, c.name AS car_category_name
    FROM completed_works cw
    JOIN services s ON cw.service_id = s.id
    JOIN car_categories c ON s.car_category_id = c.id
    WHERE cw.id = ? AND cw.employee_id = ?
");
$stmt->execute([$id, $worker_id]);
$work = $stmt->fetch();

if (!$work) {
    die('Работа не найдена');
}

$stmt_worker = $pdo->prepare("SELECT name FROM employees WHERE id = ? AND position = 'Мастер'");
$stmt_worker->execute([$worker_id]);
$worker_name = $stmt_worker->fetchColumn();
if (!$worker_name) {
    die('Мастер не найден');
}

$message = '';
if ($_POST) {
    $service_id = (int)($_POST['service_id'] ?? 0);
    $work_date = $_POST['work_date'] ?? $work['work_date'];
    $work_time = $_POST['work_time'] ?? $work['work_time'];
    $actual_duration_minutes = (int)($_POST['actual_duration_minutes'] ?? 15);
    $actual_price = floatval($_POST['actual_price'] ?? 0);
    $notes = trim($_POST['notes'] ?? '');

    if ($service_id <= 0 || $actual_duration_minutes <= 0 || $actual_price < 0) {
        $message = 'Проверьте корректность данных.';
    } else {
        $stmt = $pdo->prepare("
            UPDATE completed_works
            SET service_id = ?, work_date = ?, work_time = ?, actual_duration_minutes = ?, actual_price = ?, notes = ?
            WHERE id = ?
        ");
        $stmt->execute([$service_id, $work_date, $work_time, $actual_duration_minutes, $actual_price, $notes, $id]);
        header("Location: index.php?worker_id=$worker_id");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Редактировать работу — <?= e($worker_name) ?></title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 30px auto; padding: 20px; }
        h1 { color: #2c3e50; }
        .error { color: #dc3545; margin-bottom: 15px; }
        label { display: block; margin: 12px 0 4px; font-weight: bold; }
        input, select, textarea { padding: 8px; width: 100%; border: 1px solid #ccc; border-radius: 4px; }
        button { background: #007bff; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; }
        .btn-cancel { 
            display: inline-block; 
            margin-left: 10px; 
            padding: 10px 20px; 
            background: #6c757d; 
            color: white; 
            text-decoration: none; 
            border-radius: 4px;
        }
    </style>
</head>
<body>

<h1>Редактировать работу для: <?= e($worker_name) ?></h1>

<?php if ($message): ?>
    <div class="error"><?= e($message) ?></div>
<?php endif; ?>

<form method="post">
    <label for="service_id">Услуга *</label>
    <select name="service_id" id="service_id" required>
        <option value="">— Выберите услугу —</option>
        <?php
        $stmt_services = $pdo->query("SELECT id, name, car_category_id FROM services ORDER BY car_category_id, name");
        while ($s = $stmt_services->fetch()) {
            $stmt_cat = $pdo->prepare("SELECT name FROM car_categories WHERE id = ?");
            $stmt_cat->execute([$s['car_category_id']]);
            $cat_name = $stmt_cat->fetchColumn();
            $opt_text = $s['name'] . ' (' . $cat_name . ')';
            $selected = $s['id'] == $work['service_id'] ? ' selected' : '';
            echo '<option value="' . $s['id'] . '"' . $selected . '>' . e($opt_text) . '</option>';
        }
        ?>
    </select>

    <label for="work_date">Дата *</label>
    <input type="date" id="work_date" name="work_date" value="<?= e($work['work_date']) ?>" required>

    <label for="work_time">Время *</label>
    <input type="time" id="work_time" name="work_time" value="<?= e($work['work_time']) ?>" required>

    <label for="actual_duration_minutes">Длительность (мин) *</label>
    <input type="number" id="actual_duration_minutes" name="actual_duration_minutes" min="1" value="<?= e($work['actual_duration_minutes']) ?>" required>

    <label for="actual_price">Цена *</label>
    <input type="number" id="actual_price" name="actual_price" step="0.01" min="0" value="<?= e($work['actual_price']) ?>" required>

    <label for="notes">Примечания</label>
    <textarea id="notes" name="notes" rows="3"><?= e($work['notes']) ?></textarea>

    <div style="margin-top: 20px;">
        <button type="submit">Сохранить</button>
        <a href="index.php?worker_id=<?= (int)$worker_id ?>" class="btn-cancel">Отмена</a>
    </div>
</form>

</body>
</html>