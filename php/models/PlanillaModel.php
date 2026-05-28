<?php
require_once 'php/config/conexion.php';

class PlanillaModel {
    private $db;
    
    public function __construct() {
        $conexion = new Conexion();
        $this->db = $conexion->getConnection();
    }
    
    /**
     * Listar todas las planillas con datos del empleado
     */
    public function listarTodas() {
        $query = "SELECT p.*, e.nombre, e.apellido, e.cargo, e.tipo_salario,
                         e.fecha_ingreso, e.activo, e.fecha_retiro
                  FROM planillas p 
                  JOIN empleados e ON p.empleado_id = e.id 
                  ORDER BY p.ano DESC, p.mes DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if($result === false || $result === null) {
            return [];
        }
        return $result;
    }
    
    /**
     * Obtener planilla por ID con datos completos del empleado
     */
    public function obtenerPorId($id) {
        $query = "SELECT p.*, e.nombre, e.apellido, e.cargo, e.tipo_salario,
                         e.salario_base as salario_empleado, e.cedula, e.codigo,
                         e.fecha_ingreso, e.activo, e.fecha_retiro,
                         TIMESTAMPDIFF(YEAR, e.fecha_ingreso, CURDATE()) as antiguedad
                  FROM planillas p 
                  JOIN empleados e ON p.empleado_id = e.id 
                  WHERE p.id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener planillas de un empleado específico - MODIFICADO con validación de fechas
     */
    public function listarPorEmpleado($empleado_id) {
        $empleadoModel = new EmpleadoModel();
        $empleado = $empleadoModel->obtenerPorId($empleado_id);
        
        if(!$empleado) {
            return [];
        }
        
        // Limitar planillas según fecha de ingreso y fecha de retiro
        $fechaIngreso = $empleado['fecha_ingreso'];
        $fechaFin = $empleado['activo'] == 1 ? date('Y-m-d') : $empleado['fecha_retiro'];
        
        $query = "SELECT p.*, e.nombre, e.apellido, e.cargo
                  FROM planillas p 
                  JOIN empleados e ON p.empleado_id = e.id 
                  WHERE p.empleado_id = :empleado_id
                    AND CONCAT(p.ano, '-', LPAD(p.mes, 2, '0'), '-01') >= :fecha_inicio
                    AND CONCAT(p.ano, '-', LPAD(p.mes, 2, '0'), '-01') <= :fecha_fin
                  ORDER BY p.ano DESC, p.mes DESC";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':empleado_id', $empleado_id);
        $stmt->bindParam(':fecha_inicio', $fechaIngreso);
        $stmt->bindParam(':fecha_fin', $fechaFin);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Generar planilla - MODIFICADO con validación de período
     */
    public function generar($empleado_id, $mes, $ano) {
        $empleadoModel = new EmpleadoModel();
        
        // Validar que el período sea válido para este empleado
        $validacion = $empleadoModel->validarPeriodoPlanilla($empleado_id, $mes, $ano);
        if(!$validacion['valido']) {
            $_SESSION['error_planilla'] = $validacion['mensaje'];
            return false;
        }
        
        try {
            $query = "CALL sp_generar_planilla(:empleado_id, :mes, :ano)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':empleado_id', $empleado_id);
            $stmt->bindParam(':mes', $mes);
            $stmt->bindParam(':ano', $ano);
            return $stmt->execute();
        } catch(PDOException $e) {
            return $this->generarPlanillaManualConValidacion($empleado_id, $mes, $ano);
        }
    }
    
    /**
     * Generar planilla manualmente con validación de fechas
     */
    private function generarPlanillaManualConValidacion($empleado_id, $mes, $ano) {
        $empleadoModel = new EmpleadoModel();
        $empleado = $empleadoModel->obtenerPorId($empleado_id);
        
        if(!$empleado) {
            return false;
        }
        
        // Verificar si el empleado ya estaba trabajando en ese período
        $fechaIngreso = $empleado['fecha_ingreso'];
        $primerDiaMes = $ano . '-' . str_pad($mes, 2, '0', STR_PAD_LEFT) . '-01';
        
        if($primerDiaMes < $fechaIngreso) {
            $_SESSION['error_planilla'] = "No se puede generar planilla porque el empleado ingresó después de este período.";
            return false;
        }
        
        // Si está inactivo, verificar que no sea después de su retiro
        if($empleado['activo'] == 0 && $empleado['fecha_retiro']) {
            $ultimoDiaMes = date('Y-m-t', strtotime($primerDiaMes));
            if($ultimoDiaMes > $empleado['fecha_retiro']) {
                $_SESSION['error_planilla'] = "No se puede generar planilla para este período porque el empleado ya no laboraba.";
                return false;
            }
        }
        
        // Calcular horas normales según tipo
        $horas_normales = 0;
        if($empleado['tipo_salario'] == 'Mensual') {
            $horas_normales = 160;
        } elseif($empleado['tipo_salario'] == 'Quincenal') {
            $horas_normales = 80;
        } else {
            $horas_normales = 40;
        }
        
        // Obtener total de horas trabajadas
        $queryHoras = "SELECT SUM(horas) as total_horas 
                       FROM asistencia 
                       WHERE empleado_id = :id 
                         AND MONTH(fecha) = :mes 
                         AND YEAR(fecha) = :ano";
        $stmt = $this->db->prepare($queryHoras);
        $stmt->bindParam(':id', $empleado_id);
        $stmt->bindParam(':mes', $mes);
        $stmt->bindParam(':ano', $ano);
        $stmt->execute();
        $asistencia = $stmt->fetch(PDO::FETCH_ASSOC);
        $total_horas = $asistencia['total_horas'] ?? 0;
        
        // Calcular horas extras
        $horas_extras = 0;
        $monto_extras = 0;
        if($total_horas > $horas_normales) {
            $horas_extras = $total_horas - $horas_normales;
            $valor_hora = $empleado['salario_base'] / $horas_normales;
            $monto_extras = $horas_extras * $valor_hora * 1.5;
        }
        
        // Calcular bonificación por productividad
        $queryProd = "SELECT productividad FROM indicadores 
                      WHERE empleado_id = :id AND mes = :mes AND ano = :ano";
        $stmt = $this->db->prepare($queryProd);
        $stmt->bindParam(':id', $empleado_id);
        $stmt->bindParam(':mes', $mes);
        $stmt->bindParam(':ano', $ano);
        $stmt->execute();
        $indicador = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $bonificacion = 0;
        if($indicador && $indicador['productividad'] >= 80) {
            $bonificacion = $empleado['salario_base'] * 0.05;
        }
        
        // Calcular total
        $total = $empleado['salario_base'] + $monto_extras + $bonificacion;
        
        // Verificar si ya existe planilla para este período
        $queryCheck = "SELECT id FROM planillas WHERE empleado_id = :id AND mes = :mes AND ano = :ano";
        $stmt = $this->db->prepare($queryCheck);
        $stmt->bindParam(':id', $empleado_id);
        $stmt->bindParam(':mes', $mes);
        $stmt->bindParam(':ano', $ano);
        $stmt->execute();
        
        if($stmt->fetch()) {
            $_SESSION['error_planilla'] = "Ya existe una planilla para este período";
            return false;
        }
        
        // Insertar planilla
        $queryInsert = "INSERT INTO planillas (empleado_id, mes, ano, tipo_periodo, 
                                                salario_base, horas_extras, 
                                                monto_horas_extras, bonificacion, total)
                        VALUES (:empleado_id, :mes, :ano, :tipo, :salario_base,
                                :horas_extras, :monto_extras, :bonificacion, :total)";
        $stmt = $this->db->prepare($queryInsert);
        $stmt->bindParam(':empleado_id', $empleado_id);
        $stmt->bindParam(':mes', $mes);
        $stmt->bindParam(':ano', $ano);
        $stmt->bindParam(':tipo', $empleado['tipo_salario']);
        $stmt->bindParam(':salario_base', $empleado['salario_base']);
        $stmt->bindParam(':horas_extras', $horas_extras);
        $stmt->bindParam(':monto_extras', $monto_extras);
        $stmt->bindParam(':bonificacion', $bonificacion);
        $stmt->bindParam(':total', $total);
        
        return $stmt->execute();
    }
    
    /**
     * Obtener resumen de planillas por año - MODIFICADO para filtrar por fechas válidas
     */
    public function getResumenPorAno($ano) {
        $query = "SELECT 
                    p.mes,
                    COUNT(p.id) as total_planillas,
                    SUM(p.total) as total_monto,
                    AVG(p.total) as promedio_monto
                  FROM planillas p
                  JOIN empleados e ON p.empleado_id = e.id
                  WHERE p.ano = :ano
                    AND CONCAT(p.ano, '-', LPAD(p.mes, 2, '0'), '-01') >= e.fecha_ingreso
                    AND (e.activo = 1 OR CONCAT(p.ano, '-', LPAD(p.mes, 2, '0'), '-01') <= e.fecha_retiro)
                  GROUP BY p.mes
                  ORDER BY p.mes";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':ano', $ano);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener total de planillas por tipo de periodo
     */
    public function getResumenPorTipo() {
        $query = "SELECT 
                    tipo_periodo,
                    COUNT(*) as cantidad,
                    SUM(total) as total_monto,
                    AVG(total) as promedio
                  FROM planillas
                  GROUP BY tipo_periodo";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>