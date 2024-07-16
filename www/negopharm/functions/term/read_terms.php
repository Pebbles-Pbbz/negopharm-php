<?php
    include '../../controller/database/MysqlController.php';
    $dbController = new MysqlController();

    $response = array();
    $response['success'] = false;

    $query = sprintf(
        "SELECT *
        FROM
            negopharm_term
        WHERE
            %s",
        'TRUE'
    );

    $results = $dbController->executeQuery($query);

    if(!$results['success']){
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }

    $terms = array();
        
    for($i=0; $i<count($results['results']); $i++){
        $term = array();
        $term['id'] = $results['results'][$i]['id'];
        $term['title'] = $results['results'][$i]['title'];
        $term['contents'] = $results['results'][$i]['contents'];
        $term['createdDate'] = $results['results'][$i]['created_date'];

        $term['isEssential'] = true;
        $essential = $results['results'][$i]['essential'];
        
        if($essential == 0){
            $term['isEssential'] = false;
        }

        array_push($terms, $term);
    }

	$data = array();
    $data['terms'] = $terms;

    $response['data'] = $data;
    $response['success'] = true;
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>