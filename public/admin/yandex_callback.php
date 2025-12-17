<?php
// Запускаем сессию
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config.php';
require_once 'yandex_config.php';

// Если пользователь уже авторизован, перенаправляем на главную
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: index.php');
    exit;
}

$error_message = '';

// Обработка callback от Яндекс
if (isset($_GET['code']) && isset($_GET['state'])) {
    try {
        $code = $_GET['code'];
        $state = $_GET['state'];
        
        // Получаем токен доступа
        $token_data = getYandexAccessToken($code, $state);
        
        if (!isset($token_data['access_token'])) {
            throw new Exception('Не удалось получить токен доступа от Яндекс');
        }
        
        $access_token = $token_data['access_token'];
        
        // Получаем информацию о пользователе
        $user_data = getYandexUserInfo($access_token);
        
        if (!$user_data) {
            throw new Exception('Не удалось получить информацию о пользователе от Яндекс');
        }
        
        $yandex_id = $user_data['id'];
        $login = $user_data['login'] ?? '';
        $first_name = $user_data['first_name'] ?? '';
        $last_name = $user_data['last_name'] ?? '';
        $display_name = $user_data['display_name'] ?? '';
        $real_name = $user_data['real_name'] ?? '';
        $email = $user_data['default_email'] ?? '';
        $avatar_id = $user_data['default_avatar_id'] ?? '';
        
        // Формируем URL аватара
        $avatar_url = '';
        if ($avatar_id) {
            $avatar_url = "https://avatars.yandex.net/get-yapic/{$avatar_id}/islands-200";
        }
        
        // Проверяем, есть ли пользователь с таким Yandex ID в нашей базе
        $stmt = $pdo->prepare("SELECT id, username, role FROM admin_users WHERE yandex_id = ?");
        $stmt->execute([$yandex_id]);
        $admin_user = $stmt->fetch();
        
        if ($admin_user) {
            // Пользователь найден - авторизуем его
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin_user['id'];
            $_SESSION['admin_username'] = $admin_user['username'];
            $_SESSION['admin_role'] = $admin_user['role'];
            $_SESSION['yandex_authorized'] = true;
            
            // Обновляем информацию о пользователе
            $update_stmt = $pdo->prepare("
                UPDATE admin_users 
                SET last_login = CURRENT_TIMESTAMP, 
                    yandex_login = ?, 
                    yandex_first_name = ?,
                    yandex_last_name = ?,
                    yandex_display_name = ?,
                    yandex_real_name = ?,
                    yandex_email = ?,
                    yandex_avatar_url = ?,
                    yandex_access_token = ?
                WHERE id = ?
            ");
            $update_stmt->execute([
                $login,
                $first_name,
                $last_name,
                $display_name,
                $real_name,
                $email,
                $avatar_url,
                $access_token,
                $admin_user['id']
            ]);
            
            header('Location: index.php');
            exit;
        } else {
            // Пользователь не найден - показываем страницу привязки аккаунта
            $_SESSION['yandex_temp_data'] = [
                'id' => $yandex_id,
                'login' => $login,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'display_name' => $display_name,
                'real_name' => $real_name,
                'email' => $email,
                'avatar_url' => $avatar_url,
                'access_token' => $access_token
            ];
            header('Location: yandex_link_account.php');
            exit;
        }
    } catch (Exception $e) {
        $error_message = 'Ошибка авторизации через Яндекс: ' . $e->getMessage();
    }
} elseif (isset($_GET['error'])) {
    // Обработка ошибок от Яндекс
    $error_description = $_GET['error_description'] ?? $_GET['error'];
    $error_message = 'Ошибка авторизации: ' . $error_description;
} else {
    $error_message = 'Неверные параметры авторизации Яндекс';
}

// Если произошла ошибка, перенаправляем на страницу входа с сообщением об ошибке
$_SESSION['yandex_error'] = $error_message;
header('Location: login.php');
exit;
