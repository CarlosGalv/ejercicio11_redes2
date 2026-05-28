-- =============================================
-- SCRIPT COMPLETO - SISTEMA RRHH
-- =============================================

-- 1. Crear y usar la base de datos
CREATE DATABASE IF NOT EXISTS rrhh_sistema;
USE rrhh_sistema;

-- =============================================
-- 2. TABLA PRINCIPAL: empleados
-- =============================================
CREATE TABLE IF NOT EXISTS empleados (
    id INT PRIMARY KEY AUTO_INCREMENT,
    codigo VARCHAR(20) UNIQUE NOT NULL,
    nombre VARCHAR(50) NOT NULL,
    apellido VARCHAR(50) NOT NULL,
    cedula VARCHAR(20) UNIQUE NOT NULL,
    fecha_ingreso DATE NOT NULL,
    cargo VARCHAR(100) NOT NULL,
    salario_base DECIMAL(10,2) NOT NULL,
    tipo_salario ENUM('Mensual', 'Por Hora', 'Destajo') DEFAULT 'Mensual',
    salario_minimo DECIMAL(10,2) DEFAULT 3791.20,
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =============================================
-- 3. TABLA: historial_salarios
-- =============================================
CREATE TABLE IF NOT EXISTS historial_salarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    empleado_id INT NOT NULL,
    mes INT NOT NULL,
    ano INT NOT NULL,
    salario DECIMAL(10,2) NOT NULL,
    fecha_cambio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (empleado_id) REFERENCES empleados(id) ON DELETE CASCADE
);

-- =============================================
-- 4. TABLA: calculos_prestaciones
-- =============================================
CREATE TABLE IF NOT EXISTS calculos_prestaciones (
    id INT PRIMARY KEY AUTO_INCREMENT,
    empleado_id INT NOT NULL,
    tipo VARCHAR(50) NOT NULL,
    periodo VARCHAR(20) NOT NULL,
    monto DECIMAL(10,2) NOT NULL,
    fecha_calculo TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (empleado_id) REFERENCES empleados(id) ON DELETE CASCADE
);

-- =============================================
-- 5. TABLA: planillas
-- =============================================
CREATE TABLE IF NOT EXISTS planillas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    empleado_id INT NOT NULL,
    mes INT NOT NULL,
    ano INT NOT NULL,
    tipo_periodo ENUM('Quincenal', 'Mensual') DEFAULT 'Mensual',
    salario_base DECIMAL(10,2) NOT NULL,
    horas_extras INT DEFAULT 0,
    bonificacion DECIMAL(10,2) DEFAULT 0,
    total DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (empleado_id) REFERENCES empleados(id) ON DELETE CASCADE
);

-- =============================================
-- 6. TABLA: indicadores
-- =============================================
CREATE TABLE IF NOT EXISTS indicadores (
    id INT PRIMARY KEY AUTO_INCREMENT,
    empleado_id INT NOT NULL,
    mes INT NOT NULL,
    ano INT NOT NULL,
    tareas_asignadas INT DEFAULT 0,
    tareas_completadas INT DEFAULT 0,
    ausencias INT DEFAULT 0,
    productividad DECIMAL(5,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (empleado_id) REFERENCES empleados(id) ON DELETE CASCADE
);

-- =============================================
-- 7. TABLA: asistencia
-- =============================================
CREATE TABLE IF NOT EXISTS asistencia (
    id INT PRIMARY KEY AUTO_INCREMENT,
    empleado_id INT NOT NULL,
    fecha DATE NOT NULL,
    horas DECIMAL(5,2) DEFAULT 8,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (empleado_id) REFERENCES empleados(id) ON DELETE CASCADE
);

-- =============================================
-- 8. TABLA: prestaciones (préstamos)
-- =============================================
CREATE TABLE IF NOT EXISTS prestaciones (
    id INT PRIMARY KEY AUTO_INCREMENT,
    empleado_id INT NOT NULL,
    tipo VARCHAR(50) NOT NULL,
    monto DECIMAL(10,2) NOT NULL,
    saldo_pendiente DECIMAL(10,2) NOT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE,
    estado ENUM('Activo', 'Pagado', 'Pendiente') DEFAULT 'Activo',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (empleado_id) REFERENCES empleados(id) ON DELETE CASCADE
);

-- =============================================
-- 9. FUNCIONES
-- =============================================
DELIMITER //

CREATE FUNCTION fn_calcular_igss(p_salario DECIMAL(10,2)) 
RETURNS DECIMAL(10,2)
DETERMINISTIC
BEGIN
    RETURN p_salario * 0.0483;
END //

DELIMITER ;

-- =============================================
-- 10. PROCEDIMIENTOS ALMACENADOS
-- =============================================

-- Eliminar procedimientos si existen
DROP PROCEDURE IF EXISTS sp_calcular_aguinaldo;
DROP PROCEDURE IF EXISTS sp_calcular_bono14;
DROP PROCEDURE IF EXISTS sp_calcular_indemnizacion;
DROP PROCEDURE IF EXISTS sp_calcular_vacaciones;

DELIMITER //

CREATE PROCEDURE sp_calcular_aguinaldo(
    IN p_empleado_id INT,
    IN p_ano INT
)
BEGIN
    DECLARE v_fecha_ingreso DATE;
    DECLARE v_dias_trabajados INT;
    DECLARE v_aguinaldo DECIMAL(10,2);
    DECLARE v_salario_minimo DECIMAL(10,2);
    
    SELECT fecha_ingreso, salario_minimo INTO v_fecha_ingreso, v_salario_minimo 
    FROM empleados WHERE id = p_empleado_id;
    
    SET v_dias_trabajados = DATEDIFF(
        DATE(CONCAT(p_ano, '-12-31')),
        v_fecha_ingreso
    );
    
    IF v_dias_trabajados < 0 THEN
        SET v_dias_trabajados = 0;
    END IF;
    
    SET v_aguinaldo = (v_salario_minimo / 365) * v_dias_trabajados;
    
    INSERT INTO calculos_prestaciones (empleado_id, tipo, periodo, monto)
    VALUES (p_empleado_id, 'Aguinaldo', CONCAT(p_ano, '-12'), v_aguinaldo);
    
    SELECT v_aguinaldo as aguinaldo, v_dias_trabajados as dias_trabajados;
END //

CREATE PROCEDURE sp_calcular_bono14(
    IN p_empleado_id INT,
    IN p_ano INT
)
BEGIN
    DECLARE v_salario_promedio DECIMAL(10,2);
    DECLARE v_dias_laborados INT;
    DECLARE v_bono14 DECIMAL(10,2);
    DECLARE v_fecha_ingreso DATE;
    
    SELECT fecha_ingreso INTO v_fecha_ingreso 
    FROM empleados WHERE id = p_empleado_id;
    
    SELECT AVG(salario) INTO v_salario_promedio
    FROM historial_salarios
    WHERE empleado_id = p_empleado_id 
      AND (ano > p_ano - 1 OR (ano = p_ano - 1 AND mes >= 7))
      AND (ano < p_ano OR (ano = p_ano AND mes <= 6));
    
    IF v_salario_promedio IS NULL THEN
        SELECT salario_base INTO v_salario_promedio 
        FROM empleados WHERE id = p_empleado_id;
    END IF;
    
    SET v_dias_laborados = DATEDIFF(
        DATE(CONCAT(p_ano, '-06-30')),
        v_fecha_ingreso
    );
    
    IF v_dias_laborados < 0 THEN
        SET v_dias_laborados = 0;
    END IF;
    
    SET v_bono14 = (v_salario_promedio / 365) * v_dias_laborados;
    
    INSERT INTO calculos_prestaciones (empleado_id, tipo, periodo, monto)
    VALUES (p_empleado_id, 'Bono14', CONCAT(p_ano, '-06'), v_bono14);
    
    SELECT v_bono14 as bono14, v_dias_laborados as dias_laborados, v_salario_promedio as salario_promedio;
END //

CREATE PROCEDURE sp_calcular_indemnizacion(
    IN p_empleado_id INT,
    IN p_fecha_despido DATE
)
BEGIN
    DECLARE v_anos_trabajados INT;
    DECLARE v_salario DECIMAL(10,2);
    DECLARE v_indemnizacion DECIMAL(10,2);
    
    SELECT 
        TIMESTAMPDIFF(YEAR, fecha_ingreso, p_fecha_despido) as anos,
        salario_base
    INTO v_anos_trabajados, v_salario
    FROM empleados WHERE id = p_empleado_id;
    
    SET v_indemnizacion = v_salario * v_anos_trabajados;
    
    IF v_anos_trabajados > 3 AND v_indemnizacion < (v_salario * 3) THEN
        SET v_indemnizacion = v_salario * 3;
    END IF;
    
    SELECT v_indemnizacion as indemnizacion, v_anos_trabajados as anos_trabajados;
END //

CREATE PROCEDURE sp_calcular_vacaciones(
    IN p_empleado_id INT,
    IN p_fecha_corte DATE
)
BEGIN
    DECLARE v_anos_trabajados INT;
    DECLARE v_dias_trabajados INT;
    DECLARE v_dias_vacaciones DECIMAL(10,2);
    DECLARE v_salario_diario DECIMAL(10,2);
    DECLARE v_monto_vacaciones DECIMAL(10,2);
    DECLARE v_salario_minimo DECIMAL(10,2);
    
    SELECT 
        TIMESTAMPDIFF(YEAR, fecha_ingreso, p_fecha_corte) as anos,
        DATEDIFF(p_fecha_corte, fecha_ingreso) as dias,
        salario_minimo
    INTO v_anos_trabajados, v_dias_trabajados, v_salario_minimo
    FROM empleados WHERE id = p_empleado_id;
    
    SET v_dias_vacaciones = (v_anos_trabajados * 15);
    
    IF v_anos_trabajados = 0 THEN
        SET v_dias_vacaciones = (v_dias_trabajados / 365) * 15;
    END IF;
    
    SET v_salario_diario = v_salario_minimo / 30;
    SET v_monto_vacaciones = v_dias_vacaciones * v_salario_diario;
    
    SELECT 
        v_dias_vacaciones as dias_vacaciones, 
        v_monto_vacaciones as monto_vacaciones,
        v_salario_diario as salario_diario;
END //

DELIMITER ;

-- =============================================
-- 11. DATOS DE EJEMPLO
-- =============================================

INSERT INTO empleados (codigo, nombre, apellido, cedula, fecha_ingreso, cargo, salario_base) VALUES
('EMP001', 'Juan', 'Pérez', '1234567890123', '2023-01-15', 'Desarrollador Senior', 5000.00),
('EMP002', 'María', 'García', '9876543210987', '2022-06-20', 'Diseñadora UX/UI', 4500.00),
('EMP003', 'Carlos', 'López', '4567890123456', '2024-02-10', 'Administrador Sistemas', 4800.00)
ON DUPLICATE KEY UPDATE id=id;

-- =============================================
-- 12. VERIFICAR TABLAS CREADAS
-- =============================================
SHOW TABLES;

-- =============================================
-- FIN DEL SCRIPT
-- =============================================