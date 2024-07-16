<?php
    include '../../controller/database/MysqlController.php';
    $dbController = new MysqlController();

    $response = array();
    $response['success'] = false;

    $request = json_decode(file_get_contents('php://input'), JSON_UNESCAPED_UNICODE);
    $bidApprovalId = $request['bidApprovalId'];

    if(empty($bidApprovalId)){
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }

    //

    $query1 = sprintf(
        "SELECT a.*, b.name AS type_name
		FROM
            negopharm_pharmacy_contract a
            LEFT JOIN negopharm_pharmacy_contract_type b
            ON b.id = a.contract_type_id
        WHERE
            bid_approval_id = '%s'",
        
        $bidApprovalId
    );

    $results1 = $dbController->executeQuery($query1);

    if(!$results1['success']){
        $response['errorCode'] = '1';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }

    $contracts = array();

    for($i=0; $i<count($results1['results']); $i++){
        $contract = array();
        $contract['contractId'] = $results1['results'][$i]['id'];
        $contract['realEstateAgentUserId'] = $results1['results'][$i]['real_estate_agent_user_id'];
        $contract['pharmacyName'] = $results1['results'][$i]['pharmacy_name'];
        $contract['typeName'] = $results1['results'][$i]['type_name'];
        
        $contract['completedDate'] = $results1['results'][$i]['completed_date'];
        $contract['startDate'] = $results1['results'][$i]['start_date'];
        $contract['endDate'] = $results1['results'][$i]['end_date'];

        $contract['deposit'] = $results1['results'][$i]['deposit'];
        $contract['keyMoney'] = $results1['results'][$i]['key_money'];
        $contract['downPayment'] = $results1['results'][$i]['down_payment'];
        $contract['intermediatePayment'] = $results1['results'][$i]['intermediate_payment'];
        $contract['finalPayment'] = $results1['results'][$i]['final_payment'];
        $contract['rent'] = $results1['results'][$i]['rent'];
        
        $contract['area'] = $results1['results'][$i]['area'];
        $contract['howToProceed'] = $results1['results'][$i]['how_to_proceed'];
        $contract['beforeMonth'] = $results1['results'][$i]['before_month'];

        $contract['keyMoneyAccountName'] = $results1['results'][$i]['key_money_account_name'];
        $contract['keyMoneyAccountNumber'] = $results1['results'][$i]['key_money_account_number'];
        $contract['depositAccountName'] = $results1['results'][$i]['deposit_account_name'];
        $contract['depositAccountNumber'] = $results1['results'][$i]['deposit_account_number'];
        $contract['rentAccountName'] = $results1['results'][$i]['rent_account_name'];
        $contract['rentAccountNumber'] = $results1['results'][$i]['rent_account_number'];

        $contract['buyerApprovedDate'] = $results1['results'][$i]['buyer_approved_date'];
        $contract['sellerApprovedDate'] = $results1['results'][$i]['seller_approved_date'];
        $contract['createdDate'] = $results1['results'][$i]['created_date'];
        $contract['updatedDate'] = $results1['results'][$i]['updated_date'];

        $rentIncludeVatInt = $results1['results'][$i]['rent_include_vat'];
        $contract['rentIncludeVat'] = null;

        if($rentIncludeVatInt == 1){
            $contract['rentIncludeVat'] = true;
        }
        else if($rentIncludeVatInt == 0){
            $contract['rentIncludeVat'] = false;
        }

        $contract['particularThings'] = array();
        array_push($contracts, $contract);
    }

    //

    if(count($contracts) >= 1){
        $contractIdsStr = '';

        for($i=0; $i<count($contracts); $i++){
            $char = ', ';

            if($i <= 0){
                $char = '';
            }

            $contractIdsStr = $contractIdsStr . $char . "'" . $contracts[$i]['contractId'] . "'";
        }

        $query2 = sprintf(
            "SELECT *
            FROM
                negopharm_pharmacy_contract_particular_possession
            WHERE
                contract_id in (%s)",
            
            $contractIdsStr
        );

        $results2 = $dbController->executeQuery($query2);

        if(!$results2['success']){
            $response['errorCode'] = '2';
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            exit();
        }

        $particularThings = array();

        for($i=0; $i<count($results2['results']); $i++){
            $particular = array();
            $particular['contractId'] = $results2['results'][$i]['contract_id'];
            $particular['name'] = $results2['results'][$i]['name'];

            array_push($particularThings, $particular);
        }

        if(count($particularThings) >= 1){
            for($a=0; $a<count($contracts); $a++){
                for($b=0; $b<count($particularThings); $b++){
                    if($contracts[$a]['pharmacyId'] == $particularThings[$b]['pharmacyId']){
                        $name = $particularThings[$b]['name'];
                        array_push($contracts[$a]['particularThings'], $name);
                    }
                }
            }
        }
    }

    //

    $query3 = sprintf(
        "SELECT c.address,
            d.name AS type_name,
            c.monthly_rent
		FROM
            negopharm_pharmacy_bid_approval a,
            negopharm_pharmacy_bid b,
            negopharm_pharmacy c,
            negopharm_pharmacy_type d
        WHERE
            a.id = '%s'
            AND b.id = a.bid_id
            AND c.id = b.pharmacy_id
            AND d.id = c.pharmacy_type_id
        LIMIT 1",
        
        $bidApprovalId
    );

    $results3 = $dbController->executeQuery($query3);

    if(!$results3['success']){
        $response['errorCode'] = '3';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }
    else if(count($results3['results']) <= 0){
        $response['errorCode'] = '4';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }

    $address = $results3['results'][0]['address'];
    $typeName = $results3['results'][0]['type_name'];
    $monthlyRent = $results3['results'][0]['monthly_rent'];

    $pharmacy = array();
    $pharmacy['address'] = $address;
    $pharmacy['typeName'] = $typeName;
    $pharmacy['monthlyRent'] = $monthlyRent;

	$data = array();
    $data['contracts'] = $contracts;
    $data['pharmacy'] = $pharmacy;

    $response['data'] = $data;
    $response['success'] = true;
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>