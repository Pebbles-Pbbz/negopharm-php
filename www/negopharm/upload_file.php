<?php
    $response = array();
    $response['success'] = false;

    $folderPath = $_POST['folderPath'];
    $newFolderName = $_POST['newFolderName'];

    if(empty($folderPath)){
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }

    $key = 'file';
    $fileNameExt = basename($_FILES[$key]['name']);

    if(!empty($newFolderName)){
        $folderPath = $folderPath . $newFolderName . '/';

        if(!is_dir($folderPath)){
            mkdir($folderPath, 0777);
        }
    }

    $filePath = $folderPath . $fileNameExt;

    if(!move_uploaded_file($_FILES[$key]['tmp_name'], $filePath)){
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }

    $response['success'] = true;
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>
