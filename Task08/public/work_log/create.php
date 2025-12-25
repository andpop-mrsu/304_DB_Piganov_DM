<?php
header('Content-Type: text/html; charset=utf-8');
require_once __DIR__ . '/../src/db.php';

$worker_id = (int)($_GET['worker_id'] ?? 0);
if (!$worker_id) {
    die('worker_id обязателен');
}

$stmt = $pdo->prepare("SELECT name FROM employees WHERE id = ? AND position = 'Мастер'");
$stmt->execute([$worker_id]);
$worker_name = $stmt->fetchColumn();
if (!$worker_name) {
    die('Мастер не найден');
}

$message = '';
if ($_POST) {
    $service_id = (int)($_POST['service_id'] ?? 0);
    $work_date = $_POST['work_date'] ?? date('Y-m-d');
    $work_time = $_POST['work_time'] ?? date('H:i');
    $actual_duration_minutes = (int)($_POST['actual_duration_minutes'] ?? 15);
    $actual_price = floatval($_POST['actual_price'] ?? 0);
    $notes = trim($_POST['notes'] ?? '');

    if ($service_id <= 0 || $actual_duration_minutes <= 0 || $actual_price < 0) {
        $message = 'Проверьте корректность данных.';
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO completed_works (employee_id, box_id, service_id, work_date, work_time, actual_duration_minutes, actual_price, notes)
            VALUES (?, 1, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$worker_id, $service_id, $work_date, $work_time, $actual_duration_minutes, $actual_price, $notes]);
        header("Location: index.php?worker_id=$worker_id");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Новая работа — <?= e($worker_name) ?></title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 30px auto; padding: 20px; }
        h1 { color: #2c3e50; }
        .error { color: #dc3545; margin-bottom: 15px; }
        label { display: block; margin: 12px 0 4px; font-weight: bold; }
        input, select, textarea { padding: 8px; width: 100%; border: 1px solid #ccc; border-radius: 4px; }
        button { background: #28a745; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; }
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

<h1>Добавить работу для: <?= e($worker_name) ?></h1>

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
            echo '<option value="' . $s['id'] . '">' . e($opt_text) . '</option>';
        }
        ?>
    </select>

    <label for="work_date">Дата *</label>
    <input type="date" id="work_date" name="work_date" value="<?= date('Y-m-d') ?>" required>

    <label for="work_time">Время *</label>
    <input type="time" id="work_time" name="work_time" value="<?= date('H:i') ?>" required>

    <label for="actual_duration_minutes">Длительность (мин) *</label>
    <input type="number" id="actual_duration_minutes" name="actual_duration_minutes" min="1" value="15" required>

    <label for="actual_price">Цена *</label>
    <input type="number" id="actual_price" name="actual_price" step="0.01" min="0" value="0" required>

    <label for="notes">Примечания</label>
    <textarea id="notes" name="notes" rows="3"></textarea>

    <div style="margin-top: 20px;">
        <button type="submit">Сохранить</button>
        <a href="index.php?worker_id=<?= (int)$worker_id ?>" class="btn-cancel">Отмена</a>
    </div>
</form>

</body>
</html>