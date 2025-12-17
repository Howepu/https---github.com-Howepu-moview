<?php
// Конфигурация Яндекс ID OAuth
// ВАЖНО: В реальном проекте эти данные должны храниться в переменных окружения!

define('YANDEX_CLIENT_ID', '0a05754ab8594f6a97437159055427ee'); // Замените на ID вашего приложения
define('YANDEX_CLIENT_SECRET', '117914f90f964c09ae9920f5ed705044'); // Замените на секретный ключ приложения
define('YANDEX_REDIRECT_URI', 'http://127.0.0.1/admin/yandex_callback.php'); // URL обратного вызова

// URL для авторизации через Яндекс ID
function getYandexAuthUrl() {
    $params = [
        'response_type' => 'code',
        'client_id' => YANDEX_CLIENT_ID,
        'redirect_uri' => YANDEX_REDIRECT_URI,
        'scope' => 'login:info', // Запрашиваем только базовую информацию
        'state' => bin2hex(random_bytes(16)) // Защита от CSRF
    ];
    
    // Сохраняем state в сессии для проверки
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['yandex_oauth_state'] = $params['state'];
    
    return 'https://oauth.yandex.ru/authorize?' . http_build_query($params);
}

// Получение токена доступа
function getYandexAccessToken($code, $state) {
    // Проверяем state для защиты от CSRF
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['yandex_oauth_state']) || $_SESSION['yandex_oauth_state'] !== $state) {
        throw new Exception('Invalid state parameter');
    }
    
    unset($_SESSION['yandex_oauth_state']);
    
    $params = [
        'grant_type' => 'authorization_code',
        'code' => $code,
        'redirect_uri' => YANDEX_REDIRECT_URI,
        'client_id' => YANDEX_CLIENT_ID,
        'client_secret' => YANDEX_CLIENT_SECRET
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://oauth.yandex.ru/token');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $data = json_decode($response, true);
        if (isset($data['access_token'])) {
            return $data;
        }
    }
    
    throw new Exception('Failed to get access token: ' . $response);
}

// Получение информации о пользователе
function getYandexUserInfo($access_token) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://login.yandex.ru/info');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: OAuth ' . $access_token
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $data = json_decode($response, true);
        
        // Проверяем на ошибки
        if (isset($data['error'])) {
            throw new Exception('Yandex API error: ' . $data['error']);
        }
        
        return $data;
    }
    
    throw new Exception('Failed to get user info: HTTP ' . $httpCode);
}
