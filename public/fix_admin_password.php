<?php
// Скрипт для исправления пароля админа
require_once 'config.php';

echo "<h1>🔧 Исправление пароля админа</h1>";

try {
    // Создаем правильный хеш для пароля "admin123"
    $password = 'admin123';
    $hash = password_hash($password, PASSWORD_DEFAULT);
    
    echo "<p><strong>Новый хеш пароля:</strong> $hash</p>";
    
    // Обновляем пароль в базе данных
    $stmt = $pdo->prepare("UPDATE admin_users SET password_hash = ? WHERE username = 'admin'");
    $result = $stmt->execute([$hash]);
    
    if ($result) {
        echo "<p style='color: green;'>✅ <strong>Пароль успешно обновлен!</strong></p>";
        
        // Проверяем, что запись обновилась
        $stmt = $pdo->prepare("SELECT username, email, created_at FROM admin_users WHERE username = 'admin'");
        $stmt->execute();
        $admin = $stmt->fetch();
        
        if ($admin) {
            echo "<h3>Информация об админе:</h3>";
            echo "<ul>";
            echo "<li><strong>Логин:</strong> " . htmlspecialchars($admin['username']) . "</li>";
            echo "<li><strong>Email:</strong> " . htmlspecialchars($admin['email']) . "</li>";
            echo "<li><strong>Создан:</strong> " . htmlspecialchars($admin['created_at']) . "</li>";
            echo "</ul>";
        }
        
        echo "<p><strong>Тестовые данные для входа:</strong></p>";
        echo "<ul>";
        echo "<li><strong>Логин:</strong> admin</li>";
        echo "<li><strong>Пароль:</strong> admin123</li>";
        echo "</ul>";
        
        echo "<p><a href='admin/login.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Попробовать войти в админ-панель</a></p>";
        
    } else {
        echo "<p style='color: red;'>❌ Ошибка при обновлении пароля</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ <strong>Ошибка базы данных:</strong></p>";
    echo "<p style='background: #f8d7da; padding: 10px; border-radius: 5px; color: #721c24;'>";
    echo htmlspecialchars($e->getMessage());
    echo "</p>";
}

echo "<hr>";
echo "<p><a href='main.php'>← Вернуться на главную</a></p>";
?>

<style>
body {
    font-family: Arial, sans-serif;
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
    line-height: 1.6;
}
</style>
