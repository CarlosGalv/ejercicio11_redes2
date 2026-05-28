<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reportes</title>
    <link rel="stylesheet" href="css/estilo.css">
</head>
<body>
    <div class="container">
        <h1>Reportes del Sistema</h1>
        
        <div class="menu">
            <a href="index.php?controller=empleado&action=index" class="btn">← Volver</a>
        </div>
        
        <div class="reporte">
            <h2>Resumen General</h2>
            <ul>
                <li>Total empleados: <?php echo $resumen['total_empleados']; ?></li>
                <li>Empleados activos: <?php echo $resumen['activos']; ?></li>
                <li>Empleados inactivos: <?php echo $resumen['inactivos']; ?></li>
                <li>Promedio salarial: Q<?php echo number_format($resumen['promedio_salario'], 2); ?></li>
            </ul>
        </div>
        
        <div class="reporte">
            <h2>Planillas 2024 por Mes</h2>
            <table>
                <thead><tr><th>Mes</th><th>Total Planillas</th><th>Monto Total</th></tr></thead>
                <tbody>
                    <?php foreach($planillas_2024 as $p): ?>
                    <tr>
                        <td>Mes <?php echo $p['mes']; ?></td>
                        <td><?php echo $p['total_planillas']; ?></td>
                        <td>Q<?php echo number_format($p['total_monto'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>