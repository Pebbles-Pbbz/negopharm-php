<?php
    include '../../controller/RandomController.php';
    include '../../controller/database/MysqlController.php';

    $dbController = new MysqlController();
    date_default_timezone_set('Asia/Seoul');
    
    $response = array();
    $response['success'] = false;

    $request = json_decode(file_get_contents('php://input'), JSON_UNESCAPED_UNICODE);
    $contractId = $request['contractId'];
    $traderType = $request['traderType'];
    $approved = $request['approved'];

    if(
        empty($contractId)
        || empty($traderType)
        || !is_bool($approved)
    ){
        $response['errorCode'] = '1';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }

    //

    $query1 = sprintf(
        "SELECT *
        FROM
            negopharm_pharmacy_contract
        WHERE
            id = '%s'
        LIMIT 1",

        $contractId
    );

    $results1 = $dbController->executeQuery($query1);

    if(!$results1['success']){
        $response['errorCode'] = '2';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }
    else if(count($results1['results']) <= 0){
        $response['errorCode'] = '3';
        $response['errorMsg'] = 'noContract';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }

    $buyerApprovedDate = $results1['results'][0]['buyer_approved_date'];
    $sellerApprovedDate = $results1['results'][0]['seller_approved_date'];

    if(!empty($buyerApprovedDate) && !empty($sellerApprovedDate)){
        $response['errorCode'] = '4';
        $response['errorMsg'] = 'alreadyBothContractApproved';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }

    //

    $columnName = null;
    $approvedDate = null;

    if($traderType == 'buyer'){
        $columnName = 'buyer_approved_date';
    }
    else if($traderType == 'seller'){
        $columnName = 'seller_approved_date';
    }

    $createdDate = strval(date('Y-m-d H:i:s'));

    if($approved){
        $approvedDate = "'" . $createdDate . "'";
    }
    else{
        $approvedDate = "NULL";
    }

    $query2 = sprintf(
        "UPDATE negopharm_pharmacy_contract
        SET
            %s = %s,
            updated_date = '%s'
        WHERE
            id = '%s'",
            
        $columnName, $approvedDate,
        $createdDate,

        $contractId
    );

    $results2 = $dbController->executeQuery($query2);

    if(!$results2['success']){
        $response['errorCode'] = '5';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }
    
    //

    $query3 = sprintf(
        "SELECT buyer_approved_date,
            seller_approved_date
		FROM
            negopharm_pharmacy_contract
        WHERE
            id = '%s'
        LIMIT 1",
        
        $contractId
    );

    $results3 = $dbController->executeQuery($query3);

    if(!$results3['success']){
        $response['errorCode'] = '6';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }
    else if(count($results3['results']) <= 0){
        $response['errorCode'] = '7';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }

    $buyerApprovedDate = $results3['results'][0]['buyer_approved_date'];
    $sellerApprovedDate = $results3['results'][0]['seller_approved_date'];

    $data = array();
    $data['buyerApprovedDate'] = $buyerApprovedDate;
    $data['sellerApprovedDate'] = $sellerApprovedDate;
    
    $response['data'] = $data;
    $response['success'] = true;
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>