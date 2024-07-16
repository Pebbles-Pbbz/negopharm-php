<?php
    include '../../controller/RandomController.php';
    include '../../controller/database/MysqlController.php';

    $dbController = new MysqlController();
    $randomController = new RandomController();
    date_default_timezone_set('Asia/Seoul');
    
    $response = array();
    $response['success'] = false;

    $request = json_decode(file_get_contents('php://input'), JSON_UNESCAPED_UNICODE);
    $bidApprovalId = $request['bidApprovalId'];

    $contractId = $request['contractId'];
    $realEstateAgentUserId = $request['realEstateAgentUserId'];
    $pharmacyName = $request['pharmacyName'];
    $typeName = $request['typeName'];

    $completedDate = $request['completedDate'];
    $startDate = $request['startDate'];
    $endDate = $request['endDate'];

    $deposit = $request['deposit'];
    $keyMoney = $request['keyMoney'];
    $downPayment = $request['downPayment'];
    $intermediatePayment = $request['intermediatePayment'];
    $finalPayment = $request['finalPayment'];
    $rent = $request['rent'];

    $area = $request['area'];
    $howToProceed = $request['howToProceed'];
    $beforeMonth = $request['beforeMonth'];

    $keyMoneyAccountName = $request['keyMoneyAccountName'];
    $keyMoneyAccountNumber = $request['keyMoneyAccountNumber'];
    $depositAccountName = $request['depositAccountName'];
    $depositAccountNumber = $request['depositAccountNumber'];
    $rentAccountName = $request['rentAccountName'];
    $rentAccountNumber = $request['rentAccountNumber'];

    $rentIncludeVat = $request['rentIncludeVat'];
    $particularThings = $request['particularThings'];
    
    if(empty($bidApprovalId)){
        $response['errorCode'] = '1';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }

    //

    $query1 = sprintf(
        "SELECT *
        FROM
            negopharm_pharmacy_bid_approval
        WHERE
            id = '%s'
        LIMIT 1",

        $bidApprovalId
    );

    $results1 = $dbController->executeQuery($query1);

    if(!$results1['success']){
        $response['errorCode'] = '2';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }
    else if(count($results1['results']) <= 0){
        $response['errorCode'] = '3';
        $response['errorMsg'] = 'bothBidUnapproved';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }

    $buyerBidApprovedDate = $results1['results'][0]['buyer_approved_date'];
    $sellerBidApprovedDate = $results1['results'][0]['seller_approved_date'];

    if(empty($buyerBidApprovedDate)){
        $response['errorCode'] = '4';
        $response['errorMsg'] = 'buyerBidUnapproved';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }

    if(empty($sellerBidApprovedDate)){
        $response['errorCode'] = '5';
        $response['errorMsg'] = 'sellerBidUnapproved';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }

    //

    if(!empty($contractId)){
        $query2 = sprintf(
            "SELECT *
            FROM
                negopharm_pharmacy_contract
            WHERE
                id = '%s'
            LIMIT 1",
    
            $contractId
        );
    
        $results2 = $dbController->executeQuery($query2);
    
        if(!$results2['success']){
            $response['errorCode'] = '6';
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            exit();
        }
        else if(count($results2['results']) <= 0){
            $response['errorCode'] = '7';
            $response['errorMsg'] = 'incorrectContractId';
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            exit();
        }
    
        $buyerContractApprovedDate = $results2['results'][0]['buyer_approved_date'];
        $sellerContractApprovedDate = $results2['results'][0]['seller_approved_date'];
    
        if(!empty($buyerContractApprovedDate) && !empty($sellerContractApprovedDate)){
            $response['errorCode'] = '8';
            $response['errorMsg'] = 'alreadyBothContractApproved';
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            exit();
        }
    }

    //

    $typeId = null;

    if($typeName != null){
        $query3 = sprintf(
            "SELECT *
            FROM
                negopharm_pharmacy_contract_type
            WHERE
                name like '%s%s%s'
            LIMIT 1",

            '%', $typeName, '%'
        );

        $results3 = $dbController->executeQuery($query3);
        
        if(!$results3['success']){
            $response['errorCode'] = '9';
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            exit();
        }
        else if(count($results3['results']) <= 0){
            $response['errorCode'] = '10';
            $response['errorMsg'] = 'noType';
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            exit();
        }
        
        $typeId = $results3['results'][0]['id'];
    }

    if($typeId == null){
        $response['errorCode'] = '11';
        $response['errorMsg'] = 'noType';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }

    //

    if($realEstateAgentUserId != null){
        $query4 = sprintf(
            "SELECT *
            FROM
                negopharm_user
            WHERE
                id = '%s'
            LIMIT 1",
            
            $realEstateAgentUserId
        );

        $results4 = $dbController->executeQuery($query4);
        
        if(!$results4['success']){
            $response['errorCode'] = '12';
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            exit();
        }
        else if(count($results4['results']) <= 0){
            $response['errorCode'] = '13';
            $response['errorMsg'] = 'noRealEstateAgentUser';
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            exit();
        }
    }

    //

    if($realEstateAgentUserId == null){
        $realEstateAgentUserId = "NULL";
    }
    else{
        $realEstateAgentUserId = "'" . $realEstateAgentUserId . "'";
    }

    if($pharmacyName == null){
        $pharmacyName = "NULL";
    }
    else{
        $pharmacyName = "'" . $pharmacyName . "'";
    }
    
    if($completedDate == null){
        $completedDate = "NULL";
    }
    else{
        $completedDate = "'" . $completedDate . "'";
    }
    
    if($startDate == null){
        $startDate = "NULL";
    }
    else{
        $startDate = "'" . $startDate . "'";
    }
    
    if($endDate == null){
        $endDate = "NULL";
    }
    else{
        $endDate = "'" . $endDate . "'";
    }

    if($howToProceed == null){
        $howToProceed = "NULL";
    }
    else{
        $howToProceed = "'" . $howToProceed . "'";
    }

    if($keyMoneyAccountName == null){
        $keyMoneyAccountName = "NULL";
    }
    else{
        $keyMoneyAccountName = "'" . $keyMoneyAccountName . "'";
    }
    
    if($keyMoneyAccountNumber == null){
        $keyMoneyAccountNumber = "NULL";
    }
    else{
        $keyMoneyAccountNumber = "'" . $keyMoneyAccountNumber . "'";
    }

    if($depositAccountName == null){
        $depositAccountName = "NULL";
    }
    else{
        $depositAccountName = "'" . $depositAccountName . "'";
    }

    if($depositAccountNumber == null){
        $depositAccountNumber = "NULL";
    }
    else{
        $depositAccountNumber = "'" . $depositAccountNumber . "'";
    }

    if($rentAccountName == null){
        $rentAccountName = "NULL";
    }
    else{
        $rentAccountName = "'" . $rentAccountName . "'";
    }

    if($rentAccountNumber == null){
        $rentAccountNumber = "NULL";
    }
    else{
        $rentAccountNumber = "'" . $rentAccountNumber . "'";
    }
    
    if($deposit == null){
        $deposit = "NULL";
    }
    else{
        $deposit = strval($deposit);
    }
    
    if($keyMoney == null){
        $keyMoney = "NULL";
    }
    else{
        $keyMoney = strval($keyMoney);
    }
    
    if($downPayment == null){
        $downPayment = "NULL";
    }
    else{
        $downPayment = strval($downPayment);
    }
    
    if($intermediatePayment == null){
        $intermediatePayment = "NULL";
    }
    else{
        $intermediatePayment = strval($intermediatePayment);
    }
    
    if($finalPayment == null){
        $finalPayment = "NULL";
    }
    else{
        $finalPayment = strval($finalPayment);
    }
    
    if($rent == null){
        $rent = "NULL";
    }
    else{
        $rent = strval($rent);
    }

    if($area == null){
        $area = "NULL";
    }
    else{
        $area = strval($area);
    }

    if($beforeMonth == null){
        $beforeMonth = "NULL";
    }
    else{
        $beforeMonth = strval($beforeMonth);
    }
    
    if(!is_bool($rentIncludeVat)){
        $rentIncludeVat = "NULL";
    }
    else{
        if($rentIncludeVat){
            $rentIncludeVat = "1";
        }
        else{
            $rentIncludeVat = "0";
        }
    }
    
    $bidApprovalId = "'" . $bidApprovalId . "'";
    $createdDate = strval(date('Y-m-d H:i:s'));

    //
    
    $isInsertMode = false;

    if(empty($contractId)){
        $isInsertMode = true;
    }

    $query5 = null;

    if($isInsertMode){
        $contractId = $randomController->createString10Id();
        $query5 = sprintf(
            "INSERT INTO
                negopharm_pharmacy_contract
                    (id, contract_type_id, bid_approval_id, pharmacy_name, completed_date,
                    start_date, end_date, deposit, key_money, down_payment,
                    intermediate_payment, final_payment, rent, rent_include_vat, area,
                    how_to_proceed, real_estate_agent_user_id, before_month,
                    key_money_account_name, key_money_account_number,
                    deposit_account_name, deposit_account_number,
                    rent_account_name, rent_account_number,
                    buyer_approved_date, seller_approved_date, created_date)
            VALUES
                ('%s', '%s', %s, %s, %s,
                %s, %s, %s, %s, %s,
                %s, %s, %s, %s, %s,
                %s, %s, %s,
                %s, %s,
                %s, %s,
                %s, %s,
                NULL, NULL, '%s')",
            
            $contractId, $typeId, $bidApprovalId, $pharmacyName, $completedDate,
            $startDate, $endDate, $deposit, $keyMoney, $downPayment,
            $intermediatePayment, $finalPayment, $rent, $rentIncludeVat, $area,
            $howToProceed, $realEstateAgentUserId, $beforeMonth,
            $keyMoneyAccountName, $keyMoneyAccountNumber,
            $depositAccountName, $depositAccountNumber,
            $rentAccountName, $rentAccountNumber,
            $createdDate
        );
    }
    else{
        $query5 = sprintf(
            "UPDATE negopharm_pharmacy_contract
            SET
                contract_type_id = '%s', pharmacy_name = %s, completed_date = %s,
                start_date = %s, end_date = %s, deposit = %s, key_money = %s, down_payment = %s,
                intermediate_payment = %s, final_payment = %s, rent = %s, rent_include_vat = %s, area = %s,
                how_to_proceed = %s, real_estate_agent_user_id = %s, before_month = %s,
                key_money_account_name = %s, key_money_account_number = %s,
                deposit_account_name = %s, deposit_account_number = %s,
                rent_account_name = %s, rent_account_number = %s,
                buyer_approved_date = NULL, seller_approved_date = NULL, updated_date = '%s'
            WHERE
                id = '%s'",
            
            $typeId, $pharmacyName, $completedDate,
            $startDate, $endDate, $deposit, $keyMoney, $downPayment,
            $intermediatePayment, $finalPayment, $rent, $rentIncludeVat, $area,
            $howToProceed, $realEstateAgentUserId, $beforeMonth,
            $keyMoneyAccountName, $keyMoneyAccountNumber,
            $depositAccountName, $depositAccountNumber,
            $rentAccountName, $rentAccountNumber,
            $createdDate,
            $contractId
        );
    }

    $results5 = $dbController->executeQuery($query5);

    if(!$results5['success']){
        $response['errorCode'] = '14';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }

    //

    if(!$isInsertMode){
        $query6 = sprintf(
            "DELETE FROM
                negopharm_pharmacy_contract_particular_possession
            WHERE
                contract_id = '%s'",
            
            $contractId
        );
    
        $results6 = $dbController->executeQuery($query6);
    
        if(!$results6['success']){
            $response['errorCode'] = '15';
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            exit();
        }
    }

    //

    if(is_array($particularThings)){
        $success = true;

        for($i=0; $i<count($particularThings); $i++){
            $name = $particularThings[$i];
            $query7 = sprintf(
                "INSERT INTO
                    negopharm_pharmacy_contract_particular_possession
                        (contract_id, name, created_date)
                VALUES
                    ('%s', '%s', '%s')",
                $contractId, $name, $createdDate
            );

            $results7 = $dbController->executeQuery($query7);

            if(!$results7['success']){
                $success = false;
                continue;
            }
        }

        if(!$success){
            $response['errorCode'] = '16';
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            exit();
        }
    }

    $data = array();
    $data['contractId'] = $contractId;
    
    $response['data'] = $data;
    $response['success'] = true;
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>