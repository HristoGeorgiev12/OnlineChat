<?php
session_start();
require_once ('../Classes/Connect.class.php');
require_once ('../Classes/Template.class.php');
require_once ('TPLchat.class.php');


//Insert via jquery;
    $insertChat = new Template();
    $message = $_POST["processMessage"];
    $sender_id = $_SESSION["userId"];
    $receiver_id = $_POST["receiver_id"];

    $lastId = $insertChat->insert("chat", "chat", array("sender_id"=>$sender_id, "receiver_id"=>$receiver_id, "message"=>$message) );

//
//$lastAdded = null;
//if(isset($_SESSION["lastAdded"])) {
//    $lastAdded = $_SESSION["lastAdded"];
//}

$lastAdded = $_GET['last'];

$displayChat = new TPLchat();
//$result = $displayChat->displayChat("chat", "chat", 1, 4 );

////ид-то да се взима и да се добавя само ред след последното ид;
 function displayLastRecordInChat($dataBase,$table,$sender_id, $receiver_id, $lastAdded) {
    $db = new Connect($dataBase,$table);
//        chat.sender_id, users.nickName, chat.message
    $connect = $db->connect->prepare("SELECT *
                                          FROM ".$table."
                                          INNER JOIN users
                                          ON users.id=$table.sender_id
                                          WHERE (sender_id=$sender_id OR sender_id=$receiver_id)
                                          AND (receiver_id=$receiver_id OR receiver_id=$sender_id)
                                          AND chat.id>$lastAdded");
    $connect->execute();
    return $connect->fetchAll();
}

$result = displayLastRecordInChat("chat", "chat", 1, 4, $lastAdded);

$jsonArr = array();
foreach($result as $value) {
//    $value["nickName"].": ".$value["message"]."<br>";
    $jsonArr["message"]= $value["id"].$value["nickName"].": ".$value["message"].$value['datetime'];
//    $jsonArr["message"] = $value['datetime'];
//   $_SESSION['lastAdded'] = $value['id'];
//    $jsonArr["id"] = $value['id'];
//    $jsonArr["nick"] = $value['nickName'];
}

echo  json_encode($jsonArr);

//$favorites = $this->displayFavorites("chat", "favorites", $userId, $this->aParam["receiver_id"]);


