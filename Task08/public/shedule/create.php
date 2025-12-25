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
                INSERT INTO worker_schedule (worker_id, day_of_week, start_time, end_time, is_active)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$worker_id, $day, $start, $end, $is_active]);
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
    <title>Новая смена — <?= e($worker_name) ?></title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 30px auto; padding: 20px; }
        h1 { color: #2c3e50; }
        .error { color: #dc3545; margin-bottom: 15px; }
        label { display: block; margin: 12px 0 4px; font-weight: bold; }
        select, input { padding: 8px; width: 100%; border: 1px solid #ccc; border-radius: 4px; }
        button { background: #17a2b8; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; }
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

<h1>Добавить смену для: <?= e($worker_name) ?></h1>

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
            echo '<option value="' . $num . '">' . $name . '</option>';
        }
        ?>
    </select>

    <label for="start_time">Начало *</label>
    <input type="time" id="start_time" name="start_time" required>

    <label for="end_time">Окончание *</label>
    <input type="time" id="end_time" name="end_time" required>

    <label>
        <input type="checkbox" name="is_active" value="1" checked> Активно
    </label>

    <div style="margin-top: 20px;">
        <button type="submit">Сохранить</button>
        <a href="index.php?worker_id=<?= (int)$worker_id ?>" class="btn-cancel">Отмена</a>
    </div>
</form>

</body>
</html>