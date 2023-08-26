<?php
    use Phpfastcache\Helper\Psr16Adapter;
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
            $response = ucfirst($result['data']['nomineeData'][0]['events'][0]['label']) . "\n\n";
            $response .= ucfirst($result['data']['categoryName']) . "\n";
            $response .= ucfirst($result['data']['nomineeName']) . "\n\n";
            $response .= "Enter the number of votes  \n";
            $response .= "(GHc ".$result['data']['votePrice'] ."/vote)";

            

        
            // $_SESSION[$sessionID] = ['level' => 3, 'option' => 1.11, 'code' => $userdata];
        }
        
        else{
            $response = 'Invalid Code';
        }

        return ['message' => $response, 'data' => $result];


       
    }


    public static function votesummary($data,$userData){
        $response = "Summary\n";
        $response .= "Nominee: ".ucfirst($data['data']['nomineeName'])."\n";
        $response .= "Category: ".ucfirst($data['data']['categoryName'])."\n";
        $response .= 'Total Votes: '.$userData."\n";
        $response .= 'Total Amount: '.($data['data']['votePrice'] *  $userData)."\n";
        $response .= '1. Proceed';
        return $response;
    }

    public static function paymoney() {


                $curl = curl_init();


        curl_setopt_array($curl, array(

        CURLOPT_URL => "https://api.paystack.co/charge",

        CURLOPT_RETURNTRANSFER => true,

        CURLOPT_ENCODING => "",

        CURLOPT_MAXREDIRS => 10,

        CURLOPT_TIMEOUT => 30,

        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,

        CURLOPT_CUSTOMREQUEST => "POST",

        CURLOPT_POSTFIELDS => [

            "amount" => 100,

            "email" => "kpin463@gmail.com",

            "currency" => "GHS",

            "mobile_money" => [

            "phone" =>'0556676471',

            "provider" => 'mtn',

            ]

        ],

        CURLOPT_HTTPHEADER => array(

            "Authorization: Bearer sk_test_97695063674e5ad3dffed30fca3b5f1d0d0744c6",

            "Content-Type: application/json"

        ),

        ));


        $response = curl_exec($curl);

        $err = curl_error($curl);


        curl_close($curl);

        if ($err) {

           return "cURL Error #:" . $err;
          
          } else {
          
            return $response;

          }


    }



        
    
}







































// *******************************************
// * Copyright (c) 2022 Dollarsoft Enterprise. ( http://www.github.com/dollarstir)
// * All rights reserved.
// * This  system was developed by Dollarsoft Enterprise on  25th August 2023 