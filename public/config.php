<?php
// Настройки базы данных
define('DB_HOST', 'postgres');
define('DB_NAME', 'movie_portal');
define('DB_USER', 'postgres');
define('DB_PASS', 'postgres');

// Базовый URL приложения
define('BASE_URL', 'http://localhost');


// Подключение к базе данных с использованием пула соединений
try {
    $pdo = new PDO(
        "pgsql:host=" . DB_HOST . ";dbname=" . DB_NAME,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_PERSISTENT => true // Использование постоянных соединений для повышения производительности
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
