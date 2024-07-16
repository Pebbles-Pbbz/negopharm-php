<?php
    include '../../controller/database/MysqlController.php';
    $dbController = new MysqlController();
    
    $response = array();
    $response['success'] = false;

    $request = json_decode(file_get_contents('php://input'), JSON_UNESCAPED_UNICODE);
    $id = $request['id'];
    $inputPassword = $request['password'];

    if(empty($id) || empty($inputPassword)){
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }

    $query1 = sprintf(
        "SELECT *
        FROM
            negopharm_user
        WHERE
            id = '%s'
        LIMIT 1",
        $id
    );

    $results1 = $dbController->executeQuery($query1);

    if(!$results1['success']){
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }
    else if(count($results1['results']) <= 0){
        $response['errorMsg'] = 'noUserId';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }

    $realPassword = $results1['results'][0]['password'];
    $approvedDate = $results1['results'][0]['approved_date'];

    if(!password_verify($inputPassword, $realPassword)){
        $response['errorMsg'] = 'incorrectPassword';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }
    /* 나중에 주석 해제할 예정
    else if(empty($approvedDate) || $approvedDate == '0'){
        $response['errorMsg'] = 'unapprovedUser';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }
    else if($approvedDate == '-1'){
        $response['errorMsg'] = 'rejectedUser';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }
        */

    $name = $results1['results'][0]['name'];
    //$typeName = $results1['results'][0]['type'];
    $typeName = '마스터 관리자';

    $data = array();
    $data['name'] = $name;
    $data['typeName'] = $typeName;

    $response['data'] = $data;
    $response['success'] = true;
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>