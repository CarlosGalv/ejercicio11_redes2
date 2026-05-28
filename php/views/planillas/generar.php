<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Generar Planilla - RRHH System</title>
    <link rel="stylesheet" href="css/estilo.css">
    <style>
        .form-container {
            max-width: 600px;
            margin: 0 auto;
        }
        .info-empleado {
            background: #e8f4fd;
            border-left: 4px solid #3498db;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: none;
        }
        .info-empleado h4 {
            margin: 0 0 10px 0;
            color: #2c3e50;
        }
        .info-empleado p {
            margin: 5px 0;
            font-size: 14px;
        }
        .fecha-ingreso {
            color: #e74c3c;
            font-weight: bold;
        }
        .periodo-invalido {
            background: #f8d7da;
            border-left: 4px solid #e74c3c;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            color: #721c24;
            display: none;
        }
        .periodo-valido {
            background: #d4edda;
            border-left: 4px solid #27ae60;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            color: #155724;
            display: none;
        }
        .advertencia {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 12px;
            border-radius: 8px;
            margin: 15px 0;
            font-size: 13px;
        }
        .advertencia ul {
            margin: 8px 0 0 20px;
        }
        .cargando {
            display: none;
            text-align: center;
            padding: 10px;
            color: #3498db;
        }
        .spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .btn-generar:disabled {
            background: #95a5a6;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <div class="container form-container">
        <h1>💰 Generar Nueva Planilla</h1>
        
        <div class="menu">
            <a href="index.php?controller=planilla&action=index" class="btn cancel">← Volver a Planillas</a>
        </div>
        
        <?php if(isset($error)): ?>
            <div class="alert error">
                ⚠️ <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <!-- Información del empleado seleccionado -->
        <div id="infoEmpleado" class="info-empleado">
            <h4>📋 Información del Empleado</h4>
            <p><strong>Nombre:</strong> <span id="emp_nombre"></span></p>
            <p><strong>Cargo:</strong> <span id="emp_cargo"></span></p>
            <p><strong>Tipo de salario:</strong> <span id="emp_tipo_salario"></span></p>
            <p><strong>Salario base:</strong> Q<span id="emp_salario"></span></p>
            <p><strong>📅 Fecha de ingreso:</strong> <span id="emp_fecha_ingreso" class="fecha-ingreso"></span></p>
        </div>
        
        <!-- Mensaje de periodo válido/inválido -->
        <div id="periodoValido" class="periodo-valido">
            ✅ Período válido para generar planilla
        </div>
        <div id="periodoInvalido" class="periodo-invalido">
            ⚠️ No se puede generar planilla para este período porque el empleado aún no había ingresado a la empresa.
        </div>
        
        <!-- Indicador de carga -->
        <div id="cargando" class="cargando">
            <div class="spinner"></div> Validando período...
        </div>
        
        <form id="formPlanilla" action="index.php?controller=planilla&action=generar" method="POST" class="form">
            <div class="form-group">
                <label>👤 Empleado:</label>
                <select name="empleado_id" id="empleado_id" required>
                    <option value="">-- Seleccione un empleado --</option>
                    <?php foreach($empleados as $emp): ?>
                    <option value="<?php echo $emp['id']; ?>" 
                            data-nombre="<?php echo htmlspecialchars($emp['nombre'] . ' ' . $emp['apellido']); ?>"
                            data-cargo="<?php echo htmlspecialchars($emp['cargo']); ?>"
                            data-tipo="<?php echo htmlspecialchars($emp['tipo_salario']); ?>"
                            data-salario="<?php echo number_format($emp['salario_base'], 2); ?>"
                            data-fecha-ingreso="<?php echo $emp['fecha_ingreso']; ?>"
                            <?php echo (isset($empleado_seleccionado) && $empleado_seleccionado == $emp['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($emp['nombre'] . ' ' . $emp['apellido'] . ' - ' . $emp['cargo']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>📅 Mes:</label>
                <select name="mes" id="mes" required>
                    <option value="">-- Seleccione --</option>
                    <option value="1">Enero</option>
                    <option value="2">Febrero</option>
                    <option value="3">Marzo</option>
                    <option value="4">Abril</option>
                    <option value="5">Mayo</option>
                    <option value="6">Junio</option>
                    <option value="7">Julio</option>
                    <option value="8">Agosto</option>
                    <option value="9">Septiembre</option>
                    <option value="10">Octubre</option>
                    <option value="11">Noviembre</option>
                    <option value="12">Diciembre</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>📅 Año:</label>
                <select name="ano" id="ano" required>
                    <option value="">-- Seleccione --</option>
                    <?php 
                    $ano_actual = date('Y');
                    for($i = 2020; $i <= $ano_actual + 1; $i++): 
                    ?>
                    <option value="<?php echo $i; ?>" <?php echo $i == $ano_actual ? 'selected' : ''; ?>>
                        <?php echo $i; ?>
                    </option>
                    <?php endfor; ?>
                </select>
            </div>
            
            <div class="advertencia">
                <strong>ℹ️ Información importante:</strong>
                <ul>
                    <li>La planilla se generará automáticamente con los datos de asistencia registrados</li>
                    <li>Las horas extras se pagan al <strong>150%</strong> del valor hora normal</li>
                    <li>Si la productividad del empleado es ≥ 80%, recibirá una <strong>bonificación del 5%</strong></li>
                    <li><strong class="fecha-ingreso">No se puede generar planilla para períodos anteriores a la fecha de ingreso del empleado</strong></li>
                </ul>
            </div>
            
            <button type="submit" id="btnGenerar" class="btn" style="width: 100%;">💰 Generar Planilla</button>
            <a href="index.php?controller=planilla&action=index" class="btn cancel" style="width: 100%; margin-top: 10px; text-align: center;">Cancelar</a>
        </form>
    </div>
    
    <script>
        // Datos de empleados precargados
        const empleadosData = {};
        <?php foreach($empleados as $emp): ?>
        empleadosData[<?php echo $emp['id']; ?>] = {
            nombre: '<?php echo addslashes($emp['nombre'] . ' ' . $emp['apellido']); ?>',
            cargo: '<?php echo addslashes($emp['cargo']); ?>',
            tipo_salario: '<?php echo addslashes($emp['tipo_salario']); ?>',
            salario_base: <?php echo $emp['salario_base']; ?>,
            fecha_ingreso: '<?php echo $emp['fecha_ingreso']; ?>',
            activo: <?php echo $emp['activo']; ?>
        };
        <?php endforeach; ?>
        
        // Elementos del DOM
        const selectEmpleado = document.getElementById('empleado_id');
        const selectMes = document.getElementById('mes');
        const selectAno = document.getElementById('ano');
        const btnGenerar = document.getElementById('btnGenerar');
        const infoEmpleadoDiv = document.getElementById('infoEmpleado');
        const periodoValidoDiv = document.getElementById('periodoValido');
        const periodoInvalidoDiv = document.getElementById('periodoInvalido');
        const cargandoDiv = document.getElementById('cargando');
        
        // Elementos de información
        const empNombre = document.getElementById('emp_nombre');
        const empCargo = document.getElementById('emp_cargo');
        const empTipoSalario = document.getElementById('emp_tipo_salario');
        const empSalario = document.getElementById('emp_salario');
        const empFechaIngreso = document.getElementById('emp_fecha_ingreso');
        
        // Función para validar si el período es válido
        function validarPeriodo() {
            const empleadoId = selectEmpleado.value;
            const mes = selectMes.value;
            const ano = selectAno.value;
            
            if(!empleadoId || !mes || !ano) {
                periodoValidoDiv.style.display = 'none';
                periodoInvalidoDiv.style.display = 'none';
                btnGenerar.disabled = false;
                btnGenerar.style.opacity = '1';
                return;
            }
            
            const empleado = empleadosData[empleadoId];
            if(!empleado) {
                periodoValidoDiv.style.display = 'none';
                periodoInvalidoDiv.style.display = 'none';
                return;
            }
            
            // Mostrar información del empleado
            infoEmpleadoDiv.style.display = 'block';
            empNombre.textContent = empleado.nombre;
            empCargo.textContent = empleado.cargo;
            empTipoSalario.textContent = empleado.tipo_salario;
            empSalario.textContent = empleado.salario_base.toFixed(2);
            
            // Formatear fecha de ingreso
            const fechaIngreso = new Date(empleado.fecha_ingreso);
            const fechaIngresoFormateada = fechaIngreso.toLocaleDateString('es-ES', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
            empFechaIngreso.textContent = fechaIngresoFormateada;
            
            // Validar período
            const fechaPeriodo = new Date(parseInt(ano), parseInt(mes) - 1, 1);
            const fechaUltimoDiaMes = new Date(parseInt(ano), parseInt(mes), 0);
            
            let esValido = true;
            let mensaje = '';
            
            // Verificar que el período no sea anterior a la fecha de ingreso
            if(fechaPeriodo < fechaIngreso) {
                esValido = false;
            }
            
            // Verificar que el empleado esté activo (o que el período sea anterior a su retiro)
            if(empleado.activo === 0 && empleado.fecha_retiro) {
                const fechaRetiro = new Date(empleado.fecha_retiro);
                if(fechaPeriodo > fechaRetiro) {
                    esValido = false;
                }
            }
            
            // Mostrar resultado
            if(esValido) {
                periodoValidoDiv.style.display = 'block';
                periodoInvalidoDiv.style.display = 'none';
                btnGenerar.disabled = false;
                btnGenerar.style.opacity = '1';
                btnGenerar.title = '';
            } else {
                periodoValidoDiv.style.display = 'none';
                periodoInvalidoDiv.style.display = 'block';
                btnGenerar.disabled = true;
                btnGenerar.style.opacity = '0.6';
                btnGenerar.title = 'No se puede generar planilla para este período porque es anterior a la fecha de ingreso del empleado';
            }
        }
        
        // Función para pre-seleccionar mes y año si viene por GET
        function preseleccionarPeriodo() {
            const urlParams = new URLSearchParams(window.location.search);
            const empleadoId = urlParams.get('empleado_id');
            if(empleadoId) {
                selectEmpleado.value = empleadoId;
            }
            validarPeriodo();
        }
        
        // Validar al cambiar cualquier campo
        selectEmpleado.addEventListener('change', validarPeriodo);
        selectMes.addEventListener('change', validarPeriodo);
        selectAno.addEventListener('change', validarPeriodo);
        
        // Validar en tiempo real mientras se escribe (para asegurar)
        setInterval(validarPeriodo, 500);
        
        // Al enviar el formulario, verificar nuevamente
        document.getElementById('formPlanilla').addEventListener('submit', function(e) {
            const empleadoId = selectEmpleado.value;
            const mes = selectMes.value;
            const ano = selectAno.value;
            
            if(!empleadoId || !mes || !ano) {
                e.preventDefault();
                alert('Por favor complete todos los campos');
                return;
            }
            
            const empleado = empleadosData[empleadoId];
            if(empleado) {
                const fechaPeriodo = new Date(parseInt(ano), parseInt(mes) - 1, 1);
                const fechaIngreso = new Date(empleado.fecha_ingreso);
                
                if(fechaPeriodo < fechaIngreso) {
                    e.preventDefault();
                    alert('⚠️ No se puede generar planilla para este período porque el empleado ingresó el ' + 
                          fechaIngreso.toLocaleDateString('es-ES'));
                    return;
                }
            }
            
            // Mostrar confirmación
            if(!confirm('¿Está seguro de generar esta planilla? Verifique que los datos de asistencia estén registrados.')) {
                e.preventDefault();
            }
        });
        
        // Preseleccionar si hay empleado_id en URL
        preseleccionarPeriodo();
    </script>
</body>
</html>