<?php

define('DB_PATH', __DIR__ . '/db.sqlite');

try {
    $db = new PDO('sqlite:' . DB_PATH);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Database connection error: " . $e->getMessage() . "\n";
    exit(1);
}

// Получаем список сотрудников
$employees = $db->query(
    "SELECT id, name FROM Employees ORDER BY name"
)->fetchAll(PDO::FETCH_ASSOC);

if (empty($employees)) {
    echo "No employees found.\n";
    exit(0);
}

echo "\nAvailable Employees\n";
foreach ($employees as $emp) {
    echo sprintf("  %d: %s\n", $emp['id'], $emp['name']);
}

echo "\nEnter employee ID (or press Enter for all): ";
$input = trim(fgets(STDIN));

// Валидация input
$employeeId = null;
if ($input !== '') {
    if (!ctype_digit($input)) {
        echo "Invalid input. Please enter a number.\n";
        exit(1);
    }
    $employeeId = (int)$input;
    
    // Проверяем, существует ли такой сотрудник
    $exists = $db->prepare("SELECT COUNT(*) FROM Employees WHERE id = ?");
    $exists->execute([$employeeId]);
    if ($exists->fetchColumn() == 0) {
        echo "Employee with ID $employeeId not found.\n";
        exit(1);
    }
}

// Запрос данных
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
if ($employeeId !== null) {
    $query .= " AND e.id = ?";
    $params[] = $employeeId;
}

$query .= " ORDER BY e.name, a.appointment_start";

$stmt = $db->prepare($query);
$stmt->execute($params);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

displayTable($results);

function displayTable($data) {
    if (empty($data)) {
        echo "\nNo completed services found.\n";
        return;
    }
    
    $colWidths = [4, 20, 19, 20, 10];
    
    echo "\n";
    printRow(['ID', 'Name', 'Date', 'Service', 'Price'], $colWidths);
    printSeparator($colWidths);
    
    foreach ($data as $row) {
        $date = substr($row['appointment_start'], 0, 10);
        printRow(
            [
                $row['id'],
                substr($row['name'], 0, 19),
                $date,
                substr($row['service_name'], 0, 19),
                sprintf('%.2f', $row['appointment_price'])
            ],
            $colWidths
        );
    }
    
    printSeparator($colWidths);
    echo "\n";
}

function printRow($cols, $widths) {
    echo "| ";
    for ($i = 0; $i < count($cols); $i++) {
        echo str_pad($cols[$i], $widths[$i] - 1) . " | ";
    }
    echo "\n";
}

function printSeparator($widths) {
    echo "|-";
    foreach ($widths as $w) {
        echo str_repeat("-", $w) . "-|";
    }
    echo "\n";
}
