<?php
    include '../../controller/RandomController.php';
    include '../../controller/database/MysqlController.php';

    $dbController = new MysqlController();
    $randomController = new RandomController();
    date_default_timezone_set('Asia/Seoul');

    $response = array();
    $response['success'] = false;

    $request = json_decode(file_get_contents('php://input'), JSON_UNESCAPED_UNICODE);
    $sellerUserId = $request['sellerUserId'];
    $typeName = $request['typeName'];
    $numOfPrescription = $request['numOfPrescription'];
    $preparationFee = $request['preparationFee'];
    $ilme = $request['ilme'];
    $deposit = $request['deposit'];
    $keyMoney = $request['keyMoney'];
    $monthlyRent = $request['monthlyRent'];
    $maintenanceCost = $request['maintenanceCost'];
    $minPriceUnit = $request['minPriceUnit'];
    $address = $request['address'];
    $endDate = $request['endDate'];

    $endPrice = $request['endPrice'];
    $mapRange = $request['mapRange'];

    $hospitalNames = $request['hospitalNames'];
    $operatingTimes = $request['operatingTimes'];
    $meetingTimes = $request['meetingTimes'];
    $documents = $request['documents'];
    $particularNames = $request['particularNames'];

    if(
        empty($sellerUserId)
        || empty($typeName)
        || !is_int($numOfPrescription)
        || !is_int($preparationFee)
        || !is_int($ilme)
        || !is_int($deposit)
        || !is_int($keyMoney)
        || !is_int($monthlyRent)
        || !is_int($maintenanceCost)
        || !is_int($minPriceUnit)
        || empty($address)
        || empty($endDate)
        || !is_array($hospitalNames)
        || !is_array($operatingTimes)
        || !is_array($meetingTimes)
        || !is_array($documents)
    ){
        $response['errorCode'] = '1';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }

    //

    $query1 = sprintf(
        "SELECT *
        FROM
            negopharm_user
        WHERE
            id = '%s'
        LIMIT 1",
        
        $sellerUserId
    );

    $results1 = $dbController->executeQuery($query1);
    
    if(!$results1['success']){
        $response['errorCode'] = '2';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }
    else if(count($results1['results']) <= 0){
        $response['errorCode'] = '3';
        $response['errorMsg'] = 'noSellerUser';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }

    //

    $query2 = sprintf(
        "SELECT *
        FROM
            negopharm_pharmacy_state
        WHERE
            name = '진행 중'
            AND %s
        LIMIT 1",

        'TRUE'
    );

    $results2 = $dbController->executeQuery($query2);
    
    if(!$results2['success']){
        $response['errorCode'] = '4';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }
    else if(count($results2['results']) <= 0){
        $response['errorCode'] = '5';
        $response['errorMsg'] = 'noState';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }
    
    $stateId = $results2['results'][0]['id'];

    //

    $query3 = sprintf(
        "SELECT *
        FROM
            negopharm_pharmacy_type
        WHERE
            name like '%s%s%s'
        LIMIT 1",

        '%', $typeName, '%'
    );

    $results3 = $dbController->executeQuery($query3);
    
    if(!$results3['success']){
        $response['errorCode'] = '6';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }
    else if(count($results3['results']) <= 0){
        $response['errorCode'] = '7';
        $response['errorMsg'] = 'noType';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }
    
    $typeId = $results3['results'][0]['id'];

    //

    $pharmacyId = null;
    $isDuplicate = true;  

    do{
        $pharmacyId = $randomController->createString10Id();

        $query4 = sprintf(
            "SELECT *
            FROM
                negopharm_pharmacy
            WHERE
                id = '%s'
            LIMIT 1",
            $pharmacyId
        );

        $results4 = $dbController->executeQuery($query4);

        if(!$results4['success']){
            $response['errorCode'] = '8';
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            exit();
        }
        else if(count($results4['results']) <= 0){
            $isDuplicate = false;
        }
    }
    while ($isDuplicate);

    //

    $endPriceToInsert = 'NULL';
    $mapRangeToInsert = 'NULL';

    if(is_int($endPrice)){
        $endPriceToInsert = strval($endPrice);
    }

    if(is_int($mapRange) || is_float($mapRange)){
        $mapRangeToInsert = strval($mapRange);
    }

    $createdDate = strval(date('Y-m-d H:i:s'));

    $query5 = sprintf(
        "INSERT INTO
            negopharm_pharmacy
                (id, seller_user_id, state_id, num_of_prescription, preparation_fee, ilme,
                deposit, key_money, monthly_rent, maintenance_cost, min_price_unit,
                end_price, address, pharmacy_type_id, map_range, end_date,
                business_registration_file_path, created_date)
        VALUES
            ('%s', '%s', '%s', %d, %d, %d,
            %d, %d, %d, %d, %d,
            %s, '%s', '%s', %s, '%s',
            NULL, '%s')",
        
        $pharmacyId, $sellerUserId, $stateId, $numOfPrescription, $preparationFee, $ilme,
        $deposit, $keyMoney, $monthlyRent, $maintenanceCost, $minPriceUnit,
        $endPriceToInsert, $address, $typeId, $mapRangeToInsert, $endDate,
        $createdDate
    );

    $results5 = $dbController->executeQuery($query5);

    if(!$results5['success']){
        $response['errorCode'] = '9';
		echo json_encode($response, JSON_UNESCAPED_UNICODE);
		exit();
    }

    //

    if(is_array($hospitalNames)){
        $success = true;

        for($i=0; $i<count($hospitalNames); $i++){
            if(is_string($hospitalNames[$i])){
                $name = $hospitalNames[$i];
                $query6 = sprintf(
                    "SELECT *
                    FROM
                        negopharm_pharmacy_hospital
                    WHERE
                        name like '%s%s%s'
                    LIMIT 1",
            
                    '%', $name, '%'
                );
            
                $results6 = $dbController->executeQuery($query6);
                
                if(!$results6['success']){
                    $success = false;
                    continue;
                }
                else if(count($results6['results']) <= 0){
                    $success = false;
                    continue;
                }
                
                $hospitalId = $results6['results'][0]['id'];

                //

                $query7 = sprintf(
                    "INSERT INTO
                        negopharm_pharmacy_hospital_possession
                            (pharmacy_id, pharmacy_hospital_id, created_date)
                    VALUES
                        ('%s', '%s', '%s')",
                    
                    $pharmacyId, $hospitalId, $createdDate
                );

                $results7 = $dbController->executeQuery($query7);

                if(!$results7['success']){
                    $success = false;
                    continue;
                }
            }
        }

        if(!$success){
            $response['errorCode'] = '10';
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            exit();
        }
    }

    //

    if(is_array($operatingTimes)){
        $success = true;

        for($i=0; $i<count($operatingTimes); $i++){
            if(is_array($operatingTimes[$i])){
                $dayOfWeek = $operatingTimes[$i]['dayOfWeek'];
                $startTime = $operatingTimes[$i]['startTime'];
                $endTime = $operatingTimes[$i]['endTime'];
        
                $query8 = sprintf(
                    "INSERT INTO
                        negopharm_pharmacy_operating_time
                            (pharmacy_id, day_of_week, start_time, end_time, created_date)
                    VALUES
                        ('%s', '%s', '%s', '%s', '%s')",
                    
                    $pharmacyId, $dayOfWeek, $startTime, $endTime, $createdDate
                );
        
                $results8 = $dbController->executeQuery($query8);
        
                if(!$results8['success']){
                    $success = false;
                    continue;
                }
            }
        }

        if(!$success){
            $response['errorCode'] = '11';
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            exit();
        }
    }

    //

    if(is_array($meetingTimes)){
        $success = true;

        for($i=0; $i<count($meetingTimes); $i++){
            if(is_array($meetingTimes[$i])){
                $date = $meetingTimes[$i]['date'];
                $startTime = $meetingTimes[$i]['startTime'];
                $duration = $meetingTimes[$i]['duration'];
        
                $query9 = sprintf(
                    "INSERT INTO
                        negopharm_pharmacy_meeting_time
                            (pharmacy_id, meeting_date, start_time, duration, created_date)
                    VALUES
                        ('%s', '%s', '%s', '%s', '%s')",
                    
                    $pharmacyId, $date, $startTime, $duration, $createdDate
                );
        
                $results9 = $dbController->executeQuery($query9);
        
                if(!$results9['success']){
                    $success = false;
                    continue;
                }
            }
        }

        if(!$success){
            $response['errorCode'] = '12';
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            exit();
        }
    }

    //

    if(is_array($documents)){
        $success = true;

        for($i=0; $i<count($documents); $i++){
            if(is_array($documents[$i])){
                $documentName = $documents[$i]['name'];
                $documentPath = $documents[$i]['path'];

                $documentId = null;
                $isDuplicate = true;  

                do{
                    $documentId = $randomController->createString10Id();

                    $query10 = sprintf(
                        "SELECT *
                        FROM
                            negopharm_pharmacy_document
                        WHERE
                            id = '%s'
                        LIMIT 1",

                        $documentId
                    );

                    $results10 = $dbController->executeQuery($query10);

                    if(!$results10['success']){
                        $response['errorCode'] = '14';
                        echo json_encode($response, JSON_UNESCAPED_UNICODE);
                        exit();
                    }
                    else if(count($results10['results']) <= 0){
                        $isDuplicate = false;
                    }
                }
                while ($isDuplicate);

                //

                $fileNameExt = basename($documentPath);

                $query11 = sprintf(
                    "INSERT INTO
                        negopharm_pharmacy_document
                            (id, pharmacy_id, name, file_path, created_date)
                    VALUES
                        ('%s', '%s', '%s', '%s', '%s')",
                    
                    $documentId, $pharmacyId, $documentName, $fileNameExt, $createdDate
                );

                $results11 = $dbController->executeQuery($query11);

                if(!$results11['success']){
                    $success = false;
                    continue;
                }
            }
        }

        if(!$success){
            $response['errorCode'] = '13';
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            exit();
        }
    }

    //

    if(is_array($particularNames)){
        $success = true;

        for($i=0; $i<count($particularNames); $i++){
            if(is_string($particularNames[$i])){
                $name = $particularNames[$i];
                $query12 = sprintf(
                    "SELECT *
                    FROM
                        negopharm_pharmacy_particular
                    WHERE
                        name like '%s%s%s'
                    LIMIT 1",
            
                    '%', $name, '%'
                );
            
                $results12 = $dbController->executeQuery($query12);
                
                if(!$results12['success']){
                    $success = false;
                    continue;
                }
                else if(count($results12['results']) <= 0){
                    $success = false;
                    continue;
                }
                
                $particularId = $results12['results'][0]['id'];

                //

                $query13 = sprintf(
                    "INSERT INTO
                        negopharm_pharmacy_particular_possession
                            (pharmacy_id, pharmacy_particular_id, created_date)
                    VALUES
                        ('%s', '%s', '%s')",
                    
                    $pharmacyId, $particularId, $createdDate
                );

                $results13 = $dbController->executeQuery($query13);

                if(!$results13['success']){
                    $success = false;
                    continue;
                }
            }
        }

        if(!$success){
            $response['errorCode'] = '14';
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            exit();
        }
    }

    $data = array();
    $data['pharmacyId'] = $pharmacyId;

    $response['data'] = $data;
    $response['success'] = true;
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>