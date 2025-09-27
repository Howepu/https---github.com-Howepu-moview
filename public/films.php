<?php
require_once 'config.php';

// Получаем список фильмов с информацией о режиссерах и жанрах
$stmt = $pdo->query("
    SELECT 
        m.id,
        m.title,
        m.year,
        m.duration,
        m.country,
        m.poster_url,
        d.name AS director,
        STRING_AGG(g.name, ', ') AS genres
    FROM movies m
    JOIN directors d ON m.director_id = d.id
    JOIN movie_genres mg ON m.id = mg.movie_id
    JOIN genres g ON mg.genre_id = g.id
    GROUP BY m.id, d.name
    ORDER BY m.year DESC
");
$movies = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = "MoviePortal - Фильмы";
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="header">
        <div class="logo-container">
            <a href="index.php" class="logo">MoviePortal</a>
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
                <li><a href="index.php">Главная</a></li>
                <li><a href="films.php" class="active">Фильмы</a></li>
                <li><a href="directors.php">Режиссёры</a></li>
            </ul>
        </div>
        <div class="main-content">
            <div class="category-toggle">
                <a href="films.php" class="category-btn active">ФИЛЬМЫ</a>
                <a href="genres.php" class="category-btn">ЖАНРЫ</a>
            </div>
            <div class="movie-grid">
                <?php foreach ($movies as $movie): ?>
                <div class="movie-card">
                    <a href="movie.php?id=<?= $movie['id'] ?>">
                        <img src="<?= htmlspecialchars($movie['poster_url']) ?>" 
                             alt="<?= htmlspecialchars($movie['title']) ?>" 
                             width="125" height="125">
                        <div class="movie-info">
                            <h3><?= htmlspecialchars($movie['title']) ?></h3>
                            <p><?= htmlspecialchars($movie['year']) ?> | <?= htmlspecialchars($movie['duration']) ?> мин</p>
                            <p><?= htmlspecialchars($movie['country']) ?>, <?= htmlspecialchars($movie['genres']) ?></p>
                            <p>Режиссер: <?= htmlspecialchars($movie['director']) ?></p>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <div class="footer">
        <div class="footer-logo">
            <div class="footer-logo-container">
                <a href="index.php" class="logo">MoviePortal</a>
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