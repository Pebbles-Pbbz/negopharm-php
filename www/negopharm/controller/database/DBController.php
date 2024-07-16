<?php
    interface DBController {
        function connect();
        function disconnect($con, $statement, $resultset);
        public function executeQuery($query);
    }
?>