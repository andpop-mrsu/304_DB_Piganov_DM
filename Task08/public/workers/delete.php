<?php
header('Content-Type: text/html; charset=utf-8');
require_once __DIR__ . '/../src/db.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    die('ID не указан');
}

$stmt = $pdo->prepare("SELECT name FROM employees WHERE id = ? AND position = 'Мастер'");
$stmt->execute([$id]);
$name = $stmt->fetchColumn();

if (!$name) {
    die('Мастер не найден');
}

if ($_POST && $_POST['confirm'] === 'yes') {
    // Мягкое удаление: устанавливаем dismissal_date
    $pdo->prepare("
        UPDATE employees 
        SET dismissal_date = date('now') 
        WHERE id = ? AND position = 'Мастер'
    ")->execute([$id]);

    header('Location: ../index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Удалить мастера</title>
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

<h1>Подтверждение увольнения</h1>

<div class="warning">
    <p>Вы уверены, что хотите уволить мастера <strong><?= e($name) ?></strong>?</p>
    <p>Мастер будет скрыт из списка активных, но останется в истории бронирований и выполненных работ.</p>
</div>

<form method="post">
    <input type="hidden" name="confirm" value="yes">
    <button type="submit" class="btn-delete">Да, уволить</button>
    <a href="../index.php" class="btn-cancel">Отмена</a>
</form>

</body>
</html>