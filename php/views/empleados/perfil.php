<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Perfil - <?php echo $empleado['nombre'] . ' ' . $empleado['apellido']; ?></title>
    <link rel="stylesheet" href="css/estilo.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #e9ecef; padding: 20px; }
        .container { max-width: 1300px; margin: 0 auto; background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .perfil-header { background: #2c3e50; color: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        .perfil-header h1 { margin: 0 0 10px 0; border: none; color: white; }
        .perfil-header h2 { margin: 0; color: #ecf0f1; border: none; }
        .seccion { background: #f8f9fa; border: 1px solid #ddd; border-radius: 8px; padding: 15px; margin-bottom: 20px; }
        .seccion h3 { margin-top: 0; color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 8px; margin-bottom: 15px; }
        .grid-2 { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; }
        .grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; }
        .info-item { padding: 8px; border-bottom: 1px solid #eee; }
        .info-label { font-weight: bold; color: #555; display: inline-block; width: 140px; }
        .info-value { color: #333; }
        .estado-activo { color: #27ae60; font-weight: bold; background: #d4edda; padding: 3px 8px; border-radius: 4px; display: inline-block; }
        .estado-inactivo { color: #e74c3c; font-weight: bold; background: #f8d7da; padding: 3px 8px; border-radius: 4px; display: inline-block; }
        .card-calculo { background: white; border: 1px solid #ddd; border-radius: 8px; padding: 12px; margin-bottom: 10px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .card-calculo h4 { color: #2c3e50; margin-bottom: 10px; font-size: 14px; }
        .monto-principal { font-size: 18px; font-weight: bold; color: #27ae60; }
        .monto-detalle { font-size: 12px; color: #7f8c8d; }
        .resaltado { background: #e8f4fd; border-left: 3px solid #3498db; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        table th, table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        table th { background: #e9ecef; }
        .btn-small { padding: 5px 10px; font-size: 12px; display: inline-block; margin: 2px; }
        .acciones { margin-top: 20px; text-align: center; }
        .btn { display: inline-block; padding: 8px 16px; background: #3498db; color: white; text-decoration: none; border-radius: 4px; margin-right: 8px; }
        .btn-danger { background: #e74c3c; }
        .btn-success { background: #27ae60; }
        .btn-warning { background: #f39c12; }
        .total-resaltado { font-weight: bold; color: #27ae60; }
        .text-center { text-align: center; }
        .badge { display: inline-block; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; }
        .badge-alta { background: #d4edda; color: #155724; }
        .badge-media { background: #fff3cd; color: #856404; }
        .badge-baja { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
<div class="container">
    <!-- Encabezado del perfil -->
    <div class="perfil-header">
        <h1>📄 EXPEDIENTE LABORAL</h1>
        <h2><?php echo $empleado['nombre'] . ' ' . $empleado['apellido']; ?></h2>
        <p>Código: <?php echo $empleado['codigo']; ?> | Cédula: <?php echo $empleado['cedula']; ?></p>
    </div>
    
    <!-- SECCIÓN 1: INFORMACIÓN PERSONAL Y LABORAL -->
    <div class="seccion">
        <h3>📋 INFORMACIÓN PERSONAL Y LABORAL</h3>
        <div class="grid-2">
            <div class="info-item"><span class="info-label">Cargo:</span><span class="info-value"><?php echo $empleado['cargo']; ?></span></div>
            <div class="info-item"><span class="info-label">Tipo de salario:</span><span class="info-value"><?php echo $empleado['tipo_salario']; ?></span></div>
            <div class="info-item"><span class="info-label">Salario base:</span><span class="info-value">Q<?php echo number_format($empleado['salario_base'], 2); ?></span></div>
            <div class="info-item"><span class="info-label">Salario mínimo:</span><span class="info-value">Q<?php echo number_format($empleado['salario_minimo'], 2); ?></span></div>
            <div class="info-item"><span class="info-label">Bonificación fija:</span><span class="info-value">Q250.00</span></div>
            <div class="info-item"><span class="info-label">Fecha de ingreso:</span><span class="info-value"><?php echo $empleado['fecha_ingreso']; ?></span></div>
            <div class="info-item">
    <span class="info-label">Antigüedad:</span>
    <span class="info-value">
        <?php 
        echo $empleado['antiguedad_anos'] . ' ' . ($empleado['antiguedad_anos'] == 1 ? 'año' : 'años') . ', ';
        echo $empleado['antiguedad_meses'] . ' ' . ($empleado['antiguedad_meses'] == 1 ? 'mes' : 'meses') . ', ';
        echo $empleado['antiguedad_dias'] . ' ' . ($empleado['antiguedad_dias'] == 1 ? 'día' : 'días');
        ?>
        <br><small>(Total: <?php echo number_format($empleado['antiguedad_total_dias']); ?> días laborados)</small>
    </span>
</div>
            <div class="info-item"><span class="info-label">Estado actual:</span><span class="info-value"><?php if($empleado['activo']): ?><span class="estado-activo">✅ ACTIVO</span><?php else: ?><span class="estado-inactivo">❌ INACTIVO (Retiro: <?php echo $empleado['fecha_retiro']; ?>)</span><?php endif; ?></span></div>
        </div>
    </div>
    
    <!-- SECCIÓN 2: CÁLCULOS SALARIALES Y DESCUENTOS -->
    <div class="seccion">
        <h3>💰 CÁLCULOS SALARIALES</h3>
        <div class="grid-3">
            <div class="card-calculo">
                <h4>💵 Salario Base</h4>
                <div class="monto-principal">Q<?php echo number_format($empleado['salario_base'], 2); ?></div>
                <div class="monto-detalle">Mensual</div>
            </div>
            <div class="card-calculo">
                <h4>➕ Bonificación</h4>
                <div class="monto-principal">Q250.00</div>
                <div class="monto-detalle">Bonificación fija mensual</div>
            </div>
            <div class="card-calculo">
                <h4>➖ Descuento IGSS (4.83%)</h4>
                <div class="monto-principal" style="color: #e74c3c;">- Q<?php echo number_format($empleado['descuento_igss'], 2); ?></div>
                <div class="monto-detalle">Seguridad social</div>
            </div>
            <div class="card-calculo resaltado">
                <h4>💰 Salario Neto Mensual</h4>
                <div class="monto-principal" style="font-size: 22px;">Q<?php echo number_format($empleado['salario_neto'], 2); ?></div>
                <div class="monto-detalle">Base + Bonificación - IGSS</div>
            </div>
        </div>
    </div>
    
    <!-- SECCIÓN 3: PRESTACIONES LABORALES -->
    <div class="seccion">
        <h3>🎁 PRESTACIONES LABORALES</h3>
        <div class="grid-2">
            <div class="card-calculo">
                <h4>🎄 Aguinaldo (Diciembre)</h4>
                <?php if($empleado['aguinaldo']): ?>
                <div class="monto-principal">Q<?php echo number_format($empleado['aguinaldo']['aguinaldo'], 2); ?></div>
                <div class="monto-detalle">Basado en <?php echo floor($empleado['aguinaldo']['dias_trabajados']); ?> días trabajados</div>
                <div class="monto-detalle">Fórmula: (Salario mínimo / 365) × días trabajados</div>
                <?php else: ?>
                <div class="monto-principal">Q0.00</div>
                <div class="monto-detalle">No aplica aún</div>
                <?php endif; ?>
            </div>
            <div class="card-calculo">
                <h4>📅 Bono 14 (Junio)</h4>
                <?php if($empleado['bono14']): ?>
                <div class="monto-principal">Q<?php echo number_format($empleado['bono14']['bono14'], 2); ?></div>
                <div class="monto-detalle">Promedio salarial: Q<?php echo number_format($empleado['bono14']['salario_promedio'], 2); ?></div>
                <div class="monto-detalle">Período: 1 julio - 30 junio | <?php echo floor($empleado['bono14']['dias_laborados']); ?> días</div>
                <?php else: ?>
                <div class="monto-principal">Q0.00</div>
                <div class="monto-detalle">No aplica aún</div>
                <?php endif; ?>
            </div>
            <div class="card-calculo">
                <h4>🏖️ Vacaciones</h4>
                <?php if($empleado['vacaciones']): ?>
                <div class="monto-principal"><?php echo floor($empleado['vacaciones']['dias_vacaciones']); ?> días</div>
                <div class="monto-detalle">Monto: Q<?php echo number_format($empleado['vacaciones']['monto_vacaciones'], 2); ?></div>
                <div class="monto-detalle">15 días por año | Salario diario: Q<?php echo number_format($empleado['vacaciones']['salario_diario'], 2); ?></div>
                <?php else: ?>
                <div class="monto-principal">0 días</div>
                <div class="monto-detalle">No aplica aún</div>
                <?php endif; ?>
            </div>
            <div class="card-calculo">
                <h4>⚖️ Indemnización (Despido)</h4>
                <?php if($empleado['indemnizacion']): ?>
                <div class="monto-principal">Q<?php echo number_format($empleado['indemnizacion']['indemnizacion'], 2); ?></div>
                <div class="monto-detalle"><?php echo $empleado['indemnizacion']['anos_trabajados']; ?> años trabajados</div>
                <div class="monto-detalle">1 mes por año | Mínimo 3 meses si aplica</div>
                <?php else: ?>
                <div class="monto-principal">Q0.00</div>
                <div class="monto-detalle">Calculado al momento de despido</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- SECCIÓN 4: PRESTACIONES REGISTRADAS -->
    <div class="seccion">
        <h3>📋 PRESTACIONES REGISTRADAS</h3>
        <?php if(count($empleado['prestaciones']) > 0): ?>
        <table>
            <thead><tr><th>Tipo</th><th>Monto</th><th>Fecha Inicio</th><th>Fecha Fin</th><th>Estado</th></tr></thead>
            <tbody>
                <?php foreach($empleado['prestaciones'] as $pr): ?>
                <tr>
                    <td><?php echo $pr['tipo']; ?></td>
                    <td>Q<?php echo number_format($pr['monto'], 2); ?></td>
                    <td><?php echo $pr['fecha_inicio']; ?></td>
                    <td><?php echo $pr['fecha_fin'] ? $pr['fecha_fin'] : '-'; ?></td>
                    <td><?php echo $pr['estado']; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p>No hay prestaciones registradas.</p>
        <?php endif; ?>
    </div>
    
    <!-- SECCIÓN 5: INDICADORES DE PRODUCTIVIDAD -->
    <div class="seccion">
        <h3>📊 INDICADORES DE PRODUCTIVIDAD</h3>
        <?php if(count($empleado['indicadores']) > 0): ?>
        <table>
            <thead><tr><th>Periodo</th><th>Tareas Asignadas</th><th>Tareas Completadas</th><th>Ausencias</th><th>Productividad</th></tr></thead>
            <tbody>
                <?php foreach($empleado['indicadores'] as $i): ?>
                <tr>
                    <td><?php echo $i['mes'] . '/' . $i['ano']; ?></td>
                    <td><?php echo $i['tareas_asignadas']; ?></td>
                    <td><?php echo $i['tareas_completadas']; ?></td>
                    <td><?php echo $i['ausencias']; ?> días</td>
                    <td>
                        <?php 
                        $prod = $i['productividad'];
                        $clase = $prod >= 80 ? 'badge-alta' : ($prod >= 60 ? 'badge-media' : 'badge-baja');
                        ?>
                        <span class="badge <?php echo $clase; ?>"><?php echo number_format($prod, 1); ?>%</span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p>No hay indicadores registrados.</p>
        <?php endif; ?>
    </div>
    
    <!-- SECCIÓN 6: ASISTENCIA RECIENTE -->
    <div class="seccion">
        <h3>📅 ASISTENCIA RECIENTE</h3>
        <?php if(count($empleado['asistencia']) > 0): ?>
        <table>
            <thead><tr><th>Fecha</th><th>Horas</th><th>Estado</th></tr></thead>
            <tbody>
                <?php foreach($empleado['asistencia'] as $a): ?>
                <tr>
                    <td><?php echo $a['fecha']; ?></td>
                    <td><?php echo $a['horas']; ?> horas</td>
                    <td><?php echo $a['horas'] >= 8 ? '✓ Completo' : ($a['horas'] >= 4 ? '⚠ Media jornada' : '✗ Falta'); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p>No hay registros de asistencia.</p>
        <?php endif; ?>
    </div>
    
    <!-- SECCIÓN 7: HISTORIAL DE PLANILLAS -->
    <div class="seccion">
        <h3>💰 HISTORIAL DE PLANILLAS</h3>
        <?php if(count($empleado['planillas']) > 0): ?>
        <table>
            <thead><tr><th>Periodo</th><th>Salario Base</th><th>Horas Extras</th><th>Bonificación</th><th>Total</th></tr></thead>
            <tbody>
                <?php foreach($empleado['planillas'] as $p): ?>
                <tr>
                    <td><?php echo $p['mes'] . '/' . $p['ano']; ?></td>
                    <td>Q<?php echo number_format($p['salario_base'], 2); ?></td>
                    <td><?php echo $p['horas_extras']; ?> hrs</td>
                    <td>Q<?php echo number_format($p['bonificacion'], 2); ?></td>
                    <td class="total-resaltado">Q<?php echo number_format($p['total'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p>No hay planillas generadas.</p>
        <?php endif; ?>
    </div>
    
    <!-- BOTONES DE ACCIÓN -->
    <div class="acciones">
        <a href="index.php?controller=empleado&action=index" class="btn">← Volver a lista</a>
        <?php if($empleado['activo']): ?>
            <a href="index.php?controller=planilla&action=generar&empleado_id=<?php echo $empleado['id']; ?>" class="btn">💰 Generar Planilla</a>
            <a href="index.php?controller=prestacion&action=crear&empleado_id=<?php echo $empleado['id']; ?>" class="btn btn-success">🎁 Agregar Prestación</a>
            <a href="index.php?controller=indicador&action=crear&empleado_id=<?php echo $empleado['id']; ?>" class="btn btn-warning">📊 Agregar Indicador</a>
            <a href="index.php?controller=empleado&action=baja&id=<?php echo $empleado['id']; ?>" class="btn btn-danger">⚠ Dar de Baja</a>
        <?php endif; ?>
    </div>
</div>
</body>
</html>