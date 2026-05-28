<?php
require_once 'php/models/EmpleadoModel.php';

class EmpleadoController {
    private $model;
    
    public function __construct() {
        $this->model = new EmpleadoModel();
        session_start(); // Iniciar sesión para mensajes de error
    }
    
    public function index() {
        $empleados = $this->model->listarTodos();
        include 'php/views/empleados/lista.php';
    }
    
    public function alta() {
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $datos = [
                'codigo' => $_POST['codigo'],
                'cedula' => $_POST['cedula'],
                'nombre' => $_POST['nombre'],
                'apellido' => $_POST['apellido'],
                'cargo' => $_POST['cargo'],
                'tipo_salario' => $_POST['tipo_salario'],
                'salario_base' => $_POST['salario_base'],
                'salario_minimo' => 3791.20,
                'fecha_ingreso' => $_POST['fecha_ingreso']
            ];
            
            if($this->model->registrar($datos)) {
                header('Location: index.php?controller=empleado&action=index&msg=creado');
                exit();
            } else {
                $error = "Error al registrar el empleado";
                include 'php/views/empleados/alta.php';
            }
        } else {
            include 'php/views/empleados/alta.php';
        }
    }
    
    public function baja() {
        if(isset($_GET['id'])) {
            $empleado = $this->model->obtenerPorId($_GET['id']);
            
            // Verificar si ya está inactivo
            if($empleado['activo'] == 0) {
                $_SESSION['error_baja'] = "Este empleado ya está dado de baja desde: " . date('d/m/Y', strtotime($empleado['fecha_retiro']));
                header('Location: index.php?controller=empleado&action=index');
                exit();
            }
            
            if($_SERVER['REQUEST_METHOD'] == 'POST') {
                $fecha_baja = $_POST['fecha_retiro'];
                $tipo_baja = $_POST['tipo_baja'];
                
                // Verificar que la fecha de baja no sea anterior a la fecha de ingreso
                if($fecha_baja < $empleado['fecha_ingreso']) {
                    $error = "La fecha de retiro no puede ser anterior a la fecha de ingreso";
                    $liquidacion = $this->model->calcularLiquidacion($empleado['id'], date('Y-m-d'), 'renuncia');
                    include 'php/views/empleados/baja.php';
                    return;
                }
                
                if($this->model->darBaja($empleado['id'], $fecha_baja)) {
                    header('Location: index.php?controller=empleado&action=index&msg=baja');
                    exit();
                } else {
                    $error = "Error al dar de baja";
                    include 'php/views/empleados/baja.php';
                }
            } else {
                $fecha_actual = date('Y-m-d');
                $liquidacion = $this->model->calcularLiquidacion($empleado['id'], $fecha_actual, 'renuncia');
                include 'php/views/empleados/baja.php';
            }
        } else {
            header('Location: index.php?controller=empleado&action=index');
            exit();
        }
    }
    
    public function perfil() {
        if(isset($_GET['id'])) {
            $empleado = $this->model->getPerfilCompleto($_GET['id']);
            if($empleado) {
                include 'php/views/empleados/perfil.php';
            } else {
                header('Location: index.php?controller=empleado&action=index');
                exit();
            }
        } else {
            header('Location: index.php?controller=empleado&action=index');
            exit();
        }
    }
    
    public function eliminar() {
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['id'];
            $fecha_retiro = $_POST['fecha_retiro'];
            if($this->model->darBaja($id, $fecha_retiro)) {
                header('Location: index.php?controller=empleado&action=index&msg=baja');
                exit();
            }
        }
    }
    
    public function calcular_liquidacion() {
        if(isset($_GET['id']) && isset($_GET['fecha']) && isset($_GET['tipo'])) {
            $liquidacion = $this->model->calcularLiquidacion($_GET['id'], $_GET['fecha'], $_GET['tipo']);
            header('Content-Type: application/json');
            echo json_encode($liquidacion);
            exit();
        }
        exit();
    }
    
    public function listar_inactivos() {
        $empleados = $this->model->listarInactivos();
        include 'php/views/empleados/lista_inactivos.php';
    }
    
    public function reactivar() {
        if(isset($_GET['id'])) {
            if($this->model->reactivar($_GET['id'])) {
                header('Location: index.php?controller=empleado&action=index&msg=reactivado');
                exit();
            }
        }
        header('Location: index.php?controller=empleado&action=index');
        exit();
    }
}
?>