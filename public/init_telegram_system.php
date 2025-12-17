<?php
/**
 * Скрипт инициализации Telegram OAuth системы
 * Выполняет обновление структуры БД для поддержки Telegram авторизации
 */

require_once 'config.php';

echo "<h1>Инициализация Telegram OAuth системы</h1>\n";

try {
    // Выполняем SQL скрипт для добавления поддержки Telegram
    $sql = file_get_contents(__DIR__ . '/../telegram_oauth_setup.sql');
    
    if ($sql === false) {
        throw new Exception('Не удалось прочитать файл telegram_oauth_setup.sql');
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
    echo "✅ Telegram OAuth система успешно инициализирована!";
    echo "</div>";
    
    echo "<h2>Следующие шаги:</h2>";
    echo "<ol>";
    echo "<li>Создайте Telegram бота через <a href='https://t.me/BotFather' target='_blank'>@BotFather</a></li>";
    echo "<li>Получите токен бота и username</li>";
    echo "<li>Настройте домен бота командой <code>/setdomain</code> в @BotFather</li>";
    echo "<li>Укажите домен: <code>localhost</code></li>";
    echo "<li>Замените значения в файле <code>public/admin/telegram_config.php</code>:</li>";
    echo "<ul>";
    echo "<li><code>YOUR_BOT_TOKEN</code> - на токен вашего бота</li>";
    echo "<li><code>YOUR_BOT_USERNAME</code> - на username вашего бота (без @)</li>";
    echo "</ul>";
    echo "<li>Для привязки Telegram аккаунта к существующему администратору:</li>";
    echo "<ul>";
    echo "<li>Войдите через Telegram на странице входа</li>";
    echo "<li>Введите данные существующего администратора для привязки</li>";
    echo "</ul>";
    echo "</ol>";
    
    echo "<h2>Преимущества Telegram авторизации:</h2>";
    echo "<ul>";
    echo "<li>✅ Более простая настройка по сравнению с VK</li>";
    echo "<li>✅ Надежная криптографическая подпись данных</li>";
    echo "<li>✅ Не требует сложных OAuth потоков</li>";
    echo "<li>✅ Встроенная защита от подделки данных</li>";
    echo "</ul>";
    
    echo "<h2>Безопасность:</h2>";
    echo "<ul>";
    echo "<li>❌ Новые пользователи НЕ могут создавать аккаунты через Telegram</li>";
    echo "<li>✅ Telegram авторизация доступна только для существующих администраторов</li>";
    echo "<li>✅ Привязка Telegram аккаунта требует подтверждения паролем</li>";
    echo "<li>✅ Проверка подписи данных от Telegram</li>";
    echo "<li>✅ Проверка актуальности данных (не старше 24 часов)</li>";
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
    echo "<li>Права доступа к файлу telegram_oauth_setup.sql</li>";
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
    color: #0088cc;
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
