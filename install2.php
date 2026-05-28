<?php
// Archivo de instalación automática - NO necesita MySQL Workbench
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Instalación - Sistema RRHH</title>
    <style>
        body { font-family: 'Segoe UI', Arial; padding: 20px; background: #f0f2f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 25px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #2c3e50; border-left: 4px solid #3498db; padding-left: 15px; }
        .success { background: #d4edda; color: #155724; padding: 12px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #28a745; }
        .error { background: #f8d7da; color: #721c24; padding: 12px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #dc3545; }
        .info { background: #d1ecf1; color: #0c5460; padding: 12px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #17a2b8; }
        .warning { background: #fff3cd; color: #856404; padding: 12px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #ffc107; }
        .btn { display: inline-block; padding: 10px 25px; background: #28a745; color: white; text-decoration: none; border-radius: 5px; margin-top: 20px; font-weight: bold; }
        .btn:hover { background: #218838; }
        .progress { background: #e9ecef; border-radius: 5px; padding: 2px; margin: 15px 0; }
        .progress-bar { background: #28a745; height: 20px; border-radius: 5px; width: 0%; transition: width 0.5s; text-align: center; color: white; font-size: 12px; line-height: 20px; }
        code { background: #f8f9fa; padding: 2px 6px; border-radius: 3px; font-family: monospace; }
    </style>
</head>
<body>
<div class="container">
    <h1>📦 Instalación del Sistema RRHH</h1>
    
    <?php
    // Verificar si ya está instalado
    if(file_exists('instalado.lock')) {
        echo "<div class='info'>✅ El sistema ya está instalado.</div>";
        echo "<a href='index.php' class='btn'>🚀 Ir al Sistema</a>";
        echo "</div></body></html>";
        exit;
    }
    
    echo "<div class='progress'><div class='progress-bar' id='progressBar' style='width: 0%'>0%</div></div>";
    
    function logMessage($message, $type = 'info') {
        $class = $type == 'success' ? 'success' : ($type == 'error' ? 'error' : 'info');
        echo "<div class='$class'>$message</div>";
        ob_flush();
        flush();
    }
    
    // Paso 1: Verificar XAMPP y MySQL
    logMessage("🔍 Verificando conexión a MySQL...", 'info');
    
    try {
        $conn = new PDO("mysql:host=localhost", "root", "");
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        logMessage("✅ Conexión a MySQL exitosa", 'success');
        
        // Paso 2: Verificar si la base de datos ya existe
        $stmt = $conn->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = 'rrhh_sistema'");
        $existe = $stmt->fetch();
        
        if($existe) {
            logMessage("ℹ️ La base de datos 'rrhh_sistema' ya existe", 'info');
            
            if(isset($_GET['reinstalar']) && $_GET['reinstalar'] == 'si') {
                logMessage("🔄 Eliminando base de datos existente...", 'warning');
                $conn->exec("DROP DATABASE rrhh_sistema");
                logMessage("✅ Base de datos eliminada", 'success');
                $existe = false;
            }
        }
        
        // Paso 3: Crear base de datos
        if(!$existe) {
            logMessage("📀 Creando base de datos 'rrhh_sistema'...", 'info');
            $conn->exec("CREATE DATABASE rrhh_sistema CHARACTER SET utf8 COLLATE utf8_general_ci");
            $conn->exec("USE rrhh_sistema");
            logMessage("✅ Base de datos creada", 'success');
            
            // Paso 4: Leer y ejecutar el archivo SQL
            $sqlFile = 'mysql/bd_rrhh.sql';
            
            if(file_exists($sqlFile)) {
                logMessage("📄 Leyendo archivo SQL: $sqlFile", 'info');
                $sql = file_get_contents($sqlFile);
                
                if($sql) {
                    logMessage("⚙️ Ejecutando script SQL...", 'info');
                    
                    // Separar las consultas
                    $queries = explode(';', $sql);
                    $total = count($queries);
                    $ok = 0;
                    $errores = 0;
                    $i = 0;
                    
                    foreach($queries as $query) {
                        $query = trim($query);
                        if(!empty($query)) {
                            try {
                                $conn->exec($query);
                                $ok++;
                            } catch(PDOException $e) {
                                $errores++;
                                // No mostrar cada error, solo contar
                            }
                        }
                        $i++;
                        $percent = round(($i / $total) * 100);
                        echo "<script>document.getElementById('progressBar').style.width = '{$percent}%'; document.getElementById('progressBar').innerHTML = '{$percent}%';</script>";
                        ob_flush();
                        flush();
                    }
                    
                    echo "<script>document.getElementById('progressBar').style.width = '100%'; document.getElementById('progressBar').innerHTML = '100%';</script>";
                    
                    if($errores == 0) {
                        logMessage("✅ Script SQL ejecutado correctamente ($ok consultas)", 'success');
                    } else {
                        logMessage("⚠️ Script SQL ejecutado con $errores advertencias ($ok consultas OK)", 'warning');
                    }
                    
                } else {
                    logMessage("❌ No se pudo leer el archivo SQL", 'error');
                }
            } else {
                logMessage("❌ Archivo SQL no encontrado: $sqlFile", 'error');
                echo "<div class='warning'>📝 Debes crear el archivo mysql/bd_rrhh.sql con la estructura de la base de datos</div>";
            }
        }
        
        // Paso 5: Verificar instalación
        $conn->query("USE rrhh_sistema");
        $stmt = $conn->query("SHOW TABLES");
        $tablas = $stmt->fetchAll();
        
        logMessage("📊 Tablas creadas: " . count($tablas), 'success');
        
        // Contar empleados si hay
        $stmt = $conn->query("SELECT COUNT(*) as total FROM empleados");
        $row = $stmt->fetch();
        logMessage("👥 Empleados en sistema: " . $row['total'], 'info');
        
        // Crear archivo de instalación completada
        file_put_contents('instalado.lock', date('Y-m-d H:i:s') . "\nInstalación completada");
        
        echo "<div class='success' style='font-size: 16px; text-align: center; margin-top: 20px;'>";
        echo "✅ <strong>INSTALACIÓN COMPLETADA CON ÉXITO!</strong><br>";
        echo "El sistema está listo para usar.";
        echo "</div>";
        
        echo "<div style='text-align: center; margin-top: 20px;'>";
        echo "<a href='index.php' class='btn'>🚀 Ir al Sistema de RRHH</a>";
        echo "</div>";
        
    } catch(PDOException $e) {
        echo "<div class='error'>❌ Error de conexión a MySQL: " . $e->getMessage() . "</div>";
        echo "<div class='info'>📝 Soluciones:<br>";
        echo "• Asegúrate que XAMPP esté corriendo con MySQL activado<br>";
        echo "• Verifica que MySQL esté en el puerto 3306<br>";
        echo "• El usuario debe ser 'root' sin contraseña</div>";
    }
    ?>
    
    <div class="info" style="margin-top: 20px; font-size: 12px;">
        <strong>📌 Nota:</strong> Si necesitas reinstalar el sistema, elimina el archivo <code>instalado.lock</code> y recarga esta página.
    </div>
</div>
</body>
</html>