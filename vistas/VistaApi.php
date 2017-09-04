<?php
/**
 * Created by PhpStorm.
 * User: Francisco
 * Date: 29-08-2017
 * Time: 7:11
 */

abstract class VistaApi
{
    public $httpcodigo;
    public $cuerpo;

    public abstract function imprimir($cuerpo);
}

