<?php
/**
 * Created by PhpStorm.
 * User: Francisco
 * Date: 29-08-2017
 * Time: 7:56
 */

class ExcepcionApi extends Exception
{
    public $estado;

    public function __construct($estado, $mensaje)
    {
        $this->estado = $estado;
        $this->message = $mensaje;

    }

}