/**
 * Created by Georgievi on 23.3.2016 г..
 */

//Глобални Променливи;
var lastIdAdded=0;
var lastId = 0;

//стоиности за хронологията на чата
var limitRows = 20;
var numberOfRows = 0;

function chatDivStyle(json, orderPrepend) {
    var nickName = '<label>'+json['nickName']+'</label> ~ ';
    var sendTime = json['hour']+':'+json['minute']+'<br>';
    var message = '<span class="messages">'+json['message']+'</span>';
    var classToAdd = receiver_id==json['receiver_id']?'me' : 'interlocutor';
    var $text;

    if(orderPrepend) {
        $('#displayMessages').prepend("<div class='well well-sm messages "+classToAdd+"'>"+nickName+sendTime+message+"</div>");
        $text = $('div:first-child>.messages');
    } else {
        $('#displayMessages').append("<div class='well well-sm messages "+classToAdd+"'>"+nickName+sendTime+message+"</div>");
        $text = $('div:last-child>.messages');
    }

    //Емотикони;
    var $in = json['message'];
    $text.html($.emoticons.replace($in));
}

//Структоура на списъка с потребители;
function usersDivStyle(json) {
    var id = json['id'];
    var image = '<img src="'+json['image']+'" alt="profile_image" class="img-circle">';
    var nickName = json['nickName'];
    var span = "<span id='"+id+"' class='badge'></span>";

    return '<div id="'+id+'" data-value-type="'+id+'" onclick="userSelect(this)" class="well well-sm">'+image+nickName+span+'</div>';
}

//Рефрешване на потребителите с ajax;
function ajaxUserList(attribute) {
    $('#usersList').html("");
    $('#loading_image').show();
    $('#selectListOfUsers button').attr("disabled", true);

    var type = $(attribute).attr('id') == 'friends'? true : false;

    $.ajax({
        type: 'POST',
        url: 'Templates/processingAJAX.php',
        data: {
            receiver_id: receiver_id,
            userList: true,
            friends: type
        },
        success: function(data) {
            var json = JSON.parse(data);
            if(json.length>0) {
                $.each(json,function(i) {
                    $('#usersList').append(usersDivStyle(json[i]));
                });
            }else{
                $('#usersList').html('Няма потребители в списъка.').css('color', 'black');
            }

            $('#loading_image').hide();
            $('#selectListOfUsers button').attr("disabled", false);
        }
    });
}

//Информация за потребителя;
function userInformation() {
    //Информация за селектирания потребител
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
        }
    })

}

//Функция която нанася промени при избор на потребител за чат;
    //Занулява променливи;
    //Чисти ХТМЛ от див-тагове;
    //Вика функции;
function userSelect(selected) {

    receiver_id = selected.getAttribute('data-value-type');
    window.history.pushState("object or string", "Title", "?page=chat&selected_user="+receiver_id);

    //Ипразна div-a преди да append-не новите съобщения
    $('#selectedUser').html('');

    //Ипразна div-a преди да append-не новите съобщения
    $('#displayMessages').html('');

    //                изчиства броя на непрочетение съобщения на селектирания потребител
    var sender_id = '#'+receiver_id;
    console.log('null '+ sender_id);
    $('#usersList div '+sender_id).html('');

    //                 изтрива елемнта от масива с непрочетените съобщения
    aUnreadMessages.splice(receiver_id,1);

    //занулява на променливи при селектиране на друг потребител
    lastIdAdded = 0;
    lastId = 0;
    limitRows = 20;
    numberOfRows = 0;


    //Информация за селектирания потребител
    userInformation();

    //Зареждане на откъс от чата;
    loadMoreFromChat();
}

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

                chatDivStyle(json[i], true);
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


var countTheId;
function showMoreChatDown(chatId, operator) {
    $.ajax({
        type: 'POST',
        url: 'Templates/processingAJAX.php',
        data: {
            receiver_id: receiver_id,
            chatId: chatId,
            chatSelectedHistory: true,
            operator: operator
        },
        success: function(data) {
            var json = JSON.parse(data);

            $.each(json,function(i) {
                chatDivStyle(json[i], false);

                if(json.length>=4) {
                    $('#showMoreDown').css('display','inline-block');
                }else {
                    $('#showMoreDown').css('display','none');
                }

                countTheId = json[i]['id'];
            })
        }
    })
}


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

            console.log(json);

            $.each(json, function(i) {
                var result=0;
                var sender_id = json[i]['sender_id'];
                identifier = '#'+json[i]['sender_id'];
                console.log(identifier);
                //identifier = '#3';
                var numbersOfMessages = parseInt(json[i]['numRows']);

                if(sender_id in aUnreadMessages) {
                    aUnreadMessages[sender_id] += numbersOfMessages;
                    result=aUnreadMessages[sender_id];
                }else {
                    result = numbersOfMessages;
                }

                aUnreadMessages[sender_id]=result;

                $('#usersList div '+identifier).html(result);
                lastInsertedId = json[i]['maxId'];
            });
        }
    })

}, 4000);


//Typeahead search engine
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


$(function(){

    if(receiver_id) {
        userInformation();
        loadMoreFromChat();
        $("#showMoreUp").on('click', function() {
            loadMoreFromChat();
        });
        $('#showMoreDown').on('click', function() {
            showMoreChatDown(countTheId, '>');
        });
    }

    //Смяна на профилната снимка
    $('#profileImgTrigger').on('click', function() {
        $('#profileImg').click().on('change', function() {
            $('#profileImgForm').submit();
        });
    });

    //Отвори намерен откъс от хорнологията на чата;
    $(document).on('click','.history', function() {
        $('#displayMessages').html('');
        var chatId = $(this).data('history-id');
        showMoreChatDown(chatId,'>=');
    });

    ajaxUserList('#'+$('#selectListOfUsers .active').attr('id'));

    $('#selectListOfUsers button').on('click', function() {
        var id ='#'+$(this).attr('id');
        $('#selectListOfUsers button').toggleClass('active');
        ajaxUserList(this);
    });

    //Динамично записване  на съобщенията в базаданни;
    $("#message").keypress(function(event) {
        if (event.which == 13) {
            event.preventDefault();
            var message = $("#message").val().trim();
            $("#message").val("");

            if(message !== "") {
                    $.ajax({
                        type: 'POST',
                        url: 'Templates/processingAJAX.php',
                        data: {
                            processMessage: message,
                            receiver_id: receiver_id
                        },
                        success: function (data) {

                            var time = new Date();
                            var hour = time.getHours();
                            var minutes = time.getMinutes();

                            var json = {message:message,
                                nickName:user_nickName,
                                hour:hour,
                                minute:minutes,
                                receiver_id:receiver_id
                            };

                            chatDivStyle(json,false);

                            var d = $("#displayMessages");

                            //d.animate({ scrollTop: d.prop("scrollHeight")}, 1000);

                            $('#chat').scrollTop(d.height());
                            console.log(d.prop('scrollHeight'));
                        }
                    })
                }
        }
    });


    //Уеб сокети за чата
    // Enable pusher logging - don't include this in production
    Pusher.log = function(message) {
        if (window.console && window.console.log) {
            window.console.log(message);
        }
    };

    var pusher = new Pusher('2cbaeffba3558ad3fc0e');

    var channel = pusher.subscribe('channel-'+user_id);
    channel.bind('event', function(data) {

        $('#displayMessages').append('<div class="well well-sm" style="color:black">'+data.message+'</div>');

        alert(data.message);
    });



    //бутон за добавяне и премахване оф 'Познати' + рефрешване на списъка с потребители
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
        });

        var activeButton = '#'+$("#selectListOfUsers .active").attr('id');
        setTimeout(function(){
            ajaxUserList($('#selectListOfUsers '+activeButton));
        },1000);
    });

    //История на чата
    $(document).on('click', '#chatHistoryToggle',function() {
        $('#historySearchDiv').slideToggle();
        $(this).find('span').toggleClass('glyphicon-chevron-down glyphicon-chevron-up ');
    });

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

    //Емотикони
    var definition = {smile:{title:"Smile",codes:[":)",":=)",":-)"]},"sad-smile":{title:"Sad Smile",codes:[":(",":=(",":-("]},"big-smile":{title:"Big Smile",codes:[":D",":=D",":-D",":d",":=d",":-d"]},cool:{title:"Cool",codes:["8)","8=)","8-)","B)","B=)","B-)","(cool)"]},wink:{title:"Wink",codes:[":o",":=o",":-o",":O",":=O",":-O"]},crying:{title:"Crying",codes:[";(",";-(",";=("]},sweating:{title:"Sweating",codes:["(sweat)","(:|"]},speechless:{title:"Speechless",codes:[":|",":=|",":-|"]},kiss:{title:"Kiss",codes:[":*",":=*",":-*"]},"tongue-out":{title:"Tongue Out",codes:[":P",":=P",":-P",":p",":=p",":-p"]},blush:{title:"Blush",codes:["(blush)",":$",":-$",":=$",':">']},wondering:{title:"Wondering",codes:[":^)"]},sleepy:{title:"Sleepy",codes:["|-)","I-)","I=)","(snooze)"]},dull:{title:"Dull",codes:["|(","|-(","|=("]},"in-love":{title:"In love",codes:["(inlove)"]},"evil-grin":{title:"Evil grin",codes:["]:)",">:)","(grin)"]},talking:{title:"Talking",codes:["(talk)"]},yawn:{title:"Yawn",codes:["(yawn)","|-()"]},puke:{title:"Puke",codes:["(puke)",":&",":-&",":=&"]},"doh!":{title:"Doh!",codes:["(doh)"]},angry:{title:"Angry",codes:[":@",":-@",":=@","x(","x-(","x=(","X(","X-(","X=("]},"it-wasnt-me":{title:"It wasn't me",codes:["(wasntme)"]},party:{title:"Party!!!",codes:["(party)"]},worried:{title:"Worried",codes:[":S",":-S",":=S",":s",":-s",":=s"]},mmm:{title:"Mmm...",codes:["(mm)"]},nerd:{title:"Nerd",codes:["8-|","B-|","8|","B|","8=|","B=|","(nerd)"]},"lips-sealed":{title:"Lips Sealed",codes:[":x",":-x",":X",":-X",":#",":-#",":=x",":=X",":=#"]},hi:{title:"Hi",codes:["(hi)"]},call:{title:"Call",codes:["(call)"]},devil:{title:"Devil",codes:["(devil)"]},angel:{title:"Angel",codes:["(angel)"]},envy:{title:"Envy",codes:["(envy)"]},wait:{title:"Wait",codes:["(wait)"]},bear:{title:"Bear",codes:["(bear)","(hug)"]},"make-up":{title:"Make-up",codes:["(makeup)","(kate)"]},"covered-laugh":{title:"Covered Laugh",codes:["(giggle)","(chuckle)"]},"clapping-hands":{title:"Clapping Hands",codes:["(clap)"]},thinking:{title:"Thinking",codes:["(think)",":?",":-?",":=?"]},bow:{title:"Bow",codes:["(bow)"]},rofl:{title:"Rolling on the floor laughing",codes:["(rofl)"]},whew:{title:"Whew",codes:["(whew)"]},happy:{title:"Happy",codes:["(happy)"]},smirking:{title:"Smirking",codes:["(smirk)"]},nodding:{title:"Nodding",codes:["(nod)"]},shaking:{title:"Shaking",codes:["(shake)"]},punch:{title:"Punch",codes:["(punch)"]},emo:{title:"Emo",codes:["(emo)"]},yes:{title:"Yes",codes:["(y)","(Y)","(ok)"]},no:{title:"No",codes:["(n)","(N)"]},handshake:{title:"Shaking Hands",codes:["(handshake)"]},skype:{title:"Skype",codes:["(skype)","(ss)"]},heart:{title:"Heart",codes:["(h)","<3","(H)","(l)","(L)"]},"broken-heart":{title:"Broken heart",codes:["(u)","(U)"]},mail:{title:"Mail",codes:["(e)","(m)"]},flower:{title:"Flower",codes:["(f)","(F)"]},rain:{title:"Rain",codes:["(rain)","(london)","(st)"]},sun:{title:"Sun",codes:["(sun)"]},time:{title:"Time",codes:["(o)","(O)","(time)"]},music:{title:"Music",codes:["(music)"]},movie:{title:"Movie",codes:["(~)","(film)","(movie)"]},phone:{title:"Phone",codes:["(mp)","(ph)"]},coffee:{title:"Coffee",codes:["(coffee)"]},pizza:{title:"Pizza",codes:["(pizza)","(pi)"]},cash:{title:"Cash",codes:["(cash)","(mo)","($)"]},muscle:{title:"Muscle",codes:["(muscle)","(flex)"]},cake:{title:"Cake",codes:["(^)","(cake)"]},beer:{title:"Beer",codes:["(beer)"]},drink:{title:"Drink",codes:["(d)","(D)"]},dance:{title:"Dance",codes:["(dance)","\\o/","\\:D/","\\:d/"]},ninja:{title:"Ninja",codes:["(ninja)"]},star:{title:"Star",codes:["(*)"]},mooning:{title:"Mooning",codes:["(mooning)"]},finger:{title:"Finger",codes:["(finger)"]},bandit:{title:"Bandit",codes:["(bandit)"]},drunk:{title:"Drunk",codes:["(drunk)"]},smoking:{title:"Smoking",codes:["(smoking)","(smoke)","(ci)"]},toivo:{title:"Toivo",codes:["(toivo)"]},rock:{title:"Rock",codes:["(rock)"]},headbang:{title:"Headbang",codes:["(headbang)","(banghead)"]},bug:{title:"Bug",codes:["(bug)"]},fubar:{title:"Fubar",codes:["(fubar)"]},poolparty:{title:"Poolparty",codes:["(poolparty)"]},swearing:{title:"Swearing",codes:["(swear)"]},tmi:{title:"TMI",codes:["(tmi)"]},heidy:{title:"Heidy",codes:["(heidy)"]},myspace:{title:"MySpace",codes:["(MySpace)"]},malthe:{title:"Malthe",codes:["(malthe)"]},tauri:{title:"Tauri",codes:["(tauri)"]},priidu:{title:"Priidu",codes:["(priidu)"]}};
    $.emoticons.define(definition);

    $('#emoticonOverViewButton').on('click', function() {
        $('#overview').html($.emoticons.toString()).slideToggle();
    });

    $(document).on('click','#overview span', function() {
        var emoticonCode = $(this).text();
        var messageValue = $('#message').val();
        var text = messageValue.concat(emoticonCode);

        $('#message').val(text);
    });


})
