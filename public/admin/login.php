<?php
// Запускаем сессию только если она еще не запущена
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config.php';
require_once 'telegram_config.php';
require_once 'yandex_config.php';

// Если пользователь уже авторизован, перенаправляем на главную админ-панели
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: index.php');
    exit;
}

$error_message = '';

// Проверяем наличие ошибки Telegram авторизации
if (isset($_SESSION['telegram_error'])) {
    $error_message = $_SESSION['telegram_error'];
    unset($_SESSION['telegram_error']);
}

// Проверяем наличие ошибки Yandex авторизации
if (isset($_SESSION['yandex_error'])) {
    $error_message = $_SESSION['yandex_error'];
    unset($_SESSION['yandex_error']);
}

// Обработка формы входа
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error_message = 'Пожалуйста, заполните все поля';
    } else {
        try {
            // Поиск пользователя в базе данных
            $stmt = $pdo->prepare("SELECT id, username, password_hash, role FROM admin_users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password_hash'])) {
                // Успешная авторизация
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_username'] = $user['username'];
                $_SESSION['admin_role'] = $user['role'];
                
                // Обновляем время последнего входа
                $update_stmt = $pdo->prepare("UPDATE admin_users SET last_login = CURRENT_TIMESTAMP WHERE id = ?");
                $update_stmt->execute([$user['id']]);
                
                header('Location: index.php');
                exit;
            } else {
                $error_message = 'Неверный логин или пароль';
            }
        } catch (PDOException $e) {
            $error_message = 'Ошибка подключения к базе данных';
        }
    }
}

$pageTitle = "Вход в админ-панель - MoviePortal";
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="../styles.css?v=<?= time() ?>">
    <link rel="stylesheet" href="admin-styles.css?v=<?= time() ?>">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <h1>Админ-панель</h1>
                <h2>MoviePortal</h2>
            </div>
            
            <?php if (!empty($error_message)): ?>
                <div class="error-message">
                    <?= htmlspecialchars($error_message) ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="login-form">
                <div class="form-group">
                    <label for="username">Логин:</label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                        required
                        autocomplete="username"
                    >
                </div>
                
                <div class="form-group">
                    <label for="password">Пароль:</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required
                        autocomplete="current-password"
                    >
                </div>
                
                <button type="submit" class="btn btn-primary login-btn">Войти</button>
            </form>
            
            <div class="login-divider">
                <span>или</span>
            </div>
            
            <div id="telegram-login-container" class="telegram-login-container">
                <!-- Telegram Login Widget будет вставлен сюда через JavaScript -->
            </div>
            
            <a href="<?= getYandexAuthUrl() ?>" class="yandex-login-btn">
                🔴 Войти через Яндекс
            </a>
            
            <div class="login-footer">
                <a href="../main.php" class="back-link">← Вернуться на сайт</a>
            </div>
            
            <div class="login-info">
                <small>Тестовые данные:<br>
                Админ - логин: <strong>admin</strong>, пароль: <strong>admin123</strong><br>
                Пользователь - логин: <strong>user</strong>, пароль: <strong>user123</strong></small>
            </div>
        </div>
    </div>
    
    <script async src="https://telegram.org/js/telegram-widget.js?22" 
            data-telegram-login="<?= TELEGRAM_BOT_USERNAME ?>" 
            data-size="large" 
            data-auth-url="<?= 'http://127.0.0.1/admin/telegram_callback.php' ?>" 
            data-request-access="write">
    </script>
    
    <script>
        // Стилизация Telegram Login Widget
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.getElementById('telegram-login-container');
            if (container) {
                // Добавляем стили для контейнера
                container.style.marginTop = '1rem';
                container.style.textAlign = 'center';
                
                // Ждем загрузки виджета и стилизуем его
                setTimeout(function() {
                    const iframe = container.querySelector('iframe');
                    if (iframe) {
                        iframe.style.width = '100%';
                        iframe.style.maxWidth = '280px';
                        iframe.style.height = '50px';
                        iframe.style.border = 'none';
                        iframe.style.borderRadius = '16px';
                        iframe.style.boxShadow = '0 8px 24px rgba(0, 136, 204, 0.3)';
                    }
                }, 1000);
            }
        });
    </script>
</body>
</html>
