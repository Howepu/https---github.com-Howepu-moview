<?php
// Запускаем сессию
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config.php';

// Проверяем, есть ли временные данные Yandex
if (!isset($_SESSION['yandex_temp_data'])) {
    header('Location: login.php');
    exit;
}

$yandex_data = $_SESSION['yandex_temp_data'];
$error_message = '';
$success_message = '';

// Обработка формы привязки аккаунта
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error_message = 'Пожалуйста, заполните все поля';
    } else {
        try {
            // Проверяем существующего администратора
            $stmt = $pdo->prepare("SELECT id, username, password_hash, role FROM admin_users WHERE username = ?");
            $stmt->execute([$username]);
            $admin_user = $stmt->fetch();
            
            if ($admin_user && password_verify($password, $admin_user['password_hash'])) {
                // Проверяем, не привязан ли уже Yandex аккаунт к другому пользователю
                $check_stmt = $pdo->prepare("SELECT id FROM admin_users WHERE yandex_id = ?");
                $check_stmt->execute([$yandex_data['id']]);
                
                if ($check_stmt->fetch()) {
                    $error_message = 'Этот аккаунт Яндекс уже привязан к другому пользователю';
                } else {
                    // Привязываем Yandex аккаунт к существующему администратору
                    $update_stmt = $pdo->prepare("
                        UPDATE admin_users 
                        SET yandex_id = ?, 
                            yandex_login = ?, 
                            yandex_first_name = ?,
                            yandex_last_name = ?,
                            yandex_display_name = ?,
                            yandex_real_name = ?,
                            yandex_email = ?,
                            yandex_avatar_url = ?,
                            yandex_access_token = ?,
                            last_login = CURRENT_TIMESTAMP
                        WHERE id = ?
                    ");
                    
                    $update_stmt->execute([
                        $yandex_data['id'],
                        $yandex_data['login'],
                        $yandex_data['first_name'],
                        $yandex_data['last_name'],
                        $yandex_data['display_name'],
                        $yandex_data['real_name'],
                        $yandex_data['email'],
                        $yandex_data['avatar_url'],
                        $yandex_data['access_token'],
                        $admin_user['id']
                    ]);
                    
                    // Авторизуем пользователя
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_id'] = $admin_user['id'];
                    $_SESSION['admin_username'] = $admin_user['username'];
                    $_SESSION['admin_role'] = $admin_user['role'];
                    $_SESSION['yandex_authorized'] = true;
                    
                    // Очищаем временные данные
                    unset($_SESSION['yandex_temp_data']);
                    
                    header('Location: index.php');
                    exit;
                }
            } else {
                $error_message = 'Неверный логин или пароль администратора';
            }
        } catch (PDOException $e) {
            $error_message = 'Ошибка подключения к базе данных';
        }
    }
}

$pageTitle = "Привязка аккаунта Яндекс - MoviePortal";
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
                <h1>Привязка аккаунта</h1>
                <h2>Яндекс к админ-панели</h2>
            </div>
            
            <div class="yandex-user-info">
                <div class="yandex-avatar">
                    <?php if (!empty($yandex_data['avatar_url'])): ?>
                        <img src="<?= htmlspecialchars($yandex_data['avatar_url']) ?>" alt="Avatar">
                    <?php else: ?>
                        <div class="yandex-avatar-placeholder">🔴</div>
                    <?php endif; ?>
                </div>
                <div class="yandex-name">
                    <strong>
                        <?= htmlspecialchars($yandex_data['display_name'] ?: $yandex_data['real_name'] ?: ($yandex_data['first_name'] . ' ' . $yandex_data['last_name'])) ?>
                    </strong>
                </div>
                <?php if (!empty($yandex_data['login'])): ?>
                    <div class="yandex-login">@<?= htmlspecialchars($yandex_data['login']) ?></div>
                <?php endif; ?>
                <?php if (!empty($yandex_data['email'])): ?>
                    <div class="yandex-email"><?= htmlspecialchars($yandex_data['email']) ?></div>
                <?php endif; ?>
                <div class="yandex-id">ID: <?= htmlspecialchars($yandex_data['id']) ?></div>
            </div>
            
            <div class="link-info">
                <p>Для безопасности, привязка аккаунта Яндекс возможна только к существующим администраторам.</p>
                <p>Введите данные вашего административного аккаунта:</p>
            </div>
            
            <?php if (!empty($error_message)): ?>
                <div class="error-message">
                    <?= htmlspecialchars($error_message) ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="login-form">
                <div class="form-group">
                    <label for="username">Логин администратора:</label>
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
                    <label for="password">Пароль администратора:</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required
                        autocomplete="current-password"
                    >
                </div>
                
                <button type="submit" class="btn btn-primary login-btn">Привязать аккаунт</button>
            </form>
            
            <div class="login-footer">
                <a href="login.php" class="back-link">← Вернуться к входу</a>
            </div>
        </div>
    </div>
</body>
</html>
