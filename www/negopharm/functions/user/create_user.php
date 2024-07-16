<?php
    include '../../controller/RandomController.php';
    include '../../controller/database/MysqlController.php';
    
    $dbController = new MysqlController();
    $randomController = new RandomController();
    date_default_timezone_set('Asia/Seoul');
    
    $response = array();
    $response['success'] = false;

    $request = json_decode(file_get_contents('php://input'), JSON_UNESCAPED_UNICODE);
    $userId = $request['id'];
    $password = $request['password'];
    $name = $request['name'];
    $birthDay = $request['birthDay'];
    $phone = $request['phone'];
    $email = $request['email'];
    $documents = $request['documents'];

    if(
        empty($userId)
        || empty($password)
        || empty($name)
        || empty($birthDay)
        || empty($phone)
        || empty($email)
    ){
        $response['errorCode'] = '1';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }

    $createdDate = strval(date('Y-m-d H:i:s'));

    $query1 = sprintf(
        "INSERT INTO
            negopharm_user
                (id, password, name, birthday, phone, email, approved_date, created_date)
        VALUES
            ('%s', '%s', '%s', '%s', '%s', '%s', '0', '%s')",
        $userId, password_hash($password, PASSWORD_BCRYPT), $name, $birthDay, $phone, $email, $createdDate
    );

    $results1 = $dbController->executeQuery($query1);

    if(!$results1['success']){
        $response['errorCode'] = '2';
		echo json_encode($response, JSON_UNESCAPED_UNICODE);
		exit();
    }

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

                    $query2 = sprintf(
                        "SELECT *
                        FROM
                            negopharm_user_document
                        WHERE
                            id = '%s'
                        LIMIT 1",

                        $documentId
                    );

                    $results2 = $dbController->executeQuery($query2);

                    if(!$results2['success']){
                        $response['errorCode'] = '3';
                        echo json_encode($response, JSON_UNESCAPED_UNICODE);
                        exit();
                    }
                    else if(count($results2['results']) <= 0){
                        $isDuplicate = false;
                    }
                }
                while ($isDuplicate);

                //

                $fileNameExt = basename($documentPath);

                $query3 = sprintf(
                    "INSERT INTO
                        negopharm_user_document
                            (id, user_id, name, file_path, created_date)
                    VALUES
                        ('%s', '%s', '%s', '%s', '%s')",
                    
                    $documentId, $userId, $documentName, $fileNameExt, $createdDate
                );

                $results3 = $dbController->executeQuery($query3);

                if(!$results3['success']){
                    $success = false;
                    continue;
                }
            }
        }

        if(!$success){
            $response['errorCode'] = '4';
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            exit();
        }
    }
    
    $response['success'] = true;
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>