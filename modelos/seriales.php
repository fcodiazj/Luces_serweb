<?php
/**
 * Created by PhpStorm.
 * User: Francisco
 * Date: 02-09-2017
 * Time: 22:01
 */

require_once 'utilidades/ExcepcionApi.php';

class seriales
{

    // Datos de la tabla "seriales"
    const NOMBRE_TABLA_SERIALES = "seriales";
    const ID_SERIAL = "id_serial";
    const SERIAL = "serial";
    const CANTIDAD = "cantidad";
    const ID_USUARIO = "id_usuario";




    //determina la accion a realiza sobre el recurso
    public function post($peticion)
    {
        $accion = array_shift($peticion);
        //devuelve los seriales asociados al id_usuario
        if ($accion == 'listar') {
            return self::listar();
        } else {
            if ($accion == 'crear') {//dado un id_usuario, agrega un registro en la tabla seriales, y crea la tabla luces para ese serial
                return self::crear();
            }
            else {
                throw new ExcepcionApi(5, "Url mal formada");
            }
        }


    }


    //devuelve los seriales asociados al id_usuario
    private function listar()
    {
        $respuesta = array();
        $body = file_get_contents('php://input');
        $serial = json_decode($body);
        $id_usuario = $serial->id_usuario;
        $comando =  "SELECT "
                    .self::ID_SERIAL.", "
                    .self::SERIAL.", "
                    .self::CANTIDAD
                    ." FROM "
                    .self::NOMBRE_TABLA_SERIALES
                    ." WHERE "
                    .self::ID_USUARIO."=?";
        try {
            $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);
            $sentencia->bindParam(1, $id_usuario);
            $sentencia->execute();
            if ($sentencia) {
                return  [
                    "estado" => 1, "serial" => $sentencia->fetchAll(PDO::FETCH_ASSOC)
                ];
            } else {
                return false;
            }
        }
        catch (PDOException $e) {
            throw new ExcepcionApi(5, $e->getMessage());
        }
    }

    //Esto viene despues de crear un usuario, para asociarlo a su serial. Se llama desde el proceedimiento
    //de crear usuarios, por eso es una funcion publica
    public function crear($id_usuario, $serial){
        //revisa si existe el serial para ese usuario (asume que los seriales no se repiten)
        $check = self::check($id_usuario,$serial);
        if ($check == null) {
            //si no existe, crea el registro en la tabla seriales
            $comando =  "INSERT INTO "
                        .self::NOMBRE_TABLA_SERIALES
                        ."(".self::SERIAL.", "
                        .self::CANTIDAD.", "
                        .self::ID_USUARIO.")"
                        ." VALUES("
                        ."?, "
                        ."4, "
                        ."?)";
            try {
                $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);

            $sentencia->bindParam(1, $serial);
            $sentencia->bindParam(2, $id_usuario);
            $sentencia->execute();
            return true;
            } catch (PDOException $e) {
                throw new ExcepcionApi(15, $e->getMessage());
            }
        } else {
            //si existe se sale
            throw new ExcepcionApi(5, "El serial ya esta asociado con el usuario.");
        }
    }


    private function check($id_usuario, $serial){
        $comando = "SELECT ".self::ID_SERIAL." FROM " . self::NOMBRE_TABLA_SERIALES .
            " WHERE " . self::ID_USUARIO . "=? AND ".self::SERIAL."=?";
        $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);
        $sentencia->bindParam(1, $id_usuario);
        $sentencia->bindParam(2, $serial);
        if ($sentencia->execute())
            return $sentencia->fetch(PDO::FETCH_ASSOC);
        else
            return null;
    }




}