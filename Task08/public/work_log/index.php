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

$stmt = $pdo->prepare("
    SELECT cw.id, cw.work_date, cw.work_time, cw.actual_price, s.name AS service_name, c.name AS car_category_name
    FROM completed_works cw
    JOIN services s ON cw.service_id = s.id
    JOIN car_categories c ON s.car_category_id = c.id
    WHERE cw.employee_id = ?
    ORDER BY cw.work_date DESC, cw.work_time DESC
");
$stmt->execute([$worker_id]);
$works = $stmt->fetchAll();

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
    <title>Выполненные работы — <?= e($worker_name) ?></title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 30px auto; padding: 20px; }
        h1 { color: #2c3e50; }
        .error { color: #dc3545; margin-bottom: 15px; }
        label { display: block; margin: 12px 0 4px; font-weight: bold; }
        input, select { padding: 8px; width: 100%; border: 1px solid #ccc; border-radius: 4px; }
        .actions a { margin: 0 5px; text-decoration: none; padding: 5px 10px; border-radius: 3px; }
        .btn { background: #007bff; color: white; }
        .btn-delete { background: #dc3545; }
        .btn-add { 
            display: inline-block; 
            margin-top: 20px; 
            padding: 10px 20px; 
            background: #28a745; 
            color: white; 
            text-decoration: none; 
            border-radius: 4px;
        }
    </style>
</head>
<body>

<h1>Выполненные работы: <?= e($worker_name) ?></h1>

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
    <textarea id="notes" name="notes" rows="3" style="width:100%;"></textarea>

    <div style="margin-top: 20px;">
        <button type="submit">Добавить работу</button>
        <a href="../index.php" class="btn-add">← Назад к мастерам</a>
    </div>
</form>

<hr>

<?php if (empty($works)): ?>
    <p>Нет выполненных работ.</p>
<?php else: ?>
    <h2>Список работ</h2>
    <table style="width:100%; border-collapse: collapse; margin-top: 10px;">
        <thead>
            <tr>
                <th>Дата</th>
                <th>Время</th>
                <th>Услуга</th>
                <th>Категория</th>
                <th>Цена</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($works as $w): ?>
                <tr>
                    <td><?= e($w['work_date']) ?></td>
                    <td><?= e($w['work_time']) ?></td>
                    <td><?= e($w['service_name']) ?></td>
                    <td><?= e($w['car_category_name']) ?></td>
                    <td><?= number_format($w['actual_price'], 2, ',', ' ') ?> ₽</td>
                    <td>
                        <a href="edit.php?id=<?= (int)$w['id'] ?>&worker_id=<?= (int)$worker_id ?>" class="btn">Редактировать</a>
                        <a href="delete.php?id=<?= (int)$w['id'] ?>&worker_id=<?= (int)$worker_id ?>" class="btn btn-delete" onclick="return confirm('Удалить эту работу?')">Удалить</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

</body>
</html>