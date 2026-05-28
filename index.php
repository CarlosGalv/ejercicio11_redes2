<?php
// Router principal
$controller = isset($_GET['controller']) ? $_GET['controller'] : 'empleado';
$action = isset($_GET['action']) ? $_GET['action'] : 'index';

// Construir la ruta del controlador
$controllerFile = "php/controllers/" . ucfirst($controller) . "Controller.php";

// Verificar si el archivo existe
if(file_exists($controllerFile)) {
    require_once $controllerFile;
    
    // Crear el nombre de la clase
    $controllerClass = ucfirst($controller) . "Controller";
    
    // Verificar si la clase existe
    if(class_exists($controllerClass)) {
        $controllerObj = new $controllerClass();
        
        if(method_exists($controllerObj, $action)) {
            $controllerObj->$action();
        } else {
            echo "Error: Método '$action' no encontrado en $controllerClass";
        }
    } else {
        echo "Error: Clase '$controllerClass' no encontrada en $controllerFile";
    }
} else {
    echo "Error: Controlador no encontrado - Archivo: $controllerFile";
}
?>