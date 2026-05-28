<?php
// Archivo de instalación automática
echo "<!DOCTYPE html>
<html>
<head>
    <title>Instalación - Sistema RRHH</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        .success { background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin: 10px 0; }
        .error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin: 10px 0; }
        .info { background: #d1ecf1; color: #0c5460; padding: 10px; border-radius: 4px; margin: 10px 0; }
        .btn { display: inline-block; padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 4px; margin-top: 20px; }
        code { background: #f8f9fa; padding: 2px 5px; border-radius: 3px; }
    </style>
</head>
<body>
<div class='container'>
    <h1>📦 Instalación del Sistema RRHH</h1>";

// Verificar si ya está instalado
if(file_exists('instalado.lock')) {
    echo "<div class='info'>✅ El sistema ya está instalado.</div>";
    echo "<a href='index.php' class='btn'>Ir al Sistema</a>";
    echo "</div></body></html>";
    exit;
}

// Verificar conexión a MySQL
try {
    $conn = new PDO("mysql:host=localhost", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<div class='success'>✓ Conexión a MySQL exitosa</div>";
    
    // Verificar si la base de datos existe
    $stmt = $conn->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = 'rrhh_sistema'");
    $existe = $stmt->fetch();
    
    if($existe) {
        echo "<div class='info'>ℹ️ La base de datos 'rrhh_sistema' ya existe.</div>";
        
        // Opción para reinstalar
        if(isset($_GET['reinstalar']) && $_GET['reinstalar'] == 'si') {
            echo "<div class='info'>🔄 Reinstalando base de datos...</div>";
            $conn->exec("DROP DATABASE rrhh_sistema");
            $existe = false;
        }
    }
    
    if(!$existe) {
        echo "<div class='info'>📀 Creando base de datos y tablas...</div>";
        
        // Leer el archivo SQL
        $sql = file_get_contents('mysql/bd_rrhh.sql');
        
        if($sql) {
            // Ejecutar el script SQL
            $conn->exec("CREATE DATABASE IF NOT EXISTS rrhh_sistema");
            $conn->exec("USE rrhh_sistema");
            
            // Dividir y ejecutar las consultas
            $queries = explode(';', $sql);
            $errores = 0;
            
            foreach($queries as $query) {
                $query = trim($query);
                if(!empty($query)) {
                    try {
                        $conn->exec($query);
                    } catch(PDOException $e) {
                        $errores++;
                        echo "<div class='error'>⚠️ Error: " . $e->getMessage() . "</div>";
                    }
                }
            }
            
            if($errores == 0) {
                echo "<div class='success'>✓ Base de datos creada exitosamente</div>";
                
                // Crear archivo de instalación completada
                file_put_contents('instalado.lock', date('Y-m-d H:i:s'));
                
                echo "<div class='success'>✅ Instalación completada con éxito!</div>";
                echo "<a href='index.php' class='btn'>Ir al Sistema</a>";
            } else {
                echo "<div class='error'>❌ Se encontraron $errores errores durante la instalación</div>";
            }
        } else {
            echo "<div class='error'>❌ No se pudo leer el archivo mysql/bd_rrhh.sql</div>";
        }
    } else {
        echo "<div class='success'>✅ La base de datos ya está configurada</div>";
        
        if(!file_exists('instalado.lock')) {
            file_put_contents('instalado.lock', date('Y-m-d H:i:s'));
        }
        
        echo "<a href='index.php' class='btn'>Ir al Sistema</a>";
        echo "<br><br>";
        echo "<a href='?reinstalar=si' class='btn' style='background:#dc3545;' onclick='return confirm(\"¿Reinstalar la base de datos? Se perderán todos los datos.\")'>🔄 Reinstalar Base de Datos</a>";
    }
    
} catch(PDOException $e) {
    echo "<div class='error'>❌ Error de conexión: " . $e->getMessage() . "</div>";
    echo "<div class='info'>📝 Para instalar, asegúrate de tener XAMPP con MySQL corriendo.</div>";
}

echo "</div></body></html>";
?>