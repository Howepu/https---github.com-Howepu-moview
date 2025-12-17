<?php
// Запускаем сессию
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config.php';

// Проверяем, есть ли временные данные Telegram
if (!isset($_SESSION['telegram_temp_data'])) {
    header('Location: login.php');
    exit;
}

$telegram_data = $_SESSION['telegram_temp_data'];
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
                // Проверяем, не привязан ли уже Telegram аккаунт к другому пользователю
                $check_stmt = $pdo->prepare("SELECT id FROM admin_users WHERE telegram_id = ?");
                $check_stmt->execute([$telegram_data['id']]);
                
                if ($check_stmt->fetch()) {
                    $error_message = 'Этот аккаунт Telegram уже привязан к другому пользователю';
                } else {
                    // Привязываем Telegram аккаунт к существующему администратору
                    $update_stmt = $pdo->prepare("
                        UPDATE admin_users 
                        SET telegram_id = ?, 
                            telegram_first_name = ?, 
                            telegram_last_name = ?,
                            telegram_username = ?,
                            telegram_photo_url = ?,
                            last_login = CURRENT_TIMESTAMP
                        WHERE id = ?
                    ");
                    
                    $update_stmt->execute([
                        $telegram_data['id'],
                        $telegram_data['first_name'],
                        $telegram_data['last_name'],
                        $telegram_data['username'],
                        $telegram_data['photo_url'],
                        $admin_user['id']
                    ]);
                    
                    // Авторизуем пользователя
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_id'] = $admin_user['id'];
                    $_SESSION['admin_username'] = $admin_user['username'];
                    $_SESSION['admin_role'] = $admin_user['role'];
                    $_SESSION['telegram_authorized'] = true;
                    
                    // Очищаем временные данные
                    unset($_SESSION['telegram_temp_data']);
                    
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

$pageTitle = "Привязка аккаунта Telegram - MoviePortal";
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
                <h2>Telegram к админ-панели</h2>
            </div>
            
            <div class="telegram-user-info">
                <div class="telegram-avatar">
                    <?php if (!empty($telegram_data['photo_url'])): ?>
                        <img src="<?= htmlspecialchars($telegram_data['photo_url']) ?>" alt="Avatar">
                    <?php else: ?>
                        <div class="telegram-avatar-placeholder">📱</div>
                    <?php endif; ?>
                </div>
                <div class="telegram-name">
                    <strong>
                        <?= htmlspecialchars($telegram_data['first_name']) ?>
                        <?= htmlspecialchars($telegram_data['last_name'] ?? '') ?>
                    </strong>
                </div>
                <?php if (!empty($telegram_data['username'])): ?>
                    <div class="telegram-username">@<?= htmlspecialchars($telegram_data['username']) ?></div>
                <?php endif; ?>
                <div class="telegram-id">ID: <?= htmlspecialchars($telegram_data['id']) ?></div>
            </div>
            
            <div class="link-info">
                <p>Для безопасности, привязка аккаунта Telegram возможна только к существующим администраторам.</p>
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
