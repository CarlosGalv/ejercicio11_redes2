<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión RRHH - Empleados</title>
    <link rel="stylesheet" href="css/estilo.css">
    <style>
        .estado-activo {
            color: #27ae60;
            font-weight: bold;
            background: #d4edda;
            padding: 4px 10px;
            border-radius: 20px;
            display: inline-block;
            font-size: 12px;
        }
        .estado-inactivo {
            color: #e74c3c;
            font-weight: bold;
            background: #f8d7da;
            padding: 4px 10px;
            border-radius: 20px;
            display: inline-block;
            font-size: 12px;
        }
        .btn-pequeno {
            display: inline-block;
            padding: 4px 8px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 12px;
            margin: 2px;
        }
        .btn-baja {
            background: #e74c3c;
        }
        .btn-reactivar {
            background: #27ae60;
        }
        .btn-perfil {
            background: #3498db;
        }
        .badge-antiguedad {
            background: #f39c12;
            color: white;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 10px;
            margin-left: 5px;
        }
        .badge-nuevo {
            background: #27ae60;
            color: white;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 10px;
        }
        .badge-veterano {
            background: #8e44ad;
            color: white;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 10px;
        }
        .filtros {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }
        .filtros input, .filtros select {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .filtros button {
            padding: 8px 15px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .total-empleados {
            background: #2c3e50;
            color: white;
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: inline-block;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
            vertical-align: middle;
        }
        table th {
            background: #f8f9fa;
            font-weight: bold;
        }
        table tr:hover {
            background: #f5f5f5;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🏢 Sistema de Gestión de RRHH</h1>
        
        <div class="menu">
            <a href="index.php?controller=empleado&action=alta" class="btn">+ Alta Empleado</a>
            <a href="index.php?controller=empleado&action=listar_inactivos" class="btn" style="background:#e74c3c;">📋 Ver Inactivos</a>
            <a href="index.php?controller=planilla&action=index" class="btn">💰 Planillas</a>
            <a href="index.php?controller=prestacion&action=index" class="btn">🎁 Prestaciones</a>
            <a href="index.php?controller=indicador&action=index" class="btn">📊 Indicadores</a>
            <a href="index.php?controller=reporte&action=index" class="btn">📈 Reportes</a>
        </div>
        
        <?php if(isset($_GET['msg'])): ?>
            <div class="alert success">
                <?php if($_GET['msg'] == 'creado'): ?>
                    ✓ Empleado registrado correctamente
                <?php elseif($_GET['msg'] == 'baja'): ?>
                    ✓ Empleado dado de baja correctamente
                <?php elseif($_GET['msg'] == 'reactivado'): ?>
                    ✓ Empleado reactivado correctamente
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <?php if(isset($_SESSION['error_baja'])): ?>
            <div class="alert error">
                ⚠️ <?php echo $_SESSION['error_baja']; unset($_SESSION['error_baja']); ?>
            </div>
        <?php endif; ?>
        
        <h2>📋 Listado de Empleados Activos</h2>
        
        <!-- Filtros de búsqueda -->
        <div class="filtros">
            <input type="text" id="buscar" placeholder="🔍 Buscar por nombre, cédula o código..." style="flex: 1;">
            <select id="filtro_cargo">
                <option value="">Todos los cargos</option>
                <?php 
                $cargos = array_unique(array_column($empleados, 'cargo'));
                foreach($cargos as $cargo): 
                    if($cargo):
                ?>
                <option value="<?php echo htmlspecialchars($cargo); ?>"><?php echo htmlspecialchars($cargo); ?></option>
                <?php 
                    endif;
                endforeach; 
                ?>
            </select>
            <button onclick="filtrarTabla()">Filtrar</button>
            <button onclick="limpiarFiltros()">Limpiar</button>
        </div>
        
        <div class="total-empleados">
            📊 Total empleados activos: <strong id="total-empleados"><?php echo count(array_filter($empleados, function($e) { return $e['activo'] == 1; })); ?></strong>
        </div>
        
        <?php if(isset($empleados) && is_array($empleados) && count($empleados) > 0): ?>
        <table id="tabla-empleados" border="1" cellpadding="10" cellspacing="0">
            <thead style="background:#f2f2f2;">
                <tr>
                    <th>Código</th>
                    <th>Nombre</th>
                    <th>Cargo</th>
                    <th>Tipo Salario</th>
                    <th>Salario Base</th>
                    <th>Fecha Ingreso</th>
                    <th>Antigüedad</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </thead>
            <tbody>
                <?php foreach($empleados as $emp): ?>
                <?php 
                // Solo mostrar activos en esta tabla
                if($emp['activo'] != 1) continue;
                
                // Calcular antigüedad
                $anos = isset($emp['antiguedad_anos']) ? $emp['antiguedad_anos'] : 0;
                $meses = isset($emp['antiguedad_meses']) ? $emp['antiguedad_meses'] : 0;
                $dias = isset($emp['antiguedad_dias']) ? $emp['antiguedad_dias'] : 0;
                $total_dias = isset($emp['antiguedad_total_dias']) ? $emp['antiguedad_total_dias'] : 0;
                
                $antiguedad_texto = '';
                if($anos > 0) {
                    $antiguedad_texto = $anos . ' ' . ($anos == 1 ? 'año' : 'años');
                    if($meses > 0) {
                        $antiguedad_texto .= ' ' . $meses . ' ' . ($meses == 1 ? 'mes' : 'meses');
                    }
                } elseif($meses > 0) {
                    $antiguedad_texto = $meses . ' ' . ($meses == 1 ? 'mes' : 'meses');
                    if($dias > 0) {
                        $antiguedad_texto .= ' ' . $dias . ' ' . ($dias == 1 ? 'día' : 'días');
                    }
                } else {
                    $antiguedad_texto = $dias . ' ' . ($dias == 1 ? 'día' : 'días');
                }
                ?>
                <tr class="fila-empleado" data-cargo="<?php echo strtolower(htmlspecialchars($emp['cargo'])); ?>" data-nombre="<?php echo strtolower(htmlspecialchars($emp['nombre'] . ' ' . $emp['apellido'])); ?>" data-cedula="<?php echo htmlspecialchars($emp['cedula']); ?>" data-codigo="<?php echo htmlspecialchars($emp['codigo']); ?>">
                    <td><strong><?php echo htmlspecialchars($emp['codigo']); ?></strong></td>
                    <td>
                        <?php echo htmlspecialchars($emp['nombre'] . ' ' . $emp['apellido']); ?>
                        <br><small style="color:#666;">📄 <?php echo htmlspecialchars($emp['cedula']); ?></small>
                    </td>
                    <td><?php echo htmlspecialchars($emp['cargo']); ?></td>
                    <td><?php echo htmlspecialchars($emp['tipo_salario']); ?></td>
                    <td style="color:#27ae60; font-weight:bold;">Q<?php echo number_format($emp['salario_base'], 2); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($emp['fecha_ingreso'])); ?></td>
                    <td>
                        <?php echo $antiguedad_texto; ?>
                        <br><small>📅 <?php echo number_format($total_dias); ?> días totales</small>
                        <?php if($anos >= 5): ?>
                            <br><span class="badge-veterano">🏆 +5 años</span>
                        <?php elseif($anos == 0 && $meses < 3): ?>
                            <br><span class="badge-nuevo">🆕 Nuevo</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="estado-activo">✅ Activo</span>
                    </td>
                    <td>
                        <a href="index.php?controller=empleado&action=perfil&id=<?php echo $emp['id']; ?>" class="btn-pequeno btn-perfil">👤 Ver Perfil</a>
                        <a href="index.php?controller=planilla&action=generar&empleado_id=<?php echo $emp['id']; ?>" class="btn-pequeno" style="background:#27ae60;">💰 Planilla</a>
                        <a href="index.php?controller=empleado&action=baja&id=<?php echo $emp['id']; ?>" class="btn-pequeno btn-baja" onclick="return confirm('⚠️ ¿Está seguro de dar de baja a este empleado? Esta acción cambiará su estado a INACTIVO.')">📄 Dar de Baja</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div style="background:#d1ecf1; padding:15px; border-radius:4px;">
            📌 No hay empleados activos registrados. Haz clic en "+ Alta Empleado" para comenzar.
        </div>
        <?php endif; ?>
        
        <!-- Información de empleados inactivos (resumen) -->
        <?php 
        $inactivos = array_filter($empleados, function($e) { return $e['activo'] == 0; });
        if(count($inactivos) > 0): 
        ?>
        <div style="margin-top: 30px; padding: 15px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #e74c3c;">
            <h3 style="margin: 0 0 10px 0; color: #e74c3c;">📋 Empleados Inactivos (<?php echo count($inactivos); ?>)</h3>
            <p style="margin: 0;">
                Hay <?php echo count($inactivos); ?> empleados que han sido dados de baja. 
                <a href="index.php?controller=empleado&action=listar_inactivos" style="color: #3498db;">Ver lista completa →</a>
            </p>
        </div>
        <?php endif; ?>
    </div>
    
    <script>
        function filtrarTabla() {
            const buscar = document.getElementById('buscar').value.toLowerCase();
            const filtroCargo = document.getElementById('filtro_cargo').value.toLowerCase();
            const filas = document.querySelectorAll('.fila-empleado');
            let visibleCount = 0;
            
            filas.forEach(fila => {
                const nombre = fila.getAttribute('data-nombre') || '';
                const cedula = fila.getAttribute('data-cedula') || '';
                const codigo = fila.getAttribute('data-codigo') || '';
                const cargo = fila.getAttribute('data-cargo') || '';
                
                const coincideBusqueda = buscar === '' || 
                    nombre.includes(buscar) || 
                    cedula.includes(buscar) || 
                    codigo.includes(buscar);
                
                const coincideCargo = filtroCargo === '' || cargo === filtroCargo;
                
                if(coincideBusqueda && coincideCargo) {
                    fila.style.display = '';
                    visibleCount++;
                } else {
                    fila.style.display = 'none';
                }
            });
            
            document.getElementById('total-empleados').innerText = visibleCount;
        }
        
        function limpiarFiltros() {
            document.getElementById('buscar').value = '';
            document.getElementById('filtro_cargo').value = '';
            filtrarTabla();
        }
        
        // Evento para búsqueda en tiempo real
        document.getElementById('buscar').addEventListener('keyup', filtrarTabla);
        document.getElementById('filtro_cargo').addEventListener('change', filtrarTabla);
    </script>
    <script src="js/script.js"></script>
</body>
</html>