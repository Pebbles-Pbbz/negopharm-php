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
        "SELECT *
        FROM
            negopharm_user
        WHERE
            id = '%s'
        LIMIT 1",

        $id,
    );

    $results1 = $dbController->executeQuery($query1);

    if(!$results1['success']){
		echo json_encode($response, JSON_UNESCAPED_UNICODE);
		exit();
    }
    else if(count($results1['results']) <= 0){
        $response['errorMsg'] = 'noUser';
		echo json_encode($response, JSON_UNESCAPED_UNICODE);
		exit();
    }

    $name = $results1['results'][0]['name'];
    $typeName = null;  // 임시 방편

    $data = array();
    $data['name'] = $name;
    $data['typeName'] = $typeName;

    $response['data'] = $data;
    $response['success'] = true;
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>