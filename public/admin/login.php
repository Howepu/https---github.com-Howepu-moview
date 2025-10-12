<?php
// Запускаем сессию только если она еще не запущена
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config.php';

// Если пользователь уже авторизован, перенаправляем на главную админ-панели
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: index.php');
    exit;
}

$error_message = '';

// Обработка формы входа
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error_message = 'Пожалуйста, заполните все поля';
    } else {
        try {
            // Поиск пользователя в базе данных
            $stmt = $pdo->prepare("SELECT id, username, password_hash FROM admin_users WHERE username = ?");
            $stmt->execute([$username]);
            $admin = $stmt->fetch();
            
            if ($admin && password_verify($password, $admin['password_hash'])) {
                // Успешная авторизация
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                
                // Обновляем время последнего входа
                $update_stmt = $pdo->prepare("UPDATE admin_users SET last_login = CURRENT_TIMESTAMP WHERE id = ?");
                $update_stmt->execute([$admin['id']]);
                
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
            
            <div class="login-footer">
                <p>Нет аккаунта? <a href="register.php" class="register-link">Зарегистрироваться</a></p>
                <a href="../main.php" class="back-link">← Вернуться на сайт</a>
            </div>
            
            <div class="login-info">
                <small>Тестовые данные: логин <strong>admin</strong>, пароль <strong>admin123</strong></small>
            </div>
        </div>
    </div>
</body>
</html>
