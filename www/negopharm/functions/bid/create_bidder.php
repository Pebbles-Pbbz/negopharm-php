<?php
    include '../../controller/RandomController.php';
    include '../../controller/database/MysqlController.php';

    $dbController = new MysqlController();
    $randomController = new RandomController();
    date_default_timezone_set('Asia/Seoul');
    
    $response = array();
    $response['success'] = false;

    $request = json_decode(file_get_contents('php://input'), JSON_UNESCAPED_UNICODE);
    $userId = $request['userId'];
    $pharmacyId = $request['pharmacyId'];
    $price = $request['price'];

    if(
        empty($userId)
        || empty($pharmacyId)
        || !is_int($price)
    ){
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }

    //

    $query1 = sprintf(
        "SELECT *
        FROM
            negopharm_user
        WHERE
            id = '%s'
        LIMIT 1",

        $userId
    );

    $results1 = $dbController->executeQuery($query1);

    if(!$results1['success']){
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }
    else if(count($results1['results']) <= 0){
        $response['errorMsg'] = 'noUserId';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }

    //

    $query2 = sprintf(
        "SELECT *
        FROM
            negopharm_pharmacy
        WHERE
            id = '%s'
        LIMIT 1",

        $pharmacyId
    );

    $results2 = $dbController->executeQuery($query2);

    if(!$results2['success']){
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }
    else if(count($results2['results']) <= 0){
        $response['errorMsg'] = 'noPharmacyId';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }

    //

    $bidId = $randomController->createString10Id();
    $createdDate = strval(date('Y-m-d H:i:s'));

    $query4 = sprintf(
        "INSERT INTO negopharm_pharmacy_bid
            (id, user_id, pharmacy_id, price, created_date)
        VALUES
            ('%s', '%s', '%s', %d, '%s')",
        
        $bidId, $userId, $pharmacyId, $price, $createdDate
    );

    $results4 = $dbController->executeQuery($query4);

    if(!$results4['success']){
		echo json_encode($response, JSON_UNESCAPED_UNICODE);
		exit();
    }
    
    $response['success'] = true;
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>