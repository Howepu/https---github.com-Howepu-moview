<?php
// Запускаем сессию
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config.php';
require_once 'telegram_config.php';

// Если пользователь уже авторизован, перенаправляем на главную
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: index.php');
    exit;
}

$error_message = '';

// Обработка callback от Telegram
$auth_data = parseTelegramAuthData();

if ($auth_data) {
    try {
        // Проверяем подпись данных
        if (!verifyTelegramAuth($auth_data, TELEGRAM_BOT_TOKEN)) {
            throw new Exception('Неверная подпись данных Telegram');
        }
        
        // Проверяем актуальность данных
        if (!checkTelegramAuthTime($auth_data['auth_date'])) {
            throw new Exception('Данные авторизации устарели');
        }
        
        $telegram_id = $auth_data['id'];
        $first_name = $auth_data['first_name'] ?? '';
        $last_name = $auth_data['last_name'] ?? '';
        $username = $auth_data['username'] ?? '';
        $photo_url = $auth_data['photo_url'] ?? '';
        
        // Проверяем, есть ли пользователь с таким Telegram ID в нашей базе
        $stmt = $pdo->prepare("SELECT id, username, role FROM admin_users WHERE telegram_id = ?");
        $stmt->execute([$telegram_id]);
        $admin_user = $stmt->fetch();
        
        if ($admin_user) {
            // Пользователь найден - авторизуем его
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin_user['id'];
            $_SESSION['admin_username'] = $admin_user['username'];
            $_SESSION['admin_role'] = $admin_user['role'];
            $_SESSION['telegram_authorized'] = true;
            
            // Обновляем информацию о пользователе
            $update_stmt = $pdo->prepare("
                UPDATE admin_users 
                SET last_login = CURRENT_TIMESTAMP, 
                    telegram_first_name = ?, 
                    telegram_last_name = ?,
                    telegram_username = ?,
                    telegram_photo_url = ? 
                WHERE id = ?
            ");
            $update_stmt->execute([
                $first_name,
                $last_name,
                $username,
                $photo_url,
                $admin_user['id']
            ]);
            
            header('Location: index.php');
            exit;
        } else {
            // Пользователь не найден - показываем страницу привязки аккаунта
            $_SESSION['telegram_temp_data'] = [
                'id' => $telegram_id,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'username' => $username,
                'photo_url' => $photo_url
            ];
            header('Location: telegram_link_account.php');
            exit;
        }
    } catch (Exception $e) {
        $error_message = 'Ошибка авторизации через Telegram: ' . $e->getMessage();
    }
} else {
    $error_message = 'Неверные параметры авторизации Telegram';
}

// Если произошла ошибка, перенаправляем на страницу входа с сообщением об ошибке
$_SESSION['telegram_error'] = $error_message;
header('Location: login.php');
exit;
