<?php
    include '../../controller/database/MysqlController.php';
    $dbController = new MysqlController();
	date_default_timezone_set('Asia/Seoul');
    
    $response = array();
    $response['success'] = false;
	
    $request = json_decode(file_get_contents('php://input'), JSON_UNESCAPED_UNICODE);

	$userId = $request['userId'];
	$termIds = $request['termIds'];

    if(empty($userId) || !is_array($termIds)){
		echo json_encode($response, JSON_UNESCAPED_UNICODE);
		exit();
	}

	$createdDate = strval(date('Y-m-d H:i:s'));
	$success = true;

	for($i=0; $i<count($termIds); $i++){
		$query = sprintf(
			"INSERT INTO
				negopharm_term_agreement
					(user_id, term_id, created_date)
			VALUES
				('%s', '%s', '%s')",
			$userId, $termIds[$i], $createdDate
		);

		$results = $dbController->executeQuery($query);

		if(!$results['success']){
			$success = false;
			continue;
		}
	}

	if(!$success){
		echo json_encode($response, JSON_UNESCAPED_UNICODE);
		exit();
    }

	$response['success'] = true;
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>