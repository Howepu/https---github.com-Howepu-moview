<?php
// Настройки базы данных (с поддержкой переменных окружения для Render/Railway)

// Проверяем наличие DATABASE_URL (Render использует этот формат)
if (getenv('DATABASE_URL')) {
    $db = parse_url(getenv('DATABASE_URL'));
    define('DB_HOST', $db['host']);
    define('DB_NAME', ltrim($db['path'], '/'));
    define('DB_USER', $db['user']);
    define('DB_PASS', $db['pass']);
    define('DB_PORT', $db['port'] ?? 5432);
} else {
    // Локальная разработка или отдельные переменные
    define('DB_HOST', getenv('DB_HOST') ?: 'postgres');
    define('DB_NAME', getenv('DB_NAME') ?: 'movies_db');
    define('DB_USER', getenv('DB_USER') ?: 'postgres');
    define('DB_PASS', getenv('DB_PASSWORD') ?: 'postgres');
    define('DB_PORT', getenv('DB_PORT') ?: 5432);
}

// Базовый URL приложения
define('BASE_URL', getenv('BASE_URL') ?: 'http://localhost');
define('APP_ENV', getenv('APP_ENV') ?: 'development');

// Подключение к базе данных с использованием пула соединений
try {
    $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME;
    
    $pdo = new PDO(
        $dsn,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_PERSISTENT => (APP_ENV === 'development') // Постоянные соединения только для разработки
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