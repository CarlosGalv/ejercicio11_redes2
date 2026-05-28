<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Planillas - RRHH System</title>
    <link rel="stylesheet" href="css/estilo.css">
    <style>
        .reporte-container {
            max-width: 1000px;
            margin: 0 auto;
        }
        .header-reporte {
            background: linear-gradient(135deg, #2c3e50, #3498db);
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        .filtro-ano {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            justify-content: center;
            gap: 10px;
            align-items: flex-end;
        }
        .card-resumen {
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .card-resumen h3 {
            margin-top: 0;
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 8px;
            margin-bottom: 15px;
        }
        .grid-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }
        .stat-card .numero {
            font-size: 28px;
            font-weight: bold;
        }
        .stat-card .label {
            font-size: 12px;
            opacity: 0.9;
        }
        .stat-card.total {
            background: linear-gradient(135deg, #27ae60, #2ecc71);
        }
        .stat-card.promedio {
            background: linear-gradient(135deg, #f39c12, #e67e22);
        }
        .tabla-reporte {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .tabla-reporte th, .tabla-reporte td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }
        .tabla-reporte th {
            background: #2c3e50;
            color: white;
        }
        .monto {
            font-weight: bold;
            color: #27ae60;
        }
        .btn-descargar {
            background: #27ae60;
            margin-top: 20px;
        }
        .grafico-barras {
            margin: 20px 0;
        }
        .barra {
            background: #3498db;
            height: 30px;
            border-radius: 4px;
            margin: 5px 0;
            transition: width 0.3s;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding-right: 10px;
            color: white;
            font-size: 12px;
        }
        .mes-label {
            display: inline-block;
            width: 60px;
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="container reporte-container">
    <div class="header-reporte">
        <h1>📊 REPORTE DE PLANILLAS</h1>
        <p>Análisis de pagos de nómina</p>
    </div>
    
    <!-- Filtro por año -->
    <div class="filtro-ano">
        <form method="GET" action="index.php">
            <input type="hidden" name="controller" value="planilla">
            <input type="hidden" name="action" value="reporte">
            <label for="ano">Seleccionar año:</label>
            <select name="ano" id="ano" onchange="this.form.submit()">
                <?php for($i = 2010; $i <= date('Y'); $i++): ?>
                <option value="<?php echo $i; ?>" <?php echo ($ano_seleccionado == $i) ? 'selected' : ''; ?>>
                    <?php echo $i; ?>
                </option>
                <?php endfor; ?>
            </select>
        </form>
    </div>
    
    <!-- Resumen por tipo de periodo -->
    <div class="card-resumen">
        <h3>📈 Resumen por Tipo de Periodo</h3>
        <div class="grid-stats">
            <?php foreach($resumen_tipo as $tipo): ?>
            <div class="stat-card <?php echo strtolower($tipo['tipo_periodo']) == 'total' ? 'total' : ''; ?>">
                <div class="numero"><?php echo $tipo['cantidad']; ?></div>
                <div class="label">Planillas <?php echo $tipo['tipo_periodo']; ?></div>
                <div class="label">Total: Q<?php echo number_format($tipo['total_monto'], 2); ?></div>
                <div class="label">Promedio: Q<?php echo number_format($tipo['promedio'], 2); ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Resumen mensual -->
    <div class="card-resumen">
        <h3>📅 Planillas por Mes - Año <?php echo $ano_seleccionado; ?></h3>
        
        <?php if(count($resumen) > 0): ?>
        <table class="tabla-reporte">
            <thead>
                <tr>
                    <th>Mes</th>
                    <th>Cantidad de Planillas</th>
                    <th>Total Pagado</th>
                    <th>Promedio por Planilla</th>
                    <th>% del Año</th>
                </thead>
            <tbody>
                <?php 
                $total_anual = 0;
                foreach($resumen as $r) {
                    $total_anual += $r['total_monto'];
                }
                ?>
                <?php 
                $nombres_meses = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 
                                  'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
                ?>
                <?php foreach($resumen as $r): ?>
                <?php 
                $porcentaje = ($total_anual > 0) ? ($r['total_monto'] / $total_anual) * 100 : 0;
                ?>
                <tr>
                    <td><strong><?php echo $nombres_meses[$r['mes']]; ?></strong></td>
                    <td><?php echo $r['total_planillas']; ?></td>
                    <td class="monto">Q<?php echo number_format($r['total_monto'], 2); ?></td>
                    <td>Q<?php echo number_format($r['promedio_monto'], 2); ?></td>
                    <td><?php echo number_format($porcentaje, 1); ?>%</td>
                </tr>
                <?php endforeach; ?>
                <tr style="background: #f8f9fa; font-weight: bold;">
                    <td>TOTAL ANUAL</td>
                    <td><?php echo array_sum(array_column($resumen, 'total_planillas')); ?></td>
                    <td class="monto">Q<?php echo number_format($total_anual, 2); ?></td>
                    <td>Q<?php echo number_format($total_anual / count($resumen), 2); ?></td>
                    <td>100%</td>
                </tr>
            </tbody>
        </table>
        
        <!-- Gráfico de barras simple -->
        <div class="grafico-barras">
            <h4 style="margin: 20px 0 10px 0;">📊 Distribución Mensual</h4>
            <?php 
            $max_monto = max(array_column($resumen, 'total_monto'));
            foreach($resumen as $r): 
                $ancho = ($max_monto > 0) ? ($r['total_monto'] / $max_monto) * 100 : 0;
            ?>
            <div style="margin-bottom: 8px;">
                <span class="mes-label"><?php echo $nombres_meses[$r['mes']]; ?></span>
                <div class="barra" style="width: <?php echo $ancho; ?>%; max-width: 100%;">
                    Q<?php echo number_format($r['total_monto'], 2); ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <?php else: ?>
        <div class="alert info">
            No hay planillas registradas para el año <?php echo $ano_seleccionado; ?>.
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Información adicional -->
    <div class="card-resumen">
        <h3>ℹ️ Información Adicional</h3>
        <ul style="margin: 10px 0 0 20px;">
            <li>Las planillas se generan automáticamente con el cálculo de horas extras y bonificaciones.</li>
            <li>La bonificación del 5% se aplica cuando la productividad del empleado es ≥ 80%.</li>
            <li>Las horas extras se pagan al 150% del valor hora normal.</li>
            <li>El total anual incluye salario base + horas extras + bonificaciones.</li>
        </ul>
    </div>
    
    <!-- Botones de acción -->
    <div style="text-align: center; margin-top: 20px;">
        <a href="index.php?controller=planilla&action=index" class="btn">← Volver a Planillas</a>
        <a href="index.php?controller=planilla&action=generar" class="btn btn-success">+ Generar Nueva Planilla</a>
        <button onclick="window.print();" class="btn btn-descargar">🖨️ Imprimir Reporte</button>
    </div>
</div>
</body>
</html>