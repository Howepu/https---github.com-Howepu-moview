<?php
// Конфигурация Telegram OAuth
// ВАЖНО: В реальном проекте эти данные должны храниться в переменных окружения!

define('TELEGRAM_BOT_TOKEN', '7713884699:AAFgnIT-g_Wmf4esHUwt01ui3ZGLrOmJDBg'); // Замените на токен вашего бота
define('TELEGRAM_BOT_USERNAME', 'oauth_barrier_bot'); // Замените на username вашего бота (без @)

// Проверка подписи данных от Telegram
function verifyTelegramAuth($auth_data, $bot_token) {
    $check_hash = $auth_data['hash'];
    unset($auth_data['hash']);
    
    $data_check_arr = [];
    foreach ($auth_data as $key => $value) {
        $data_check_arr[] = $key . '=' . $value;
    }
    sort($data_check_arr);
    
    $data_check_string = implode("\n", $data_check_arr);
    $secret_key = hash('sha256', $bot_token, true);
    $hash = hash_hmac('sha256', $data_check_string, $secret_key);
    
    return strcmp($hash, $check_hash) === 0;
}

// Проверка актуальности данных (не старше 24 часов)
function checkTelegramAuthTime($auth_time) {
    return (time() - $auth_time) < 86400; // 24 часа
}

// Получение данных пользователя из Telegram callback
function parseTelegramAuthData() {
    $required_fields = ['id', 'first_name', 'auth_date', 'hash'];
    $auth_data = [];
    
    foreach ($required_fields as $field) {
        if (!isset($_GET[$field])) {
            return false;
        }
        $auth_data[$field] = $_GET[$field];
    }
    
    // Опциональные поля
    $optional_fields = ['last_name', 'username', 'photo_url'];
    foreach ($optional_fields as $field) {
        if (isset($_GET[$field])) {
            $auth_data[$field] = $_GET[$field];
        }
    }
    
    return $auth_data;
}
