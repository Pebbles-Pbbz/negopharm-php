<?php
    include '../../controller/database/MysqlController.php';
    $dbController = new MysqlController();

    $response = array();
    $response['success'] = false;

    $request = json_decode(file_get_contents('php://input'), JSON_UNESCAPED_UNICODE);
    $pharmacyId = $request['pharmacyId'];
    $userId = $request['userId'];

    if(
        empty($pharmacyId)
    ){
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }

    $subQuery1 = null;

    if(empty($userId)){
        $subQuery1 = sprintf(
            "pharmacy_id = '%s'",
            $pharmacyId
        );
    }
    else{
        $subQuery1 = sprintf(
            "pharmacy_id = '%s' AND user_id = '%s'",
            $pharmacyId, $userId
        );
    }

    if(empty($subQuery1)){
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }

    $query = sprintf(
        "SELECT *
        FROM
            negopharm_pharmacy_bid
        WHERE
            %s
        ORDER BY
            created_date",
        
        $subQuery1
    );

    $results = $dbController->executeQuery($query);

    if(!$results['success']){
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }

    $bidders = array();

    for($i=0; $i<count($results['results']); $i++){
        $bidder = array();
        $bidder['bidId'] = $results['results'][$i]['id'];
        $bidder['userId'] = $results['results'][$i]['user_id'];
        $bidder['pharmacyId'] = $results['results'][$i]['pharmacy_id'];
        $bidder['price'] = $results['results'][$i]['price'];
        $bidder['createdDate'] = $results['results'][$i]['created_date'];

        array_push($bidders, $bidder);
    }

	$data = array();
    $data['bidders'] = $bidders;

    $response['data'] = $data;
    $response['success'] = true;
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>