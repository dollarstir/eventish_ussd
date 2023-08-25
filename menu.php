<?php

// *******************************************
// * Copyright (c) 2022 Dollarsoft Enterprise. ( http://www.github.com/dollarstir)
// * All rights reserved.
// * This  system was developed by Dollarsoft Enterprise on  25th August 2023 
class menu{
    public static function mainmenu($sessionID){
        session_start();
        $_SESSION[$sessionID] = ['level' => 1, 'option' => 1];
        $response = 'Welcome to Eventish. Please select an option to continue
        1.Vote 
        2. Ticket
        3. Contact Us';
        
        return  $response;
    }


    public static function vote(){
      
       
        $response = ' Enter Nominee\'s Code ';
        
        return  $response;
    }




    // getting 

    public function getnomineedata($userdata){
      

        $url = "https://vote.eventish.tech/api/ussdvote?code=".$userdata;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        $result = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($result, true);
        return $result;


    }

    public   function voteamount($userdata){
        session_start();
        $result = $this->getnomineedata($userdata);
        if ($result['status'] == 1) {
            $response = $result['data']['nomineeData'][0]['events'][0]['label'] . "\n\n";
            $response .= $result['data']['categoryName'] . "\n";
            $response .= $result['data']['nomineeName'];
        
            // $_SESSION[$sessionID] = ['level' => 3, 'option' => 1.11, 'code' => $userdata];
        }
        
        else{
            $response = 'Invalid Code';
        }

        return $response;


       
    }
}







































// *******************************************
// * Copyright (c) 2022 Dollarsoft Enterprise. ( http://www.github.com/dollarstir)
// * All rights reserved.
// * This  system was developed by Dollarsoft Enterprise on  25th August 2023 