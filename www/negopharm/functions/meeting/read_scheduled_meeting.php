<?php
    include '../../controller/database/MysqlController.php';
    $dbController = new MysqlController();
    
    $response = array();
    $response['success'] = false;

    $request = json_decode(file_get_contents('php://input'), JSON_UNESCAPED_UNICODE);
    $sellerUserId = $request['sellerUserId'];
    $buyerUserId = $request['buyerUserId'];
    $pharmacyId = $request['pharmacyId'];
    $date = $request['date'];

    if(
        empty($sellerUserId)
        && empty($buyerUserId)
        && empty($pharmacyId)
        && empty($date)
    ){
		echo json_encode($response, JSON_UNESCAPED_UNICODE);
		exit();
	}

    //

    $subQuery1 = '';

    if(!empty($sellerUserId)){
        $subQuery1 = $subQuery1 . ", negopharm_pharmacy b";
    }

    //

    $subQuery2 = '';

    if(!empty($sellerUserId)){
        $subQuery2 = $subQuery2 . sprintf(
            " AND b.seller_user_id = '%s' AND b.id = a.pharmacy_id",
            $sellerUserId
        );
    }

    if(!empty($buyerUserId)){
        $subQuery2 = $subQuery2 . sprintf(
            " AND a.user_id = '%s'",
            $buyerUserId
        );
    }

    if(!empty($pharmacyId)){
        $subQuery2 = $subQuery2 . sprintf(
            " AND a.pharmacy_id = '%s'",
            $pharmacyId
        );
    }

    if(!empty($date)){
        $subQuery2 = $subQuery2 . sprintf(
            " AND a.meeting_date = '%s'",
            $date
        );
    }

    if(empty($subQuery2)){
		echo json_encode($response, JSON_UNESCAPED_UNICODE);
		exit();
    }

    $query1 = sprintf(
        "SELECT a.*
        FROM
            negopharm_pharmacy_meeting_schedule a
            %s
        WHERE
            TRUE
            %s",
        $subQuery1, $subQuery2
    );

    $results1 = $dbController->executeQuery($query1);

    if(!$results1['success']){
		echo json_encode($response, JSON_UNESCAPED_UNICODE);
		exit();
    }

    $meetings = array();

    for($i=0; $i<count($results1['results']); $i++){
        $meeting = array();
        $meeting['userId'] = $results1['results'][$i]['user_id'];
        $meeting['pharmacyId'] = $results1['results'][$i]['pharmacy_id'];
        $meeting['meetingDate'] = $results1['results'][$i]['meeting_date'];
        $meeting['startTime'] = $results1['results'][$i]['start_time'];
        $meeting['createdDate'] = $results1['results'][$i]['created_date'];
        $meeting['updatedDate'] = $results1['results'][$i]['updated_date'];

        array_push($meetings, $meeting);
    }

    $data = array();
    $data['meetings'] = $meetings;

    $response['data'] = $data;
    $response['success'] = true;
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>