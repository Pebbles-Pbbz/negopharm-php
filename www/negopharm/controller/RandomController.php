<?php
    class RandomController {
        public function createString10Id(){
            $result = '';

            for($n=1; $n<=10; $n++){
                $charType = rand(1, 3);
                $asciiNum = null;
    
                if($charType == 1){
                    $asciiNum = rand(48, 57);
                }
                else if($charType == 2){
                    $asciiNum = rand(65, 90);
                }
                else if($charType == 3){
                    $asciiNum = rand(97, 122);
                }
                else{
                    $asciiNum = rand(97, 122);
                }
    
                $result = $result . chr($asciiNum);
            }

            return $result;
        }
    }
?>