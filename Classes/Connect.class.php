<?php
/**
 * Created by PhpStorm.
 * User: Georgievi
 * Date: 17.8.2015 �.
 * Time: 21:42 �.
 */

class Connect {
    public $connect;
    protected $database;
    public $table;

    public function __construct($database, $table) {
        $this->connect = new PDO('mysql:host=localhost;dbname='.$database.';charset=utf8', 'root', '');
        $this->connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->table = $table;
    }

    //Insert method;
    public function implodeInsertedData($insertParams) {
        if(isset($insertParams)) {
            //Implode keys;
            $keyParam = implode(', ', array_keys($insertParams));

            //Implode values
            $valueConcatArray = array();
            foreach ($insertParams as $key => $value) {
                $valueConcatArray[] = " :" . $key;
            }
            $valueParam = implode(',', $valueConcatArray);

            $array = array($keyParam, $valueParam);
            return $array;
        }

    }
}