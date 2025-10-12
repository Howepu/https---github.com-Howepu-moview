<?php
// Запускаем сессию только если она еще не запущена
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = "Доступ запрещен - MoviePortal";
$current_user = null;

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    $current_user = [
        'username' => $_SESSION['admin_username'] ?? null,
        'role' => $_SESSION['admin_role'] ?? 'user'
    ];
}
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
                <h1>Доступ запрещен</h1>
                <h2>MoviePortal</h2>
            </div>
            
            <div class="error-message">
                <p>У вас недостаточно прав для доступа к этой странице.</p>
                <?php if ($current_user): ?>
                    <p>Вы вошли как: <strong><?= htmlspecialchars($current_user['username']) ?></strong> 
                    (роль: <strong><?= htmlspecialchars($current_user['role']) ?></strong>)</p>
                    <p>Для доступа к административным функциям требуется роль <strong>admin</strong>.</p>
                <?php else: ?>
                    <p>Пожалуйста, войдите в систему.</p>
                <?php endif; ?>
            </div>
            
            <div class="login-footer">
                <?php if ($current_user): ?>
                    <a href="index.php" class="back-link">← Вернуться в панель</a>
                    <br><br>
                    <a href="auth.php?action=logout" class="register-link">Выйти из системы</a>
                <?php else: ?>
                    <a href="login.php" class="back-link">← Войти в систему</a>
                <?php endif; ?>
                <br><br>
                <a href="../main.php" class="back-link">← Вернуться на сайт</a>
            </div>
        </div>
    </div>
</body>
</html>
