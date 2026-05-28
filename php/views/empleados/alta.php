<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Alta de Empleado</title>
    <link rel="stylesheet" href="css/estilo.css">
</head>
<body>
    <div class="container">
        <h1>Registrar Nuevo Empleado</h1>
        
        <?php if(isset($error)): ?>
            <div class="alert error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form action="index.php?controller=empleado&action=alta" method="POST" class="form">
            <div class="form-group">
                <label>Código empleado:</label>
                <input type="text" name="codigo" required placeholder="Ej: EMP001">
            </div>
            
            <div class="form-group">
                <label>Cédula / Dpi:</label>
                <input type="text" name="cedula" required>
            </div>
            
            <div class="form-group">
                <label>Nombre:</label>
                <input type="text" name="nombre" required>
            </div>
            
            <div class="form-group">
                <label>Apellido:</label>
                <input type="text" name="apellido" required>
            </div>
            
            <div class="form-group">
                <label>Cargo:</label>
                <input type="text" name="cargo" required>
            </div>
            
            <div class="form-group">
                <label>Tipo de salario:</label>
                <select name="tipo_salario" required>
                    <option value="Mensual">Mensual</option>
                    <option value="Semanal">Semanal</option>
                    <option value="Quincenal">Quincenal</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Salario base (Q):</label>
                <input type="number" step="0.01" name="salario_base" required>
            </div>
            
            <div class="form-group">
                <label>Fecha de ingreso:</label>
                <input type="date" name="fecha_ingreso" required>
            </div>
            
            <button type="submit" class="btn">Guardar Empleado</button>
            <a href="index.php?controller=empleado&action=index" class="btn cancel">Cancelar</a>
        </form>
    </div>
</body>
</html>