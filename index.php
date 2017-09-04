<?php
/**
 * Created by PhpStorm.
 * User: Francisco
 * Date: 28-08-2017
 * Time: 6:46
 */

require 'utilidades/ConexionBD.php';
require 'vistas/VistaJson.php';
require 'modelos/usuarios.php';

//Objeto de salida
$vista = new VistaJson();

//manejo de excepciones
set_exception_handler(function ($exception) use ($vista) {
    $cuerpo = array(
        "estado" => $exception->estado,
        "mensaje" => $exception->getMessage()
    );
    $vista->imprimir($cuerpo);
});


// Obtener recurso
$peticion = pathinfo($_GET['PATH_INFO']);
$recurso = array_shift($peticion);


// Comprobar si existe el recurso
$recursos_existentes = array('usuario');
if (!in_array($recurso, $recursos_existentes)) {
    // Respuesta error
    throw new ExcepcionApi(2, "El recurso solicitado no existe", 404);
}

//direccionar por metodo
$metodo = strtolower($_SERVER['REQUEST_METHOD']);
switch ($metodo) {
    case 'get':
        // Procesar método get
        break;

    case 'post':
        // Procesar método post
        $vista->imprimir(usuarios::post($peticion));
        break;
    case 'put':
        // Procesar método put
        break;

    case 'delete':
        // Procesar método delete
        break;
    default:
        // Método no aceptado
        throw new ExcepcionApi(2, "Método no aceptado para el recurso solicitado", 404);
}