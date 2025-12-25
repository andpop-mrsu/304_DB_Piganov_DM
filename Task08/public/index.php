<?php
header('Content-Type: text/html; charset=utf-8');
require_once __DIR__ . '/src/db.php';

// Получаем только работающих мастеров, сортируем по ФИО (фамилии)
$stmt = $pdo->query("
    SELECT id, name, hire_date
    FROM employees
    WHERE position = 'Мастер' 
      AND dismissal_date IS NULL
    ORDER BY name COLLATE NOCASE
");
$workers = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мастера автомойки</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f9f9f9; }
        h1 { color: #2c3e50; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; background: white; box-shadow: 0 1px 4px rgba(0,0,0,0.1); }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f4f4f4; }
        .btn {
            display: inline-block;
            padding: 6px 12px;
            margin: 2px;
            text-decoration: none;
            border-radius: 4px;
            font-size: 0.9em;
            color: white;
            background: #007bff;
        }
        .btn-delete { background: #dc3545; }
        .btn-schedule { background: #17a2b8; }
        .btn-logs { background: #6f42c1; }
        .add-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
        }
    </style>
</head>
<body>

<h1>Мастера автомойки</h1>

<?php if (empty($workers)): ?>
    <p>Нет работающих мастеров.</p>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>ФИО</th>
                <th>Дата приёма</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($workers as $w): ?>
                <tr>
                    <td><?= e($w['name']) ?></td>
                    <td><?= e($w['hire_date']) ?></td>
                    <td>
                        <a href="workers/edit.php?id=<?= (int)$w['id'] ?>" class="btn">Редактировать</a>
                        <a href="workers/delete.php?id=<?= (int)$w['id'] ?>" 
                           class="btn btn-delete"
                           onclick="return confirm('Уволить мастера <?= addslashes(e($w['name'])) ?>?')">
                            Уволить
                        </a>
                        <a href="schedule/index.php?worker_id=<?= (int)$w['id'] ?>" class="btn btn-schedule">График</a>
                        <a href="work_logs/index.php?worker_id=<?= (int)$w['id'] ?>" class="btn btn-logs">Выполненные работы</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<a href="workers/create.php" class="add-btn">➕ Добавить мастера</a>

</body>
</html>