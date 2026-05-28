<?php
require_once 'php/config/conexion.php';

class ReporteModel {
    private $db;
    
    public function __construct() {
        $conexion = new Conexion();
        $this->db = $conexion->getConnection();
    }
    
    public function resumenGeneral() {
        $query = "SELECT 
                    COUNT(*) as total_empleados,
                    SUM(CASE WHEN activo = 1 THEN 1 ELSE 0 END) as activos,
                    SUM(CASE WHEN activo = 0 THEN 1 ELSE 0 END) as inactivos,
                    AVG(salario_base) as promedio_salario
                  FROM empleados";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function planillasPorMes($ano) {
        $query = "SELECT 
                    p.mes,
                    COUNT(p.id) as total_planillas,
                    SUM(p.total) as total_monto
                  FROM planillas p
                  WHERE p.ano = :ano
                  GROUP BY p.mes
                  ORDER BY p.mes";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':ano', $ano);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>