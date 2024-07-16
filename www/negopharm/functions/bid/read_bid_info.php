<?php
    include '../../controller/database/MysqlController.php';
    $dbController = new MysqlController();

    $response = array();
    $response['success'] = false;

    $request = json_decode(file_get_contents('php://input'), JSON_UNESCAPED_UNICODE);
    $id = $request['id'];

    if(
        empty($id)
    ){
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }

    $query = sprintf(
        "SELECT a.*,
            a.user_id AS buyer_user_id,
	        b.seller_user_id
        FROM
            negopharm_pharmacy_bid a,
            negopharm_pharmacy b
        WHERE
            a.id = '%s'
            AND b.id = a.pharmacy_id
        LIMIT 1",
        
        $id
    );

    $results = $dbController->executeQuery($query);

    if(!$results['success']){
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }

    $bidder = null;

    if(count($results['results']) >= 1){
        $bidder = array();
        $bidder['buyerUserId'] = $results['results'][0]['buyer_user_id'];
        $bidder['sellerUserId'] = $results['results'][0]['seller_user_id'];
        $bidder['pharmacyId'] = $results['results'][0]['pharmacy_id'];
        $bidder['price'] = $results['results'][0]['price'];
        $bidder['createdDate'] = $results['results'][0]['created_date'];
    }

	$data = array();
    $data['bidder'] = $bidder;

    $response['data'] = $data;
    $response['success'] = true;
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>