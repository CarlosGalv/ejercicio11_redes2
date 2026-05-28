-- ============================================
-- SISTEMA DE GESTIÓN DE RRHH
-- Base de datos: rrhh_sistema
-- ============================================

-- Crear base de datos
CREATE DATABASE IF NOT EXISTS rrhh_sistema;
USE rrhh_sistema;

-- ============================================
-- TABLA: EMPLEADOS
-- ============================================
CREATE TABLE empleados (
    id INT PRIMARY KEY AUTO_INCREMENT,
    codigo VARCHAR(20) UNIQUE NOT NULL,
    cedula VARCHAR(20) UNIQUE NOT NULL,
    nombre VARCHAR(50) NOT NULL,
    apellido VARCHAR(50) NOT NULL,
    cargo VARCHAR(50) NOT NULL,
    tipo_salario ENUM('Mensual', 'Semanal', 'Quincenal') NOT NULL,
    salario_base DECIMAL(10,2) NOT NULL,
    fecha_ingreso DATE NOT NULL,
    fecha_retiro DATE NULL,
    activo BOOLEAN DEFAULT TRUE,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- TABLA: ASISTENCIA
-- ============================================
CREATE TABLE asistencia (
    id INT PRIMARY KEY AUTO_INCREMENT,
    empleado_id INT NOT NULL,
    fecha DATE NOT NULL,
    horas DECIMAL(5,2) NOT NULL,
    FOREIGN KEY (empleado_id) REFERENCES empleados(id) ON DELETE CASCADE
);

-- ============================================
-- TABLA: PLANILLAS
-- ============================================
CREATE TABLE planillas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    empleado_id INT NOT NULL,
    mes INT NOT NULL,
    ano INT NOT NULL,
    tipo_periodo ENUM('Mensual', 'Semanal', 'Quincenal') NOT NULL,
    salario_base DECIMAL(10,2) NOT NULL,
    horas_extras DECIMAL(5,2) DEFAULT 0,
    monto_horas_extras DECIMAL(10,2) DEFAULT 0,
    bonificacion DECIMAL(10,2) DEFAULT 0,
    total DECIMAL(10,2) NOT NULL,
    fecha_generacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (empleado_id) REFERENCES empleados(id) ON DELETE CASCADE
);

-- ============================================
-- TABLA: PRESTACIONES
-- ============================================
CREATE TABLE prestaciones (
    id INT PRIMARY KEY AUTO_INCREMENT,
    empleado_id INT NOT NULL,
    tipo VARCHAR(50) NOT NULL,
    monto DECIMAL(10,2) NOT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE,
    estado ENUM('Activo', 'Finalizado', 'Pendiente') DEFAULT 'Activo',
    FOREIGN KEY (empleado_id) REFERENCES empleados(id) ON DELETE CASCADE
);

-- ============================================
-- TABLA: INDICADORES
-- ============================================
CREATE TABLE indicadores (
    id INT PRIMARY KEY AUTO_INCREMENT,
    empleado_id INT NOT NULL,
    mes INT NOT NULL,
    ano INT NOT NULL,
    tareas_completadas INT DEFAULT 0,
    tareas_asignadas INT DEFAULT 0,
    ausencias INT DEFAULT 0,
    productividad DECIMAL(5,2) DEFAULT 0,
    FOREIGN KEY (empleado_id) REFERENCES empleados(id) ON DELETE CASCADE
);

-- ============================================
-- FUNCIÓN: Calcular antigüedad (en meses)
-- ============================================
DELIMITER //
CREATE FUNCTION fn_antiguedad(fecha_ingreso DATE) 
RETURNS INT
DETERMINISTIC
BEGIN
    RETURN TIMESTAMPDIFF(MONTH, fecha_ingreso, CURDATE());
END //
DELIMITER ;

-- ============================================
-- PROCEDIMIENTO: Generar planilla automática
-- ============================================
DELIMITER //
CREATE PROCEDURE sp_generar_planilla(
    IN p_empleado_id INT,
    IN p_mes INT,
    IN p_ano INT
)
BEGIN
    DECLARE v_salario DECIMAL(10,2);
    DECLARE v_tipo VARCHAR(20);
    DECLARE v_total_horas DECIMAL(5,2);
    DECLARE v_horas_extras DECIMAL(5,2);
    DECLARE v_monto_extras DECIMAL(10,2);
    DECLARE v_bonificacion DECIMAL(10,2);
    DECLARE v_total DECIMAL(10,2);
    DECLARE v_horas_normales INT;
    
    -- Obtener datos del empleado
    SELECT salario_base, tipo_salario INTO v_salario, v_tipo
    FROM empleados WHERE id = p_empleado_id AND activo = 1;
    
    -- Determinar horas normales según tipo de periodo
    IF v_tipo = 'Mensual' THEN
        SET v_horas_normales = 160;
    ELSEIF v_tipo = 'Quincenal' THEN
        SET v_horas_normales = 80;
    ELSE
        SET v_horas_normales = 40;
    END IF;
    
    -- Calcular total de horas trabajadas en el periodo
    SELECT IFNULL(SUM(horas), 0) INTO v_total_horas
    FROM asistencia
    WHERE empleado_id = p_empleado_id 
      AND MONTH(fecha) = p_mes 
      AND YEAR(fecha) = p_ano;
    
    -- Calcular horas extras (si excede las horas normales)
    IF v_total_horas > v_horas_normales THEN
        SET v_horas_extras = v_total_horas - v_horas_normales;
        SET v_monto_extras = (v_salario / v_horas_normales) * v_horas_extras * 1.5;
    ELSE
        SET v_horas_extras = 0;
        SET v_monto_extras = 0;
    END IF;
    
    -- Bonificación del 5% si productividad > 80%
    SELECT IFNULL(productividad, 0) INTO v_bonificacion
    FROM indicadores
    WHERE empleado_id = p_empleado_id AND mes = p_mes AND ano = p_ano;
    
    IF v_bonificacion >= 80 THEN
        SET v_bonificacion = v_salario * 0.05;
    ELSE
        SET v_bonificacion = 0;
    END IF;
    
    -- Calcular total
    SET v_total = v_salario + v_monto_extras + v_bonificacion;
    
    -- Insertar planilla
    INSERT INTO planillas (
        empleado_id, mes, ano, tipo_periodo, salario_base,
        horas_extras, monto_horas_extras, bonificacion, total
    ) VALUES (
        p_empleado_id, p_mes, p_ano, v_tipo, v_salario,
        v_horas_extras, v_monto_extras, v_bonificacion, v_total
    );
    
END //
DELIMITER ;

-- ============================================
-- TRIGGER: Al dar de baja un empleado
-- ============================================
DELIMITER //
CREATE TRIGGER tr_baja_empleado
BEFORE UPDATE ON empleados
FOR EACH ROW
BEGIN
    IF NEW.fecha_retiro IS NOT NULL AND OLD.fecha_retiro IS NULL THEN
        SET NEW.activo = FALSE;
    END IF;
END //
DELIMITER ;

-- ============================================
-- DATOS DE PRUEBA
-- ============================================
INSERT INTO empleados (codigo, cedula, nombre, apellido, cargo, tipo_salario, salario_base, fecha_ingreso) VALUES
('EMP001', '101-1234567', 'Juan Carlos', 'Pérez Gómez', 'Gerente General', 'Mensual', 12000.00, '2022-01-10'),
('EMP002', '102-2345678', 'María Elena', 'García López', 'Coordinadora RRHH', 'Mensual', 8500.00, '2022-03-15'),
('EMP003', '103-3456789', 'Carlos Andrés', 'Rodríguez Mora', 'Asistente', 'Mensual', 5500.00, '2023-01-20'),
('EMP004', '104-4567890', 'Ana Lucía', 'Martínez Solano', 'Operaria', 'Semanal', 2500.00, '2023-06-01'),
('EMP005', '105-5678901', 'Pedro José', 'Ramírez Soto', 'Supervisor', 'Quincenal', 4000.00, '2023-08-15');

-- Asistencias de prueba para enero 2024
INSERT INTO asistencia (empleado_id, fecha, horas) VALUES
(1, '2024-01-02', 8), (1, '2024-01-03', 8), (1, '2024-01-04', 9), (1, '2024-01-05', 8), (1, '2024-01-08', 8),
(2, '2024-01-02', 8), (2, '2024-01-03', 8), (2, '2024-01-04', 8), (2, '2024-01-05', 8), (2, '2024-01-08', 8);

-- Indicadores de prueba
INSERT INTO indicadores (empleado_id, mes, ano, tareas_completadas, tareas_asignadas, ausencias, productividad) VALUES
(1, 1, 2024, 18, 20, 0, 90.00),
(2, 1, 2024, 16, 20, 1, 80.00);

-- Prestaciones de prueba
INSERT INTO prestaciones (empleado_id, tipo, monto, fecha_inicio, estado) VALUES
(1, 'Aguinaldo', 12000.00, '2024-12-01', 'Pendiente'),
(2, 'Vacaciones', 4250.00, '2024-03-01', 'Activo');