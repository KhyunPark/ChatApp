<?php
    // 1. Problem
    // If User input slowly, it doesn't work. So I have to change it with adventlistener of changing.


    // 2. Problem
    // Without Refresh, innner Chat has to be blank, and when the other user comes, it has to work as same as before.

    // All filter private msges with PHP!!!!!!!!!!!!!!!!!!!!!!!

    // 2. <PROCESS> Get input from stdin to get message body (JSON)
    $inputStr = file_get_contents("php://input"); 

    $inputObj = json_decode($inputStr); // from html, JSON String comes in, so Make it as an Object.

    // $personObj= {};
    // $privateObj= {};
    // $contentObj= {};
    // $personObj{} = $inputObj->person;
    // $privateObj{} = $inputObj->private;
    // $contentObj{} = $inputObj->content;
    $timeStr = date('m/d/Y H:i:s', time());
        
    // Initialize 
    $personObj= "";
    $privateObj= "";
    $contentObj= "";
    $activityObj= "";

    $currentChatTimeObj= "";

    if(isset($inputObj->person)) $personObj = $inputObj->person; // Get person input
    if(isset($inputObj->private)) $privateObj = $inputObj->private; // if there is PRIVATE, get private input
    if(isset($inputObj->content)) $contentObj = $inputObj->content; // Get content input
    $activityObj = $inputObj->activity; // Get activity input ( New Msg or Get History )
    if(isset($inputObj->currentChatTime)) $currentChatTimeObj = $inputObj->currentChatTime;

    // 3. <PROCESS> Use the above Objects, Put all objects into ChatPate.in.log which is history file as CSV form with Pipe line 
    if( isset($activityObj) && $activityObj !== "GET HISTORY" ) {
        date_default_timezone_set('Asia/Seoul');

        file_put_contents("chatPage.in.log"
            , date('m/d/Y H:i:s', time()) . "|" . " [{$_SERVER['REMOTE_ADDR']}]: "  . "|"
                                                                                    . $personObj
                                                                                    . "|" 
                                                                                    . $privateObj
                                                                                    . "|"
                                                                                    . $contentObj
                                                                                    . "|"
                                                                                    . $activityObj
                                                                                    . PHP_EOL
            , FILE_APPEND);
    }

    // 4. <PROCESS> Now HISTORY STUFF 
    // Organizse chat history first
    $historyStr = file_get_contents("chatPage.in.log");
    $historyArr = explode(PHP_EOL, trim($historyStr));
    $jsnArr = [];
    for( $i = 0 ; $i < count($historyArr) ; $i++ ){
        $jsnArr[] = explode("|", $historyArr[$i]);
    }; 
    
    // Last chat and the one before last chat
    $lastChatLength = count($historyArr)-1;
    $lastChatBeforeLength = count($historyArr)-2;

    // 01/19/2018 16:47:48 | [::1]: | Cat |     | Wait | GET HISTORY | Time Stamp
    if( intval($currentChatTimeObj) !== 0 ){
        $chatTime = new DateTime($currentChatTimeObj);
        $historyTime = new DateTime($jsnArr[count($historyArr)-1][0]);
        // $unixTime = $date->getTimestamp();
        // $currentChatTimeObj;
        // Check if PHP workS for GET HISTORY or NEW MESSAGE
        $chatTimeOrder = 0;
        for( $i = 0 ; $i < count($historyArr) ; $i++ ) {
            $checkHistorywithChatTime = new DateTime($jsnArr[$i][0]);
            if( $checkHistorywithChatTime->format("U") === $chatTime->format("U") ) {
                $chatTimeOrder = $i+1;
                // echo("This is chatTiimeOrder : " . $chatTimeOrder . PHP_EOL);
                break;
            }
        }
        $iNum = $chatTimeOrder;
        // echo("This is iNum : " . $iNum . PHP_EOL);
        // echo("This is historyArr length : " . count($historyArr) . PHP_EOL);
    }
    else { 
        $chatTime = 0;
        $historyTime = count($historyArr);
        $iNum = 0;
    }

    if($activityObj === "GET HISTORY"){ // GET HISTORY
        $returnArr = [];
        // $currentChatTimeObj 가 몇번째인지 알아야함 historyArr 안에서...!!!
        if($chatTime < $historyTime ) {
            for( $i = $iNum ; $i < count($historyArr) ; $i++ ){
                if($jsnArr[$i][3] !== "") {
                    if($jsnArr[$i][3] === $personObj || $jsnArr[$i][2] === $personObj) {
                        $thisObj = new stdClass();
                        $thisObj->timing = $jsnArr[$i][0];
                        $thisObj->person = $jsnArr[$i][2];
                        $thisObj->private = $jsnArr[$i][3];
                        $thisObj->content = $jsnArr[$i][4];
                        $returnArr[] = $thisObj;
                    }
                    else { continue; }
                }
                else {
                    $thisObj = new stdClass();
                    $thisObj->timing = $jsnArr[$i][0];
                    $thisObj->person = $jsnArr[$i][2];
                    $thisObj->content = $jsnArr[$i][4];
                    $returnArr[] = $thisObj;
                }
            }
        echo(json_encode($returnArr));            
        }
    }
    else {  
        // NEW MESSAGE
        $thisTime = date('m/d/Y H:i:s', time());
        $inputStr = substr($inputStr, 1);
        $inputStr = substr_replace("{","{ \"timing\":\"{$thisTime}\",". $inputStr, 0); // Get rid of this park (  "{"  )
        echo($inputStr);
    }
?>