<?php
require_once 'php/models/PlanillaModel.php';
require_once 'php/models/EmpleadoModel.php';

class PlanillaController {
    private $model;
    private $empleadoModel;
    
    public function __construct() {
        $this->model = new PlanillaModel();
        $this->empleadoModel = new EmpleadoModel();
        session_start(); // Iniciar sesión para mensajes de error
    }
    
    /**
     * Listar todas las planillas
     */
    public function index() {
        $planillas = $this->model->listarTodas();
        
        if(!is_array($planillas)) {
            $planillas = [];
        }
        
        include 'php/views/planillas/lista.php';
    }
    
    /**
     * Ver detalle de una planilla específica
     */
    public function ver() {
        if(isset($_GET['id'])) {
            $planilla = $this->model->obtenerPorId($_GET['id']);
            if($planilla) {
                $empleado = $this->empleadoModel->obtenerPorId($planilla['empleado_id']);
                include 'php/views/planillas/detalle.php';
            } else {
                header('Location: index.php?controller=planilla&action=index');
                exit();
            }
        } else {
            header('Location: index.php?controller=planilla&action=index');
            exit();
        }
    }
    
    /**
     * Formulario para generar planilla - MODIFICADO con validación de fechas
     */
    public function generar() {
        // Mostrar error si existe
        if(isset($_SESSION['error_planilla'])) {
            $error = $_SESSION['error_planilla'];
            unset($_SESSION['error_planilla']);
        }
        
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $empleado_id = $_POST['empleado_id'];
            $mes = $_POST['mes'];
            $ano = $_POST['ano'];
            
            // Verificar que el período no sea anterior al ingreso del empleado
            $empleado = $this->empleadoModel->obtenerPorId($empleado_id);
            if($empleado) {
                $fechaPeriodo = $ano . '-' . str_pad($mes, 2, '0', STR_PAD_LEFT) . '-01';
                $fechaIngreso = $empleado['fecha_ingreso'];
                
                if($fechaPeriodo < $fechaIngreso) {
                    $error = "No se puede generar planilla para {$mes}/{$ano} porque el empleado ingresó el " . date('d/m/Y', strtotime($fechaIngreso));
                    $empleados = $this->empleadoModel->listarActivos();
                    include 'php/views/planillas/generar.php';
                    return;
                }
            }
            
            if($this->model->generar($empleado_id, $mes, $ano)) {
                header('Location: index.php?controller=planilla&action=index&msg=generada');
                exit();
            } else {
                // Si no se generó, mostrar el error
                if(!isset($error)) {
                    $error = "Error al generar planilla. Verifique que el empleado tenga asistencias registradas.";
                }
                $empleados = $this->empleadoModel->listarActivos();
                include 'php/views/planillas/generar.php';
            }
        } else {
            $empleado_seleccionado = isset($_GET['empleado_id']) ? $_GET['empleado_id'] : null;
            $empleados = $this->empleadoModel->listarActivos();
            include 'php/views/planillas/generar.php';
        }
    }
    
    /**
     * Ver planillas de un empleado específico - MODIFICADO
     */
    public function empleado() {
        if(isset($_GET['id'])) {
            $empleado = $this->empleadoModel->obtenerPorId($_GET['id']);
            if($empleado) {
                $planillas = $this->model->listarPorEmpleado($_GET['id']);
                include 'php/views/planillas/por_empleado.php';
            } else {
                header('Location: index.php?controller=planilla&action=index');
                exit();
            }
        } else {
            header('Location: index.php?controller=planilla&action=index');
            exit();
        }
    }
    
    /**
     * Reporte de planillas por año
     */
    public function reporte() {
        $ano_actual = date('Y');
        $ano_seleccionado = isset($_GET['ano']) ? $_GET['ano'] : $ano_actual;
        
        $resumen = $this->model->getResumenPorAno($ano_seleccionado);
        $resumen_tipo = $this->model->getResumenPorTipo();
        
        include 'php/views/planillas/reporte.php';
    }
}
?>