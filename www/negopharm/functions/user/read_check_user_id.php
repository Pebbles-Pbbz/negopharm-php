<?php
    include '../../controller/database/MysqlController.php';
    $dbController = new MysqlController();
    
    $response = array();
    $response['success'] = false;

    $request = json_decode(file_get_contents('php://input'), JSON_UNESCAPED_UNICODE);
    $id = $request['id'];
    $typeName = $request['typeName'];

    if(empty($id)){
		echo json_encode($response, JSON_UNESCAPED_UNICODE);
		exit();
	}

    $subQuery1 = '';
    $subQuery2 = '';

    if(!empty($typeName)){
        $subQuery1 = ", negopharm_user_type b";

        $subQuery2 = sprintf(
            "AND b.name = '%s'
            AND b.id = a.user_type_id",
            $typeName
        );
    }

    $query1 = sprintf(
        "SELECT a.*
        FROM
            negopharm_user a
            %s
        WHERE
            a.id = '%s'
            %s
        LIMIT 1",
        $subQuery1,
        $id,
        $subQuery2
    );

    $results1 = $dbController->executeQuery($query1);

    if(!$results1['success']){
		echo json_encode($response, JSON_UNESCAPED_UNICODE);
		exit();
    }

    $userExist = false;

    if(count($results1['results']) >= 1){
        $userExist = true;
    }

    $data = array();
    $data['exist'] = $userExist;

    $response['data'] = $data;
    $response['success'] = true;
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>