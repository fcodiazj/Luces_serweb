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
require 'modelos/seriales.php';

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


// Obtener recurso.
$peticion = pathinfo($_GET['PATH_INFO']);
/*if (is_array($peticion)) {
    var_dump($peticion);
} else {
    print "no es arreglo";
}
*/
$recurso = array_shift($peticion);
//var_dump($recurso);

// Comprobar si existe el recurso
$recursos_existentes = array('usuarios','seriales','luces');
if (!in_array($recurso, $recursos_existentes)) {
    // Respuesta error
    throw new ExcepcionApi(2, "El recurso solicitado no existe");
}

//direccionar por metodo
$metodo = strtolower($_SERVER['REQUEST_METHOD']);
switch ($metodo) {
    case 'get':
        // Procesar método get
        print "nada aun";
        break;

    case 'post':
        // Procesar método post
        if ($recurso == 'usuarios'){
            $vista->imprimir(usuarios::post($peticion));
        }
        if ($recurso == 'seriales'){
            $vista->imprimir(seriales::post($peticion));
        }
        break;
    case 'put':
        // Procesar método put
        print "nada aun";
        break;

    case 'delete':
        // Procesar método delete
        print "nada aun";
        break;
    default:
        // Método no aceptado
        throw new ExcepcionApi(2, "Método no aceptado para el recurso solicitado");
}