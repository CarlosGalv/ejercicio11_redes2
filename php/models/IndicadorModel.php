<?php
require_once 'php/config/conexion.php';

class IndicadorModel {
    private $db;
    
    public function __construct() {
        $conexion = new Conexion();
        $this->db = $conexion->getConnection();
    }
    
    public function listarTodos() {
        // ✅ MODIFICADO: Solo mostrar indicadores desde la fecha de contratación
        $query = "SELECT i.*, e.nombre, e.apellido, e.fecha_ingreso
                  FROM indicadores i 
                  JOIN empleados e ON i.empleado_id = e.id 
                  WHERE STR_TO_DATE(CONCAT(i.ano, '-', i.mes, '-01'), '%Y-%m-%d') >= e.fecha_ingreso
                  ORDER BY i.ano DESC, i.mes DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function registrar($datos) {
        // ✅ Verificar que el indicador NO sea de fecha anterior a la contratación
        $empleado_id = isset($datos['empleado_id']) ? $datos['empleado_id'] : null;
        $mes = isset($datos['mes']) ? $datos['mes'] : null;
        $ano = isset($datos['ano']) ? $datos['ano'] : null;
        
        // Verificar fecha de contratación
        $queryCheck = "SELECT fecha_ingreso FROM empleados WHERE id = :id";
        $stmtCheck = $this->db->prepare($queryCheck);
        $stmtCheck->bindParam(':id', $empleado_id);
        $stmtCheck->execute();
        $empleado = $stmtCheck->fetch(PDO::FETCH_ASSOC);
        
        if($empleado) {
            $fechaIngreso = new DateTime($empleado['fecha_ingreso']);
            $fechaIndicador = new DateTime("$ano-$mes-01");
            
            // No permitir indicadores de fechas anteriores a la contratación
            if($fechaIndicador < $fechaIngreso) {
                return false;
            }
        }
        
        $tareas_completadas = isset($datos['tareas_completadas']) ? $datos['tareas_completadas'] : 0;
        $tareas_asignadas = isset($datos['tareas_asignadas']) ? $datos['tareas_asignadas'] : 0;
        $ausencias = isset($datos['ausencias']) ? $datos['ausencias'] : 0;
        
        $productividad = 0;
        if($tareas_asignadas > 0) {
            $productividad = ($tareas_completadas / $tareas_asignadas) * 100;
        }
        
        $query = "INSERT INTO indicadores (empleado_id, mes, ano, tareas_completadas, tareas_asignadas, ausencias, productividad) 
                  VALUES (:empleado_id, :mes, :ano, :tareas_completadas, :tareas_asignadas, :ausencias, :productividad)";
        $stmt = $this->db->prepare($query);
        
        $stmt->bindParam(':empleado_id', $empleado_id);
        $stmt->bindParam(':mes', $mes);
        $stmt->bindParam(':ano', $ano);
        $stmt->bindParam(':tareas_completadas', $tareas_completadas);
        $stmt->bindParam(':tareas_asignadas', $tareas_asignadas);
        $stmt->bindParam(':ausencias', $ausencias);
        $stmt->bindParam(':productividad', $productividad);
        
        return $stmt->execute();
    }
}
?>