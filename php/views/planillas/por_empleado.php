<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Planillas de <?php echo $empleado['nombre'] . ' ' . $empleado['apellido']; ?></title>
    <link rel="stylesheet" href="css/estilo.css">
</head>
<body>
    <div class="container">
        <h1>📄 Planillas de <?php echo $empleado['nombre'] . ' ' . $empleado['apellido']; ?></h1>
        
        <div class="info-empleado" style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <div class="grid-info" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px;">
                <div><strong>Código:</strong> <?php echo $empleado['codigo']; ?></div>
                <div><strong>Cédula:</strong> <?php echo $empleado['cedula']; ?></div>
                <div><strong>Cargo:</strong> <?php echo $empleado['cargo']; ?></div>
                <div><strong>Tipo salario:</strong> <?php echo $empleado['tipo_salario']; ?></div>
                <div><strong>Salario base:</strong> Q<?php echo number_format($empleado['salario_base'], 2); ?></div>
                <div><strong>Fecha ingreso:</strong> <?php echo $empleado['fecha_ingreso']; ?></div>
            </div>
            <div style="margin-top: 15px;">
                <a href="index.php?controller=empleado&action=perfil&id=<?php echo $empleado['id']; ?>" class="btn">👤 Ver Perfil Completo</a>
                <a href="index.php?controller=planilla&action=generar&empleado_id=<?php echo $empleado['id']; ?>" class="btn btn-success">💰 Generar Nueva Planilla</a>
                <a href="index.php?controller=planilla&action=index" class="btn">← Volver</a>
            </div>
        </div>
        
        <h2>Historial de Planillas</h2>
        
        <?php if(count($planillas) > 0): ?>
        <table border="1" cellpadding="8" cellspacing="0" style="width:100%; border-collapse: collapse;">
            <thead>
                <tr><th>ID</th><th>Periodo</th><th>Tipo</th><th>Salario Base</th><th>Horas Extras</th><th>Bonificación</th><th>Total</th><th>Fecha</th><th>Acción</th></tr>
            </thead>
            <tbody>
                <?php foreach($planillas as $p): ?>
                <tr>
                    <td><?php echo $p['id']; ?></td>
                    <td><?php echo $p['mes'] . '/' . $p['ano']; ?></td>
                    <td><?php echo $p['tipo_periodo']; ?></td>
                    <td>Q<?php echo number_format($p['salario_base'], 2); ?></td>
                    <td><?php echo $p['horas_extras']; ?> hrs</td>
                    <td>Q<?php echo number_format($p['bonificacion'], 2); ?></td>
                    <td><strong>Q<?php echo number_format($p['total'], 2); ?></strong></td>
                    <td><?php echo isset($p['fecha_generacion']) ? $p['fecha_generacion'] : (isset($p['created_at']) ? $p['created_at'] : date('Y-m-d H:i:s')); ?></td>
                    <td><a href="index.php?controller=planilla&action=ver&id=<?php echo $p['id']; ?>" class="btn-ver" style="background:#3498db; color:white; padding:4px 8px; text-decoration:none; border-radius:4px;">Ver</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="alert info">No hay planillas generadas para este empleado.</div>
        <?php endif; ?>
    </div>
</body>
</html>