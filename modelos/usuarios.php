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

        switch ($accion) {
            case 'registrar':
                return self::registrar();
            break;

            case 'loguear';
                return self::loguear();
            break;

            case 'mostrar';
                return self::mostrar();
            break;

            default:
                throw new ExcepcionApi(5, utf8_encode("Url mal formada"));
            break;
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

    //determina los datos del usuario segun el rut
    private function obtenerUsuarioPorRut($rut)
    {
        $comando = "SELECT " .self::ID_USUARIOS . "," .self::RUT . "," . self::NOMBRES . "," .self::APELLIDOS . " FROM " . self::NOMBRE_TABLA .
            " WHERE " . self::RUT . "=?";
        $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);
        $sentencia->bindParam(1, $rut);
        if ($sentencia->execute()){
            return $sentencia->fetch(PDO::FETCH_ASSOC);
        } else {
            return null;
        }
    }

    //determina si un serial ya esta registrado
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

    //registra a un nuevo usuario en la base de datos
    private function registrar()
    {
        $body = file_get_contents('php://input');
        $usuario = json_decode($body);
        $rut = $usuario->rut;
        $serial = $usuario->serial;

        //determinamos si el usuario ya existe
        $usuarioenDB = self::obtenerUsuarioPorRut($rut);
        if ($usuarioenDB == NULL)
        {
            //determinamos si el serial ya esta registrado
            $serialenDB = self::checkserial($serial);
            if ($serialenDB == NULL)
            {
                $nombre = $usuario->nombre;
                $correo = $usuario->correo;
                $password = $usuario->password;
                $respuesta = array();
                $comando = "INSERT INTO " . self::NOMBRE_TABLA . " ("
                    . self::NOMBRES . ", "
                    . self::CORREO . ", "
                    . self::RUT . ", "
                    . self::LOGIN . ", "
                    . self::PASSWORD . ")"
                    . "VALUES (?,?,?,?,?)";
                try
                {
                    $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);
                    $sentencia->bindParam(1, $nombre);
                    $sentencia->bindParam(2, $correo);
                    $sentencia->bindParam(3, $rut);
                    $sentencia->bindParam(4, $rut);
                    $sentencia->bindParam(5, $password);
                    $sentencia->execute();
                } catch (PDOException $exception) {
                    throw new ExcepcionApi(10, $exception->getMessage());
                }
                //una vez ejecutado el insert en la tabla de usuarios, obtenemos que id le corresponde
                //y se ejecuta el insert en la tabla de seriales
                $id_usuario = self::obtenerUsuarioPorRut($rut);
                seriales::crear($id_usuario['id_usuarios'], $serial);
                http_response_code(200);
                $respuesta["rut"] = $id_usuario["rut"];
                $respuesta["nombres"] = $id_usuario["nombres"];
                $respuesta["apellidos"] = $id_usuario["apellidos"];
                return ["rut" => $respuesta["rut"], "mensaje" => "Usuario creado de forma exitosa"];
            } else {
                throw new ExcepcionApi(5, "El serial ya existe en el sistema");
            }
        } else {
            throw new ExcepcionApi(5, "El rut del usuario ya existe en el sistema");
        }
    }

    //permite el ingreso de un usuario al sistema
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

    //permite el ingreso de un usuario al sistema
    private function mostrar()
    {
        $respuesta = array();
        $body = file_get_contents('php://input');
        $usuario = json_decode($body);
        $rut = $usuario->rut;
        $usuarioBD = self::obtenerUsuarioPorRut($rut);
        if ($usuarioBD != NULL)
        {
            $seriales = seriales::obtenerSerialesPorIdUsuario($usuarioBD["id_usuarios"]);
            if ($seriales != null)
            {
                $respuesta = luces::obtenerLucesporIdSerial($seriales["id_serial"]);
                http_response_code(200);
                return ["estado" => 1, "usuario" => $respuesta];
            } else {
                throw new ExcepcionApi(5,"El serial no esta registrado en el sistema");
            }
        } else {
            throw new ExcepcionApi(5,"El rut no esta registrado en el sistema");
        }
    }


}