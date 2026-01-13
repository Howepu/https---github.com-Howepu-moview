<?php
// Тестовая страница для проверки подключения к БД
require_once '../config.php';

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>DB Test</title></head><body>";
echo "<h1>Проверка подключения к базе данных</h1>";

try {
    // Проверяем подключение
    echo "<p>✓ Подключение к БД установлено</p>";
    
    // Проверяем таблицу admin_users
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM admin_users");
    $result = $stmt->fetch();
    echo "<p>✓ Таблица admin_users существует</p>";
    echo "<p>Количество пользователей: " . $result['count'] . "</p>";
    
    // Проверяем админа
    $stmt = $pdo->prepare("SELECT username, role, yandex_id FROM admin_users WHERE username = 'admin'");
    $stmt->execute();
    $admin = $stmt->fetch();
    
    if ($admin) {
        echo "<p>✓ Админ найден:</p>";
        echo "<pre>";
        print_r($admin);
        echo "</pre>";
    } else {
        echo "<p>✗ Админ НЕ найден!</p>";
    }
    
    // Список всех таблиц
    $stmt = $pdo->query("SELECT tablename FROM pg_tables WHERE schemaname = 'public' ORDER BY tablename");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<h3>Таблицы в БД:</h3>";
    echo "<ul>";
    foreach ($tables as $table) {
        echo "<li>$table</li>";
    }
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Ошибка: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "</body></html>";
