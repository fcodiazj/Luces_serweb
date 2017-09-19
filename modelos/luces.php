<?php
/**
 * Created by PhpStorm.
 * User: Francisco
 * Date: 04-09-2017
 * Time: 1:42
 */


require_once 'utilidades/ExcepcionApi.php';

class luces
{

    // Datos de la tabla "luces"
    const NOMBRE_TABLA_LUCES = "luces";
    const ID_LUZ = "id_luz";
    const COLOR = "color";
    const POS_FILA = "pos_fila";
    const POS_COL = "pos_col";
    const ID_SERIAL = "id_serial";


    //determina la accion a realiza sobre el recurso
    public function post($peticion)
    {
        //Las acciones son crear una luz, borra una luz, cambiar color, y estado
        $accion = array_shift($peticion);
        if ($accion == 'crear') {//crea una nueva luz en la tabla
            return self::crear();
        }
        if ($accion == 'borrar') {//borra uan luz de la tabla
            return self::borrar();
        }
        if ($accion == 'cambiar') {//cambia el color de la luz
            return self::borrar();
        }
        if ($accion == 'listar') {//devuelve el estado de las luces asociadas al id_serial
            return self::listar();
        } else {
            throw new ExcepcionApi(5, "Url mal formada");
        }
    }

    //obtiene los datos de las luces asociadas al id_serial
    public function obtenerLucesporIdSerial($id_serial)
    {
        $comando = "SELECT "
            . self::ID_LUZ . ", "
            . self::COLOR . ", "
            . self::POS_FILA . ", "
            . self::POS_COL
            . " FROM "
            . self::NOMBRE_TABLA_LUCES
            . " WHERE "
            . self::ID_SERIAL . "=?";
        try {
            $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);
            $sentencia->bindParam(1, $id_serial);
            $sentencia->execute();
            if ($sentencia->execute()) {
                return $sentencia->fetch(PDO::FETCH_ASSOC);
            } else {
                return null;
            }
        } catch (PDOException $e) {
            throw new ExcepcionApi(5, $e->getMessage());
        }
    }

    //crea una luz en la tabla de luces, asociada al id_serial
    private function crear(){}

}


