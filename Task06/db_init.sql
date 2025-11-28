DROP TABLE IF EXISTS completed_works;
DROP TABLE IF EXISTS bookings;
DROP TABLE IF EXISTS services;
DROP TABLE IF EXISTS car_categories;
DROP TABLE IF EXISTS boxes;
DROP TABLE IF EXISTS employees;

CREATE TABLE employees (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    position TEXT NOT NULL CHECK(position IN ('Мастер', 'Администратор', 'Менеджер')),
    salary_percentage REAL NOT NULL CHECK(salary_percentage >= 0 AND salary_percentage <= 100),
    hire_date DATE NOT NULL DEFAULT (date('now')),
    dismissal_date DATE,
    phone TEXT,
    email TEXT,
    CHECK(dismissal_date IS NULL OR dismissal_date >= hire_date)
);

CREATE TABLE car_categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL UNIQUE,
    description TEXT
);

CREATE TABLE boxes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    number INTEGER NOT NULL UNIQUE CHECK(number > 0),
    is_active INTEGER NOT NULL DEFAULT 1 CHECK(is_active IN (0, 1)),
    description TEXT
);

CREATE TABLE services (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    duration_minutes INTEGER NOT NULL CHECK(duration_minutes > 0),
    price REAL NOT NULL CHECK(price >= 0),
    car_category_id INTEGER NOT NULL,
    description TEXT,
    FOREIGN KEY (car_category_id) REFERENCES car_categories(id) ON DELETE RESTRICT,
    UNIQUE(name, car_category_id)
);

CREATE TABLE bookings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    client_name TEXT NOT NULL,
    client_phone TEXT NOT NULL,
    box_id INTEGER NOT NULL,
    employee_id INTEGER NOT NULL,
    service_id INTEGER NOT NULL,
    booking_date DATE NOT NULL,
    booking_time TIME NOT NULL,
    status TEXT NOT NULL DEFAULT 'pending' CHECK(status IN ('pending', 'completed', 'cancelled')),
    created_at DATETIME NOT NULL DEFAULT (datetime('now')),
    notes TEXT,
    FOREIGN KEY (box_id) REFERENCES boxes(id) ON DELETE RESTRICT,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE RESTRICT,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE RESTRICT
);

CREATE TABLE completed_works (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    booking_id INTEGER,
    employee_id INTEGER NOT NULL,
    box_id INTEGER NOT NULL,
    service_id INTEGER NOT NULL,
    work_date DATE NOT NULL DEFAULT (date('now')),
    work_time TIME NOT NULL,
    actual_duration_minutes INTEGER NOT NULL CHECK(actual_duration_minutes > 0),
    actual_price REAL NOT NULL CHECK(actual_price >= 0),
    notes TEXT,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE SET NULL,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE RESTRICT,
    FOREIGN KEY (box_id) REFERENCES boxes(id) ON DELETE RESTRICT,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE RESTRICT
);

-- Сотрудники
INSERT INTO employees (name, position, salary_percentage, hire_date, dismissal_date, phone, email) VALUES
('Иванов Иван Иванович', 'Мастер', 25.0, '2023-01-15', NULL, '+7-900-123-45-67', 'ivanov@example.com'),
('Петров Петр Петрович', 'Мастер', 30.0, '2023-02-01', NULL, '+7-900-234-56-78', 'petrov@example.com'),
('Сидорова Мария Сергеевна', 'Мастер', 28.0, '2023-03-10', NULL, '+7-900-345-67-89', 'sidorova@example.com'),
('Козлов Алексей Викторович', 'Мастер', 27.0, '2022-11-20', '2024-06-30', '+7-900-456-78-90', 'kozlov@example.com'),
('Смирнова Анна Дмитриевна', 'Администратор', 0.0, '2023-01-05', NULL, '+7-900-567-89-01', 'smirnova@example.com'),
('Волков Дмитрий Александрович', 'Менеджер', 0.0, '2023-04-01', NULL, '+7-900-678-90-12', 'volkov@example.com');

-- Категории автомобилей
INSERT INTO car_categories (name, description) VALUES
('Легковые', 'Легковые автомобили до 5 метров'),
('Внедорожники', 'Внедорожники и кроссоверы'),
('Грузовые', 'Грузовые автомобили и микроавтобусы'),
('Мотоциклы', 'Мотоциклы и скутеры');

-- Боксы
INSERT INTO boxes (number, is_active, description) VALUES
(1, 1, 'Бокс 1 - стандартный'),
(2, 1, 'Бокс 2 - стандартный'),
(3, 1, 'Бокс 3 - для грузовых'),
(4, 1, 'Бокс 4 - стандартный'),
(5, 0, 'Бокс 5 - на ремонте');

-- Услуги
-- Легковые
INSERT INTO services (name, duration_minutes, price, car_category_id, description) VALUES
('Мойка кузова', 15, 500.0, 1, 'Стандартная мойка кузова'),
('Мойка кузова + сушка', 25, 800.0, 1, 'Мойка с сушкой'),
('Полная мойка', 45, 1500.0, 1, 'Мойка кузова, салона, багажника'),
('Полировка кузова', 60, 3000.0, 1, 'Полировка кузова автомобиля'),
('Химчистка салона', 90, 4000.0, 1, 'Полная химчистка салона');

-- Внедорожники
INSERT INTO services (name, duration_minutes, price, car_category_id, description) VALUES
('Мойка кузова', 20, 700.0, 2, 'Стандартная мойка кузова'),
('Мойка кузова + сушка', 30, 1100.0, 2, 'Мойка с сушкой'),
('Полная мойка', 50, 2000.0, 2, 'Мойка кузова, салона, багажника'),
('Полировка кузова', 75, 4000.0, 2, 'Полировка кузова автомобиля'),
('Химчистка салона', 100, 5000.0, 2, 'Полная химчистка салона');

-- Грузовые
INSERT INTO services (name, duration_minutes, price, car_category_id, description) VALUES
('Мойка кузова', 30, 1200.0, 3, 'Стандартная мойка кузова'),
('Мойка кузова + сушка', 45, 1800.0, 3, 'Мойка с сушкой'),
('Полная мойка', 90, 3500.0, 3, 'Мойка кузова, салона, багажника'),
('Мойка фургона', 60, 2500.0, 3, 'Мойка грузового фургона');

-- Мотоциклы
INSERT INTO services (name, duration_minutes, price, car_category_id, description) VALUES
('Мойка мотоцикла', 20, 400.0, 4, 'Стандартная мойка мотоцикла'),
('Полная мойка мотоцикла', 35, 800.0, 4, 'Мойка с полировкой');

-- Предварительные записи
INSERT INTO bookings (client_name, client_phone, box_id, employee_id, service_id, booking_date, booking_time, status, notes) VALUES
('Сергеев Сергей Сергеевич', '+7-911-111-11-11', 1, 1, 1, '2024-12-20', '10:00', 'pending', 'Клиент просит быть аккуратным с бампером'),
('Николаев Николай Николаевич', '+7-911-222-22-22', 2, 2, 2, '2024-12-20', '11:00', 'pending', NULL),
('Александров Александр Александрович', '+7-911-333-33-33', 1, 1, 3, '2024-12-20', '14:00', 'pending', NULL),
('Дмитриев Дмитрий Дмитриевич', '+7-911-444-44-44', 3, 2, 7, '2024-12-21', '09:00', 'pending', 'Грузовик большой, нужен бокс 3'),
('Елена Еленова', '+7-911-555-55-55', 2, 3, 5, '2024-12-19', '15:00', 'completed', 'Работа выполнена'),
('Федоров Федор Федорович', '+7-911-666-66-66', 4, 1, 1, '2024-12-18', '12:00', 'cancelled', 'Клиент отменил запись');

-- Выполненные работы
INSERT INTO completed_works (booking_id, employee_id, box_id, service_id, work_date, work_time, actual_duration_minutes, actual_price, notes) VALUES
(5, 3, 2, 5, '2024-12-19', '15:00', 95, 4000.0, 'Работа выполнена качественно'),
(NULL, 1, 1, 1, '2024-12-18', '10:30', 15, 500.0, 'Работа без предварительной записи'),
(NULL, 2, 2, 2, '2024-12-18', '11:00', 28, 800.0, NULL),
(NULL, 1, 1, 3, '2024-12-17', '14:00', 50, 1500.0, 'Клиент доволен'),
(NULL, 3, 4, 1, '2024-12-17', '16:00', 16, 500.0, NULL),
(NULL, 2, 3, 7, '2024-12-16', '10:00', 32, 1200.0, 'Грузовик'),
(NULL, 1, 1, 4, '2024-12-16', '13:00', 65, 3000.0, 'Полировка выполнена'),
(NULL, 3, 2, 2, '2024-12-15', '11:30', 27, 800.0, NULL),
(NULL, 2, 3, 8, '2024-12-15', '14:00', 48, 1800.0, 'Грузовик с сушкой'),
(NULL, 1, 1, 1, '2024-12-14', '09:00', 14, 500.0, NULL),
(NULL, 4, 2, 1, '2024-06-15', '10:00', 15, 500.0, NULL),
(NULL, 4, 3, 7, '2024-06-14', '14:00', 30, 1200.0, 'Грузовик'),
(NULL, 4, 1, 3, '2024-06-10', '11:00', 45, 1500.0, 'Полная мойка');