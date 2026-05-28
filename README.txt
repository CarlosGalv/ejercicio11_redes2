============================================
SISTEMA DE GESTIÓN DE RRHH
============================================

REQUISITOS:
- XAMPP (Apache y MySQL)
- PHP 7.4 o superior
- Navegador web

INSTALACIÓN EN OTRA COMPUTADORA:

1. Instalar XAMPP en la nueva computadora
2. Copiar toda la carpeta "rrhh" a:
   C:\xampp\htdocs\
3. Iniciar Apache y MySQL en XAMPP
4. Abrir navegador y acceder a:
   http://localhost/rrhh/install.php
5. Seguir las instrucciones de instalación

O INSTALACIÓN MANUAL:

1. Copiar la carpeta a C:\xampp\htdocs\rrhh
2. En phpMyAdmin, crear base de datos "rrhh_sistema"
3. Importar el archivo mysql/bd_rrhh.sql
4. Acceder a: http://localhost/rrhh/

CREDENCIALES POR DEFECTO:
- Usuario MySQL: root
- Contraseña: (vacío)

CONTACTO: [Tu nombre]
FECHA: [Fecha del proyecto]
============================================




PROCESO COMPLETO EN LA NUEVA COMPUTADORA
Paso 1: Instalar XAMPP (solo una vez)
Descargar XAMPP desde https://www.apachefriends.org/

Instalarlo en C:\xampp

Paso 2: Copiar tu proyecto
Copiar la carpeta rrhh a C:\xampp\htdocs\

Paso 3: Iniciar XAMPP
Abrir XAMPP Control Panel

Hacer clic en "Start" en Apache y MySQL

Paso 4: Ejecutar el instalador
Abrir navegador

Ir a: http://localhost/rrhh/install.php

El instalador hará TODO automáticamente:

Creará la base de datos

Creará todas las tablas

Creará los procedimientos almacenados

Creará los triggers

Creará las funciones

Insertará datos de prueba (si los tienes en el SQL)

Paso 5: Usar el sistema
Ir a: http://localhost/rrhh/

¡Todo funcionando!