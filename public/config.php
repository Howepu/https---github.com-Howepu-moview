<?php
// Настройки базы данных (с поддержкой переменных окружения для Railway)
define('DB_HOST', getenv('DB_HOST') ?: 'postgres');
define('DB_NAME', getenv('DB_NAME') ?: 'movies_db');
define('DB_USER', getenv('DB_USER') ?: 'postgres');
define('DB_PASS', getenv('DB_PASSWORD') ?: 'postgres');

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
    
    // Автоматический запуск миграций при первом подключении
    require_once __DIR__ . '/includes/migrations.php';
    $migrationResult = runDatabaseMigrations($pdo);
    
    if (!$migrationResult['success']) {
        error_log("MoviePortal Migration Error: " . $migrationResult['message']);
    } elseif (!$migrationResult['skipped']) {
        error_log("MoviePortal: " . $migrationResult['message']);
    }
    
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    http_response_code(500);
    die("Database connection failed. Please check configuration.");
}
?>

