<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle Planilla - <?php echo $planilla['nombre'] . ' ' . $planilla['apellido']; ?></title>
    <link rel="stylesheet" href="css/estilo.css">
    <style>
        .detalle-container {
            max-width: 900px;
            margin: 0 auto;
        }
        .header-planilla {
            background: linear-gradient(135deg, #2c3e50, #3498db);
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        .info-empleado {
            background: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .info-empleado h3 {
            margin-top: 0;
            color: #2c3e50;
        }
        .grid-info {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-top: 10px;
        }
        .detalle-calculo {
            background: #fff;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .detalle-calculo h3 {
            margin-top: 0;
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 8px;
        }
        .fila-calculo {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .fila-total {
            background: #e8f4fd;
            padding: 15px;
            border-radius: 6px;
            margin-top: 10px;
            font-size: 18px;
            font-weight: bold;
        }
        .badge-tipo {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        .tipo-mensual { background: #d4edda; color: #155724; }
        .tipo-semanal { background: #fff3cd; color: #856404; }
        .tipo-quincenal { background: #cce5ff; color: #004085; }
        .btn-ver-perfil {
            background: #27ae60;
            margin-left: 10px;
        }
    </style>
</head>
<body>
<div class="container detalle-container">
    <!-- Encabezado -->
    <div class="header-planilla">
        <h1>💰 DETALLE DE PLANILLA</h1>
        <p>Período: <?php echo $planilla['mes'] . '/' . $planilla['ano']; ?> | Tipo: <?php echo $planilla['tipo_periodo']; ?></p>
        <p>Fecha de generación: <?php echo isset($planilla['fecha_generacion']) ? $planilla['fecha_generacion'] : (isset($planilla['created_at']) ? $planilla['created_at'] : date('Y-m-d H:i:s')); ?></p>
    </div>
    
    <!-- Información del Empleado -->
    <div class="info-empleado">
        <h3>📋 INFORMACIÓN DEL EMPLEADO</h3>
        <div class="grid-info">
            <div><strong>Nombre:</strong> <?php echo $planilla['nombre'] . ' ' . $planilla['apellido']; ?></div>
            <div><strong>Código:</strong> <?php echo isset($planilla['codigo']) ? $planilla['codigo'] : 'N/A'; ?></div>
            <div><strong>Cédula:</strong> <?php echo isset($planilla['cedula']) ? $planilla['cedula'] : 'N/A'; ?></div>
            <div><strong>Cargo:</strong> <?php echo $planilla['cargo']; ?></div>
            <div><strong>Tipo salario:</strong> 
                <span class="badge-tipo tipo-<?php echo strtolower($planilla['tipo_periodo']); ?>">
                    <?php echo $planilla['tipo_periodo']; ?>
                </span>
            </div>
            <div><strong>Fecha ingreso:</strong> <?php echo isset($planilla['fecha_ingreso']) ? $planilla['fecha_ingreso'] : 'N/A'; ?></div>
            <div><strong>Antigüedad:</strong> <?php echo isset($planilla['antiguedad']) ? $planilla['antiguedad'] : 'N/A'; ?> meses</div>
            <div><strong>Estado:</strong> <?php echo isset($planilla['activo']) ? ($planilla['activo'] ? 'Activo' : 'Inactivo') : 'Activo'; ?></div>
        </div>
        <div style="margin-top: 15px; text-align: right;">
            <a href="index.php?controller=empleado&action=perfil&id=<?php echo $planilla['empleado_id']; ?>" class="btn btn-ver-perfil">
                👤 Ver Perfil Completo
            </a>
            <a href="index.php?controller=planilla&action=empleado&id=<?php echo $planilla['empleado_id']; ?>" class="btn">
                📄 Ver todas sus planillas
            </a>
        </div>
    </div>
    
    <!-- Detalle del Cálculo -->
    <div class="detalle-calculo">
        <h3>📊 DETALLE DEL CÁLCULO</h3>
        
        <div class="fila-calculo">
            <span>💰 Salario Base:</span>
            <strong>Q<?php echo number_format($planilla['salario_base'], 2); ?></strong>
        </div>
        
        <div class="fila-calculo">
            <span>⏰ Horas Extras:</span>
            <strong><?php echo isset($planilla['horas_extras']) ? $planilla['horas_extras'] : 0; ?> horas 
            (Q<?php echo number_format(isset($planilla['monto_horas_extras']) ? $planilla['monto_horas_extras'] : 0, 2); ?>)</strong>
        </div>
        
        <div class="fila-calculo">
            <span>🎁 Bonificación (5% si productividad ≥ 80%):</span>
            <strong>Q<?php echo number_format(isset($planilla['bonificacion']) ? $planilla['bonificacion'] : 0, 2); ?></strong>
        </div>
        
        <div class="fila-total">
            <div style="display: flex; justify-content: space-between;">
                <span>💰 TOTAL A PAGAR:</span>
                <span style="font-size: 24px; color: #27ae60;">Q<?php echo number_format($planilla['total'], 2); ?></span>
            </div>
        </div>
    </div>
    
    <!-- Botones de acción -->
    <div style="text-align: center; margin-top: 20px;">
        <a href="index.php?controller=planilla&action=index" class="btn">← Volver a Planillas</a>
        <a href="index.php?controller=planilla&action=generar&empleado_id=<?php echo $planilla['empleado_id']; ?>" class="btn btn-success">
            + Nueva Planilla para este empleado
        </a>
    </div>
</div>
</body>
</html>