<?php
require_once 'php/config/conexion.php';

class PrestacionModel {
    private $db;
    
    public function __construct() {
        $conexion = new Conexion();
        $this->db = $conexion->getConnection();
    }
    
    public function listarTodas() {
        $query = "SELECT pr.*, e.nombre, e.apellido 
                  FROM prestaciones pr 
                  JOIN empleados e ON pr.empleado_id = e.id 
                  ORDER BY pr.id DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function registrar($datos) {
        $query = "INSERT INTO prestaciones (empleado_id, tipo, monto, fecha_inicio, estado) 
                  VALUES (:empleado_id, :tipo, :monto, :fecha_inicio, :estado)";
        $stmt = $this->db->prepare($query);
        return $stmt->execute($datos);
    }
}
?>