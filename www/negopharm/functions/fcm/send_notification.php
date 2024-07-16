<?php

// 필요한 패키지 및 클래스 로드
require_once __DIR__ . '/../../../../vendor/autoload.php';

use Google\Auth\ApplicationDefaultCredentials;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

// FCM 메시지 전송 함수 정의
function sendPushNotification($deviceToken, $title, $body)
{
    // 서비스 계정 키 파일 경로 (절대 경로)
    $serviceAccountPath = __DIR__ . '/../../../../serviceAccountKey.json';
    if (!file_exists($serviceAccountPath)) {
        return json_encode(['success' => false, 'error' => 'Service account key file not found at ' . $serviceAccountPath]);
    }
    putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $serviceAccountPath);

    // FCM API 엔드포인트
    $projectId = 'negopharm-temp'; // Firebase 프로젝트 ID를 여기에 넣습니다.
    $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

    // OAuth 2.0 토큰 생성
    $scope = 'https://www.googleapis.com/auth/firebase.messaging';
    $middleware = ApplicationDefaultCredentials::getMiddleware($scope);
    $credentials = ApplicationDefaultCredentials::getCredentials($scope);

    // Guzzle 클라이언트 설정
    $handler = HandlerStack::create();
    $handler->push($middleware);
    $client = new Client(['handler' => $handler]);

    // 메시지 페이로드 구성
    $message = [
        'validate_only' => false,
        'message' => [
            'token' => $deviceToken,
            'notification' => [
                'title' => $title,
                'body' => $body,
                'image' => 'https://www.google.com/images/branding/googlelogo/2x/googlelogo_light_color_92x30dp.png',
            ],
        ]
    ];

    // if ($data !== null) {
    //     $message['message']['data'] = $data;
    // }

    try {
        // OAuth 2.0 토큰 획득
        $token = $credentials->fetchAuthToken();
        $accessToken = $token['access_token'];

        // FCM API에 POST 요청 전송
        $response = $client->post($url, [
            'json' => $message,
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ],
        ]);

        // 성공적으로 전송된 경우
        return json_encode(['success' => true, 'response' => $response->getBody()->getContents()]);
    } catch (Exception $e) {
        // 오류 발생 시
        return json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
