<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Prestaciones</title>
    <link rel="stylesheet" href="css/estilo.css">
</head>
<body>
    <div class="container">
        <h1>Gestión de Prestaciones</h1>
        
        <div class="menu">
            <a href="index.php?controller=prestacion&action=crear" class="btn">+ Nueva Prestación</a>
            <a href="index.php?controller=empleado&action=index" class="btn">← Volver</a>
        </div>
        
        <?php if(isset($_GET['msg'])): ?>
            <div class="alert success">✓ Prestación registrada</div>
        <?php endif; ?>
        
        <h2>Listado de Prestaciones</h2>
        
        <table>
            <thead>
                <tr><th>Empleado</th><th>Tipo</th><th>Monto</th><th>Fecha Inicio</th><th>Estado</th></tr>
            </thead>
            <tbody>
                <?php foreach($prestaciones as $p): ?>
                <tr>
                    <td><?php echo $p['nombre'] . ' ' . $p['apellido']; ?></td>
                    <td><?php echo $p['tipo']; ?></td>
                    <td>Q<?php echo number_format($p['monto'], 2); ?></td>
                    <td><?php echo $p['fecha_inicio']; ?></td>
                    <td class="<?php echo strtolower($p['estado']); ?>"><?php echo $p['estado']; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>