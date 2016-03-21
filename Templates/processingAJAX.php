<?php
session_start();
require_once ('../Classes/Connect.class.php');
require_once ('../Classes/Template.class.php');
require_once ('TPLchat.class.php');

require('../pusher-websocket/pusher.php');
$sender_id = $_SESSION["userId"];

//SELECT nickName, favorites.id AS favID FROM users LEFT OUTER JOIN favorites ON favorites.receiver_id = users.id WHERE users.id=2

//Load dynamically the messages into DB;
function loadMessages($sender_id) {
    //Insert via jquery;
    $insertChat = new Template();

    $receiver_id = $_POST["receiver_id"];
    $message = $_POST["processMessage"];


    $insertChat->insert("chat", "chat", array("sender_id"=>$sender_id, "receiver_id"=>$receiver_id, "message"=>$message) );

}

////ид-то да се взима и да се добавя само ред след последното ид;
function displayLastRecordInChat($dataBase,$table,$sender_id, $receiver_id, $lastAdded, $order, $limit ) {
    $db = new Connect($dataBase,$table);
//        chat.sender_id, users.nickName, chat.message
    $connect = $db->connect->prepare("SELECT
                                          chat.id AS id,
                                          users.nickName AS nickName,
                                          chat.receiver_id AS receiver_id,
                                          chat.message AS message,
                                          (SELECT
                                              id
                                              FROM chat
                                              WHERE 1
                                              AND (sender_id=$sender_id OR sender_id=$receiver_id)
                                              AND (receiver_id=$receiver_id OR receiver_id=$sender_id)
                                              ORDER BY id DESC
                                          LIMIT 1) AS higher_id,
                                          EXTRACT(YEAR FROM datetime) year,
                                          EXTRACT(HOUR FROM datetime) hour,
	                                      EXTRACT(MINUTE FROM datetime) minute
                                      FROM ".$table."
                                      INNER JOIN users
                                        ON users.id=$table.sender_id
                                      WHERE 1
                                          AND (sender_id=$sender_id OR sender_id=$receiver_id)
                                          AND (receiver_id=$receiver_id OR receiver_id=$sender_id)
                                          AND chat.id>=$lastAdded
                                      ORDER BY id $order
                                      LIMIT $limit");
    $connect->execute();
    $result = $connect->fetchAll();

    return json_encode($result);
}

//функция която да връща броя непрочетениете съобщения
function countUnreadMessages($dataBase, $table, $sender_id, $lastAdded) {
    $db = new Connect($dataBase,$table);
    $connect = $db->connect->prepare("SELECT MAX(id) AS maxId, sender_id, COUNT(id) AS numberOfRows
                                          FROM ".$table."
                                          WHERE receiver_id=$sender_id
                                          AND chat.id>$lastAdded
                                          GROUP BY sender_id
                                          ORDER BY maxId");
    $connect->execute();
    $result = $connect->fetchAll();
    return json_encode($result);
}

//search engine
function search($dataBase, $table, $searchValue) {
    $db = new Connect($dataBase,$table);

    $connect = $db->connect->prepare("SELECT id, nickName, email, image
                                          FROM $table
                                          WHERE nickName Like '$searchValue%'");
    $connect->execute();
    $result = $connect->fetchAll();

    $jsonArr = array();
    if(!empty($result)) {
        foreach($result as $value) {
            $obj = new stdClass();

            $obj->id = $value['id'];
            $obj->nickName = $value['nickName'];
            $obj->email = $value['email'];
            $obj->image = $value['image'];

            array_push($jsonArr,$obj);
        }
    }else {
        $obj = new stdClass();
        $obj->notFound = "Не е намерен резултат";

        array_push($jsonArr, $obj);
    }

    return json_encode($jsonArr);
}

//selectedUser
function selectedUser($dataBase,$table, $sender_id, $receiver_id) {
    $db = new Connect($dataBase,$table);
    $connect = $db->connect->prepare("    SELECT
                                                u.id user_id,
                                                u.nickName,
                                                u.image,
                                                f.id
                                            FROM users u
                                            LEFT JOIN favorites f ON f.sender_id=$sender_id AND f.receiver_id=$receiver_id
                                            WHERE u.id=$receiver_id
                                            LIMIT 1");



    $connect->execute();
    $result=$connect->fetch();

    return json_encode($result);
}

//добавяне и премахване от списък с приятели
function friendOrStranger($dataBase, $table, $receiver_id ) {
//    $db = new Connect($dataBase,$table);
    $template = new Template();

    $check = $template->selectWhere($dataBase, $table, array('sender_id'=>$_SESSION['userId'], 'receiver_id'=>$receiver_id));

    if($check) {
        $template->delete($dataBase, $table, $_SESSION['userId'], $receiver_id);
        $returnResult = 'Добави';
    }else {
        $template->insert($dataBase, $table, array('sender_id'=>$_SESSION['userId'], 'receiver_id'=>$receiver_id));
        $returnResult = 'Премахни';
    }

    return json_encode($returnResult);
}

//Списък на потребителите;
function userListDisplay($dataBase, $table, $sender_id) {
    $db = new Connect($dataBase,$table);
    $connect = $db->connect->prepare("    SELECT
                                                id,
                                                nickName,
                                                image
                                            FROM users
                                            WHERE id!=$sender_id");


    $connect->execute();
    $result=$connect->fetchAll();

    return json_encode($result);
}

//хронология на чата;
function chatHistory($dataBase, $table, $sender_id, $receiver_id, $inputValue) {
    $db = new Connect($dataBase,$table);

    $connect = $db->connect->prepare("SELECT
                                        t.id,
                                        t.receiver_id,
                                        t.message,
                                        t.datetime,
                                        u.nickName
                                      FROM $table t
                                      INNER JOIN users u
                                      ON sender_id = u.id
                                      WHERE 1
                                       AND ($receiver_id = sender_id OR $sender_id = sender_id)
                                       AND ($receiver_id = receiver_id OR $sender_id = receiver_id)
                                       AND message Like '$inputValue%'");
    $connect->execute();
    $result = $connect->fetchAll();

    if(!$result) {
        $result = 'Няма резултат от търсенето';
    }

    return json_encode($result);
}

//pusher trigger
function triggerPusher($receiver_id, $message) {
    $options = array(
        'encrypted' => false
    );
    $pusher = new Pusher(
        '2cbaeffba3558ad3fc0e',
        'e4883360ebac2d081ab0',
        '189122',
        $options
    );

    $channel = 'channel-'.$receiver_id;
    $data['message'] = $message;
    $pusher->trigger($channel, 'event', $data);

//    $usersData['message']='hoho';
//    $pusher->trigger('users', 'event', $usersData);
}

//check

//история на чата;
if(isset($_POST['chatHistorySearch'])) {
    echo chatHistory('chat','chat', $sender_id, $_POST['receiver_id'], $_POST['keyWord']);
}
//Зареди чат от даден период на хронологията
elseif(isset($_POST['chatSelectedHistory'])) {
    echo displayLastRecordInChat("chat", "chat", $sender_id, $_POST['receiver_id'], $_POST['chatId'], 'ASC', 4);
}
//въвеждане на съобщение в БД
elseif(isset($_POST["processMessage"])) {
    loadMessages($sender_id);
    triggerPusher($_POST['receiver_id'], $_POST["processMessage"]);
}
//търсене на абонат
elseif(isset($_POST['searchText']) && !empty($_POST['searchText'])) {
    echo search('chat', 'users', $_POST['searchText']);
}
//брой на новите съобщения
elseif(isset($_POST['countNewMessages'])) {
    echo countUnreadMessages('chat', 'chat', $sender_id, $_POST['lastInsertedId']);
}
//информация за селектирания потребител
elseif(isset($_POST['selectedUser'])) {
    echo selectedUser('chat', 'users',$sender_id,  $_POST['receiver_id']);
}
//добавяне и премахване от списъка с приятели
elseif(isset($_POST['friendOrStranger'])) {
    echo friendOrStranger('chat', 'favorites', $_POST['receiver_id']);
}
//визуализиране на съобщенията
elseif(isset($_POST['chatDisplay'])) {
    echo displayLastRecordInChat("chat", "chat", $sender_id, $_POST['receiver_id'], $_POST['last'], 'DESC', $_POST['limitRows']);
}
//Динамично зареждане на съобщенията;
elseif(isset($_POST['dynamicChatCheck'])) {
    echo displayLastRecordInChat("chat", "chat", $sender_id, $_POST['receiver_id'], $_POST['last'], 'ASC', 10);
}
//Списък на потребителите
elseif(isset($_POST['userList'])) {
    echo userListDisplay('chat', 'chat', $sender_id);
}
