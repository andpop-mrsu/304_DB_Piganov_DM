PRAGMA foreign_keys = ON;

CREATE TABLE CarCategories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL UNIQUE
);

CREATE TABLE Services (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL UNIQUE
);

CREATE TABLE ServiceDetails (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    service_id INTEGER NOT NULL,
    car_category_id INTEGER NOT NULL,
    duration_minutes INTEGER NOT NULL,
    price REAL NOT NULL,
    UNIQUE (service_id, car_category_id),
    FOREIGN KEY (service_id) REFERENCES Services(id) ON DELETE RESTRICT,
    FOREIGN KEY (car_category_id) REFERENCES CarCategories(id) ON DELETE RESTRICT,
    CHECK (duration_minutes > 0),
    CHECK (price >= 0)
);

CREATE TABLE Employees (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    hire_date DATE NOT NULL,
    dismissal_date DATE DEFAULT NULL,
    salary_percentage REAL NOT NULL,
    CHECK (salary_percentage BETWEEN 0 AND 100)
);

CREATE TABLE Bays (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL UNIQUE
);

CREATE TABLE Appointments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    customer_name TEXT NOT NULL,
    appointment_start DATETIME NOT NULL,
    appointment_duration_minutes INTEGER NOT NULL,
    appointment_price REAL NOT NULL,
    appointment_end DATETIME NOT NULL,
    bay_id INTEGER NOT NULL,
    employee_id INTEGER NOT NULL,
    service_detail_id INTEGER NOT NULL,
    status TEXT DEFAULT 'planned' CHECK (status IN ('planned', 'completed', 'cancelled')),
    actual_end DATETIME DEFAULT NULL,
    FOREIGN KEY (bay_id) REFERENCES Bays(id) ON DELETE RESTRICT,
    FOREIGN KEY (employee_id) REFERENCES Employees(id) ON DELETE RESTRICT,
    FOREIGN KEY (service_detail_id) REFERENCES ServiceDetails(id) ON DELETE RESTRICT,
    CHECK (appointment_duration_minutes > 0),
    CHECK (appointment_price >= 0)
);

CREATE INDEX idx_appointments_bay_start ON Appointments(bay_id, appointment_start);
CREATE INDEX idx_appointments_employee_start ON Appointments(employee_id, appointment_start);
CREATE INDEX idx_appointments_status ON Appointments(status);


INSERT INTO CarCategories (name)
VALUES ('Sedan'), ('SUV');

INSERT INTO Services (name)
VALUES ('Wash'), ('Polish');

INSERT INTO ServiceDetails (service_id, car_category_id, duration_minutes, price)
VALUES
    (1, 1, 30, 20.00),
    (1, 2, 45, 30.00),
    (2, 1, 60, 50.00);

INSERT INTO Employees (name, hire_date, salary_percentage)
VALUES
    ('John Doe', '2025-01-01', 20.0),
    ('Jane Smith', '2025-01-01', 25.0);

INSERT INTO Bays (name)
VALUES ('Bay 1'), ('Bay 2');

INSERT INTO Appointments (
    customer_name,
    appointment_start,
    appointment_duration_minutes,
    appointment_price,
    appointment_end,
    bay_id,
    employee_id,
    service_detail_id,
    status
) VALUES
    ('Client A', '2025-11-15 10:00:00', 30, 20.00, '2025-11-15 10:30:00', 1, 1, 1, 'completed'),
    ('Client B', '2025-11-15 11:00:00', 45, 30.00, '2025-11-15 11:45:00', 2, 2, 2, 'planned');


PRAGMA foreign_keys = ON;


INSERT INTO Employees (name, hire_date, salary_percentage)
VALUES 
    ('Mike Johnson', '2025-01-15', 22.0),
    ('Sarah Williams', '2025-02-01', 28.0),
    ('David Brown', '2025-03-01', 24.0),
    ('Emily Davis', '2025-04-01', 26.0);


INSERT INTO Appointments (
    customer_name,
    appointment_start,
    appointment_duration_minutes,
    appointment_price,
    appointment_end,
    bay_id,
    employee_id,
    service_detail_id,
    status
) VALUES

    ('Client C', '2025-11-10 09:00:00', 30, 20.00, '2025-11-10 09:30:00', 1, 1, 1, 'completed'),
    ('Client D', '2025-11-12 14:00:00', 45, 30.00, '2025-11-12 14:45:00', 2, 1, 2, 'completed'),
    ('Client E', '2025-11-14 10:00:00', 60, 50.00, '2025-11-14 11:00:00', 1, 1, 3, 'completed'),
    ('Client F', '2025-11-16 15:30:00', 30, 20.00, '2025-11-16 16:00:00', 2, 1, 1, 'completed'),
    ('Client P', '2025-12-01 08:00:00', 60, 50.00, '2025-12-01 09:00:00', 1, 1, 3, 'completed'),
    

    ('Client G', '2025-11-10 11:00:00', 45, 30.00, '2025-11-10 11:45:00', 1, 2, 2, 'completed'),
    ('Client H', '2025-11-12 16:00:00', 30, 20.00, '2025-11-12 16:30:00', 2, 2, 1, 'completed'),
    ('Client I', '2025-11-14 12:00:00', 60, 50.00, '2025-11-14 13:00:00', 1, 2, 3, 'completed'),
    ('Client J', '2025-11-18 09:00:00', 45, 30.00, '2025-11-18 09:45:00', 2, 2, 2, 'completed'),
    

    ('Client K', '2025-11-11 10:00:00', 30, 20.00, '2025-11-11 10:30:00', 1, 3, 1, 'completed'),
    ('Client L', '2025-11-13 14:00:00', 60, 50.00, '2025-11-13 15:00:00', 2, 3, 3, 'completed'),
    ('Client M', '2025-11-15 11:00:00', 45, 30.00, '2025-11-15 11:45:00', 1, 3, 2, 'completed'),
    

    ('Client N', '2025-11-11 13:00:00', 60, 50.00, '2025-11-11 14:00:00', 2, 4, 3, 'completed'),
    ('Client O', '2025-11-13 10:00:00', 30, 20.00, '2025-11-13 10:30:00', 1, 4, 1, 'completed'),
    ('Client Q', '2025-12-02 14:00:00', 45, 30.00, '2025-12-02 14:45:00', 2, 4, 2, 'completed'),
    

    ('Client R', '2025-11-17 10:00:00', 30, 20.00, '2025-11-17 10:30:00', 1, 5, 1, 'completed'),
    ('Client S', '2025-11-19 15:00:00', 60, 50.00, '2025-11-19 16:00:00', 2, 5, 3, 'completed'),
    

    ('Client T', '2025-11-20 09:00:00', 45, 30.00, '2025-11-20 09:45:00', 1, 6, 2, 'completed'),
    ('Client U', '2025-11-21 11:00:00', 30, 20.00, '2025-11-21 11:30:00', 2, 6, 1, 'completed');


INSERT INTO Appointments (
    customer_name,
    appointment_start,
    appointment_duration_minutes,
    appointment_price,
    appointment_end,
    bay_id,
    employee_id,
    service_detail_id,
    status
) VALUES
    ('Future Client 1', '2025-12-10 10:00:00', 30, 20.00, '2025-12-10 10:30:00', 1, 1, 1, 'planned'),
    ('Future Client 2', '2025-12-11 11:00:00', 60, 50.00, '2025-12-11 12:00:00', 2, 2, 3, 'cancelled');
