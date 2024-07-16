<?php
    include '../../controller/database/MysqlController.php';
    $dbController = new MysqlController();

    $response = array();
    $response['success'] = false;

    $request = json_decode(file_get_contents('php://input'), JSON_UNESCAPED_UNICODE);
    $bidId = $request['bidId'];

    if(
        empty($bidId)
    ){
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }

    $query1 = sprintf(
        "SELECT *
        FROM
            negopharm_pharmacy_bid_approval
        WHERE
            bid_id = '%s'
        LIMIT 1",
        
        $bidId
    );

    $results1 = $dbController->executeQuery($query1);

    if(!$results1['success']){
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }

    $bidApproval = array();
    $bidApproval['id'] = null;
    $bidApproval['buyerApprovedDate'] = null;
    $bidApproval['sellerApprovedDate'] = null;

    if(count($results1['results']) >= 1){
        $bidApprovalId = $results1['results'][0]['id'];
        $buyerApprovedDate = $results1['results'][0]['buyer_approved_date'];
        $sellerApprovedDate = $results1['results'][0]['seller_approved_date'];

        $bidApproval['id'] = $bidApprovalId;

        if(!empty($buyerApprovedDate)){
            if(strlen($buyerApprovedDate) >= 4){
                $bidApproval['buyerApprovedDate'] = $buyerApprovedDate;
            }
        }
        if(!empty($sellerApprovedDate)){
            if(strlen($sellerApprovedDate) >= 4){
                $bidApproval['sellerApprovedDate'] = $sellerApprovedDate;
            }
        }
    }

	$data = array();
    $data['bidApproval'] = $bidApproval;

    $response['data'] = $data;
    $response['success'] = true;
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>