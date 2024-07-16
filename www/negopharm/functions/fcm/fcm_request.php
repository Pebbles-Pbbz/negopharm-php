<?php

require_once __DIR__ . '/send_notification.php';

$request = json_decode(file_get_contents('php://input'), JSON_UNESCAPED_UNICODE);

$deviceToken = $request['deviceToken'] ?? null;
$title = $request['title'] ?? null;
$body = $request['body'] ?? null;

// error_log($deviceToken);
// error_log($title);
// error_log($body);

// FCM 메시지 전송 함수 호출
$result = sendPushNotification($deviceToken, $title, $body);

// 결과 출력
echo $result;
