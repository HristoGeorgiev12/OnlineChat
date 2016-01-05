<?php
/**
 * Created by PhpStorm.
 * User: Georgievi
 * Date: 10.11.2015 ã.
 * Time: 14:05 ÷.
 */

class Template {

    //merge Get and Post into one array;
    public $aParam=array();

    //check if GET isset or not;
    public function getParam($key) {
        return isset($this->aParams[$key])? $this->aParams[$key] : "";
    }

    public function setParams($parameters) {
        $this->aParam = $parameters;
    }



    //Select method;
    protected function select($dataBase,$table) {
        $db = new Connect($dataBase,$table);

        $connect = $db->connect->prepare("SELECT *
                                          FROM $table" );
        $connect->execute();
        return $connect->fetchAll();

    }


    //Select method;
    protected function selectWhere($dataBase,$table,$insertArray, $fetchAll=false) {
        $db = new Connect($dataBase,$table);

        $queryArray = array();
        foreach ($insertArray as $key => $value) {
            $queryArray[] = $key . "='{$value}'";
        }
        $queryArray = implode(' AND ', $queryArray);

        $connect = $db->connect->prepare("SELECT *
                                          FROM  $table
                                          WHERE $queryArray");

        $connect->execute();
        return !$fetchAll ? $connect->fetch() : $connect->fetchAll();
//        if(!$fetchAll)
//            return $connect->fetch();
//        else
//            return $connect->fetchAll();
    }



    //Insert data in the corresponding database and table;
    public function insert($dataBase, $table, $insertArray) {
        $db = new Connect($dataBase,$table);

        $connectInsert = $db->implodeInsertedData($insertArray);
        $keyParam = $connectInsert[0];
        $valueParam = $connectInsert[1];


        $connect = $db->connect->prepare("INSERT INTO
                                          $table" ." (".$keyParam.")
										  VALUES(".$valueParam.")");
        $connect->execute($insertArray);
    }

    //Implode the insert method;
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

    //update via arrays
    protected function update($dataBase, $table, $insertArray, $userId) {
        $db = new Connect($dataBase,$table);

        $queryArray = array();
        foreach ($insertArray as $key => $value) {
            $queryArray[] = $key . "='{$value}'";
        }
        $queryArray = implode(', ', $queryArray);

        $update =$db->connect->prepare("UPDATE {$table} SET {$queryArray} WHERE id={$userId}");

        $update->execute();

        return $insertArray;
    }

    protected function delete($dataBase, $table, $sender_id, $receiver_id) {
        $db = new Connect($dataBase,$table);

        $delete =$db->connect->prepare("DELETE FROM $table
                                        WHERE sender_id=$sender_id
                                        AND receiver_id=$receiver_id;");

        $delete->execute();
    }

    protected function Title() {
        return "Index";
    }

    protected function Body() {
        ?>
            <h1>Testing</h1>
        <?php
    }

    public function HTML() {
        ?>
            <html>
                <head>
                    <!-- Meta-->
                    <meta name="viewport" content="width=device-width, initial-scale=1">
                    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<!--                    Title-->
                    <title><?php echo $this->Title();?></title>

<!--                    CSS-->
                    <link rel="stylesheet" type="text/css" href="CSS/styles.css">
                    <link rel="stylesheet" type="text/css" href="CSS/bootstrap.css">

<!--                    JS-->
                    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
                    <script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>

                </head>
                <body>
                    <?php $this->Body();?>
                </body>
            </html>
        <?php
    }
}