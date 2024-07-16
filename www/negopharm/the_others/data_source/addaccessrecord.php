<?php
    //include 'controller/database/MysqlController.php';
    
    function addAccessRecord($serverFileName){
        $result = false;
        $dbController = new MysqlController();
        date_default_timezone_set('Asia/Seoul');

        $publicIPAddress = $_SERVER['REMOTE_ADDR'];
        $createdDate = strval(date('Y-m-d H:i:s'));

        $query = sprintf(
            "INSERT INTO buildgreen_access_record(public_ip, server_file_name, created_date) VALUES('%s', '%s', '%s')",
            $publicIPAddress, $serverFileName, $createdDate
        );

        $results = $dbController->executeQuery($query);
    
        if(!$results['success']){
            return $result;
        }

        $result = true;
        return $result;
    }
?>