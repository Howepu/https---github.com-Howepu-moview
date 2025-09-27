<?php
require_once 'config.php';

// Получаем ID жанра из GET-параметра
$genre_id = isset($_GET['genre_id']) ? (int)$_GET['genre_id'] : null;

if (!$genre_id) {
    $movies = [];
    $genre_name = "Не указан жанр";
} else {
    // Получение названия жанра
    $genre_query = "SELECT name FROM genres WHERE id = :genre_id";
    $genre_stmt = $pdo->prepare($genre_query);
    $genre_stmt->execute(['genre_id' => $genre_id]);
    $genre = $genre_stmt->fetch(PDO::FETCH_ASSOC);
    $genre_name = $genre ? $genre['name'] : "Жанр не найден";

    // Получение списка фильмов по жанру
    $query = "
        SELECT m.title, m.year, m.duration, m.country, m.poster_url, d.name as director_name
        FROM movies m
        JOIN directors d ON m.director_id = d.id
        JOIN movie_genres mg ON m.id = mg.movie_id
        WHERE mg.genre_id = :genre_id
        ORDER BY m.year DESC
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['genre_id' => $genre_id]);
    $movies = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MoviePortal - Фильмы жанра <?php echo htmlspecialchars($genre_name); ?></title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="header">
        <div class="logo-container">
            <a href="main.html" class="logo">MoviePortal</a>
        </div>
        <div class="menu-toggle">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </div>
    <div class="container">
        <div class="nav">
            <ul>
                <li><a href="main.html">Главная</a></li>
                <li><a href="films.html">Фильмы</a></li>
                <li><a href="directors.html">Режиссёры</a></li>
            </ul>
        </div>
        <div class="main-content">
            <h2>Фильмы жанра: <?php echo htmlspecialchars($genre_name); ?></h2>
            <?php if (empty($movies)): ?>
                <p>Фильмы не найдены или жанр не указан. Укажите genre_id в URL, например: films_by_genres.php?genre_id=1</p>
            <?php else: ?>
                <div class="movie-grid">
                    <?php foreach ($movies as $movie): ?>
                        <div class="movie-card">
                            <img src="<?php echo htmlspecialchars($movie['poster_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($movie['title']); ?>" 
                                 width="125" height="125"
                                 onerror="this.src='https://via.placeholder.com/125x125'">
                            <div class="movie-info">
                                <h3><?php echo htmlspecialchars($movie['title']); ?></h3>
                                <p><?php echo $movie['year'] . ' | ' . $movie['duration'] . ' мин'; ?></p>
                                <p><?php echo htmlspecialchars($movie['country']) . ', Режиссер: ' . htmlspecialchars($movie['director_name']); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="footer">
        <div class="footer-logo">
            <div class="footer-logo-container">
                <a href="#" class="logo">MoviePortal</a>
            </div>
        </div>
        <div class="social-links">
            <a href="#" class="social-icon" id="telegram">Telegram</a>
            <a href="#" class="social-icon" id="vk">VK</a>
            <a href="#" class="social-icon" id="youtube">YouTube</a>
        </div>
    </div>
    <script>
        const menuToggle = document.querySelector('.menu-toggle');
        const nav = document.querySelector('.nav');

        menuToggle.addEventListener('click', () => {
            nav.classList.toggle('active');
            menuToggle.classList.toggle('active');
        });
    </script>
</body>
</html>