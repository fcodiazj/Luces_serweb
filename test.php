<?php
/**
 * Created by PhpStorm.
 * User: Francisco
 * Date: 04-09-2017
 * Time: 3:35
 */

require 'utilidades/ConexionBD.php';
// Datos de la tabla "seriales"
const NOMBRE_TABLA_SERIALES = "seriales";
const ID_SERIAL = "id_serial";
const SERIAL = "serial";
const CANTIDAD = "cantidad";
const ID_USUARIO = "id_usuario";
$id_usuario="1";
$serial="ABC123";

$comando = "SELECT ".ID_SERIAL." FROM " . NOMBRE_TABLA_SERIALES .
    " WHERE " . ID_USUARIO . "=? AND ".SERIAL."=?";
$sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);
$sentencia->bindParam(1, $id_usuario);
$sentencia->bindParam(2, $serial);
if ($sentencia->execute()){
    $sentencia->fetch(PDO::FETCH_ASSOC);
    var_dump($sentencia);
} else return null;