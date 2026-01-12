<?php
/**
 * Скрипт инициализации VK OAuth системы
 * Выполняет обновление структуры БД для поддержки VK авторизации
 */

require_once 'config.php';

echo "<h1>Инициализация VK OAuth системы</h1>\n";

try {
    // Выполняем SQL скрипт для добавления поддержки VK
    $sql = file_get_contents(__DIR__ . '/../sql/vk_oauth_setup.sql');
    
    if ($sql === false) {
        throw new Exception('Не удалось прочитать файл vk_oauth_setup.sql');
    }
    
    // Разбиваем SQL на отдельные команды
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    $pdo->beginTransaction();
    
    foreach ($statements as $statement) {
        if (!empty($statement) && !str_starts_with(trim($statement), '--')) {
            echo "<p>Выполняется: " . htmlspecialchars(substr($statement, 0, 50)) . "...</p>\n";
            $pdo->exec($statement);
        }
    }
    
    $pdo->commit();
    
    echo "<div style='color: green; font-weight: bold; margin: 20px 0;'>";
    echo "✅ VK OAuth система успешно инициализирована!";
    echo "</div>";
    
    echo "<h2>Следующие шаги:</h2>";
    echo "<ol>";
    echo "<li>Создайте приложение ВКонтакте на <a href='https://vk.com/apps?act=manage' target='_blank'>https://vk.com/apps?act=manage</a></li>";
    echo "<li>Получите App ID и App Secret</li>";
    echo "<li>Укажите Redirect URI: <code>http://localhost/admin/vk_callback.php</code></li>";
    echo "<li>Замените значения в файле <code>public/admin/vk_config.php</code>:</li>";
    echo "<ul>";
    echo "<li><code>YOUR_VK_APP_ID</code> - на ваш App ID</li>";
    echo "<li><code>YOUR_VK_APP_SECRET</code> - на ваш App Secret</li>";
    echo "</ul>";
    echo "<li>Для привязки VK аккаунта к существующему администратору:</li>";
    echo "<ul>";
    echo "<li>Войдите через ВК на странице входа</li>";
    echo "<li>Введите данные существующего администратора для привязки</li>";
    echo "</ul>";
    echo "</ol>";
    
    echo "<h2>Безопасность:</h2>";
    echo "<ul>";
    echo "<li>❌ Новые пользователи НЕ могут создавать аккаунты через VK</li>";
    echo "<li>✅ VK авторизация доступна только для существующих администраторов</li>";
    echo "<li>✅ Привязка VK аккаунта требует подтверждения паролем</li>";
    echo "</ul>";
    
    echo "<p><a href='admin/login.php'>← Перейти к странице входа</a></p>";
    
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    echo "<div style='color: red; font-weight: bold; margin: 20px 0;'>";
    echo "❌ Ошибка инициализации: " . htmlspecialchars($e->getMessage());
    echo "</div>";
    
    echo "<p>Проверьте:</p>";
    echo "<ul>";
    echo "<li>Подключение к базе данных</li>";
    echo "<li>Права доступа к файлу vk_oauth_setup.sql</li>";
    echo "<li>Права пользователя БД на изменение структуры таблиц</li>";
    echo "</ul>";
}
?>

<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
    line-height: 1.6;
}

h1, h2 {
    color: #333;
}

code {
    background: #f4f4f4;
    padding: 2px 4px;
    border-radius: 3px;
    font-family: monospace;
}

a {
    color: #0077ff;
    text-decoration: none;
}

a:hover {
    text-decoration: underline;
}

ul, ol {
    margin-left: 20px;
}

li {
    margin-bottom: 5px;
}
</style>
