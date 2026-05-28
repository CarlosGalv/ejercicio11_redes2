<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Empleados Inactivos</title>
    <link rel="stylesheet" href="css/estilo.css">
</head>
<body>
    <div class="container">
        <h1>📋 Empleados Inactivos (Dados de Baja)</h1>
        
        <div class="menu">
            <a href="index.php?controller=empleado&action=index" class="btn">← Volver a Activos</a>
        </div>
        
        <?php if(isset($empleados) && is_array($empleados) && count($empleados) > 0): ?>
        <table>
            <thead>
                <tr><th>Código</th><th>Nombre</th><th>Cargo</th><th>Fecha Ingreso</th><th>Fecha Retiro</th><th>Acciones</th></tr>
            </thead>
            <tbody>
                <?php foreach($empleados as $emp): ?>
                <tr>
                    <td><?php echo $emp['codigo']; ?></td>
                    <td><?php echo $emp['nombre'] . ' ' . $emp['apellido']; ?></td>
                    <td><?php echo $emp['cargo']; ?></td>
                    <td><?php echo date('d/m/Y', strtotime($emp['fecha_ingreso'])); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($emp['fecha_retiro'])); ?></td>
                    <td>
                        <a href="index.php?controller=empleado&action=perfil&id=<?php echo $emp['id']; ?>" class="btn-small">Ver Perfil</a>
                        <a href="index.php?controller=empleado&action=reactivar&id=<?php echo $emp['id']; ?>" class="btn-small" style="background:#27ae60;" onclick="return confirm('¿Reactivar este empleado?')">Reactivar</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="alert info">No hay empleados inactivos.</div>
        <?php endif; ?>
    </div>
</body>
</html>