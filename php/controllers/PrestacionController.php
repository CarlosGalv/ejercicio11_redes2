<?php
require_once 'php/models/PrestacionModel.php';
require_once 'php/models/EmpleadoModel.php';

class PrestacionController {
    private $model;
    private $empleadoModel;
    
    public function __construct() {
        $this->model = new PrestacionModel();
        $this->empleadoModel = new EmpleadoModel();
    }
    
    public function index() {
        $prestaciones = $this->model->listarTodas();
        include 'php/views/prestaciones/index.php';
    }
    
    public function crear() {
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $datos = [
                ':empleado_id' => $_POST['empleado_id'],
                ':tipo' => $_POST['tipo'],
                ':monto' => $_POST['monto'],
                ':fecha_inicio' => $_POST['fecha_inicio'],
                ':estado' => $_POST['estado']
            ];
            
            if($this->model->registrar($datos)) {
                header('Location: index.php?controller=prestacion&action=index&msg=creada');
            }
        } else {
            $empleados = $this->empleadoModel->listarActivos();
            include 'php/views/prestaciones/crear.php';
        }
    }
}
?>