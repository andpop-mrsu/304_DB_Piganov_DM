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

if ($_POST && $_POST['confirm'] === 'yes') {
    $pdo->prepare("DELETE FROM worker_schedule WHERE id = ?")->execute([$id]);
    header("Location: index.php?worker_id=$worker_id");
    exit;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Удалить смену</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 40px auto; padding: 20px; }
        h1 { color: #dc3545; }
        .warning { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 6px; margin: 20px 0; }
        .btn-delete { 
            background: #dc3545; 
            color: white; 
            border: none; 
            padding: 10px 20px; 
            border-radius: 4px; 
            cursor: pointer;
        }
        .btn-cancel { 
            background: #6c757d; 
            color: white; 
            text-decoration: none; 
            padding: 10px 20px; 
            border-radius: 4px;
            display: inline-block;
            margin-left: 10px;
        }
    </style>
</head>
<body>

<h1>Подтверждение удаления</h1>

<div class="warning">
    <p>Вы уверены, что хотите удалить смену:</p>
    <p><strong>День:</strong> <?= e($days[$schedule['day_of_week']] ?? $schedule['day_of_week']) ?></p>
    <p><strong>Время:</strong> <?= e($schedule['start_time']) ?> – <?= e($schedule['end_time']) ?></p>
    <p>для мастера <strong><?= e($schedule['worker_name']) ?></strong>?</p>
</div>

<form method="post">
    <input type="hidden" name="confirm" value="yes">
    <button type="submit" class="btn-delete">Да, удалить</button>
    <a href="index.php?worker_id=<?= (int)$worker_id ?>" class="btn-cancel">Отмена</a>
</form>

</body>
</html>