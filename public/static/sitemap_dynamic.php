<?php
/**
 * Динамическая генерация sitemap.xml
 * Генерирует карту сайта на основе данных из БД
 */

require_once 'config.php';

// Устанавливаем заголовок XML
header('Content-Type: application/xml; charset=utf-8');

// Начинаем XML
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

// Базовый URL сайта (в продакшене заменить на реальный домен)
$base_url = 'http://localhost';

// Статичные страницы
$static_pages = [
    ['url' => '/main.php', 'priority' => '1.0', 'changefreq' => 'daily'],
    ['url' => '/films.php', 'priority' => '0.9', 'changefreq' => 'daily'],
    ['url' => '/genres.php', 'priority' => '0.8', 'changefreq' => 'weekly'],
    ['url' => '/directors.php', 'priority' => '0.8', 'changefreq' => 'weekly'],
    ['url' => '/help.php', 'priority' => '0.5', 'changefreq' => 'monthly'],
];

foreach ($static_pages as $page) {
    $file_path = __DIR__ . $page['url'];
    $lastmod = file_exists($file_path) ? date('Y-m-d', filemtime($file_path)) : date('Y-m-d');
    
    echo "  <url>\n";
    echo "    <loc>" . htmlspecialchars($base_url . $page['url']) . "</loc>\n";
    echo "    <lastmod>{$lastmod}</lastmod>\n";
    echo "    <changefreq>{$page['changefreq']}</changefreq>\n";
    echo "    <priority>{$page['priority']}</priority>\n";
    echo "  </url>\n";
}

// Динамические страницы фильмов из БД
try {
    $stmt = $pdo->query("SELECT id, title, created_at FROM movies ORDER BY id");
    $movies = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($movies as $movie) {
        $lastmod = $movie['created_at'] ? date('Y-m-d', strtotime($movie['created_at'])) : date('Y-m-d');
        
        echo "  <url>\n";
        echo "    <loc>" . htmlspecialchars($base_url . '/film_page.php?movie_id=' . $movie['id']) . "</loc>\n";
        echo "    <lastmod>{$lastmod}</lastmod>\n";
        echo "    <changefreq>monthly</changefreq>\n";
        echo "    <priority>0.7</priority>\n";
        echo "  </url>\n";
    }
} catch (PDOException $e) {
    // В случае ошибки БД просто пропускаем динамические URL
    error_log("Sitemap generation error: " . $e->getMessage());
}

// Страницы жанров
try {
    $stmt = $pdo->query("SELECT id, name FROM genres ORDER BY name");
    $genres = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($genres as $genre) {
        echo "  <url>\n";
        echo "    <loc>" . htmlspecialchars($base_url . '/films.php?genre_id=' . $genre['id']) . "</loc>\n";
        echo "    <lastmod>" . date('Y-m-d') . "</lastmod>\n";
        echo "    <changefreq>weekly</changefreq>\n";
        echo "    <priority>0.6</priority>\n";
        echo "  </url>\n";
    }
} catch (PDOException $e) {
    error_log("Sitemap genres error: " . $e->getMessage());
}

// Страницы режиссёров
try {
    $stmt = $pdo->query("SELECT id, name FROM directors ORDER BY name");
    $directors = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($directors as $director) {
        echo "  <url>\n";
        echo "    <loc>" . htmlspecialchars($base_url . '/films_by_directors.php?director_id=' . $director['id']) . "</loc>\n";
        echo "    <lastmod>" . date('Y-m-d') . "</lastmod>\n";
        echo "    <changefreq>monthly</changefreq>\n";
        echo "    <priority>0.6</priority>\n";
        echo "  </url>\n";
    }
} catch (PDOException $e) {
    error_log("Sitemap directors error: " . $e->getMessage());
}

echo '</urlset>';
