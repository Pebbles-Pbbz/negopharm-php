<?php
    include '../../controller/database/MysqlController.php';
    $dbController = new MysqlController();

    $response = array();
    $response['success'] = false;

    $request = json_decode(file_get_contents('php://input'), JSON_UNESCAPED_UNICODE);
    $id = $request['id'];

    if(empty($id)){
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }

    $query1 = sprintf(
        "SELECT a.*,
            b.name AS pharmacy_type_name
        FROM
            negopharm_pharmacy a,
            negopharm_pharmacy_type b
        WHERE
            a.id = '%s'
            AND b.id = a.pharmacy_type_id
        LIMIT 1",

        $id
    );

    $results1 = $dbController->executeQuery($query1);

    if(!$results1['success']){
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }

    $pharmacy = null;

    if(count($results1['results']) >= 1){
        $pharmacy = array();
        $pharmacy['sellerUserId'] = $results1['results'][0]['seller_user_id'];
        $pharmacy['numOfPrescription'] = $results1['results'][0]['num_of_prescription'];
        $pharmacy['preparationFee'] = $results1['results'][0]['preparation_fee'];
        $pharmacy['ilme'] = $results1['results'][0]['ilme'];
        $pharmacy['deposit'] = $results1['results'][0]['deposit'];
        $pharmacy['keyMoney'] = $results1['results'][0]['key_money'];
        $pharmacy['monthlyRent'] = $results1['results'][0]['monthly_rent'];
        $pharmacy['maintenanceCost'] = $results1['results'][0]['maintenance_cost'];
        $pharmacy['minPriceUnit'] = $results1['results'][0]['min_price_unit'];
        $pharmacy['endPrice'] = $results1['results'][0]['end_price'];
        $pharmacy['address'] = $results1['results'][0]['address'];
        $pharmacy['pharmacyTypeName'] = $results1['results'][0]['pharmacy_type_name'];
        $pharmacy['mapRange'] = $results1['results'][0]['map_range'];
        $pharmacy['endDate'] = $results1['results'][0]['end_date'];
        $pharmacy['createdDate'] = $results1['results'][0]['created_date'];
        $pharmacy['updatedDate'] = $results1['results'][0]['updated_date'];

        $pharmacy['meetingTimes'] = array();
        $pharmacy['operatingTimes'] = array();
        $pharmacy['images'] = array();
        $pharmacy['particularThings'] = array();
        $pharmacy['hospitals'] = array();
    
        //

        $query2 = sprintf(
            "SELECT *
            FROM
                negopharm_pharmacy_meeting_time
            WHERE
                pharmacy_id = '%s'",
            
            $id
        );
    
        $results2 = $dbController->executeQuery($query2);
    
        if(!$results2['success']){
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            exit();
        }
    
        for($i=0; $i<count($results2['results']); $i++){
            $meetingTime = array();
            $meetingTime['date'] = $results2['results'][$i]['meeting_date'];
            $meetingTime['startTime'] = $results2['results'][$i]['start_time'];
    
            array_push($pharmacy['meetingTimes'], $meetingTime);
        }

        //

        $query3 = sprintf(
            "SELECT *
            FROM
                negopharm_pharmacy_operating_time
            WHERE
                pharmacy_id = '%s'",
            
            $id
        );
    
        $results3 = $dbController->executeQuery($query3);
    
        if(!$results3['success']){
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            exit();
        }
    
        for($i=0; $i<count($results3['results']); $i++){
            $operatingTime = array();
            $operatingTime['dayOfWeek'] = $results3['results'][$i]['day_of_week'];
            $operatingTime['startTime'] = $results3['results'][$i]['start_time'];
            $operatingTime['endTime'] = $results3['results'][$i]['end_time'];
    
            array_push($pharmacy['operatingTimes'], $operatingTime);
        }

        //

        $query4 = sprintf(
            "SELECT *
            FROM
                negopharm_pharmacy_image
            WHERE
                pharmacy_id = '%s'",
            
            $id
        );
    
        $results4 = $dbController->executeQuery($query4);
    
        if(!$results4['success']){
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            exit();
        }
    
        for($i=0; $i<count($results4['results']); $i++){
            $image = array();
            $image['fileLink'] = $results4['results'][$i]['file_path'];
    
            array_push($pharmacy['images'], $image);
        }

        //

        $query5 = sprintf(
            "SELECT b.*
            FROM
                negopharm_pharmacy_particular_possession a,
                negopharm_pharmacy_particular b
            WHERE
                a.pharmacy_id = '%s'
                AND b.id = a.pharmacy_particular_id",
            
            $id
        );
    
        $results5 = $dbController->executeQuery($query5);
    
        if(!$results5['success']){
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            exit();
        }
    
        for($i=0; $i<count($results5['results']); $i++){
            $particularThing = array();
            $particularThing['name'] = $results5['results'][$i]['name'];
    
            array_push($pharmacy['particularThings'], $particularThing);
        }

        //

        $query6 = sprintf(
            "SELECT b.*
            FROM
                negopharm_pharmacy_hospital_possession a,
                negopharm_pharmacy_hospital b
            WHERE
                a.pharmacy_id = '%s'
                AND b.id = a.pharmacy_hospital_id",
            
            $id
        );
    
        $results6 = $dbController->executeQuery($query6);
    
        if(!$results6['success']){
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            exit();
        }
    
        for($i=0; $i<count($results6['results']); $i++){
            $hospital = array();
            $hospital['name'] = $results6['results'][$i]['name'];
    
            array_push($pharmacy['hospitals'], $hospital);
        }
    }

	$data = array();
    $data['pharmacy'] = $pharmacy;

    $response['data'] = $data;
    $response['success'] = true;
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>