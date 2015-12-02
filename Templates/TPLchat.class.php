<?php
/**
 * Created by PhpStorm.
 * User: Georgievi
 * Date: 12.11.2015 г.
 * Time: 18:19 ч.
 */

class TPLchat extends Template {



    public $receiver_id;

    //Търсене на абонати в база данни
    private function search() {
        if(isset($this->aParam["searchSubmit"])) {
            $search=array();
            $search['nickName']=$this->aParam["searchText"];

            $result=$this->selectWhere("chat", "users", $search);
            return $result["nickName"];
        }

    }

    //Масив от потребители;
    private function displayUsers($dataBase, $table, $insertArray) {
        $db = new Connect($dataBase,$table);

        $queryArray = array("id!={$_SESSION['userId']}");
//        $queryArray[] = "id!=1";
        foreach ($insertArray as $key => $value) {
            $queryArray[] ="id!='{$value["receiver_id"]}'";
        }
        $queryArray = implode(' AND ', $queryArray);

        $connect = $db->connect->prepare("SELECT *
                                          FROM $table
                                          WHERE $queryArray" );
        $connect->execute();
        return $connect->fetchAll();
    }

    //Визуализиране на чата;
    public function displayChat($dataBase,$table,$sender_id, $receiver_id) {
        $db = new Connect($dataBase,$table);
//        chat.sender_id, users.nickName, chat.message
        $connect = $db->connect->prepare("SELECT *
                                          FROM ".$table."
                                          INNER JOIN users
                                          ON users.id=$table.sender_id
                                          WHERE (sender_id=$sender_id OR sender_id=$receiver_id)
                                          AND (receiver_id=$receiver_id OR receiver_id=$sender_id)");
        $connect->execute();
        return $connect->fetchAll();
    }

    //Визуализиране на чата;
    private function displayFavorites($dataBase,$table,$sender_id, $receiver_id) {
        $db = new Connect($dataBase,$table);
//        chat.sender_id, users.nickName, chat.message
        $connect = $db->connect->prepare("SELECT *
                                          FROM ".$table."
                                          INNER JOIN users
                                          ON users.id=$table.receiver_id
                                          WHERE sender_id=$sender_id");
        $connect->execute();
        return $connect->fetchAll();
    }

//    Добавяне на приятелство
//    private function addToFavoriteList($sender_id, $receiver_id) {
//        $insertParams = array();
//        $insertParams["sender_id"]=$sender_id;
//        $insertParams["receiver_id"]=$receiver_id;
//
//        $query = $this->insert("chat", "friendships", $insertParams);
//
//    }


    public function Title() {
        return "Chat";
    }

    public function Body() {
            $userNickName =  $_SESSION['successfulLogin'];
            $userId =  $_SESSION['userId'];


        if(!isset($this->aParam["receiver_id"])) {
            $this->aParam["receiver_id"] = null;
        }else {
            $receiver_id  = $this->aParam["receiver_id"];
        }

        echo $userNickName;
        echo  $userId;
        ?>
        <?php
        if(isset($this->aParam["addToFavoriteList"])) {
            echo "hello";
//            $this->addToFavoriteList($userId,$this->aParam["receiver_id"]);
                $this->insert("chat", "favorites", array("sender_id"=>$userId, "receiver_id"=>$this->aParam["receiver_id"]));
        }

        if(isset($this->aParam["removeFromFavorites"])) {
            $this->delete("chat", "favorites", $userId, $this->aParam["receiver_id"]);
        }


        if(isset($this->aParam['messageSubmit'])) {
            $insertParams = array();
            $insertParams["sender_id"]=$userId;
            $insertParams["receiver_id"]=$this->aParam['receiver_id'];
            $insertParams["message"]=$this->aParam['message'];


            if(!empty($this->aParam['message'])) {
                $this->insert('chat', 'chat', $insertParams);
            }
        }
        ?>

        <div id="wrapper">
            <div id="chatBox">
                <!--Визуализация на профила на селектирания от чат списъка -->
                <div id="selectedUser">
                    <!--Добавяне на приятел-->
                    <?php
                        $selectedUser = $this->selectWhere("chat", "users", array("id"=>$this->aParam['receiver_id']), false);
                        echo ' <img src="'.$selectedUser["image"].'" alt="picture" style="width:60px;height:60px" />'.$selectedUser["nickName"].$selectedUser["thoughts"];
                        //                    Ако върне резултат инициализирай "Премахване на приятел", ако не върне нищо "Добаване на приятел"
                        if($this->selectWhere("chat", "favorites", array("sender_id"=>$userId, "receiver_id"=>$this->aParam["receiver_id"]) )) {
                    ?>
                        <form action="" method="post">
                            <input type="submit"
                                   name="removeFromFavorites"
                                   value="Премахни приятел">
                        </form>
                    <?php
                        }else {
                    ?>
                        <form action="" method="post">
                            <input type="submit"
                                   name="addToFavoriteList"
                                   value="Добави към приятели">
                        </form>
                    <?php
                        }
                    ?>
                </div><!--END of selectedUser -->
                <!--        Визуализиране на съобщенията -->
                <div id="displayMessages">
<!--                    --><?php
                        if(isset($this->aParam["receiver_id"])) {
                            $chatMessages =  $this->displayChat("chat", "chat", $userId, $this->aParam["receiver_id"]);
                            foreach($chatMessages as $key=>$value) {
                                echo $value["nickName"].": ".$value["message"]."<br>";
                            }
                        }
//                    ?>
                </div><!--END of displayMessages-->
                 <!--        Изпращане ан съобщения-->
                <div id="sendMessages">
<!--                    <form action="" method="post">-->
                        <input type="text"
                               id="message"
                               placeholder="Напиши съобщението си тук.."
                               required>
                        <input type="submit"
                               id="messageSubmit"
                               value="Изпрати">
<!--                    </form>-->
                </div><!--  sendMessages-->
            </div><!--  END of chatBox-->
            <div id="options">
                <div id="profile">
                    <!--            Качване на снимка -->
                    <form action="?page=chat" method="post" enctype="multipart/form-data">
                        <input type="file"
                               name="image">
                        <input type="submit"
                               name="imageSubmit"
                               value="Качи снимката">
                    </form>
                    <?php
                    //        TODO: Insert and display images;
                                    if(isset($_POST["imageSubmit"])) {
                                        //провери дали избраният файл е изображение
                                        $fileTypeCheck = getimagesize($_FILES["image"]["tmp_name"]);
                                        if(!$fileTypeCheck) {
                                            echo "Не сте избрали изображение";
                                        }else {
                                            $s = base64_encode(file_get_contents($_FILES["image"]["tmp_name"]));
                                            $image = addslashes('data: '.mime_content_type($_FILES["image"]["tmp_name"]).';base64,'.$s);
                                            $imageName = addslashes($_FILES["image"]["name"]);
                                            $this->update("chat","users",array("image"=>$image, "imageName"=>$imageName), 4);
                                        }
                                    }

//                                $imageSelect=$this->selectWhere("chat","users",array("id"=>$userId), false)["image"];

//                            var_dump($imageSelectArr);

                    //                header("Content-type:image/jpeg");

//                                    echo $imageSelect;
//                                    echo "Image:<img src='{$imageSelect}' height='100' width='100'>";
//        echo '<img src="data:image/jpeg;base64,'.base64_encode( $imageSelectArr[16]["image"] ).'"/>';
                    if(isset($this->aParam['thoughtsSubmit'])) {
                        $this->update("chat","users",array('thoughts'=>$this->aParam['thoughtsText']),$userId);
                    }

                    ?>
                    <img src="<?php echo $this->selectWhere("chat","users",array("id"=>$userId), false)["image"];?>" alt="picture" style="width:128px;height:128px" />
                    <!--        Споделете нещо за вас тук -->
                    <form action="" method="post">
                        <textarea name="thoughtsText" placeholder="Представете се.."><?php echo $this->selectWhere("chat", "users", array("id"=>$userId))["thoughts"];?></textarea>
                        <input type="submit" name="thoughtsSubmit">
                    </form>
                    <!--            Излизане от профила-->
                    <form action="?page=login" method="post">
                        <!--            Testing logout-->
                        <!--             TODO: make logout as a link without using form and submit input-->
                        <input type="submit"
                               name="logout"
                               value="Излез от профила">
                    </form>
                </div><!--  END of profile-->

                <div id="search">
                    <!--                Търсене на абонати -->
                    <form action="" method="post">
                        <input type="text"
                               name="searchText"
                               placeholder="Търсене"
                               required>
                        <input type="submit"
                               name="searchSubmit"
                               value="Търси">
                    </form>
                    <?php
                    if(isset($this->aParam['searchSubmit'])) {
                        if($this->search()) {
                            echo "Намерен е търсеният абонат";
                            echo $this->search();
                        }else {
                            echo "Няма такъв абонат";
                        }
                    }

                    ?>


                </div><!-- END of search-->
                <div id="usersList">
                    <p id="strangersList">Списък с недобавени абонати</p>
                    <!--            Списък с абонати -->
                    <?php
                    $display=$this->selectWhere("chat", "favorites",array("sender_id"=>$userId), true);
                    $displayUsers=$this->displayUsers("chat","users", $display);
//                            var_dump($selectWhere);
                    foreach($displayUsers as $value) {
                        echo ' <img src="'.$this->selectWhere("chat","users",array("id"=>$value["id"]), false)["image"].'" alt="picture" style="width:28px;height:28px" />'."<a href='?page=chat&receiver_id={$value['id']}'>".$value['nickName']."</a><br>";
                    }
                    ?>

                    <p id="friendsList">Списък на приятели</p>
                    <?php
//                        $favorites = $this->selectWhere("chat", "favorites", array("sender_id"=>$userId),true);
//                    $favorites = $this->displayFavorites("chat", "favorites", $userId, $this->aParam["receiver_id"]);
//                        var_dump($favorites);
//                        foreach($favorites as $value) {
//                            echo ' <img src="'.$this->selectWhere("chat","users",array("id"=>$value["id"]), false)["image"].'" alt="picture" style="width:28px;height:28px" />'."<a href='?page=chat&receiver_id={$value['id']}'>".$value['nickName']."</a><br>";
//                        }
                    ?>

                </div><!--END of usersList-->
            </div><!-- END of options-->

        </div><!--END of Wrapper-->
        <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
        <script>
//          display chat conversation dynamically
            var interval = setInterval(function() {

                var date;
                $.ajax({
                    success: function(data) {
                        var json = JSON.parse(data);
                        $.each(JSON.parse(data), function() {
                            $('#displayMessages').append(json.message+"<br>");
                            date= json.id;
                        });
                    },
                    type: 'GET',
                    url: 'Templates/processingAJAX.php?last='+date

                })
            }, 1000);

//            insert data via ajax and jquery;
            $("#messageSubmit").on('click', function() {
                $.ajax({
                    type: 'POST',
                    url: 'Templates/processingAJAX.php',
                    data: {
                        processMessage:$("#message").val(),
                        receiver_id:'<?php echo $this->aParam["receiver_id"];?>'
                    },
                    success: function(data) {
                         $('#displayMessages').append($("#message").val()+ "<br>");
                    }
                })
            });

$("#friendsList").on('click', function() {
    $.ajax({
        type: 'GET',
        url: 'Templates/processingAJAX.php',
//        data: {
//            processMessage:$("#message").val(),
//            receiver_id:'<?php //echo $this->aParam["receiver_id"];?>//'
//        },
        success: function(data) {
            $('#displayMessages').append($("#message").val()+ "<br>");
        }
    })
});

            $("#displayMessages"). scrollTop($(document).height());
        </script>

        <?php
    }
}