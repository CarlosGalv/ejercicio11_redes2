<?php
require_once 'php/models/ReporteModel.php';

class ReporteController {
    private $model;
    
    public function __construct() {
        $this->model = new ReporteModel();
    }
    
    public function index() {
        $resumen = $this->model->resumenGeneral();
        $planillas_2024 = $this->model->planillasPorMes(2024);
        include 'php/views/reportes/index.php';
    }
}
?>