<?php
/**
 * Created by PhpStorm.
 * User: Francisco
 * Date: 29-08-2017
 * Time: 8:16
 */


require_once 'utilidades/ExcepcionApi.php';


class usuarios
{
    // Datos de la tabla "usuarios"
    const NOMBRE_TABLA = "usuarios";
    const ID_USUARIOS = "id_usuarios";
    const NOMBRES = "nombres";
    const APELLIDOS = "apellidos";
    const RUT = "rut";
    const CORREO = "correo";
    const LOGIN = "login";
    const PASSWORD = "password";

    // Datos de la tabla "seriales"
    const NOMBRE_TABLA_SERIALES = "seriales";
    const ID_SERIAL = "id_serial";
    const SERIAL = "serial";


    //determina la accion a realiza sobre el recurso
    public function post($peticion)
    {
        $accion = array_shift($peticion);

        if ($accion == 'registrar') {
            return self::registrar();
        } else {
            if ($accion == 'loguear') {
                return self::loguear();
            } else {
                throw new ExcepcionApi(5, "Url mal formada");
            }
        }
    }


    private function loguear()
    {

        $respuesta = array();
        $body = file_get_contents('php://input');
        $usuario = json_decode($body);
        $rut = $usuario->rut;
        $password = $usuario->password;

        if (self::autenticar($rut, $password)) {
            $usuarioBD = self::obtenerUsuarioPorRut($rut);
            if ($usuarioBD != NULL) {
                http_response_code(200);
                $respuesta["id_usuarios"] = $usuarioBD["id_usuarios"];
                $respuesta["nombres"] = $usuarioBD["nombres"];
                $respuesta["apellidos"] = $usuarioBD["apellidos"];
                return ["estado" => 1, "usuario" => $respuesta];
            } else {
                throw new ExcepcionApi(5,"Las credenciales no corresponden a ningun usuario registrado");
            }
        } else {
            throw new ExcepcionApi(5,utf8_encode("Correo o contraseña inválidos"));
        }
    }

    //comprobamos si el password es el correcto para el rut entregado
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
                //var_dump($resultado);
                if ($resultado['password'] == $password) {
                    //print "logeado";
                    return true;
                } else {
                    //print "no logeado";
                    return false;
                }
            } else {
                return false;
            }
        }
            catch (PDOException $e) {
            throw new ExcepcionApi(5, $e->getMessage());
        }
    }

    private function obtenerUsuarioPorRut($rut)
    {
        $comando = "SELECT " .self::ID_USUARIOS . "," .self::NOMBRES . "," .self::APELLIDOS . " FROM " . self::NOMBRE_TABLA .
            " WHERE " . self::RUT . "=?";
        $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);
        $sentencia->bindParam(1, $rut);
        if ($sentencia->execute())
            return $sentencia->fetch(PDO::FETCH_ASSOC);
        else
            return null;
    }


    private function checkserial($serial)
    {
        $comando =  "SELECT "
                    .self::ID_SERIAL
                    ." FROM " .self::NOMBRE_TABLA_SERIALES
                    ." WHERE "
                    .self::SERIAL."=?";
        $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);
        $sentencia->bindParam(1, $serial);
        if ($sentencia->execute())
            return $sentencia->fetch(PDO::FETCH_ASSOC);
        else
            return null;
    }

    private function registrar(){
        $respuesta = array();
        $body = file_get_contents('php://input');
        $usuario = json_decode($body);
        $rut = $usuario->rut;
        $password = $usuario->password;
        $serial = $usuario->serial;
        $usuarioenDB = self::obtenerUsuarioPorRut($rut);
        if ($usuarioenDB == NULL) {
            $serialenDB = self::checkserial($serial);
            if ($serialenDB == NULL) {
                try {
                    $comando = "INSERT INTO " . self::NOMBRE_TABLA . " ("
                        . self::RUT . ", "
                        . self::LOGIN . ", "
                        . self::PASSWORD . ")"
                        . "VALUES (?,?,?)";

                    $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);
                    $sentencia->bindParam(1, $rut);
                    $sentencia->bindParam(2, $rut);
                    $sentencia->bindParam(3, $password);
                    $sentencia->execute();
                    $id_usuario = self::obtenerUsuarioPorRut($rut);
                    seriales::crear($id_usuario['id_usuarios'], $serial);
                } catch (PDOException $e) {
                    throw new ExcepcionApi(5, $e->getMessage());
                }
            } else {
                throw new ExcepcionApi(5, "El serial ya existe en el sistema");
            }
        } else {
            throw new ExcepcionApi(5, "El usuario ya existe en el sistema");
        }

    }
}