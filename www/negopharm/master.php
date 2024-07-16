<?php
    include 'controller/network/httpController.php';
    include 'the_others/data_source/getdomains.php';

    function isCorrectAuth($auth){
        $result = false;

        $numOfNumber = 0;
        $numOfLargeAlphabet = 0;
        $numOfSmallAlphabet = 0;

        for($i=0; $i<strlen($auth); $i++){
            $char = substr($auth, $i, 1);

            if($char >= '0' && $char <= '9'){
                $numOfNumber++;
            }
            else if($char >= 'A' && $char <= 'Z'){
                $numOfLargeAlphabet++;
            }
            else if($char >= 'a' && $char <= 'z'){
                $numOfSmallAlphabet++;
            }
        }

        if(
            !(
                $numOfNumber == 3
                && $numOfLargeAlphabet == 3
                && $numOfSmallAlphabet == 3
            )
        ){
            return $result;
        }

        $result = true;
        return $result;
    }

    //

    $httpController = new HttpController();

    $response = array();
    $response['success'] = false;

    $request = json_decode(file_get_contents('php://input'), JSON_UNESCAPED_UNICODE);
    $subLink = $request['subLink'];
    $method = $request['method'];
    $auth = $request['auth'];
    $subRequest = $request['request'];

    if(empty($subLink) || empty($method) || empty($auth)){
        $response['errorMsg'] = 'noEssentialParameters';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }

    if(!isCorrectAuth($auth)){
        $response['errorMsg'] = 'incorrectAuth';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }

    $subResponse = null;
    $link = getProtocol() . getDomain() . 'functions/';

    if(strtoupper($method) == 'GET'){
        $subResponse = $httpController->get(
            $link . $subLink,
            $subRequest
        );
    }
    else if(strtoupper($method) == 'POST_JSON'){
        $subResponse = $httpController->postJson(
            $link . $subLink,
            $subRequest
        );
    }
    
    if($subResponse == null){
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }

    if(array_key_exists('debug', $subResponse)){
        $response['debug'] = $subResponse['debug'];
    }

    if(!$subResponse['success']){
        if(array_key_exists('errorMsg', $subResponse)){
            $response['errorMsg'] = $subResponse['errorMsg'];
        }

        if(array_key_exists('errorCode', $subResponse)){
            $response['errorCode'] = $subResponse['errorCode'];
        }

        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }

    if(array_key_exists('data', $subResponse)){
        $response['data'] = $subResponse['data'];
    }

    $response['success'] = true;
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>