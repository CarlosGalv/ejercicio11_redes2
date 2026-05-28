<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Planillas - RRHH System</title>
    <link rel="stylesheet" href="css/estilo.css">
</head>
<body>
    <div class="container">
        <h1>💰 Gestión de Planillas</h1>
        
        <div class="menu">
            <a href="index.php?controller=planilla&action=generar" class="btn">+ Generar Nueva Planilla</a>
            <a href="index.php?controller=planilla&action=reporte" class="btn">📊 Reporte por Año</a>
            <a href="index.php?controller=empleado&action=index" class="btn">← Volver a Empleados</a>
        </div>
        
        <?php if(isset($_GET['msg']) && $_GET['msg'] == 'generada'): ?>
            <div class="alert success" style="background:#d4edda; padding:10px; border-radius:4px; margin:10px 0;">
                ✓ Planilla generada correctamente
            </div>
        <?php endif; ?>
        
        <h2>Historial de Planillas</h2>
        
        <?php 
        // Verificar si existen planillas
        if(!isset($planillas) || !is_array($planillas) || count($planillas) == 0): 
        ?>
            <div style="background:#d1ecf1; padding:15px; border-radius:4px;">
                No hay planillas generadas aún. Haz clic en "Generar Nueva Planilla" para comenzar.
            </div>
        <?php else: ?>
        <table border="1" cellpadding="8" cellspacing="0" style="width:100%; border-collapse: collapse;">
            <thead style="background:#f2f2f2;">
                <tr>
                    <th>ID</th>
                    <th>Empleado</th>
                    <th>Periodo</th>
                    <th>Tipo</th>
                    <th>Salario Base</th>
                    <th>Horas Extras</th>
                    <th>Bonificación</th>
                    <th>Total</th>
                    <th>Acciones</th>
                </thead>
            <tbody>
                <?php foreach($planillas as $p): ?>
                <tr>
                    <td><?php echo $p['id']; ?></td>
                    <td>
                        <strong><?php echo $p['nombre'] . ' ' . $p['apellido']; ?></strong>
                    </td>
                    <td><?php echo $p['mes'] . '/' . $p['ano']; ?></td>
                    <td><?php echo $p['tipo_periodo']; ?></td>
                    <td>Q<?php echo number_format($p['salario_base'], 2); ?></td>
                    <td><?php echo $p['horas_extras']; ?> hrs</td>
                    <td>Q<?php echo number_format($p['bonificacion'], 2); ?></td>
                    <td style="font-weight: bold; color:#27ae60;">Q<?php echo number_format($p['total'], 2); ?></td>
                    <td>
                        <a href="index.php?controller=planilla&action=ver&id=<?php echo $p['id']; ?>" style="background:#3498db; color:white; padding:4px 8px; text-decoration:none; border-radius:4px;">Ver Detalle</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</body>
</html>