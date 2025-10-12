<?php
// Тестовая страница для проверки подключения к базе данных

echo "<h1>Тест подключения к базе данных</h1>";

// Настройки базы данных
define('DB_HOST', 'postgres');
define('DB_NAME', 'movie_portal');
define('DB_USER', 'postgres');
define('DB_PASS', 'postgres');

echo "<h2>Настройки подключения:</h2>";
echo "<ul>";
echo "<li><strong>Host:</strong> " . DB_HOST . "</li>";
echo "<li><strong>Database:</strong> " . DB_NAME . "</li>";
echo "<li><strong>User:</strong> " . DB_USER . "</li>";
echo "<li><strong>Password:</strong> " . (DB_PASS ? '[установлен]' : '[не установлен]') . "</li>";
echo "</ul>";

echo "<h2>Результат подключения:</h2>";

try {
    $pdo = new PDO(
        "pgsql:host=" . DB_HOST . ";dbname=" . DB_NAME,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
    
    echo "<p style='color: green;'>✅ <strong>Подключение к базе данных успешно!</strong></p>";
    
    // Проверяем существующие таблицы
    echo "<h3>Существующие таблицы:</h3>";
    $stmt = $pdo->query("
        SELECT table_name 
        FROM information_schema.tables 
        WHERE table_schema = 'public' 
        ORDER BY table_name
    ");
    $tables = $stmt->fetchAll();
    
    if (empty($tables)) {
        echo "<p style='color: orange;'>⚠️ Таблицы не найдены. Необходимо выполнить инициализацию.</p>";
        echo "<p><a href='init_admin.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Инициализировать базу данных</a></p>";
    } else {
        echo "<ul>";
        foreach ($tables as $table) {
            echo "<li>" . htmlspecialchars($table['table_name']) . "</li>";
        }
        echo "</ul>";
        
        // Проверяем таблицу admin_users
        $stmt = $pdo->query("
            SELECT table_name 
            FROM information_schema.tables 
            WHERE table_schema = 'public' AND table_name = 'admin_users'
        ");
        $admin_table = $stmt->fetch();
        
        if ($admin_table) {
            echo "<p style='color: green;'>✅ Таблица admin_users существует</p>";
            
            // Проверяем количество админов
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM admin_users");
            $count = $stmt->fetch()['count'];
            echo "<p>Количество админов в системе: <strong>$count</strong></p>";
            
            if ($count > 0) {
                echo "<p><a href='admin/login.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Перейти к входу в админ-панель</a></p>";
            }
        } else {
            echo "<p style='color: orange;'>⚠️ Таблица admin_users не найдена</p>";
            echo "<p><a href='init_admin.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Создать таблицу админов</a></p>";
        }
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ <strong>Ошибка подключения:</strong></p>";
    echo "<p style='background: #f8d7da; padding: 10px; border-radius: 5px; color: #721c24;'>";
    echo htmlspecialchars($e->getMessage());
    echo "</p>";
    
    echo "<h3>Возможные причины:</h3>";
    echo "<ul>";
    echo "<li>PostgreSQL контейнер не запущен</li>";
    echo "<li>База данных еще не создана</li>";
    echo "<li>Неверные учетные данные</li>";
    echo "<li>Проблемы с сетью Docker</li>";
    echo "</ul>";
    
    echo "<h3>Рекомендации:</h3>";
    echo "<ol>";
    echo "<li>Проверьте статус контейнеров: <code>docker-compose ps</code></li>";
    echo "<li>Перезапустите контейнеры: <code>docker-compose down && docker-compose up</code></li>";
    echo "<li>Проверьте логи PostgreSQL: <code>docker-compose logs postgres</code></li>";
    echo "</ol>";
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
code {
    background: #f4f4f4;
    padding: 2px 5px;
    border-radius: 3px;
    font-family: monospace;
}
</style>
