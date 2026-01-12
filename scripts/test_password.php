<?php
// Тестируем создание и проверку пароля
$password = 'admin123';

echo "<h1>🔐 Тест хеширования пароля</h1>";

// Создаем новый хеш
$new_hash = password_hash($password, PASSWORD_DEFAULT);
echo "<p><strong>Новый хеш:</strong> $new_hash</p>";

// Проверяем хеш
$verify_result = password_verify($password, $new_hash);
echo "<p><strong>Проверка хеша:</strong> " . ($verify_result ? '✅ Успешно' : '❌ Ошибка') . "</p>";

// Старый хеш из базы
$old_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
echo "<p><strong>Старый хеш:</strong> $old_hash</p>";

$verify_old = password_verify($password, $old_hash);
echo "<p><strong>Проверка старого хеша:</strong> " . ($verify_old ? '✅ Успешно' : '❌ Ошибка') . "</p>";

// Обновляем в базе данных
require_once 'config.php';

try {
    $stmt = $pdo->prepare("UPDATE admin_users SET password_hash = ? WHERE username = 'admin'");
    $result = $stmt->execute([$new_hash]);
    
    if ($result) {
        echo "<p style='color: green;'>✅ <strong>Пароль в базе данных обновлен!</strong></p>";
    } else {
        echo "<p style='color: red;'>❌ Ошибка обновления пароля</p>";
    }
    
    // Проверяем что получилось
    $stmt = $pdo->prepare("SELECT password_hash FROM admin_users WHERE username = 'admin'");
    $stmt->execute();
    $db_hash = $stmt->fetchColumn();
    
    echo "<p><strong>Хеш в базе данных:</strong> $db_hash</p>";
    $verify_db = password_verify($password, $db_hash);
    echo "<p><strong>Проверка хеша из БД:</strong> " . ($verify_db ? '✅ Успешно' : '❌ Ошибка') . "</p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Ошибка БД: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='admin/login.php'>Попробовать войти в админ-панель</a></p>";
?>

<style>
body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
</style>
