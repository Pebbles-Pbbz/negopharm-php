<?php
    include '../../controller/RandomController.php';
    include '../../controller/database/MysqlController.php';

    $dbController = new MysqlController();
    $randomController = new RandomController();
    date_default_timezone_set('Asia/Seoul');
    
    $response = array();
    $response['success'] = false;

    $request = json_decode(file_get_contents('php://input'), JSON_UNESCAPED_UNICODE);
    $bidId = $request['bidId'];
    $traderType = $request['traderType'];
    $approved = $request['approved'];

    if(
        empty($bidId)
        || empty($traderType)
        || !is_bool($approved)
    ){
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }

    //

    $query1 = sprintf(
        "SELECT *
        FROM
            negopharm_pharmacy_bid
        WHERE
            id = '%s'
        LIMIT 1",

        $bidId
    );

    $results1 = $dbController->executeQuery($query1);

    if(!$results1['success']){
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }
    else if(count($results1['results']) <= 0){
        $response['errorMsg'] = 'noBid';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }

    //

    $query2 = sprintf(
        "SELECT *
        FROM
            negopharm_pharmacy_bid_approval
        WHERE
            bid_id = '%s'
        LIMIT 1",
        
        $bidId
    );

    $results2 = $dbController->executeQuery($query2);

    if(!$results2['success']){
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }

    $bidApprovalId = null;
    $isInsertMode = true;
    
    if(count($results2['results']) >= 1){
        $bidApprovalId = $results2['results'][0]['id'];
        $isInsertMode = false;
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
        $approvedDate = $createdDate;
    }
    else{
        $approvedDate = '-1';
    }

    if($isInsertMode){
        $bidApprovalId = $randomController->createString10Id();
        $query3 = sprintf(
            "INSERT INTO
                negopharm_pharmacy_bid_approval
                    (id, bid_id, %s, created_date)
            VALUES
                ('%s', '%s', '%s', '%s')",
            
            $columnName,
            $bidApprovalId, $bidId, $approvedDate, $createdDate
        );
    }
    else{
        $query3 = sprintf(
            "UPDATE negopharm_pharmacy_bid_approval
            SET
                %s = '%s',
                updated_date = '%s'
            WHERE
                id = '%s'",
                
            $columnName, $approvedDate,
            $createdDate,

            $bidApprovalId
        );
    }

    $results3 = $dbController->executeQuery($query3);

    if(!$results3['success']){
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }

    $data = array();
    $data['bidApprovalId'] = $bidApprovalId;

    $response['data'] = $data;
    $response['success'] = true;
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>