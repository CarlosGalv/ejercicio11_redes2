<?php
require_once 'php/config/conexion.php';

class EmpleadoModel {
    private $db;
    
    public function __construct() {
        $conexion = new Conexion();
        $this->db = $conexion->getConnection();
    }
    
    // ============================================
    // MÉTODOS BÁSICOS CRUD (MODIFICADOS)
    // ============================================
    
    /**
     * Listar todos los empleados con antigüedad detallada
     */
    public function listarTodos() {
        // IMPORTANTE: Mostrar también los inactivos pero diferenciados
        $query = "SELECT * FROM empleados ORDER BY activo DESC, id DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $empleados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Calcular antigüedad detallada para cada empleado
        foreach ($empleados as &$emp) {
            $antiguedad = $this->calcularAntiguedadDetallada($emp['fecha_ingreso']);
            $emp['antiguedad_anos'] = $antiguedad['anos'];
            $emp['antiguedad_meses'] = $antiguedad['meses'];
            $emp['antiguedad_dias'] = $antiguedad['dias'];
            $emp['antiguedad_texto'] = $antiguedad['texto'];
            $emp['antiguedad_total_dias'] = $antiguedad['total_dias'];
        }
        
        return $empleados;
    }
    
    /**
     * Obtener empleado por ID
     */
    public function obtenerPorId($id) {
        $query = "SELECT * FROM empleados WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $empleado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($empleado) {
            $antiguedad = $this->calcularAntiguedadDetallada($empleado['fecha_ingreso']);
            $empleado['antiguedad_anos'] = $antiguedad['anos'];
            $empleado['antiguedad_meses'] = $antiguedad['meses'];
            $empleado['antiguedad_dias'] = $antiguedad['dias'];
            $empleado['antiguedad_texto'] = $antiguedad['texto'];
            $empleado['antiguedad_total_dias'] = $antiguedad['total_dias'];
        }
        
        return $empleado;
    }
    
    /**
     * Registrar nuevo empleado
     */
    public function registrar($datos) {
        $query = "INSERT INTO empleados (codigo, cedula, nombre, apellido, cargo, 
                                         tipo_salario, salario_base, salario_minimo, 
                                         fecha_ingreso, activo) 
                  VALUES (:codigo, :cedula, :nombre, :apellido, :cargo, 
                          :tipo_salario, :salario_base, :salario_minimo, 
                          :fecha_ingreso, 1)";  // Siempre activo = 1 al registrar
        $stmt = $this->db->prepare($query);
        
        return $stmt->execute([
            ':codigo' => $datos['codigo'],
            ':cedula' => $datos['cedula'],
            ':nombre' => $datos['nombre'],
            ':apellido' => $datos['apellido'],
            ':cargo' => $datos['cargo'],
            ':tipo_salario' => $datos['tipo_salario'],
            ':salario_base' => $datos['salario_base'],
            ':salario_minimo' => isset($datos['salario_minimo']) ? $datos['salario_minimo'] : 3791.20,
            ':fecha_ingreso' => $datos['fecha_ingreso']
        ]);
    }
    
    /**
     * Dar de baja a un empleado - CORREGIDO: Ahora actualiza el campo 'activo'
     */
    public function darBaja($id, $fecha_retiro) {
        // CORRECCIÓN: Actualizar activo = 0 Y guardar fecha_retiro
        $query = "UPDATE empleados 
                  SET activo = 0, 
                      fecha_retiro = :fecha_retiro,
                      updated_at = NOW()
                  WHERE id = :id AND activo = 1";  // Solo si está activo
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':fecha_retiro', $fecha_retiro);
        $stmt->bindParam(':id', $id);
        
        if($stmt->execute()) {
            // Registrar la baja en historial de prestaciones
            $this->registrarBajaHistorial($id, $fecha_retiro);
            return true;
        }
        return false;
    }
    
    /**
     * Registrar la baja en el historial
     */
    private function registrarBajaHistorial($empleado_id, $fecha_baja) {
        $query = "INSERT INTO calculos_prestaciones (empleado_id, tipo, periodo, monto) 
                  VALUES (:empleado_id, 'BAJA', :fecha, 0)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':empleado_id', $empleado_id);
        $stmt->bindParam(':fecha', $fecha_baja);
        return $stmt->execute();
    }
    
    /**
     * REACTIVAR empleado (opcional - útil si fue dado de baja por error)
     */
    public function reactivar($id) {
        $query = "UPDATE empleados 
                  SET activo = 1, 
                      fecha_retiro = NULL,
                      updated_at = NOW()
                  WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
    
    /**
     * Listar solo empleados activos
     */
    public function listarActivos() {
        $query = "SELECT * FROM empleados 
                  WHERE activo = 1 
                  ORDER BY nombre";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Listar solo empleados inactivos (dados de baja)
     */
    public function listarInactivos() {
        $query = "SELECT * FROM empleados 
                  WHERE activo = 0 
                  ORDER BY fecha_retiro DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // ============================================
    // NUEVAS FUNCIONES PARA VALIDACIÓN DE FECHAS
    // ============================================
    
    /**
     * Validar si una fecha está dentro del período laboral del empleado
     * @param int $empleado_id ID del empleado
     * @param string $fecha_consulta Fecha a validar (Y-m-d)
     * @return bool True si la fecha es válida para consulta
     */
    public function validarFechaConsulta($empleado_id, $fecha_consulta) {
        $empleado = $this->obtenerPorId($empleado_id);
        if(!$empleado) {
            return false;
        }
        
        $fechaIngreso = strtotime($empleado['fecha_ingreso']);
        $fechaConsulta = strtotime($fecha_consulta);
        $fechaActual = strtotime(date('Y-m-d'));
        
        // No puede consultar antes de su fecha de ingreso
        if($fechaConsulta < $fechaIngreso) {
            return false;
        }
        
        // Si está activo, no puede consultar después de hoy
        if($empleado['activo'] == 1 && $fechaConsulta > $fechaActual) {
            return false;
        }
        
        // Si está inactivo, no puede consultar después de su fecha de retiro
        if($empleado['activo'] == 0 && $empleado['fecha_retiro']) {
            $fechaRetiro = strtotime($empleado['fecha_retiro']);
            if($fechaConsulta > $fechaRetiro) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Obtener rango de fechas válidas para consultas de un empleado
     * @param int $empleado_id ID del empleado
     * @return array ['fecha_inicio' => 'Y-m-d', 'fecha_fin' => 'Y-m-d']
     */
    public function getRangoFechasValido($empleado_id) {
        $empleado = $this->obtenerPorId($empleado_id);
        if(!$empleado) {
            return null;
        }
        
        $rango = [
            'fecha_inicio' => $empleado['fecha_ingreso'],
            'fecha_fin' => $empleado['activo'] == 1 ? date('Y-m-d') : $empleado['fecha_retiro']
        ];
        
        return $rango;
    }
    
    /**
     * Verificar si se puede generar planilla para un período específico
     * @param int $empleado_id ID del empleado
     * @param int $mes Mes a validar
     * @param int $ano Año a validar
     * @return array ['valido' => bool, 'mensaje' => string]
     */
    public function validarPeriodoPlanilla($empleado_id, $mes, $ano) {
        $empleado = $this->obtenerPorId($empleado_id);
        if(!$empleado) {
            return ['valido' => false, 'mensaje' => 'Empleado no encontrado'];
        }
        
        $fechaPeriodo = $ano . '-' . str_pad($mes, 2, '0', STR_PAD_LEFT) . '-01';
        $ultimoDiaMes = date('Y-m-t', strtotime($fechaPeriodo));
        $fechaIngreso = $empleado['fecha_ingreso'];
        
        // El período debe ser DESPUÉS de la fecha de ingreso
        if($ultimoDiaMes < $fechaIngreso) {
            return [
                'valido' => false, 
                'mensaje' => "No se puede generar planilla para {$mes}/{$ano} porque el empleado ingresó el " . date('d/m/Y', strtotime($fechaIngreso))
            ];
        }
        
        // Si está inactivo, verificar que el período sea antes de su retiro
        if($empleado['activo'] == 0 && $empleado['fecha_retiro']) {
            if($fechaPeriodo > $empleado['fecha_retiro']) {
                return [
                    'valido' => false,
                    'mensaje' => "No se puede generar planilla para {$mes}/{$ano} porque el empleado ya no laboraba (retiro: " . date('d/m/Y', strtotime($empleado['fecha_retiro'])) . ")"
                ];
            }
        }
        
        return ['valido' => true, 'mensaje' => 'Período válido'];
    }
    
    // ============================================
    // CÁLCULOS DE PRESTACIONES LABORALES (sin cambios)
    // ============================================
    
    /**
     * Calcular descuento IGSS (4.83% del salario)
     */
    public function calcularIGSS($salario) {
        return $salario * 0.0483;
    }
    
    /**
     * Calcular antigüedad detallada (años, meses, días)
     */
    public function calcularAntiguedadDetallada($fecha_ingreso) {
        $fecha_ing = new DateTime($fecha_ingreso);
        $fecha_actual = new DateTime(date('Y-m-d'));
        $diferencia = $fecha_ing->diff($fecha_actual);
        
        return [
            'anos' => $diferencia->y,
            'meses' => $diferencia->m,
            'dias' => $diferencia->d,
            'total_dias' => $diferencia->days,
            'texto' => $this->formatearAntiguedad($diferencia->y, $diferencia->m, $diferencia->d)
        ];
    }
    
    /**
     * Formatear antigüedad en texto legible
     */
    private function formatearAntiguedad($anos, $meses, $dias) {
        $partes = [];
        
        if ($anos > 0) {
            $partes[] = $anos . ' ' . ($anos == 1 ? 'año' : 'años');
        }
        if ($meses > 0) {
            $partes[] = $meses . ' ' . ($meses == 1 ? 'mes' : 'meses');
        }
        if ($dias > 0 && $anos == 0 && $meses == 0) {
            $partes[] = $dias . ' ' . ($dias == 1 ? 'día' : 'días');
        }
        
        return empty($partes) ? 'Recién ingresado' : implode(', ', $partes);
    }
    
    // ============================================
    // MÉTODOS PARA LIQUIDACIÓN DE PRESTACIONES (sin cambios)
    // ============================================
    
    /**
     * Calcular liquidación por renuncia o despido
     */
    public function calcularLiquidacion($empleado_id, $fecha_baja, $tipo_baja = 'renuncia') {
        $empleado = $this->obtenerPorId($empleado_id);
        if(!$empleado) {
            return null;
        }
        
        $fecha_ingreso = new DateTime($empleado['fecha_ingreso']);
        $fecha_baja_obj = new DateTime($fecha_baja);
        $anos_trabajados = $fecha_ingreso->diff($fecha_baja_obj)->y;
        $meses_trabajados = $fecha_ingreso->diff($fecha_baja_obj)->m;
        $dias_trabajados_periodo = $fecha_ingreso->diff($fecha_baja_obj)->days;
        
        $salario_base = $empleado['salario_base'];
        $salario_minimo = $empleado['salario_minimo'];
        
        // 1. Salario de días trabajados del mes
        $salario_dias = $this->calcularSalarioDiasTrabajados($empleado, $fecha_baja_obj);
        
        // 2. Aguinaldo proporcional
        $aguinaldo = $this->calcularAguinaldoProporcional($empleado, $fecha_baja_obj);
        
        // 3. Bono 14 proporcional
        $bono14 = $this->calcularBono14Proporcional($empleado, $fecha_baja_obj);
        
        // 4. Vacaciones proporcionales
        $vacaciones = $this->calcularVacacionesProporcional($empleado, $fecha_baja_obj);
        
        // 5. Indemnización (solo para despido)
        $indemnizacion = 0;
        if($tipo_baja == 'despido') {
            $indemnizacion = $this->calcularIndemnizacionDespido($empleado, $anos_trabajados, $salario_base);
        }
        
        $total_liquidacion = $salario_dias['monto'] + $aguinaldo['monto'] + $bono14['monto'] + $vacaciones['monto'] + $indemnizacion;
        
        return [
            'empleado' => $empleado,
            'salario_base_usado' => $salario_base,
            'salario_minimo_usado' => $salario_minimo,
            'fecha_baja' => $fecha_baja,
            'tipo_baja' => $tipo_baja,
            'anos_trabajados' => $anos_trabajados,
            'meses_trabajados' => $meses_trabajados,
            'dias_trabajados_total' => $dias_trabajados_periodo,
            'salario_dias_trabajados' => $salario_dias,
            'aguinaldo' => $aguinaldo,
            'bono14' => $bono14,
            'vacaciones' => $vacaciones,
            'indemnizacion' => $indemnizacion,
            'total_liquidacion' => $total_liquidacion
        ];
    }
    
    /**
     * Calcular aguinaldo proporcional por renuncia
     */
    private function calcularAguinaldoProporcional($empleado, $fecha_baja) {
        $salario_minimo = $empleado['salario_minimo'];
        $ano_baja = (int)$fecha_baja->format('Y');
        
        $inicio_periodo = new DateTime($ano_baja . '-12-01');
        if($fecha_baja < $inicio_periodo) {
            $inicio_periodo = new DateTime(($ano_baja - 1) . '-12-01');
        }
        
        $dias_trabajados = 0;
        if($fecha_baja > $inicio_periodo) {
            $dias_trabajados = $inicio_periodo->diff($fecha_baja)->days;
        }
        
        $aguinaldo_proporcional = ($salario_minimo / 365) * $dias_trabajados;
        
        return [
            'monto' => $aguinaldo_proporcional,
            'dias_trabajados' => $dias_trabajados,
            'periodo_inicio' => $inicio_periodo->format('d/m/Y'),
            'periodo_fin' => $fecha_baja->format('d/m/Y'),
            'es_completo' => false
        ];
    }
    
    /**
     * Calcular Bono 14 proporcional por renuncia
     */
    private function calcularBono14Proporcional($empleado, $fecha_baja) {
        $salario_base = $empleado['salario_base'];
        $ano_baja = (int)$fecha_baja->format('Y');
        
        $inicio_periodo = new DateTime($ano_baja . '-07-01');
        if($fecha_baja < $inicio_periodo) {
            $inicio_periodo = new DateTime(($ano_baja - 1) . '-07-01');
        }
        
        $dias_trabajados = 0;
        if($fecha_baja > $inicio_periodo) {
            $dias_trabajados = $inicio_periodo->diff($fecha_baja)->days;
        }
        
        $bono14_proporcional = ($salario_base / 365) * $dias_trabajados;
        
        return [
            'monto' => $bono14_proporcional,
            'dias_trabajados' => $dias_trabajados,
            'periodo_inicio' => $inicio_periodo->format('d/m/Y'),
            'periodo_fin' => $fecha_baja->format('d/m/Y'),
            'es_completo' => false
        ];
    }
    
    /**
     * Calcular vacaciones proporcionales
     */
    private function calcularVacacionesProporcional($empleado, $fecha_baja) {
        $salario_minimo = $empleado['salario_minimo'];
        $fecha_ingreso = new DateTime($empleado['fecha_ingreso']);
        
        $anos_completos = $fecha_ingreso->diff($fecha_baja)->y;
        $dias_trabajados_ultimo_ano = $fecha_ingreso->diff($fecha_baja)->days;
        
        $dias_vacaciones = $anos_completos * 15;
        
        if($anos_completos == 0 && $dias_trabajados_ultimo_ano > 0) {
            $dias_vacaciones = ($dias_trabajados_ultimo_ano / 365) * 15;
        }
        
        $salario_diario = $salario_minimo / 30;
        $monto_vacaciones = $dias_vacaciones * $salario_diario;
        
        return [
            'monto' => $monto_vacaciones,
            'dias' => $dias_vacaciones,
            'anos_completos' => $anos_completos,
            'salario_diario' => $salario_diario
        ];
    }
    
    /**
     * Calcular indemnización por despido injustificado
     */
    private function calcularIndemnizacionDespido($empleado, $anos_trabajados, $salario_base) {
        $indemnizacion = $salario_base * $anos_trabajados;
        
        if($anos_trabajados > 3 && $indemnizacion < ($salario_base * 3)) {
            $indemnizacion = $salario_base * 3;
        }
        
        return $indemnizacion;
    }
    
    /**
     * Calcular salario por días trabajados en el mes de la baja
     */
    private function calcularSalarioDiasTrabajados($empleado, $fecha_baja) {
        $salario_base = $empleado['salario_base'];
        $dias_en_mes = (int)$fecha_baja->format('t');
        $dia_baja = (int)$fecha_baja->format('d');
        
        $salario_diario = $salario_base / $dias_en_mes;
        $salario_dias_trabajados = $salario_diario * $dia_baja;
        
        $bonificacion_diaria = 250 / $dias_en_mes;
        $bonificacion_proporcional = $bonificacion_diaria * $dia_baja;
        
        return [
            'monto' => $salario_dias_trabajados + $bonificacion_proporcional,
            'salario_base' => $salario_base,
            'dias_trabajados' => $dia_baja,
            'salario_diario' => $salario_diario,
            'bonificacion_proporcional' => $bonificacion_proporcional
        ];
    }
    
    // ============================================
    // PERFIL COMPLETO DEL EMPLEADO (MODIFICADO)
    // ============================================
    
    /**
     * Obtener perfil completo de un empleado con todos sus datos
     */
    public function getPerfilCompleto($id) {
        $empleado = $this->obtenerPorId($id);
        
        if(!$empleado) {
            return null;
        }
        
        $empleado['descuento_igss'] = $this->calcularIGSS($empleado['salario_base']);
        $empleado['salario_neto'] = $empleado['salario_base'] - $empleado['descuento_igss'] + 250;
        
        // Solo calcular prestaciones si el empleado está activo
        if($empleado['activo'] == 1) {
            $empleado['aguinaldo'] = $this->calcularAguinaldoManual($id);
            $empleado['bono14'] = $this->calcularBono14Manual($id);
        } else {
            $empleado['aguinaldo'] = ['aguinaldo' => 0, 'dias_trabajados' => 0];
            $empleado['bono14'] = ['bono14' => 0, 'dias_laborados' => 0, 'salario_promedio' => 0];
        }
        
        $empleado['indemnizacion'] = $this->calcularIndemnizacionManual($id, date('Y-m-d'));
        $empleado['vacaciones'] = $this->calcularVacacionesManual($id, date('Y-m-d'));
        
        // Obtener planillas (solo hasta su fecha de retiro si está inactivo)
        $fecha_limite = $empleado['activo'] == 1 ? date('Y-m-d') : $empleado['fecha_retiro'];
        $query = "SELECT p.*, p.salario_base as salario_base_planilla, p.total as total_planilla
                  FROM planillas p 
                  WHERE p.empleado_id = :id 
                    AND CONCAT(p.ano, '-', LPAD(p.mes, 2, '0'), '-01') <= :fecha_limite
                  ORDER BY p.ano DESC, p.mes DESC 
                  LIMIT 6";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':fecha_limite', $fecha_limite);
        $stmt->execute();
        $empleado['planillas'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Obtener prestaciones registradas
        $query = "SELECT * FROM prestaciones 
                  WHERE empleado_id = :id 
                  ORDER BY fecha_inicio DESC";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $empleado['prestaciones'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Obtener indicadores de productividad
        $query = "SELECT * FROM indicadores 
                  WHERE empleado_id = :id 
                    AND CONCAT(ano, '-', LPAD(mes, 2, '0'), '-01') <= :fecha_limite
                  ORDER BY ano DESC, mes DESC 
                  LIMIT 6";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':fecha_limite', $fecha_limite);
        $stmt->execute();
        $empleado['indicadores'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Obtener asistencia reciente
        $query = "SELECT * FROM asistencia 
                  WHERE empleado_id = :id 
                    AND fecha <= :fecha_limite
                  ORDER BY fecha DESC 
                  LIMIT 10";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':fecha_limite', $fecha_limite);
        $stmt->execute();
        $empleado['asistencia'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $empleado;
    }
    
    /**
     * Cálculo manual de aguinaldo
     */
    private function calcularAguinaldoManual($empleado_id) {
        $empleado = $this->obtenerPorId($empleado_id);
        if (!$empleado) {
            return ['aguinaldo' => 0, 'dias_trabajados' => 0];
        }
        
        $fechaIngreso = new DateTime($empleado['fecha_ingreso']);
        $finAnio = new DateTime(date('Y') . '-12-31');
        
        if ($fechaIngreso > $finAnio) {
            return ['aguinaldo' => 0, 'dias_trabajados' => 0];
        }
        
        $diasTrabajados = $fechaIngreso->diff($finAnio)->days;
        $salarioMinimo = $empleado['salario_minimo'];
        $aguinaldo = ($salarioMinimo / 365) * $diasTrabajados;
        
        return [
            'aguinaldo' => $aguinaldo,
            'dias_trabajados' => $diasTrabajados
        ];
    }
    
    /**
     * Cálculo manual de Bono 14
     */
    private function calcularBono14Manual($empleado_id) {
        $empleado = $this->obtenerPorId($empleado_id);
        if (!$empleado) {
            return ['bono14' => 0, 'dias_laborados' => 0, 'salario_promedio' => 0];
        }
        
        $fechaIngreso = new DateTime($empleado['fecha_ingreso']);
        $finPeriodo = new DateTime(date('Y') . '-06-30');
        
        if ($fechaIngreso > $finPeriodo) {
            return ['bono14' => 0, 'dias_laborados' => 0, 'salario_promedio' => 0];
        }
        
        $diasLaborados = $fechaIngreso->diff($finPeriodo)->days;
        $salarioPromedio = $empleado['salario_base'];
        $bono14 = ($salarioPromedio / 365) * $diasLaborados;
        
        return [
            'bono14' => $bono14,
            'dias_laborados' => $diasLaborados,
            'salario_promedio' => $salarioPromedio
        ];
    }
    
    /**
     * Cálculo manual de indemnización
     */
    private function calcularIndemnizacionManual($empleado_id, $fecha_despido) {
        $empleado = $this->obtenerPorId($empleado_id);
        if (!$empleado) {
            return ['indemnizacion' => 0, 'anos_trabajados' => 0];
        }
        
        $fechaIngreso = new DateTime($empleado['fecha_ingreso']);
        $fechaFin = new DateTime($fecha_despido);
        $anosTrabajados = $fechaIngreso->diff($fechaFin)->y;
        $salarioBase = $empleado['salario_base'];
        
        $indemnizacion = $salarioBase * $anosTrabajados;
        
        if ($anosTrabajados > 3 && $indemnizacion < ($salarioBase * 3)) {
            $indemnizacion = $salarioBase * 3;
        }
        
        return [
            'indemnizacion' => $indemnizacion,
            'anos_trabajados' => $anosTrabajados
        ];
    }
    
    /**
     * Cálculo manual de vacaciones
     */
    private function calcularVacacionesManual($empleado_id, $fecha_corte) {
        $empleado = $this->obtenerPorId($empleado_id);
        if (!$empleado) {
            return ['dias_vacaciones' => 0, 'monto_vacaciones' => 0, 'salario_diario' => 0];
        }
        
        $fechaIngreso = new DateTime($empleado['fecha_ingreso']);
        $fechaCorte = new DateTime($fecha_corte);
        $anosTrabajados = $fechaIngreso->diff($fechaCorte)->y;
        $diasTrabajados = $fechaIngreso->diff($fechaCorte)->days;
        $salarioMinimo = $empleado['salario_minimo'];
        
        $diasVacaciones = $anosTrabajados * 15;
        
        if ($anosTrabajados == 0 && $diasTrabajados > 0) {
            $diasVacaciones = ($diasTrabajados / 365) * 15;
        }
        
        $salarioDiario = $salarioMinimo / 30;
        $montoVacaciones = $diasVacaciones * $salarioDiario;
        
        return [
            'dias_vacaciones' => $diasVacaciones,
            'monto_vacaciones' => $montoVacaciones,
            'salario_diario' => $salarioDiario
        ];
    }
    
    // ============================================
    // MÉTODOS ADICIONALES PARA REPORTES
    // ============================================
    
    public function getResumenGeneral() {
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
    
    public function buscar($termino) {
        $termino = "%$termino%";
        $query = "SELECT * FROM empleados 
                  WHERE nombre LIKE :termino 
                     OR apellido LIKE :termino 
                     OR cedula LIKE :termino 
                     OR codigo LIKE :termino
                  ORDER BY nombre";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':termino', $termino);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>