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
$success_message = '';

// Обработка формы регистрации
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Валидация данных
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error_message = 'Пожалуйста, заполните все поля';
    } elseif (strlen($username) < 3) {
        $error_message = 'Логин должен содержать минимум 3 символа';
    } elseif (strlen($password) < 6) {
        $error_message = 'Пароль должен содержать минимум 6 символов';
    } elseif ($password !== $confirm_password) {
        $error_message = 'Пароли не совпадают';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Введите корректный email адрес';
    } else {
        try {
            // Проверяем, не существует ли уже пользователь с таким логином
            $stmt = $pdo->prepare("SELECT id FROM admin_users WHERE username = ?");
            $stmt->execute([$username]);
            
            if ($stmt->fetch()) {
                $error_message = 'Пользователь с таким логином уже существует';
            } else {
                // Проверяем email
                $stmt = $pdo->prepare("SELECT id FROM admin_users WHERE email = ?");
                $stmt->execute([$email]);
                
                if ($stmt->fetch()) {
                    $error_message = 'Пользователь с таким email уже существует';
                } else {
                    // Создаем нового пользователя
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);
                    
                    $stmt = $pdo->prepare("
                        INSERT INTO admin_users (username, password_hash, email, created_at) 
                        VALUES (?, ?, ?, CURRENT_TIMESTAMP)
                    ");
                    
                    if ($stmt->execute([$username, $password_hash, $email])) {
                        $success_message = 'Регистрация успешно завершена! Теперь вы можете войти в систему.';
                        
                        // Очищаем поля формы после успешной регистрации
                        $username = '';
                        $email = '';
                    } else {
                        $error_message = 'Ошибка при создании пользователя';
                    }
                }
            }
        } catch (PDOException $e) {
            $error_message = 'Ошибка подключения к базе данных: ' . $e->getMessage();
        }
    }
}

$pageTitle = "Регистрация админа - MoviePortal";
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
                <h1>Регистрация</h1>
                <h2>Админ-панель MoviePortal</h2>
            </div>
            
            <?php if (!empty($error_message)): ?>
                <div class="error-message">
                    <?= htmlspecialchars($error_message) ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success_message)): ?>
                <div class="success-message">
                    <?= htmlspecialchars($success_message) ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="login-form">
                <div class="form-group">
                    <label for="username">Логин:</label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        value="<?= htmlspecialchars($username ?? '') ?>"
                        required
                        minlength="3"
                        maxlength="50"
                        autocomplete="username"
                        placeholder="Введите логин (минимум 3 символа)"
                    >
                </div>
                
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        value="<?= htmlspecialchars($email ?? '') ?>"
                        required
                        autocomplete="email"
                        placeholder="admin@example.com"
                    >
                </div>
                
                <div class="form-group">
                    <label for="password">Пароль:</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required
                        minlength="6"
                        autocomplete="new-password"
                        placeholder="Минимум 6 символов"
                    >
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Подтвердите пароль:</label>
                    <input 
                        type="password" 
                        id="confirm_password" 
                        name="confirm_password" 
                        required
                        minlength="6"
                        autocomplete="new-password"
                        placeholder="Повторите пароль"
                    >
                </div>
                
                <button type="submit" class="btn btn-primary login-btn">Зарегистрироваться</button>
            </form>
            
            <div class="login-footer">
                <p>Уже есть аккаунт? <a href="login.php" class="register-link">Войти</a></p>
                <a href="../main.php" class="back-link">← Вернуться на сайт</a>
            </div>
        </div>
    </div>

    <script>
    // Проверка совпадения паролей в реальном времени
    document.getElementById('confirm_password').addEventListener('input', function() {
        const password = document.getElementById('password').value;
        const confirmPassword = this.value;
        
        if (confirmPassword && password !== confirmPassword) {
            this.setCustomValidity('Пароли не совпадают');
        } else {
            this.setCustomValidity('');
        }
    });
    
    // Проверка длины пароля
    document.getElementById('password').addEventListener('input', function() {
        const confirmPassword = document.getElementById('confirm_password');
        if (confirmPassword.value && this.value !== confirmPassword.value) {
            confirmPassword.setCustomValidity('Пароли не совпадают');
        } else {
            confirmPassword.setCustomValidity('');
        }
    });
    </script>
</body>
</html>
