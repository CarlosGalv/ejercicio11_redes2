<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nueva Prestación</title>
    <link rel="stylesheet" href="css/estilo.css">
</head>
<body>
    <div class="container">
        <h1>Registrar Prestación</h1>
        
        <form action="index.php?controller=prestacion&action=crear" method="POST" class="form">
            <div class="form-group">
                <label>Empleado:</label>
                <select name="empleado_id" required>
                    <option value="">Seleccione...</option>
                    <?php foreach($empleados as $emp): ?>
                    <option value="<?php echo $emp['id']; ?>"><?php echo $emp['nombre'] . ' ' . $emp['apellido']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Tipo de prestación:</label>
                <select name="tipo" required>
                    <option value="Aguinaldo">Aguinaldo</option>
                    <option value="Vacaciones">Vacaciones</option>
                    <option value="Bono 14">Bono 14</option>
                    <option value="Indemnización">Indemnización</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Monto (Q):</label>
                <input type="number" step="0.01" name="monto" required>
            </div>
            
            <div class="form-group">
                <label>Fecha de inicio:</label>
                <input type="date" name="fecha_inicio" required>
            </div>
            
            <div class="form-group">
                <label>Estado:</label>
                <select name="estado" required>
                    <option value="Activo">Activo</option>
                    <option value="Pendiente">Pendiente</option>
                </select>
            </div>
            
            <button type="submit" class="btn">Registrar</button>
            <a href="index.php?controller=prestacion&action=index" class="btn cancel">Cancelar</a>
        </form>
    </div>
</body>
</html>