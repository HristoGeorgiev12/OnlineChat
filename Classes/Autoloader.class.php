<?php
/**
 * Created by PhpStorm.
 * User: Georgievi
 * Date: 10.11.2015 ã.
 * Time: 14:05 ÷.
 */


class Autoloader {
    protected static $paths = array('Classes','Templates');

    public static function autoload($className) {
        foreach(self::$paths as $path) {
            $file = "$path/$className.class.php";
            if(file_exists($file)){
                require_once($file);
            }
        }
    }
}

spl_autoload_register(array('Autoloader', 'autoload'));