<?php

define('DB_PATH', __DIR__ . '/db.sqlite');

try {
    $db = new PDO('sqlite:' . DB_PATH);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database error: " . htmlspecialchars($e->getMessage()));
}

// Получаем список сотрудников для dropdown
$employeesStmt = $db->query(
    "SELECT id, name FROM Employees ORDER BY name"
);
$employees = $employeesStmt->fetchAll(PDO::FETCH_ASSOC);

// Получаем выбранного сотрудника
$selectedEmployeeId = $_GET['employee_id'] ?? null;

// Запрос услуг
$query = "
    SELECT 
        e.id,
        e.name,
        a.appointment_start,
        s.name as service_name,
        a.appointment_price
    FROM Appointments a
    JOIN Employees e ON a.employee_id = e.id
    JOIN ServiceDetails sd ON a.service_detail_id = sd.id
    JOIN Services s ON sd.service_id = s.id
    WHERE a.status = 'completed'
";

$params = [];
if ($selectedEmployeeId !== null && $selectedEmployeeId !== '') {
    $query .= " AND e.id = ?";
    $params[] = (int)$selectedEmployeeId;
}

$query .= " ORDER BY e.name, a.appointment_start";

$stmt = $db->prepare($query);
$stmt->execute($params);
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Services Report</title>
</head>
<body>
    <h1>Employee Services Report</h1>
    
    <div class="filter">
        <form method="get">
            <label for="employee_id">Filter by Employee:</label>
            <select name="employee_id" id="employee_id" onchange="this.form.submit()">
                <option value="">All Employees</option>
                <?php foreach ($employees as $emp): ?>
                    <option value="<?= $emp['id'] ?>" <?php if ($selectedEmployeeId == $emp['id']): ?>selected<?php endif; ?>>
                    <?= htmlspecialchars($emp['id'] . ' ' . $emp['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>
    
    <?php if (empty($services)): ?>
        <p>No completed services found.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Employee ID</th>
                    <th>Employee Name</th>
                    <th>Date</th>
                    <th>Service</th>
                    <th>Price</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($services as $service): ?>
                    <tr>
                        <td><?= htmlspecialchars($service['id']) ?></td>
                        <td><?= htmlspecialchars($service['name']) ?></td>
                        <td><?= htmlspecialchars(substr($service['appointment_start'], 0, 10)) ?></td>
                        <td><?= htmlspecialchars($service['service_name']) ?></td>
                        <td><?= sprintf('%.2f', $service['appointment_price']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</body>
</html>
