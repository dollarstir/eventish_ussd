<?php

require __DIR__ . '/vendor/autoload.php';

use Phpfastcache\Helper\Psr16Adapter;

$defaultDriver = 'Files';
$Psr16Adapter =  new Psr16Adapter($defaultDriver);
require_once 'menu.php';
$menu  = new menu();

header('Content-Type: application/json');
// getting data from ussd gateway
$data = file_get_contents('php://input');
$data = json_decode($data, true);
$sessionID = $data['sessionID'];
$newSession = $data['newSession'];
$userID = $data['userID'];
$msisdn = $data['msisdn'];
$userData = $data['userData'];
$network = $data['network'];

// session_start();


if($newSession  && $userData == '*928*998#'){
    $message  = menu::mainmenu($sessionID);
   
    $continueSession = true;


     // Keep track of the USSD state of the user and their session
     $currentState = [
        'sessionID' => $sessionID,
        'msisdn' => $msisdn,
        'userData' => $userData,
        'network'   => $network,
        'newSession' => $newSession,
        'message' => $message,
        'level' => 1,
        'page' => 1,
    ];


    $userResponseTracker = $Psr16Adapter->get($sessionID);

    !$userResponseTracker
        ? $userResponseTracker = [$currentState]
        : $userResponseTracker[] = $currentState;

    $Psr16Adapter->set($sessionID, $userResponseTracker);

    $userResponseTracker = $Psr16Adapter->get($sessionID) ?? [];
    
}

if($newSession == false && $userData == '1' && $_SESSION[$sessionID]['level'] == 1){
    $message =  menu::vote($sessionID);
    $_SESSION[$sessionID] = ['level' => 2, 'option' => 1.1];
    $continueSession = true;
}
elseif($newSession == false && $_SESSION[$sessionID]['level'] == 2 && $_SESSION[$sessionID]['option'] == 1.1){
    $message =  $menu->voteamount($userData,$sessionID);
    $continueSession = true;
}
else{
    $message = $userResponseTracker ;
    $continueSession = true;}



// sending response to ussd  sever 

header('Content-Type: application/json');
$respose = [
    "sessionID" => $sessionID,
    'userID ' => $userID,
    'msisdn' => $msisdn,
    "message" => $message,
    "continueSession" => $continueSession,
    

];
echo json_encode($respose);
