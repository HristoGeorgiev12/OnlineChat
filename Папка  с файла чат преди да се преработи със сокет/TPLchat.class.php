<?php
/**
 * Created by PhpStorm.
 * User: Georgievi
 * Date: 12.11.2015 г.
 * Time: 18:19 ч.
 */

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
                            <button id="emoticonOverViewButton" class="btn btn-default"><img src="images/mensagens-de-texto-01.png" alt="emoticon overview"></button>
<!--                            <button id="submit" class="btn btn-default"><span class="glyphicon glyphicon-search"></span></button>-->
<!--                        TODO: edit the submit button to become a emoticon select button-->
<!--                            <button type="submit" id="messageSubmit" class="btn btn-link pull-right">-->
<!--                                <span class="glyphicon glyphicon-send"></span>-->
<!--                            </button>-->
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
                        <ul class="nav nav-tabs">
                            <li><a id="favorites" data-toggle="tab" href="#friendsList">Познати</a></li>
                            <li class="active"><a id="strangers" data-toggle="tab" href="#strangersList">Непознати</a></li>
                        </ul>
                    </div>

<!--                    <script>-->
<!--                        $('#selectListOfUsers li').on('click', function() {-->
<!--                            $.ajax({-->
<!--                                type: 'POST',-->
<!--                                url: 'Templates/processingAJAX.php',-->
<!--                                data: {-->
<!--                                    receiver_id: receiver_id,-->
<!--                                    friendOrStranger: true-->
<!--                                },-->
<!--                                success: function(data) {-->
<!--                                    var json = JSON.parse(data);-->
<!---->
<!--                                    var id = json[i]['id'];-->
<!--                                    var image = json[i]['image'];-->
<!--                                    var nickName = json[i]['nickName'];-->
<!---->
<!--                                    var userInformation = '<a href="#"><img class="img-circle" src="'+image+'" alt="image">'+nickName+'<span id="'+id+'" class="badge"></span></a>';-->
<!---->
<!--                                    $.each(json, function() {-->
<!--                                        echo "<div class='well well-sm' data-value-type='{$value['id']}' id='{$value['id']}' onclick='userSelect(this)'>";-->
<!--////                                    echo "<a  href='?page=chat&receiver_id={$value['id']}' data-value='{$value['id']}' id='{$value['id']}'>".'<img class="img-circle" src="'.$this->selectWhere("chat","users",array("id"=>$value["id"]), false)["image"].'" alt="picture" />'.$value['nickName']."<span id='testing_3' class='badge'></span></a><br>";-->
<!--                                    echo "<a  href='#'>".'<img class="img-circle" src="'.$this->selectWhere("chat","users",array("id"=>$value["id"]), false)["image"].'" alt="picture" />'.$value['nickName']."<span id='{$value['id']}' class='badge'></span></a><br>";-->
<!--                                    echo "</div>";-->
<!--                                    })-->
<!--                                }-->
<!--                            })-->
<!--                        })-->
<!---->
<!---->
<!--                    </script>-->

                    <!--Списък с абонати-->
                    <div >




<!--                     //Добавени-->
                        <div id="usersList" class="tab-content well well-sm">



                            <div id="friendsList" class="tab-pane fade">
                                <?php

                                $favorites = $this->displayFavorites("chat", "favorites", $userId, $receiver_id);

                                foreach($favorites as $value) {
                                    echo "<div class='well well-sm' data-value-type='{$value['id']}' id='{$value['id']}' onclick='userSelect(this)'>";
                                    echo "<a href='#'>".'<img class="img-circle" src="' . $value["image"] . '" alt="picture" /><label>' . "" . $value['nickName'] . "</label><span id='{$value['id']}' class='badge'></span></a><br>";
                                    echo "</div>";
                                }
                                ?>
                            </div>
<!--                            //Край на добавени


<!--                            //Недобавени-->
                            <div id="strangersList" class="tab-pane fade in active">
                                <?php
                                $display=$this->selectWhere("chat", "favorites",array("sender_id"=>$userId), true);

                                $displayUsers=$this->displayUsers("chat","users", $display);

                                foreach($displayUsers as $value) {
                                    echo "<div class='well well-sm' data-value-type='{$value['id']}' id='{$value['id']}' onclick='userSelect(this)'>";
//                                    echo "<a  href='?page=chat&receiver_id={$value['id']}' data-value='{$value['id']}' id='{$value['id']}'>".'<img class="img-circle" src="'.$this->selectWhere("chat","users",array("id"=>$value["id"]), false)["image"].'" alt="picture" />'.$value['nickName']."<span id='testing_3' class='badge'></span></a><br>";
                                    echo "<a  href='#'>".'<img class="img-circle" src="'.$this->selectWhere("chat","users",array("id"=>$value["id"]), false)["image"].'" alt="picture" />'.$value['nickName']."<span id='{$value['id']}' class='badge'></span></a><br>";
                                    echo "</div>";
                                }
                                ?>

<!--                                <button id="showMoreUp">Show</button>-->
                            </div>
<!--                            //Край на недобавени-->
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script>
            <?php
                if(isset($_GET['selected_user'])) {
                    $receiver_id = $_GET['selected_user'];
                }
            ?>

            var receiver_id=<?php echo $receiver_id;?>;
            var lastIdAdded=0;
            var lastId = 0;

            //стоиности за хронологията на чата
            var limitRows = 20;
            var numberOfRows = 0;

            function userSelect(selected) {

                //              Ипразна div-a преди да append-не новите съобщения
                $('#displayMessages').html('');

                //                изчиства броя на непрочетение съобщения на селектирания потребител
                var sender_id = '#'+receiver_id;
                $('#usersList a '+sender_id).html('');

                //                 изтрива елемнта от масива с непрочетените съобщения
                aUnreadMessages.splice(receiver_id,1);

                //занулява на променливи при селектиране на друг потребител
                lastIdAdded = 0;
                lastId = 0;
                limitRows = 20;
                numberOfRows = 0;

                receiver_id = selected.getAttribute('data-value-type');
                window.history.pushState("object or string", "Title", "?page=chat&selected_user="+receiver_id);

                loadMoreFromChat();

            }

            $(document).on('click','#friendOrStranger' , function() {
                $.ajax({
                    type: 'POST',
                    url: 'Templates/processingAJAX.php',
                    data: {
                        receiver_id: receiver_id,
                        friendOrStranger: true
                    },
                    success: function(data) {
                        var json = JSON.parse(data);
                        $('#friendOrStranger').html(json);
                    }
                })
            });

            //Trigger browse image via a-tag
            $('#profileImgTrigger').on('click', function() {
                $('#profileImg').click().on('change', function() {
                    $('#profileImgForm').submit();
                });
            });


//            typeahed engine
            var json = [];
            var searchText = $('#searchText').val();
            $('#search .typeahead').typeahead({
                    hint: true,
                    highlight: true,
                    minLength: 1
                },
                {
                    name: 'json',
                    displayKey: 'nickName',
                    source: function(searchText, q , cb) {
                         $.ajax({
                            type: 'POST',
                            url: 'Templates/processingAJAX.php',
                            dataType:'JSON',
                            data: {
                                searchText: searchText
                            },
                            success: function(data) {
                                return cb(data);
                            }
                        })
                    },
                    templates: {
                        suggestion: function(data){
                            if(data.nickName) {
                                 template =  '<div><img src="'+data.image+'" alt="picture" height="42" width="42">'
                                    +'<strong>' + data.nickName + '</strong> - ' + data.email + '</div>';
                            }else if(data.notFound) {
                                template = '<div class="notFound">'+data.notFound+'</div>';
                            }
                            return template;
                        }
                    },
                }).on('typeahead:selected', function(obj, data) {
                    location.replace('http://localhost/OnlineChat/?page=chat&receiver_id='+data.id);
                });

            //ид-то на последния ред на БД-'chat'
            <?php function returnLastId() {
                $db = new Connect("chat", "chat");
                $connect = $db->connect->prepare("SELECT id FROM chat ORDER BY id DESC");
                $connect->execute();
                return $connect->fetch()['id'];
            }?>
            var lastInsertedId =<?php echo returnLastId();?>;


//          емотикони
            var definition = {smile:{title:"Smile",codes:[":)",":=)",":-)"]},"sad-smile":{title:"Sad Smile",codes:[":(",":=(",":-("]},"big-smile":{title:"Big Smile",codes:[":D",":=D",":-D",":d",":=d",":-d"]},cool:{title:"Cool",codes:["8)","8=)","8-)","B)","B=)","B-)","(cool)"]},wink:{title:"Wink",codes:[":o",":=o",":-o",":O",":=O",":-O"]},crying:{title:"Crying",codes:[";(",";-(",";=("]},sweating:{title:"Sweating",codes:["(sweat)","(:|"]},speechless:{title:"Speechless",codes:[":|",":=|",":-|"]},kiss:{title:"Kiss",codes:[":*",":=*",":-*"]},"tongue-out":{title:"Tongue Out",codes:[":P",":=P",":-P",":p",":=p",":-p"]},blush:{title:"Blush",codes:["(blush)",":$",":-$",":=$",':">']},wondering:{title:"Wondering",codes:[":^)"]},sleepy:{title:"Sleepy",codes:["|-)","I-)","I=)","(snooze)"]},dull:{title:"Dull",codes:["|(","|-(","|=("]},"in-love":{title:"In love",codes:["(inlove)"]},"evil-grin":{title:"Evil grin",codes:["]:)",">:)","(grin)"]},talking:{title:"Talking",codes:["(talk)"]},yawn:{title:"Yawn",codes:["(yawn)","|-()"]},puke:{title:"Puke",codes:["(puke)",":&",":-&",":=&"]},"doh!":{title:"Doh!",codes:["(doh)"]},angry:{title:"Angry",codes:[":@",":-@",":=@","x(","x-(","x=(","X(","X-(","X=("]},"it-wasnt-me":{title:"It wasn't me",codes:["(wasntme)"]},party:{title:"Party!!!",codes:["(party)"]},worried:{title:"Worried",codes:[":S",":-S",":=S",":s",":-s",":=s"]},mmm:{title:"Mmm...",codes:["(mm)"]},nerd:{title:"Nerd",codes:["8-|","B-|","8|","B|","8=|","B=|","(nerd)"]},"lips-sealed":{title:"Lips Sealed",codes:[":x",":-x",":X",":-X",":#",":-#",":=x",":=X",":=#"]},hi:{title:"Hi",codes:["(hi)"]},call:{title:"Call",codes:["(call)"]},devil:{title:"Devil",codes:["(devil)"]},angel:{title:"Angel",codes:["(angel)"]},envy:{title:"Envy",codes:["(envy)"]},wait:{title:"Wait",codes:["(wait)"]},bear:{title:"Bear",codes:["(bear)","(hug)"]},"make-up":{title:"Make-up",codes:["(makeup)","(kate)"]},"covered-laugh":{title:"Covered Laugh",codes:["(giggle)","(chuckle)"]},"clapping-hands":{title:"Clapping Hands",codes:["(clap)"]},thinking:{title:"Thinking",codes:["(think)",":?",":-?",":=?"]},bow:{title:"Bow",codes:["(bow)"]},rofl:{title:"Rolling on the floor laughing",codes:["(rofl)"]},whew:{title:"Whew",codes:["(whew)"]},happy:{title:"Happy",codes:["(happy)"]},smirking:{title:"Smirking",codes:["(smirk)"]},nodding:{title:"Nodding",codes:["(nod)"]},shaking:{title:"Shaking",codes:["(shake)"]},punch:{title:"Punch",codes:["(punch)"]},emo:{title:"Emo",codes:["(emo)"]},yes:{title:"Yes",codes:["(y)","(Y)","(ok)"]},no:{title:"No",codes:["(n)","(N)"]},handshake:{title:"Shaking Hands",codes:["(handshake)"]},skype:{title:"Skype",codes:["(skype)","(ss)"]},heart:{title:"Heart",codes:["(h)","<3","(H)","(l)","(L)"]},"broken-heart":{title:"Broken heart",codes:["(u)","(U)"]},mail:{title:"Mail",codes:["(e)","(m)"]},flower:{title:"Flower",codes:["(f)","(F)"]},rain:{title:"Rain",codes:["(rain)","(london)","(st)"]},sun:{title:"Sun",codes:["(sun)"]},time:{title:"Time",codes:["(o)","(O)","(time)"]},music:{title:"Music",codes:["(music)"]},movie:{title:"Movie",codes:["(~)","(film)","(movie)"]},phone:{title:"Phone",codes:["(mp)","(ph)"]},coffee:{title:"Coffee",codes:["(coffee)"]},pizza:{title:"Pizza",codes:["(pizza)","(pi)"]},cash:{title:"Cash",codes:["(cash)","(mo)","($)"]},muscle:{title:"Muscle",codes:["(muscle)","(flex)"]},cake:{title:"Cake",codes:["(^)","(cake)"]},beer:{title:"Beer",codes:["(beer)"]},drink:{title:"Drink",codes:["(d)","(D)"]},dance:{title:"Dance",codes:["(dance)","\\o/","\\:D/","\\:d/"]},ninja:{title:"Ninja",codes:["(ninja)"]},star:{title:"Star",codes:["(*)"]},mooning:{title:"Mooning",codes:["(mooning)"]},finger:{title:"Finger",codes:["(finger)"]},bandit:{title:"Bandit",codes:["(bandit)"]},drunk:{title:"Drunk",codes:["(drunk)"]},smoking:{title:"Smoking",codes:["(smoking)","(smoke)","(ci)"]},toivo:{title:"Toivo",codes:["(toivo)"]},rock:{title:"Rock",codes:["(rock)"]},headbang:{title:"Headbang",codes:["(headbang)","(banghead)"]},bug:{title:"Bug",codes:["(bug)"]},fubar:{title:"Fubar",codes:["(fubar)"]},poolparty:{title:"Poolparty",codes:["(poolparty)"]},swearing:{title:"Swearing",codes:["(swear)"]},tmi:{title:"TMI",codes:["(tmi)"]},heidy:{title:"Heidy",codes:["(heidy)"]},myspace:{title:"MySpace",codes:["(MySpace)"]},malthe:{title:"Malthe",codes:["(malthe)"]},tauri:{title:"Tauri",codes:["(tauri)"]},priidu:{title:"Priidu",codes:["(priidu)"]}};

            $.emoticons.define(definition);


            //Съобщенията от чата
            function loadMoreFromChat() {
                $.ajax({
                    type: 'POST',
                    url: 'Templates/processingAJAX.php',
                    data: {
                        receiver_id: receiver_id,
                        last: lastIdAdded,
                        limitRows: limitRows,
                        chatDisplay: true
                    },
                    success: function(data) {
                        var json = JSON.parse(data);

                        $('#noCorrespondence').css('display', (receiver_id==='' || json.length===0)? 'block' : 'none');

                        for(var i=numberOfRows; i<json.length; i++) {
                            var nickName = '<label>'+json[i]['nickName']+'</label> ~ ';
                            var sendTime = json[i]['hour']+':'+json[i]['minute']+'<br>';
                            var message = '<span class="messages">'+json[i]['message']+'</span>';
                            var classToAdd;

                            $('#displayMessages').prepend("<div class='well well-sm messages'>"+nickName+sendTime+message+"</div>");

                            receiver_id==json[i]['receiver_id']?classToAdd = 'me' : classToAdd='interlocutor';
                            $('div:first-child').addClass(classToAdd);

                            lastId= parseInt(json[0]['higher_id'])+1;


                            var $text = $('div:first-child>.messages'),
                                $in = json[i]['message'];

                                $text.html($.emoticons.replace($in));
                        }

                        numberOfRows+=20;
                        limitRows+=20;

                        if(json.length>numberOfRows || json.length===numberOfRows ) {
                            $('#showMoreUp').css('display','inline-block');
                        }else{
                            $('#showMoreUp').css('display','none');
                        }



                    }
                })

            }



            $(document).ready(function() {
                loadMoreFromChat();
                $("#showMoreUp").on('click', function() {
                    loadMoreFromChat();
                });
            });


            $(document).on('click', '#chatHistoryToggle',function() {
                $('#historySearchDiv').slideToggle();

                $(this).find('span').toggleClass('glyphicon-chevron-down glyphicon-chevron-up ');
            });

            var current_id = 0;
            var interval = setInterval(function() {
                //Информация за селектирания потребител
                if(receiver_id!==current_id) {
                    $.ajax({
                        type: 'POST',
                        url: 'Templates/processingAJAX.php',
                        data: {
                            receiver_id: receiver_id,
                            selectedUser: true
                        },
                        success: function(data) {
                            var json = JSON.parse(data);

                            //Бутон добави/премахни
                            var button = '';
                            if(json['id'] === null) {
                                button = 'Добави';
                            }else {
                                button = 'Премахни';
                            }

                            $('#selectedUser').html("<h2>"+json['nickName']+"</h2>"
                                +'<button id="chatHistoryToggle" class="btn btn-default btn-xs pull-right">История на чата|<span class="glyphicon glyphicon-chevron-down"></span></button>'
                                +'<button class="btn btn-default pull-right" id="friendOrStranger">'+button+'</button><br>'
                                +"<img class='img-circle' src='"+json['image']+"' alt='picture' style='width:76px;height:76px'>"
                            );

//                            $('#selectedUser').html("<h2>"+json['nickName']+"</h2>"
//                                +'<input type="text" id="chatHistory" class="form-control pull-right" placeholder="Хронология">' +
//                                '<button class="btn btn-default pull-right" id="chatHistorySearch" >Търси</button>' +
//                                '<button class="btn btn-default pull-right" id="friendOrStranger">'+button+'</button><br>'
//                                +"<img class='img-circle' src='"+json['image']+"' alt='picture' style='width:76px;height:76px'>"
//                            );

//                            $('#selectedUser').html("<h2>"+json['nickName']+"</h2>"
//                                +'<div class="input-group "><input id="chatHistory" type="text" class="form-control pull-right" placeholder="Хронология...">'
//                                    +'<span class="input-group-btn"><button class="btn btn-secondary" type="button">Търси</button>'+
//                               '</span>'
//                                +'</div>'+
//                                '<button class="btn btn-default pull-right" id="friendOrStranger">'+button+'</button><br>'
//                                +"<img class='img-circle' src='"+json['image']+"' alt='picture' style='width:76px;height:76px'>"
//                            );


//                            <div class="input-group">
//                                <input type="text" class="form-control" placeholder="Хронология...">
//                                <span class="input-group-btn ">
//                                <button class="btn btn-secondary" type="button">Търси</button>
//                                </span>
//                                </div>

                        }
                    })
                    current_id = receiver_id;
                }

                //Добавя новите съобения
                $.ajax({
                    type: 'POST',
                    url: 'Templates/processingAJAX.php',
                    data: {
                        receiver_id: receiver_id,
                        last: lastId,
                        dynamicChatCheck: true
                    },
                    success: function(data) {
                        var json = JSON.parse(data);

                        //Проверява дали има кореспонденция и ако има премахва съобщението, че не е открита такава
                        if(json.length>0)
                            $('#noCorrespondence').css('display', 'none');


                        $.each(json, function(i) {
                            var nickName = '<label>'+json[i]['nickName']+'</label> ~ ';
                            var sendTime = json[i]['hour']+':'+json[i]['minute']+'<br>';
                            var message = '<span class="messages">'+json[i]['message']+'</span>';
                            var classToAdd;

                            $('#displayMessages').append("<div class='well well-sm'>"+nickName+sendTime+message+"</div>");

                            receiver_id==json[i]['receiver_id']?classToAdd = 'me' : classToAdd='interlocutor';
                            $('div:last-child').addClass(classToAdd);

                            var $text = $('div:last-child>.messages'),
                                $in = json[i]['message'];
                            $text.html($.emoticons.replace($in));

                            lastId= parseInt(json[0]['higher_id'])+1;

                        });
                    }
                })
            }, 4000);

            //Търси хронология на чата;
            $(document).on('click', '#chatHistorySearch',function() {
                $.ajax({
                    type: 'POST',
                    url: 'Templates/processingAJAX.php',
                    data: {
                        receiver_id: receiver_id,
                        keyWord: $('#chatHistory').val(),
                        chatHistorySearch: true
                    },
                    success: function(data) {
                        var json = JSON.parse(data);
                        $('#displayMessages').html('');
                        $.each(json,function(i) {
                            var id = json[i]['id'];
                            var nickName = '<label>'+json[i]['nickName']+'</label> ~ ';
                            var datetime = json[i]['datetime']+'</br>';
                            var message = json[i]['message'];
                            $('#displayMessages').append("<div data-history-id='"+id+"' class='history well well-sm'>"+nickName+datetime+message+"</div>");
                        })
                    }
                })
            })

            //Отвори намерен откъс от хорнологията на чата;
            $(document).on('click','.history', function() {
                var chatId = $(this).data('history-id');
                $.ajax({
                    type: 'POST',
                    url: 'Templates/processingAJAX.php',
                    data: {
                        receiver_id: receiver_id,
                        chatId: chatId,
                        chatSelectedHistory: true
                    },
                    success: function(data) {

                        console.log(chatId);

                        var json = JSON.parse(data);
                        $('#displayMessages').html('');
                        $.each(json,function(i) {
                            var nickName = '<label>'+json[i]['nickName']+'</label> ~ ';
                            var sendTime = json[i]['hour']+':'+json[i]['minute']+'<br>';
                            var message = json[i]['message'];
                            var classToAdd;

                            $('#displayMessages').append("<div class=' well well-sm'>"+nickName+sendTime+message+"</div>");
                            receiver_id==json[i]['receiver_id']?classToAdd = 'me' : classToAdd='interlocutor';
                            $('div:last-child').addClass(classToAdd);
                        })
                    }
                })
            });

            //Индикатори за нови непрочетени съобщения;
            var identifier='';

            //масив който пази непрочетените съобщения;
            var aUnreadMessages=[];

            var interval = setInterval(function() {

                $.ajax({
                    type: 'POST',
                    url: 'Templates/processingAJAX.php',
                    data: {
                        receiver_id: receiver_id,
                        lastInsertedId: lastInsertedId,
                        countNewMessages: true
                    },
                    success: function(data) {
                        var json = JSON.parse(data);

                        $.each(json, function(i) {
                            var result=0;
                            var sender_id = json[i]['sender_id'];
                            identifier = '#'+json[i]['sender_id'];
                            var numbersOfMessages = parseInt(json[i]['numberOfRows']);

                            if(sender_id in aUnreadMessages) {
                                aUnreadMessages[sender_id] += numbersOfMessages;
                                result=aUnreadMessages[sender_id];
                            }else {
                                result = numbersOfMessages;
                            }

                            aUnreadMessages[sender_id]=result;


                            $('#usersList a '+identifier).html(result);
                            lastInsertedId = json[i]['maxId'];


                        });
                    }
                })

            }, 4000);

            //Динамично записване  на съобщенията в базаданни;
            $("#message").keypress(function(event) {
                if (event.which == 13) {
                    event.preventDefault();
                    var message = $("#message").val().trim();
//                    if(message !== "") {
                        $.ajax({
                            type: 'POST',
                            url: 'Templates/processingAJAX.php',
                            data: {
//                                processMessage: $("#message").val(),
                                processMessage: message,
                                receiver_id: receiver_id
                            },
                            success: function (data) {
                                $("#message").val("");
                            }
                        })
//                    }
                }
            });


//            $("#displayMessages"). scrollTop($("#displayMessages").height());
//
//            alert($("#displayMessages").height());

//            var objDiv = document.getElementById("#displayMessages");
//            objDiv.scrollTop = objDiv.scrollHeight;



//            Емотикони
            (function() {
                $('#overview').html($.emoticons.toString())
                $('#message').on('keypress', function() {
                    var $text = $('#text'),
                        $in = $(this);

//                    setTimeout(function() {
//                        $text.html($.emoticons.replace($in.val()));
//                    }, 100);
                });
            }());

//
            $('#emoticonOverViewButton').on('click', function() {
                $('#overview').slideToggle();
            });

            $('#overview span').on('click', function() {
                var emoticonCode = $(this).text();
                var messageValue = $('#message').val();
                var text = messageValue.concat(emoticonCode);

                $('#message').val(text);
            });


        </script>

        <?php
    }
}