<?php
header('Content-Type: text/html; charset=utf-8');
require_once __DIR__ . '/../src/db.php';

$message = '';

if ($_POST) {
    $name = trim($_POST['name'] ?? '');
    $salary_percentage = floatval($_POST['salary_percentage'] ?? 25.0);
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $hire_date = $_POST['hire_date'] ?? date('Y-m-d');

    if ($name === '') {
        $message = 'Поле "ФИО" обязательно.';
    } elseif ($salary_percentage < 0 || $salary_percentage > 100) {
        $message = 'Процент должен быть от 0 до 100.';
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO employees (name, position, salary_percentage, hire_date, phone, email)
            VALUES (?, 'Мастер', ?, ?, ?, ?)
        ");
        $stmt->execute([$name, $salary_percentage, $hire_date, $phone, $email]);

        header('Location: ../index.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Добавить мастера</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 20px auto; padding: 20px; }
        h1 { color: #2c3e50; }
        .error { color: red; margin-bottom: 15px; }
        label { display: block; margin: 10px 0 5px; font-weight: bold; }
        input { width: 100%; padding: 8px; margin-bottom: 10px; border: 1px solid #ccc; border-radius: 4px; }
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

<h1>Добавить мастера</h1>

<?php if ($message): ?>
    <div class="error"><?= e($message) ?></div>
<?php endif; ?>

<form method="post">
    <label for="name">ФИО *</label>
    <input type="text" id="name" name="name" value="<?= e($_POST['name'] ?? '') ?>" required>

    <label for="salary_percentage">Процент от выручки (%) *</label>
    <input type="number" id="salary_percentage" name="salary_percentage" 
           step="0.1" min="0" max="100" 
           value="<?= e($_POST['salary_percentage'] ?? '25.0') ?>" required>

    <label for="phone">Телефон</label>
    <input type="text" id="phone" name="phone" value="<?= e($_POST['phone'] ?? '') ?>">

    <label for="email">Email</label>
    <input type="email" id="email" name="email" value="<?= e($_POST['email'] ?? '') ?>">

    <label for="hire_date">Дата приёма *</label>
    <input type="date" id="hire_date" name="hire_date" 
           value="<?= e($_POST['hire_date'] ?? date('Y-m-d')) ?>" required>

    <div style="margin-top: 20px;">
        <button type="submit">Сохранить</button>
        <a href="../index.php" class="btn-cancel">← Отмена</a>
    </div>
</form>

</body>
</html>