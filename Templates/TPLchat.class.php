<?php
/**
 * Created by PhpStorm.
 * User: Georgievi
 * Date: 12.11.2015 г.
 * Time: 18:19 ч.
 */

//require('../pusher-websocket/pusher.php');
//
//$options = array(
//    'encrypted' => true
//);
//$pusher = new Pusher(
//    '2cbaeffba3558ad3fc0e',
//    'e4883360ebac2d081ab0',
//    '189122',
//    $options
//);
//
//$data['message'] = 'hello world';
//$pusher->trigger('test_channel', 'my_event', $data);
//


class TPLchat extends Template {




    public $receiver_id;

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
        $connect = $db->connect->prepare("SELECT *, $table.id AS id
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

    public function Title() {
        return "Chat";
    }

    public function Body() {
            $userNickName =  $_SESSION['successfulLogin'];
            $userId =  $_SESSION['userId'];
            $receiver_id = 0;


        if(isset($this->aParam["receiver_id"])) {
            $receiver_id  = $this->aParam["receiver_id"];
        }

//       Добави към списък с фаворити;
        if(isset($this->aParam["addToFavoriteList"])) {
                $this->insert("chat", "favorites", array("sender_id"=>$userId, "receiver_id"=>$receiver_id));
        }

//        Премахни от списък с фаворити;
        if(isset($this->aParam["removeFromFavorites"])) {
            $this->delete("chat", "favorites", $userId, $receiver_id);
        }


        if(isset($this->aParam['messageSubmit'])) {
            $insertParams = array();
            $insertParams["sender_id"]=$userId;
            $insertParams["receiver_id"]=$receiver_id;
            $insertParams["message"]=$this->aParam['message'];


            if(!empty($this->aParam['message'])) {
                $this->insert('chat', 'chat', $insertParams);
            }
        }
        ?>

        <div class="container" id="wrapper">
            <div class="row">

                <div class="col-md-7"  id="chatBox">
                    <!--Визуализация на профила на селектирания от чат списъка -->
                    <div class="interlocutor well well-sm" id="selectedUser">

                    </div><!--END of selectedUser -->

                    <!--Търсачка на Хронология-->
                    <div id="historySearchDiv" class="input-group well well-sm" style="display:none">
                        <input id="chatHistory" type="text" class="form-control" placeholder="Хронология...">
                          <span class="input-group-btn ">
                            <button id="chatHistorySearch" class="btn btn-default" type="button">
                                Търси
                            </button>
                          </span>
                    </div>

                    <!--чат прозорец-->
                    <div id="chat" class="well well-sm">
                        <!-- Бутон за отваряне на още хронология 'Нагоре'-->
                        <div id="showMoreUp"><button class="btn btn-link">Покажи нагоре</button></div>


                        <div id='noCorrespondence' class='well well-sm'>Не е открита кореспонденция със селектирания потребител</div>

                        <!--Визуализиране на съобщенията -->
                        <div id="displayMessages">

                        </div><!--END of displayMessages-->

                        <!-- Бутон за отваряне на още хронология 'Надолу'-->
                        <div id="showMoreDown"><button class="btn btn-link">Покажи надолу</button></div>

                    </div>


                    <div id="text"></div>
                    <div id="overview"></div>
                    <!--        Изпращане ан съобщения-->
                    <div class="well well-sm" id="sendMessages">
                            <textarea id="message" class="form-control"></textarea>
                            <button id="emoticonOverViewButton" class="btn btn-default">
                                <img src="images/mensagens-de-texto-01.png" alt="emoticon overview">
                            </button>

                    </div><!--  sendMessages-->


                </div><!--  END of chatBox-->

                <div class="col-md-5" id="options">
                    <nav class="navbar navbar-default">
                        <div class="container-fluid">
                            <ul class="nav navbar-nav">
                                <li class="dropdown"><a class="dropdown-toggle" data-toggle="dropdown" href="#">Промяна на профил<span class="caret"></span></a>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a id="profileImgTrigger" href="#" onclick="">Снимка</a>
                                        </li>
                                        <li>
                                            <a href="#">Мисли</a>
                                        </li>
                                    </ul>
                                </li>
                                <li class="dropdown"><a class="dropdown-toggle" data-toggle="dropdown" href="#">Теми<span class="caret"></span></a>
                                    <ul class="dropdown-menu">
                                        <li><a href="#">Цветове</a></li>
                                        <li><a href="#">Шрипт</a></li>
                                    </ul>
                                </li>
                            </ul>
                            <ul class="nav navbar-nav navbar-right">
                                <li><a href="?page=login&logOut=true">Изход <span class="glyphicon glyphicon-log-out"></span></a></li>
                            </ul>
                        </div>
                    </nav>


<!--                        Профилна снимка и мисли-->
                        <?php
                        if(isset($_FILES["image"])) {
                            //провери дали избраният файл е изображение
                            $fileTypeCheck = getimagesize($_FILES["image"]["tmp_name"]);
                            if(!$fileTypeCheck) {
                                echo  "<script>alert('Не сте избрали изображение')</script>";
                            }else {
                                $s = base64_encode(file_get_contents($_FILES["image"]["tmp_name"]));
                                $image = addslashes('data: '.mime_content_type($_FILES["image"]["tmp_name"]).';base64,'.$s);
                                $imageName = addslashes($_FILES["image"]["name"]);
                                $this->update("chat","users",array("image"=>$image, "imageName"=>$imageName), $userId);
                            }
                        }

                        if(isset($this->aParam['thoughtsSubmit'])) {
                            $this->update("chat","users",array('thoughts'=>$this->aParam['thoughtsText']),$userId);
                        }

                        ?>
                    <!--                Профил-->
                    <div class="well well-sm" id="profile">
                        <a href="<?php echo $this->selectWhere("chat","users",array("id"=>$userId), false)["image"];?>" data-lightbox="profilePicture">
                            <img id="profilePicture" class="img-circle" src="<?php echo $this->selectWhere("chat","users",array("id"=>$userId), false)["image"];?>" alt="picture" style="width:128px;height:128px" />
                        </a>


                        <!--                        Споделете нещо за вас тук -->

<!--                        <label>--><?php //echo $this->selectWhere("chat", "users", array("id"=>$userId))["thoughts"];?><!--</label>-->
<!--                        <form id="share" action="" method="post">-->
<!--                            <textarea class="form-control" name="thoughtsText" placeholder="Представете се..">--><?php //echo $this->selectWhere("chat", "users", array("id"=>$userId))["thoughts"];?><!--</textarea>-->
<!--                            <input class="btn btn-link" type="submit" name="thoughtsSubmit">-->
<!--                        </form>-->
                    </div><!--  END of profile-->



<!--                        Качване на снимка-->
                        <form id="profileImgForm" action="" method="post" enctype="multipart/form-data">

                            <input type="file"
                                   id="profileImg"
                                   name="image">
                        </form>



<!--                    </div><!--  END of profile-->

                    <!--                Търсачка-->
                    <div class="well well-sm">
                        <div id="search">
                            <input
                                type="text"
                                id="searchText"
                                class="typeahead form-control"
                                name="searchText"
                                data-provide="typeahead"
                                autocomplete="off"
                                placeholder="Търсене на потребители..."
                                >

                            <div id="liveSearch"></div>
                        </div><!-- END of search-->
                    </div>

                    <!--Бутони за навигиране през различните списъци-->
                    <div id="selectListOfUsers" class="fixed">
                        <button id="friends" class="active btn btn-link">Познати</button>
                        <button id="strangers" class="btn btn-link">Непознати</button>
                    </div>


                    <!--Списък с абонати-->
                    <div id="usersList" class="tab-content well well-sm">
                        <img src="images/loading.gif" alt="loading" id="loading_image" style="display: none" >
                    </div>
                </div>
            </div>
        </div>
        <script>
            var user_id = <?php echo $userId;?>;
            var user_nickName = '<?php echo $userNickName;?>';
            var receiver_id=<?php echo isset($_GET['selected_user'])? $_GET["selected_user"]: 0 ?>;

//
//            // Enable pusher logging - don't include this in production
//            Pusher.log = function(message) {
//                if (window.console && window.console.log) {
//                    window.console.log(message);
//                }
//            };
//
//            var pusher = new Pusher('2cbaeffba3558ad3fc0e');
//
//            var channel = pusher.subscribe('channel-'+user_id);
//            channel.bind('event', function(data) {
//
//                $('#displayMessages').append('<div class="well well-sm" style="color:black">'+data.message+'</div>');
//
//                alert(data.message);
//            });
//










            //ид-то на последния ред на БД-'chat'
            <?php function returnLastId() {
                $db = new Connect("chat", "chat");
                $connect = $db->connect->prepare("SELECT id FROM chat ORDER BY id DESC");
                $connect->execute();
                return $connect->fetch()['id'];
            }?>
            var lastInsertedId =<?php echo returnLastId();?>;





//            //Съобщенията от чата
//            function loadMoreFromChat() {
//                $.ajax({
//                    type: 'POST',
//                    url: 'Templates/processingAJAX.php',
//                    data: {
//                        receiver_id: receiver_id,
//                        last: lastIdAdded,
//                        limitRows: limitRows,
//                        chatDisplay: true
//                    },
//                    success: function(data) {
//                        var json = JSON.parse(data);
//
//                        $('#noCorrespondence').css('display', (receiver_id==='' || json.length===0)? 'block' : 'none');
//
//                        for(var i=numberOfRows; i<json.length; i++) {
//
//                            chatDivStyle(json[i]);
//                        }
//
//                        numberOfRows+=20;
//                        limitRows+=20;
//
//                        if(json.length>numberOfRows || json.length===numberOfRows ) {
//                            $('#showMoreUp').css('display','inline-block');
//                        }else{
//                            $('#showMoreUp').css('display','none');
//                        }
//
//                    }
//                })
//
//            }

//                $(document).ready(function() {
//                    if(receiver_id) {
//                        userInformation();
//                        loadMoreFromChat();
//                        $("#showMoreUp").on('click', function() {
//                            loadMoreFromChat();
//                        });
//                        $('#showMoreDown').on('click', function() {
//                            console.log('show id '+countTheId);
//                            showMoreChatDown(countTheId);
//                        });
//                    }
//                    ajaxUserList('#'+$('#selectListOfUsers .active').attr('id'));
//                });


//            $(document).on('click', '#chatHistoryToggle',function() {
//                $('#historySearchDiv').slideToggle();
//                $(this).find('span').toggleClass('glyphicon-chevron-down glyphicon-chevron-up ');
//            });


            //Търси хронология на чата;
//            $(document).on('click', '#chatHistorySearch',function() {
//                $.ajax({
//                    type: 'POST',
//                    url: 'Templates/processingAJAX.php',
//                    data: {
//                        receiver_id: receiver_id,
//                        keyWord: $('#chatHistory').val(),
//                        chatHistorySearch: true
//                    },
//                    success: function(data) {
//                        var json = JSON.parse(data);
//                        $('#displayMessages').html('');
//                        $.each(json,function(i) {
//                            var id = json[i]['id'];
//                            var nickName = '<label>'+json[i]['nickName']+'</label> ~ ';
//                            var datetime = json[i]['datetime']+'</br>';
//                            var message = json[i]['message'];
//                            $('#displayMessages').append("<div data-history-id='"+id+"' class='history well well-sm'>"+nickName+datetime+message+"</div>");
//
//                        })
//                    }
//                })
//            })

//            function showMoreChatDown(chatId) {
//                $.ajax({
//                    type: 'POST',
//                    url: 'Templates/processingAJAX.php',
//                    data: {
//                        receiver_id: receiver_id,
//                        chatId: chatId,
//                        chatSelectedHistory: true
//                    },
//                    success: function(data) {
//                        var json = JSON.parse(data);
//
//                        $.each(json,function(i) {
//                            chatDivStyle(json[i], false);
//                            if(json.length>=4) {
//                                $('#showMoreDown').css('display','inline-block');
//                            }else {
//                                $('#showMoreDown').css('display','none');
//                            }
//                        })
//                    }
//                })
//            }



//Индокатори за съобщения
//            //Индикатори за нови непрочетени съобщения;
//            var identifier='';
//            //масив който пази непрочетените съобщения;
//            var aUnreadMessages=[];
//            var interval = setInterval(function() {
//                $.ajax({
//                    type: 'POST',
//                    url: 'Templates/processingAJAX.php',
//                    data: {
//                        receiver_id: receiver_id,
//                        lastInsertedId: lastInsertedId,
//                        countNewMessages: true
//                    },
//                    success: function(data) {
//                        var json = JSON.parse(data);
//
//                        $.each(json, function(i) {
//                            var result=0;
//                            var sender_id = json[i]['sender_id'];
//                            identifier = '#'+json[i]['sender_id'];
//                            var numbersOfMessages = parseInt(json[i]['numRows']);
//
//                            if(sender_id in aUnreadMessages) {
//                                aUnreadMessages[sender_id] += numbersOfMessages;
//                                result=aUnreadMessages[sender_id];
//                            }else {
//                                result = numbersOfMessages;
//                            }
//
//                            aUnreadMessages[sender_id]=result;
//
//                            $('#usersList a '+identifier).html(result);
//                            lastInsertedId = json[i]['maxId'];
//                        });
//                    }
//                })
//
//            }, 4000);

//TODO: at the bottom of the chat window

            /*var d = $("#displayMessages");
            d.scrollTop(d.prop("scrollHeight"));*/

//            $("#displayMessages").scrollTop($("#displayMessages")[0].scrollHeight);
//
//            alert($("#displayMessages").height());

//            var objDiv = document.getElementById("#displayMessages");
//            objDiv.scrollTop = objDiv.scrollHeight;






        </script>



        <?php
    }
}