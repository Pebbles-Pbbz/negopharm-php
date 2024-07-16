<?php
    include 'EmailController.php';

    class NaverWorks_EmailController extends EmailController {

        private string $clientId = 'q8HF1FQuvJKOhRK7pVz3';
        private string $clientSecret = 'ngH4apez3t';

        function renewAccessToken($refreshToken){
            $newAccessToken = null;

            $ch1 = curl_init();
            $url = 'https://auth.worksmobile.com/oauth2/v2.0/token';

            try{
                $request1_params = array();
                $request1_params['refresh_token'] = $refreshToken;
                $request1_params['grant_type'] = 'refresh_token';
                $request1_params['client_id'] = $this->clientId;
                $request1_params['client_secret'] = $this->clientSecret;

                $request1_headers = array();
                $request1_headers['Content-Type'] = 'application/x-www-form-urlencoded';

                $keys = array_keys($request1_headers);
                $request1_headerArr = array();

                for($i=0; $i<count($keys); $i++){
                    $key = $keys[$i];
                    $value = $request1_headers[$keys[$i]];
                    array_push($request1_headerArr, sprintf('%s: %s', $key, $value));
                }

                $keys = array_keys($request1_params);
                $request1_paramStr = '';

                for($i=0; $i<count($keys); $i++){
                    $key = $keys[$i];
                    $value = $request1_params[$keys[$i]];
                    $char = '&';

                    if($i <= 0){
                        $char = '';
                    }

                    $request1_paramStr = $request1_paramStr . $char . sprintf('%s=%s', $key, urlencode($value));
                }
                
                curl_setopt($ch1, CURLOPT_URL, $url);
                curl_setopt($ch1, CURLOPT_POST, true);
                curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch1, CURLOPT_HTTPHEADER, $request1_headerArr);
                curl_setopt($ch1, CURLOPT_POSTFIELDS, $request1_paramStr);
                curl_setopt($ch1, CURLOPT_CONNECTTIMEOUT, 60 * 30);
                curl_setopt($ch1, CURLOPT_TIMEOUT, 60 * 30);  // 30분

                $response1 = curl_exec($ch1);
                $response1_array = json_decode($response1, JSON_UNESCAPED_UNICODE);
                $response1_code = curl_getinfo($ch1, CURLINFO_HTTP_CODE);

                if(!is_numeric($response1_code)){
                    throw new Exception('');
                }
                else if(!($response1_code >= 200 && $response1_code <= 299)){
                    throw new Exception('');
                }

                if(!is_array($response1_array)){
                    throw new Exception('');
                }
                else if(!array_key_exists('access_token', $response1_array)){
                    throw new Exception('');
                }

                $newAccessToken = $response1_array['access_token'];
            }
            catch(Exception $e){
            }
            finally{
                try{
                    curl_close($ch1);
                }
                catch(Exception $e){
                }
            }

            return $newAccessToken;
        }

        public function sendEmail($receiver, $title, $contents, $contentsType){
            
            $success = false;
            $dbController = new MysqlController();

            $purpose = '이메일 전송';
            $api_platform_name = 'NAVER WORKS';

            $query1 = sprintf(
                "SELECT *
                FROM
                    buildgreen_manager_access_token
                WHERE
                    purpose = '%s'
                    AND api_platform_name = '%s'
                LIMIT 1",
                $purpose, $api_platform_name
            );

            $results1 = $dbController->executeQuery($query1);

            if(!$results1['success']){
                return $success;
            }
            else if(count($results1['results']) <= 0){
                return $success;
            }

            $accessToken = $results1['results'][0]['access_token'];
            $refreshToken = $results1['results'][0]['refresh_token'];
            $accessToken_expiredDateStr = $results1['results'][0]['access_token_expired_date'];

            $nowDate = new DateTime('now');
            $accessToken_expiredDate = new DateTime($accessToken_expiredDateStr);

            if($nowDate >= $accessToken_expiredDate){
                $newAccessToken = $this->renewAccessToken($refreshToken);
                
                if(empty($newAccessToken)){
                    return $success;
                }

                $nowDateStr = $nowDate->format('Y-m-d H:i:s');
                $calculatedDate = new DateTime($nowDateStr);
                $calculatedDate->add(new DateInterval('PT23H'));
                $calculatedDateStr = $calculatedDate->format('Y-m-d H:i:s');

                $query2 = sprintf(
                    "UPDATE buildgreen_manager_access_token
                    SET
                        access_token = '%s',
                        access_token_expired_date = '%s',
                        updated_date = '%s'
                    WHERE
                        purpose = '%s'
                        AND api_platform_name = '%s'",
                    $newAccessToken, $calculatedDateStr, $nowDateStr,
                    $purpose, $api_platform_name
                );
            
                $results2 = $dbController->executeQuery($query2);
            
                if(!$results2['success']){
                    return $success;
                }

                $accessToken = $newAccessToken;
            }

            $isSent = $this->send($receiver, $title, $contents, $contentsType, $accessToken);

            if(!$isSent){
                return $success;
            }

            $success = true;
            return $success;
        }

        function send($receiver, $title, $contents, $contentsType, $accessToken){
            $isSent = false;

            $ch2 = curl_init();
            $url = 'https://www.worksapis.com/v1.0/users/me/mail';

            try{
                $request2_params = array();
                $request2_params['to'] = $receiver;
                $request2_params['subject'] = $title;
                $request2_params['body'] = $contents;
                $request2_params['contentType'] = $contentsType;
                $request2_params['userName'] = $this->senderName;
                $request2_params['isSaveSentMail'] = true;
                $request2_params['isSaveTracking'] = true;
                $request2_params['isSendSeparately'] = false;

                $request2_headers = array();
                $request2_headers['Content-Type'] = 'application/json';
                $request2_headers['Authorization'] = sprintf('Bearer %s', $accessToken);

                $keys = array_keys($request2_headers);
                $request2_headerArr = array();

                for($i=0; $i<count($keys); $i++){
                    $key = $keys[$i];
                    $value = $request2_headers[$keys[$i]];
                    array_push($request2_headerArr, sprintf('%s: %s', $key, $value));
                }
                
                curl_setopt($ch2, CURLOPT_URL, $url);
                curl_setopt($ch2, CURLOPT_POST, true);
                curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch2, CURLOPT_HTTPHEADER, $request2_headerArr);
                curl_setopt($ch2, CURLOPT_POSTFIELDS, json_encode($request2_params, JSON_UNESCAPED_UNICODE));
                curl_setopt($ch2, CURLOPT_CONNECTTIMEOUT, 60 * 30);
                curl_setopt($ch2, CURLOPT_TIMEOUT, 60 * 30);  // 30분

                $response2 = curl_exec($ch2);
                $response2_code = curl_getinfo($ch2, CURLINFO_HTTP_CODE);

                if(!is_numeric($response2_code)){
                    throw new Exception('');
                }
                else if(!($response2_code >= 200 && $response2_code <= 299)){
                    throw new Exception('');
                }

                $isSent = true;
            }
            catch(Exception $e){
            }
            finally{
                try{
                    curl_close($ch2);
                }
                catch(Exception $e){
                }
            }

            return $isSent;
        }
    }
?>