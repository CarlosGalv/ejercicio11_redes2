<?php
require_once 'php/models/IndicadorModel.php';
require_once 'php/models/EmpleadoModel.php';

class IndicadorController {
    private $model;
    private $empleadoModel;
    
    public function __construct() {
        $this->model = new IndicadorModel();
        $this->empleadoModel = new EmpleadoModel();
    }
    
    public function index() {
        $indicadores = $this->model->listarTodos();
        include 'php/views/indicadores/index.php';
    }
    
  public function crear() {
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $datos = [
            'empleado_id' => $_POST['empleado_id'],
            'mes' => $_POST['mes'],
            'ano' => $_POST['ano'],
            'tareas_completadas' => $_POST['tareas_completadas'],
            'tareas_asignadas' => $_POST['tareas_asignadas'],
            'ausencias' => $_POST['ausencias']
        ];
        
        if($this->model->registrar($datos)) {
            header('Location: index.php?controller=indicador&action=index&msg=creado');
        } else {
            $error = "Error al registrar el indicador";
            $empleados = $this->empleadoModel->listarActivos();
            include 'php/views/indicadores/crear.php';
        }
    } else {
        $empleados = $this->empleadoModel->listarActivos();
        include 'php/views/indicadores/crear.php';
    }
}
}
?>