<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Indicadores</title>
    <link rel="stylesheet" href="css/estilo.css">
</head>
<body>
    <div class="container">
        <h1>Indicadores de Productividad</h1>
        
        <div class="menu">
            <a href="index.php?controller=indicador&action=crear" class="btn">+ Nuevo Indicador</a>
            <a href="index.php?controller=empleado&action=index" class="btn">← Volver</a>
        </div>
        
        <table>
            <thead>
                <tr><th>Empleado</th><th>Periodo</th><th>Tareas</th><th>Completadas</th><th>Ausencias</th><th>Productividad</th></tr>
            </thead>
            <tbody>
                <?php foreach($indicadores as $i): ?>
                <tr>
                    <td><?php echo $i['nombre'] . ' ' . $i['apellido']; ?></td>
                    <td><?php echo $i['mes'] . '/' . $i['ano']; ?></td>
                    <td><?php echo $i['tareas_asignadas']; ?></td>
                    <td><?php echo $i['tareas_completadas']; ?></td>
                    <td><?php echo $i['ausencias']; ?></td>
                    <td class="productividad"><?php echo number_format($i['productividad'], 1); ?>%</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>