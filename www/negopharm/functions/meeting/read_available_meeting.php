<?php
    include '../../controller/database/MysqlController.php';
    $dbController = new MysqlController();
    
    $response = array();
    $response['success'] = false;

    $request = json_decode(file_get_contents('php://input'), JSON_UNESCAPED_UNICODE);
    $pharmacyId = $request['pharmacyId'];
    $date = $request['date'];

    if(
        empty($pharmacyId)
    ){
		echo json_encode($response, JSON_UNESCAPED_UNICODE);
		exit();
	}

    $subQuery1 = '';

    if(!empty($date)){
        $subQuery1 = $subQuery1 . sprintf(
            " AND meeting_date = '%s'",
            $date
        );
    }

    $query1 = sprintf(
        "SELECT *
        FROM
            negopharm_pharmacy_meeting_time
        WHERE
            pharmacy_id = '%s'
            %s
        ORDER BY
            meeting_date, start_time",
        
        $pharmacyId,
        $subQuery1
    );

    $results1 = $dbController->executeQuery($query1);

    if(!$results1['success']){
		echo json_encode($response, JSON_UNESCAPED_UNICODE);
		exit();
    }

    $meetings = array();

    for($i=0; $i<count($results1['results']); $i++){
        $meeting = array();
        $meeting['pharmacyId'] = $results1['results'][$i]['pharmacy_id'];
        $meeting['meetingDate'] = $results1['results'][$i]['meeting_date'];
        $meeting['startTime'] = $results1['results'][$i]['start_time'];
        $meeting['duration'] = $results1['results'][$i]['duration'];
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