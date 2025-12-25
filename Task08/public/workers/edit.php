<?php
header('Content-Type: text/html; charset=utf-8');
require_once __DIR__ . '/../src/db.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    die('ID не указан');
}

$stmt = $pdo->prepare("SELECT * FROM employees WHERE id = ? AND position = 'Мастер'");
$stmt->execute([$id]);
$worker = $stmt->fetch();

if (!$worker) {
    die('Мастер не найден');
}

$message = '';

if ($_POST) {
    $name = trim($_POST['name'] ?? '');
    $salary_percentage = floatval($_POST['salary_percentage'] ?? 0);
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $hire_date = $_POST['hire_date'] ?? $worker['hire_date'];
    $dismissal_date = !empty($_POST['dismissal_date']) ? $_POST['dismissal_date'] : null;

    if ($name === '') {
        $message = 'Поле "ФИО" обязательно.';
    } elseif ($salary_percentage < 0 || $salary_percentage > 100) {
        $message = 'Процент должен быть от 0 до 100.';
    } elseif ($dismissal_date && $dismissal_date < $hire_date) {
        $message = 'Дата увольнения не может быть раньше даты приёма.';
    } else {
        $stmt = $pdo->prepare("
            UPDATE employees
            SET 
                name = ?,
                salary_percentage = ?,
                hire_date = ?,
                dismissal_date = ?,
                phone = ?,
                email = ?
            WHERE id = ?
        ");
        $stmt->execute([$name, $salary_percentage, $hire_date, $dismissal_date, $phone, $email, $id]);

        header('Location: ../index.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Редактировать мастера</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 30px auto; padding: 20px; }
        h1 { color: #2c3e50; }
        .error { color: #dc3545; margin-bottom: 15px; }
        label { display: block; margin: 12px 0 4px; font-weight: bold; }
        input { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
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

<h1>Редактировать: <?= e($worker['name']) ?></h1>

<?php if ($message): ?>
    <div class="error"><?= e($message) ?></div>
<?php endif; ?>

<form method="post">
    <label for="name">ФИО *</label>
    <input type="text" id="name" name="name" value="<?= e($worker['name']) ?>" required>

    <label for="salary_percentage">Процент от выручки (%) *</label>
    <input type="number" id="salary_percentage" name="salary_percentage" 
           step="0.1" min="0" max="100" 
           value="<?= e($worker['salary_percentage']) ?>" required>

    <label for="phone">Телефон</label>
    <input type="text" id="phone" name="phone" value="<?= e($worker['phone']) ?>">

    <label for="email">Email</label>
    <input type="email" id="email" name="email" value="<?= e($worker['email']) ?>">

    <label for="hire_date">Дата приёма *</label>
    <input type="date" id="hire_date" name="hire_date" value="<?= e($worker['hire_date']) ?>" required>

    <label for="dismissal_date">Дата увольнения</label>
    <input type="date" id="dismissal_date" name="dismissal_date" value="<?= e($worker['dismissal_date']) ?>">
    <small style="display:block; margin-top:4px; color:#666;">Оставьте пустым, если мастер работает</small>

    <div style="margin-top: 20px;">
        <button type="submit">Сохранить</button>
        <a href="../index.php" class="btn-cancel">Отмена</a>
    </div>
</form>

</body>
</html>