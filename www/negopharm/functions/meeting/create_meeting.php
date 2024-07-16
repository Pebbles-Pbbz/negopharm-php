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
    $meetingDate = $request['meetingDate'];
    $startTime = $request['startTime'];

    if(
        empty($userId)
        || empty($pharmacyId)
        || empty($meetingDate)
        || empty($startTime)
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
        $response['errorMsg'] = 'noUser';
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
        $response['errorMsg'] = 'noPharmacy';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }

    //

    $meetingId = null;
    $isDuplicate = true;  

    do{
        $meetingId = $randomController->createString10Id();

        $query3 = sprintf(
            "SELECT *
            FROM
                negopharm_pharmacy_meeting_schedule
            WHERE
                id = '%s'
            LIMIT 1",
            $meetingId
        );

        $results3 = $dbController->executeQuery($query3);

        if(!$results3['success']){
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            exit();
        }
        else if(count($results3['results']) <= 0){
            $isDuplicate = false;
        }
    }
    while ($isDuplicate);

    //

    $createdDate = strval(date('Y-m-d H:i:s'));

    $query4 = sprintf(
        "INSERT INTO
            negopharm_pharmacy_meeting_schedule
                (id, user_id, pharmacy_id, meeting_date,
                start_time, created_date)
        VALUES
            ('%s', '%s', '%s', '%s',
            '%s', '%s')",
        
        $meetingId, $userId, $pharmacyId, $meetingDate,
        $startTime, $createdDate
    );

    $results4 = $dbController->executeQuery($query4);

    if(!$results4['success']){
		echo json_encode($response, JSON_UNESCAPED_UNICODE);
		exit();
    }

    $response['success'] = true;
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>