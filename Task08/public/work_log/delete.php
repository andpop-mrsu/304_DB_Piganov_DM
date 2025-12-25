<?php
header('Content-Type: text/html; charset=utf-8');
require_once __DIR__ . '/../src/db.php';

$id = (int)($_GET['id'] ?? 0);
$worker_id = (int)($_GET['worker_id'] ?? 0);
if (!$id || !$worker_id) {
    die('ID или worker_id не указаны');
}

$stmt = $pdo->prepare("
    SELECT cw.*, s.name AS service_name, c.name AS car_category_name, e.name AS worker_name
    FROM completed_works cw
    JOIN services s ON cw.service_id = s.id
    JOIN car_categories c ON s.car_category_id = c.id
    JOIN employees e ON cw.employee_id = e.id
    WHERE cw.id = ? AND cw.employee_id = ?
");
$stmt->execute([$id, $worker_id]);
$work = $stmt->fetch();

if (!$work) {
    die('Работа не найдена');
}

if ($_POST && $_POST['confirm'] === 'yes') {
    $pdo->prepare("DELETE FROM completed_works WHERE id = ?")->execute([$id]);
    header("Location: index.php?worker_id=$worker_id");
    exit;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Удалить работу</title>
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
    <p>Вы уверены, что хотите удалить работу:</p>
    <p><strong>Услуга:</strong> <?= e($work['service_name']) ?> (<?= e($work['car_category_name']) ?>)</p>
    <p><strong>Дата/время:</strong> <?= e($work['work_date']) ?> <?= e($work['work_time']) ?></p>
    <p><strong>Цена:</strong> <?= number_format($work['actual_price'], 2, ',', ' ') ?> ₽</p>
    <p>для мастера <strong><?= e($work['worker_name']) ?></strong>?</p>
</div>

<form method="post">
    <input type="hidden" name="confirm" value="yes">
    <button type="submit" class="btn-delete">Да, удалить</button>
    <a href="index.php?worker_id=<?= (int)$worker_id ?>" class="btn-cancel">Отмена</a>
</form>

</body>
</html>