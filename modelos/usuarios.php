<?php
/**
 * Created by PhpStorm.
 * User: Francisco
 * Date: 29-08-2017
 * Time: 8:16
 */


require 'utilidades/ExcepcionApi.php';


class usuarios
{
    // Datos de la tabla "usuarios"
    const NOMBRE_TABLA = "usuarios";
    const ID_USUARIO = "id_usuarios";
    const NOMBRE = "nombres";
    const APELLIDOS = "apellidos";
    const RUT = "rut";
    const CORREO = "correo";
    const LOGIN = "login";
    const PASSWORD = "password";
    const SERIAL = "serial";
    const CLAVE_API = "claveApi";


    //determina la accion a realiza sobre el recurso
    public static function post($peticion)
    {
        if ($peticion[0] == 'registro') {
            return self::registrar();
        } else if ($peticion[0] == 'loguear') {
            return self::loguear();
        } else {
            throw new ExcepcionApi(5, "Url mal formada", 400);
        }
    }



    private function validarContrasena($contrasenaPlana, $contrasenaHash)
    {
        return password_verify($contrasenaPlana, $contrasenaHash);
    }


    private function autenticar($rut, $password)
    {
        $comando = "SELECT password FROM " . self::NOMBRE_TABLA .
            " WHERE " . self::RUT . "=?";

        try {

            $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);
            $sentencia->bindParam(1, $rut);
            $sentencia->execute();

            if ($sentencia) {
                $resultado = $sentencia->fetch();

                if (self::validarContrasena($password, $resultado['contrasena'])) {
                    return true;
                } else return false;
            } else {
                return false;
            }
        } catch (PDOException $e) {
            //throw new ExcepcionApi(self::ESTADO_ERROR_BD, $e->getMessage());
        }
    }

    private function obtenerUsuarioPorRut($rut)
    {
        $comando = "SELECT " .
            self::NOMBRE . "," .
            self::CONTRASENA . "," .
            self::CORREO . "," .
            self::CLAVE_API .
            " FROM " . self::NOMBRE_TABLA .
            " WHERE " . self::RUT . "=?";

        $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);

        $sentencia->bindParam(1, $RUT);

        if ($sentencia->execute())
            return $sentencia->fetch(PDO::FETCH_ASSOC);
        else
            return null;
    }


    private function loguear()
    {
        $respuesta = array();
        print "voya aca";
        $body = file_get_contents('php://input');
        $usuario = json_decode($body);

        $rut = $usuario->rut;
        $password = $usuario->contrasena;


        /*if (self::autenticar($rut, $password)) {
            $usuarioBD = self::obtenerUsuarioPorRut($rut);

            if ($usuarioBD != NULL) {
                http_response_code(200);
                $respuesta["nombre"] = $usuarioBD["nombre"];
                $respuesta["correo"] = $usuarioBD["correo"];
                $respuesta["claveApi"] = $usuarioBD["claveApi"];
                return ["estado" => 1, "usuario" => $respuesta];
            } else {
                //throw new ExcepcionApi(self::ESTADO_FALLA_DESCONOCIDA,"Ha ocurrido un error");
            }
        } else {
            //throw new ExcepcionApi(self::ESTADO_PARAMETROS_INCORRECTOS,
                //utf8_encode("Correo o contraseña inválidos"));
        }*/
    }




}