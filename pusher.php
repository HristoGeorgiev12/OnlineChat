<?php
//error_reporting(E_ALL);

require('pusher-websocket/pusher.php');

//class MyLogger {
//    public function log( $msg ) {
//        print_r( $msg . "<br />" );
//    }
//}

$options = array(
    'encrypted' => false
);
$pusher = new Pusher(
    '2cbaeffba3558ad3fc0e',
    'e4883360ebac2d081ab0',
    '189122',
    $option
);

//$logger = new MyLogger();
//$pusher->set_logger( $logger );

$data['message'] = 'hello weeeeeeeeeeeeeeorld';
$pusher->trigger('test_channel', 'my_event', $data);
//$result = $pusher->trigger('test_channel', 'my_event', $data);
//$logger->log( "---- My Result ---" );
//$logger->log( $result );

//$channel = 'test_channel'; //Type name of channel here
//
//$channelInfo = $pusher->get_channel_info($channel);
//var_dump($channelInfo); //What's the result?

//                    var_dump($pusher->trigger('test_channel', 'my_event', array('data'=>'hello')));

?>