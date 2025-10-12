<?php
/**
 * Скрипт для инициализации таблицы админов
 * Запустите этот файл один раз для создания таблицы admin_users
 */

require_once 'config.php';

try {
    // Читаем SQL-скрипт
    $sql_file = '../admin_setup.sql';
    if (!file_exists($sql_file)) {
        throw new Exception("SQL файл не найден: $sql_file");
    }
    
    $sql = file_get_contents($sql_file);
    
    // Выполняем SQL-команды
    $pdo->exec($sql);
    
    echo "<h1>✅ Инициализация завершена успешно!</h1>";
    echo "<p>Таблица admin_users создана и настроена.</p>";
    echo "<p><strong>Тестовый админ:</strong></p>";
    echo "<ul>";
    echo "<li><strong>Логин:</strong> admin</li>";
    echo "<li><strong>Пароль:</strong> admin123</li>";
    echo "</ul>";
    echo "<p><a href='admin/login.php'>Перейти к входу в админ-панель</a></p>";
    echo "<p><a href='main.php'>Вернуться на главную</a></p>";
    
    echo "<hr>";
    echo "<p><small><strong>Важно:</strong> После инициализации рекомендуется удалить этот файл из соображений безопасности.</small></p>";
    
} catch (Exception $e) {
    echo "<h1>❌ Ошибка инициализации</h1>";
    echo "<p>Произошла ошибка: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Убедитесь, что:</p>";
    echo "<ul>";
    echo "<li>База данных доступна</li>";
    echo "<li>SQL-файл admin_setup.sql существует</li>";
    echo "<li>У пользователя есть права на создание таблиц</li>";
    echo "</ul>";
}
?>
