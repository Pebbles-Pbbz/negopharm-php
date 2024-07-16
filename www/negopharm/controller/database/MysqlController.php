<?php
    include 'DBController.php';

    class MysqlController implements DBController {
        function connect(){
            $result = null;

            try {
                $mysql_host = '127.0.0.1';
                $mysql_username = 'deargreenpublic1';
                $mysql_password = 'jhs951101';
                $mysql_dbname = 'deargreenpublic1';

                mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
                $result = new mysqli($mysql_host, $mysql_username, $mysql_password, $mysql_dbname);
            }
            catch(Exception $e){}

            return $result;
        }

        function disconnect($con, $statement, $resultset){
            $success = false;

            try {
                if($resultset != null){
                    $resultset->close();
                }

                if($statement != null){
                    $statement->close();
                }

                if($con != null){
                    $con->close();
                }

                $success = true;
            }
            catch(Exception $e){}

            return $success;
        }

        public function executeQuery($query){
            $result = array();
            $result['success'] = false;
            $result['results'] = array();

            $con = null;
            $statement = null;
            $resultset = null;

            try {
                $con = $this->connect();
                $statement = $con->prepare($query);
                $statement->execute();

                if(strtoupper(explode(' ', $query)[0]) == 'SELECT'){
                    $resultset = $statement->get_result();

                    while($row = $resultset->fetch_assoc()){
                        array_push($result['results'], $row);
                    }
                }
                
                $result['success'] = true;
            }
            catch(Exception $e){}
            finally{
                $this->disconnect($con, $statement, $resultset);
            }

            return $result;
        }
    }
?>