<?php

require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/menu.php';
$menu = new menu();

use Phpfastcache\Helper\Psr16Adapter;

$defaultDriver = 'Files';
$Psr16Adapter = new Psr16Adapter($defaultDriver);

// Get the JSON contents
$json = file_get_contents('php://input');

// decode the json data
$data = json_decode($json, true);

$sessionID = $data['sessionID'];
$userID = $data['userID'];
$newSession = $data['newSession'];
$msisdn = $data['msisdn'];
$userData = $data['userData'];
$network = $data['network'];

$resultkey = $sessionID.'result';


// $userdata = '*928*998*8ndm';
$extracteddata = explode('*', $userData);



if ($newSession && count($extracteddata) == 3) {
    $message = "Welcome to Eventish select option to continue" .
        "\n1. Votes" .
        "\n2. Tickets" .
        "\n3. Contact Us" ;
        // "\n4. USSD" .
        // "\n5. Payments";
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

    http_response_code(200);

    // treat this as json
    header('Content-Type: application/json');

    echo json_encode([
        'sessionID' => $sessionID,
        'msisdn' => $msisdn,
        'userID' => $userID,
        'continueSession' => $continueSession,
        'message' => $message,
    ]);
    exit();
}


if ($newSession && count($extracteddata) == 4) {
    $res = $menu->voteamount($extracteddata[3]);
    $message = $res['message'];

    $Psr16Adapter->set($resultkey, $res['data']);

    $continueSession = ($res['message'] !== 'Invalid Code')? true : false;
   

    // Keep track of the USSD state of the user and their session
    $currentState = [
        'sessionID' => $sessionID,
        'msisdn' => $msisdn,
        'userData' => $userData,
        'network'   => $network,
        'newSession' => $newSession,
        'message' => $message,
        'level' => 2,
        'page' => 2,
    ];

    $userResponseTracker = $Psr16Adapter->get($sessionID);

    !$userResponseTracker
        ? $userResponseTracker = [$currentState]
        : $userResponseTracker[] = $currentState;

    $Psr16Adapter->set($sessionID, $userResponseTracker);

    http_response_code(200);

    // treat this as json
    header('Content-Type: application/json');

    echo json_encode([
        'sessionID' => $sessionID,
        'msisdn' => $msisdn,
        'userID' => $userID,
        'continueSession' => $continueSession,
        'message' => $message,
    ]);
    exit();
}



// if ($newSession && count($extracteddata)== 4 ) {
//     // $res = $menu->voteamount($extracteddata[3]);
//     //     $message = $res['message'];
// $message = "Thank you for voting!";
//     $continueSession = false;

//         // $continueSession = ($res['message'] !== 'Invalid Code')? true : false;
       
//         // $Psr16Adapter->set($resultkey, $res['data']);

//         // $currentState = [
//         //     'sessionID' => $sessionID,
//         //     'msisdn' => $msisdn,
//         //     'userData' => $userData,
//         //     'network'   => $network,
//         //     'newSession' => $newSession,
//         //     'message' => $message,
//         //     'level' => 2,
//         //     'page' => 2,
//         // ];

//         // $userResponseTracker[] = $currentState;
//         // $Psr16Adapter->set($sessionID, $userResponseTracker);
// }






$userResponseTracker = $Psr16Adapter->get($sessionID) ?? [];

if (!(count($userResponseTracker) > 0)) {
    http_response_code(200);

    echo json_encode([
        'sessionID' => $sessionID,
        'msisdn' => $msisdn,
        'userID' => $userID,
        'continueSession' => false,
        'message' => 'Error! Please dial code again!',
    ]);
    exit();
}

$lastResponse = $userResponseTracker[count($userResponseTracker) - 1];

$message = "Bad Option";
$continueSession = false;

if ($lastResponse['level'] === 1) {
    if (in_array($userData, ["2", "3", "4", "5"])) {

        $message = "Thank you for voting!";
        $continueSession = false;
    } else if ($userData === '1') {
        $message =  menu::vote();

        $continueSession = true;

        $currentState = [
            'sessionID' => $sessionID,
            'msisdn' => $msisdn,
            'userData' => $userData,
            'network'   => $network,
            'newSession' => $newSession,
            'message' => $message,
            'level' => 2,
            'page' => 1,
        ];

        $userResponseTracker[] = $currentState;
        $Psr16Adapter->set($sessionID, $userResponseTracker);
    }
} else if ($lastResponse['level'] === 2) {
    if ($lastResponse['page'] === 1 ) {
        $res = $menu->voteamount($userData);
        $message = $res['message'];

        $continueSession = ($res['message'] !== 'Invalid Code')? true : false;
       
        $Psr16Adapter->set($resultkey, $res['data']);

        $currentState = [
            'sessionID' => $sessionID,
            'msisdn' => $msisdn,
            'userData' => $userData,
            'network'   => $network,
            'newSession' => $newSession,
            'message' => $message,
            'level' => 2,
            'page' => 2,
        ];

        $userResponseTracker[] = $currentState;
        $Psr16Adapter->set($sessionID, $userResponseTracker);
    } else if ($lastResponse['page'] === 2 && $lastResponse['page'] == 2) {
        // Useful Resources
        $message = menu::votesummary($Psr16Adapter->get($resultkey),$userData);

        $continueSession = true;

        $currentState = [
            'sessionID' => $sessionID,
            'msisdn' => $msisdn,
            'userData' => $userData,
            'network'   => $network,
            'newSession' => $newSession,
            'message' => $message,
            'level' => 2,
            'page' => 3,
        ];

        $userResponseTracker[] = $currentState;
        $Psr16Adapter->set($sessionID, $userResponseTracker);
    } else if ($lastResponse['page'] === 3 && $userData === '*') {
        $message = "For SMS which of the features do you like best?" .
            "\n3. Bulk SMS" .
            "\n\n*. Go Back" .
            "\n#. Next Page";

        $continueSession = true;

        $currentState = [
            'sessionID' => $sessionID,
            'msisdn' => $msisdn,
            'userData' => $userData,
            'network'   => $network,
            'newSession' => $newSession,
            'message' => $message,
            'level' => 2,
            'page' => 2,
        ];

        $userResponseTracker[] = $currentState;
        $Psr16Adapter->set($sessionID, $userResponseTracker);
    } else if ($lastResponse['page'] === 2 && $userData === '*') {
        $message = "For SMS which of the features do you like best?" .
            "\n1. From File" .
            "\n2. Quick SMS" .
            "\n\n #. Next Page";

        $continueSession = true;
        $currentState = [
            'sessionID' => $sessionID,
            'msisdn' => $msisdn,
            'userData' => $userData,
            'network'   => $network,
            'newSession' => $newSession,
            'message' => $message,
            'level' => 2,
            'page' => 1,
        ];

        $userResponseTracker[] = $currentState;
        $Psr16Adapter->set($sessionID, $userResponseTracker);
    } else if (in_array($userData, ["1", "2", "3", "4"])) {
        $message = menu::paymoney();
       
        $continueSession = false;
    } else {
        $message = "Bad choice!";
        $continueSession = false;
    }
}

http_response_code(200);

// treat this as json
header('Content-Type: application/json');

echo json_encode([
    'sessionID' => $sessionID,
    'msisdn' => $msisdn,
    'userID' => $userID,
    'continueSession' => $continueSession,
    'message' => $message,
]);