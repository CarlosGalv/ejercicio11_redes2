<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nuevo Indicador</title>
    <link rel="stylesheet" href="css/estilo.css">
</head>
<body>
    <div class="container">
        <h1>Registrar Indicador de Productividad</h1>
        
        <?php if(isset($error)): ?>
            <div class="alert error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form action="index.php?controller=indicador&action=crear" method="POST" class="form">
            <div class="form-group">
                <label>Empleado:</label>
                <select name="empleado_id" required>
                    <option value="">Seleccione un empleado...</option>
                    <?php foreach($empleados as $emp): ?>
                    <option value="<?php echo $emp['id']; ?>">
                        <?php echo $emp['nombre'] . ' ' . $emp['apellido'] . ' - ' . $emp['cargo']; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Mes:</label>
                <select name="mes" required>
                    <option value="">Seleccione...</option>
                    <?php for($i=1; $i<=12; $i++): ?>
                    <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Año:</label>
                <select name="ano" required>
                    <option value="">Seleccione...</option>
                    <?php for($i=2023; $i<=2025; $i++): ?>
                    <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Tareas asignadas:</label>
                <input type="number" name="tareas_asignadas" required min="0" value="0">
            </div>
            
            <div class="form-group">
                <label>Tareas completadas:</label>
                <input type="number" name="tareas_completadas" required min="0" value="0">
            </div>
            
            <div class="form-group">
                <label>Ausencias (días):</label>
                <input type="number" name="ausencias" required min="0" value="0">
            </div>
            
            <button type="submit" class="btn">Registrar Indicador</button>
            <a href="index.php?controller=indicador&action=index" class="btn cancel">Cancelar</a>
        </form>
        
        <div class="info" style="margin-top: 20px;">
            <h3>Información:</h3>
            <ul>
                <li>La productividad se calcula automáticamente como: (tareas completadas / tareas asignadas) × 100</li>
                <li>Si la productividad es ≥ 80%, el empleado recibe una bonificación del 5% en su planilla</li>
                <li>Las ausencias afectan otros indicadores</li>
            </ul>
        </div>
    </div>
</body>
</html>