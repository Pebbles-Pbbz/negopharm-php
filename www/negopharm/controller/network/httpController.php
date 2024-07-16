<?php
    class HttpController {
        public function get($originalLink, $requestArr){
            $result = null;

            try{
                $link = $originalLink;
                $keys = array_keys($requestArr);

                for($i=0; $i<count($keys); $i++){
                    $key = $keys[$i];
                    $value = $requestArr[$keys[$i]];

                    $c = '&';

                    if($i <= 0){
                        $c = '?';
                    }

                    $link = $link . $c . $key . '=' . $value;
                }

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $link);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60 * 30);  // 30분
                curl_setopt($ch, CURLOPT_TIMEOUT, 60 * 30);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

                $responseJson = curl_exec($ch);
                $result = json_decode($responseJson, JSON_UNESCAPED_UNICODE);
            }
            catch(Exception $e){
            }
            finally{
                try{
                    curl_close($ch);
                }
                catch(Exception $e){
                }
            }

            return $result;
        }

        public function postJson($link, $requestArr){
            $result = null;

            try{
                $headers = ['Content-Type: application/json'];
                $requestJson = json_encode($requestArr, JSON_UNESCAPED_UNICODE);

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $link);
                curl_setopt($ch, CURLOPT_POST, TRUE);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $requestJson);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60 * 30);
                curl_setopt($ch, CURLOPT_TIMEOUT, 60 * 30);

                $responseJson = curl_exec($ch);
                $result = json_decode($responseJson, JSON_UNESCAPED_UNICODE);
            }
            catch(Exception $e){
            }
            finally{
                try{
                    curl_close($ch);
                }
                catch(Exception $e){
                }
            }

            return $result;
        }
    }
?>