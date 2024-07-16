<?php
    include '../../controller/database/MysqlController.php';
    $dbController = new MysqlController();

    $response = array();
    $response['success'] = false;

    $request = json_decode(file_get_contents('php://input'), JSON_UNESCAPED_UNICODE);
    $userId = $request['userId'];
    $traderType = $request['traderType'];

    $query1 = sprintf(
        "SELECT *
        FROM
            negopharm_pharmacy
        WHERE
            %s
        ORDER BY
            created_date",
        'TRUE'
    );

    if(
        !empty($userId)
        && (
            $traderType == 'buyer'
            || $traderType == 'seller'
        )
    ){
        if($traderType == 'buyer'){
            $query1 = sprintf(
                "SELECT a.*,
                    b.name AS state_name,
                    c.id AS buyer_bid_id
                FROM
                    negopharm_pharmacy a,
                    negopharm_pharmacy_state b,
                    negopharm_pharmacy_bid c
                WHERE
                    a.id = c.pharmacy_id
                    AND b.id = a.state_id
                    AND c.user_id = '%s'
                ORDER BY
                    a.created_date DESC",
                
                $userId
            );
        }
        else if($traderType == 'seller'){
            $query1 = sprintf(
                "SELECT a.*, b.name AS state_name
                FROM
                    negopharm_pharmacy a,
                    negopharm_pharmacy_state b
                WHERE
                    b.id = a.state_id
                    AND a.seller_user_id = '%s'
                ORDER BY
                    a.created_date DESC",
                
                $userId
            );
        }
    }

    $results1 = $dbController->executeQuery($query1);

    if(!$results1['success']){
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }

    $pharmacies = array();

    for($i=0; $i<count($results1['results']); $i++){
        $pharmacy = array();
        $pharmacy['pharmacyId'] = $results1['results'][$i]['id'];
        $pharmacy['deposit'] = $results1['results'][$i]['deposit'];
        $pharmacy['keyMoney'] = $results1['results'][$i]['key_money'];
        $pharmacy['monthlyRent'] = $results1['results'][$i]['monthly_rent'];
        $pharmacy['stateName'] = $results1['results'][$i]['state_name'];
        $pharmacy['address'] = $results1['results'][$i]['address'];
        $pharmacy['preparationFee'] = $results1['results'][$i]['preparation_fee'];
        $pharmacy['ilme'] = $results1['results'][$i]['ilme'];
        $pharmacy['createdDate'] = $results1['results'][$i]['created_date'];
        
        $pharmacy['buyerBidId'] = $results1['results'][$i]['buyer_bid_id'];
        $pharmacy['numOfBids'] = 0;
        $pharmacy['maxPrice'] = 0;
        array_push($pharmacies, $pharmacy);
    }

    //

    if(count($pharmacies) >= 1){
        $pharmacyIdsStr = '';

        for($i=0; $i<count($pharmacies); $i++){
            $char = ', ';

            if($i <= 0){
                $char = '';
            }

            $pharmacyIdsStr = $pharmacyIdsStr . $char . "'" . $pharmacies[$i]['pharmacyId'] . "'";
        }

        $query2 = sprintf(
            "SELECT pharmacy_id,
                COUNT(*) as num_of_bids,
                MAX(price) as max_price
            FROM
                negopharm_pharmacy_bid
            WHERE
                pharmacy_id in (%s)
            GROUP BY
                pharmacy_id",
            
            $pharmacyIdsStr
        );

        $results2 = $dbController->executeQuery($query2);

        if(!$results2['success']){
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            exit();
        }

        $bids = array();

        for($i=0; $i<count($results2['results']); $i++){
            $bid = array();
            $bid['pharmacyId'] = $results2['results'][$i]['pharmacy_id'];
            $bid['numOfBids'] = $results2['results'][$i]['num_of_bids'];
            $bid['maxPrice'] = $results2['results'][$i]['max_price'];

            array_push($bids, $bid);
        }

        if(count($bids) >= 1){
            for($a=0; $a<count($pharmacies); $a++){
                for($b=0; $b<count($bids); $b++){
                    if($pharmacies[$a]['pharmacyId'] == $bids[$b]['pharmacyId']){
                        $pharmacies[$a]['numOfBid'] = $bids[$b]['numOfBid'];
                        $pharmacies[$a]['maxPrice'] = $bids[$b]['maxPrice'];
                        break;
                    }
                }
            }
        }
    }

	$data = array();
    $data['pharmacies'] = $pharmacies;

    $response['data'] = $data;
    $response['success'] = true;
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>