<?php
header('Content-Type: text/html; charset=utf-8');
require_once __DIR__ . '/../src/db.php';

$id = (int)($_GET['id'] ?? 0);
$worker_id = (int)($_GET['worker_id'] ?? 0);
if (!$id || !$worker_id) {
    die('ID или worker_id не указаны');
}

$stmt = $pdo->prepare("
    SELECT ws.*, e.name AS worker_name
    FROM worker_schedule ws
    JOIN employees e ON ws.worker_id = e.id
    WHERE ws.id = ? AND ws.worker_id = ? AND e.position = 'Мастер'
");
$stmt->execute([$id, $worker_id]);
$schedule = $stmt->fetch();

if (!$schedule) {
    die('Запись не найдена');
}

$message = '';
if ($_POST) {
    $day = (int)($_POST['day_of_week'] ?? 0);
    $start = $_POST['start_time'] ?? '';
    $end = $_POST['end_time'] ?? '';
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if (!in_array($day, range(1, 7))) {
        $message = 'Выберите день недели.';
    } elseif (!$start || !$end) {
        $message = 'Укажите время начала и окончания.';
    } elseif ($start >= $end) {
        $message = 'Время окончания должно быть позже начала.';
    } else {
        try {
            $stmt = $pdo->prepare("
                UPDATE worker_schedule
                SET day_of_week = ?, start_time = ?, end_time = ?, is_active = ?
                WHERE id = ?
            ");
            $stmt->execute([$day, $start, $end, $is_active, $id]);
            header("Location: index.php?worker_id=$worker_id");
            exit;
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'UNIQUE constraint failed') !== false) {
                $message = 'На этот день недели расписание уже задано.';
            } else {
                $message = 'Ошибка: ' . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Редактировать смену — <?= e($schedule['worker_name']) ?></title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 30px auto; padding: 20px; }
        h1 { color: #2c3e50; }
        .error { color: #dc3545; margin-bottom: 15px; }
        label { display: block; margin: 12px 0 4px; font-weight: bold; }
        select, input { padding: 8px; width: 100%; border: 1px solid #ccc; border-radius: 4px; }
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

<h1>Редактировать смену для: <?= e($schedule['worker_name']) ?></h1>

<?php if ($message): ?>
    <div class="error"><?= e($message) ?></div>
<?php endif; ?>

<form method="post">
    <label for="day_of_week">День недели *</label>
    <select name="day_of_week" id="day_of_week" required>
        <option value="">— Выберите день —</option>
        <?php
        $days = [
            1 => 'Понедельник',
            2 => 'Вторник',
            3 => 'Среда',
            4 => 'Четверг',
            5 => 'Пятница',
            6 => 'Суббота',
            7 => 'Воскресенье'
        ];
        foreach ($days as $num => $name) {
            $selected = $num == $schedule['day_of_week'] ? ' selected' : '';
            echo '<option value="' . $num . '"' . $selected . '>' . $name . '</option>';
        }
        ?>
    </select>

    <label for="start_time">Начало *</label>
    <input type="time" id="start_time" name="start_time" value="<?= e($schedule['start_time']) ?>" required>

    <label for="end_time">Окончание *</label>
    <input type="time" id="end_time" name="end_time" value="<?= e($schedule['end_time']) ?>" required>

    <label>
        <input type="checkbox" name="is_active" value="1"<?= $schedule['is_active'] ? ' checked' : '' ?>> Активно
    </label>

    <div style="margin-top: 20px;">
        <button type="submit">Сохранить</button>
        <a href="index.php?worker_id=<?= (int)$worker_id ?>" class="btn-cancel">Отмена</a>
    </div>
</form>

</body>
</html>