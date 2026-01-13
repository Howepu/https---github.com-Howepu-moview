<?php
require_once 'config.php';

// Установка Last-Modified заголовка
header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime(__FILE__)) . ' GMT');

header('Content-Type: application/json');

$query = isset($_GET['q']) ? trim($_GET['q']) : '';

if (strlen($query) < 2) {
    echo json_encode(['results' => [], 'message' => 'Введите минимум 2 символа']);
    exit;
}

try {
    // Поиск фильмов
    $stmt = $pdo->prepare("
        SELECT 
            m.id,
            m.title,
            m.year,
            m.poster_url,
            d.name AS director,
            STRING_AGG(g.name, ', ') AS genres
        FROM movies m
        JOIN directors d ON m.director_id = d.id
        LEFT JOIN movie_genres mg ON m.id = mg.movie_id
        LEFT JOIN genres g ON mg.genre_id = g.id
        WHERE 
            LOWER(m.title) LIKE LOWER(?) 
            OR LOWER(d.name) LIKE LOWER(?)
            OR LOWER(m.country) LIKE LOWER(?)
        GROUP BY m.id, d.name
        ORDER BY m.year DESC
        LIMIT 10
    ");
    
    $searchTerm = '%' . $query . '%';
    $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'results' => $results,
        'count' => count($results),
        'query' => $query
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'error' => 'Ошибка поиска',
        'results' => []
    ]);
}
