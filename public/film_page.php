<?php
require_once 'config.php';

// Получаем ID фильма из GET-параметра
$movie_id = isset($_GET['movie_id']) ? (int)$_GET['movie_id'] : null;

if (!$movie_id) {
    $movie = null;
    $actors = [];
} else {
    // Получение данных о фильме
    $query = "
        SELECT m.title, m.year, m.duration, m.country, m.poster_url, m.description, 
               d.name as director_name
        FROM movies m
        JOIN directors d ON m.director_id = d.id
        WHERE m.id = :movie_id
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['movie_id' => $movie_id]);
    $movie = $stmt->fetch(PDO::FETCH_ASSOC);

    // Получение списка актёров
    $actors_query = "
        SELECT a.name
        FROM actors a
        JOIN movie_actors ma ON a.id = ma.actor_id
        WHERE ma.movie_id = :movie_id
    ";
    $actors_stmt = $pdo->prepare($actors_query);
    $actors_stmt->execute(['movie_id' => $movie_id]);
    $actors = $actors_stmt->fetchAll(PDO::FETCH_COLUMN);
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MoviePortal - <?php echo $movie ? htmlspecialchars($movie['title'] . " (" . $movie['year'] . ")") : "Фильм не найден"; ?></title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="header">
        <div class="logo-container">
            <a href="main.php" class="logo">MoviePortal</a>
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
                <li><a href="main.php">Главная</a></li>
                <li><a href="films.php">Фильмы</a></li>
                <li><a href="directors.php">Режиссёры</a></li>
            </ul>
        </div>
        <div class="main-content">
            <div class="movie-page">
                <?php if (!$movie): ?>
                    <h1>Фильм не найден</h1>
                    <p>Укажите movie_id в URL, например: film.php?movie_id=1</p>
                <?php else: ?>
                    <h1><?php echo htmlspecialchars($movie['title'] . " (" . $movie['year'] . ")"); ?></h1>
                    <div class="movie-info">
                        <div class="movie-poster">
                            <img src="<?php echo htmlspecialchars($movie['poster_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($movie['title']); ?>" 
                                 width="300" height="450"
                                 onerror="this.src='https://via.placeholder.com/300x450'">
                        </div>
                        <div class="movie-details">
                            <p><strong>Год производства:</strong> <?php echo $movie['year']; ?></p>
                            <p><strong>Страна:</strong> <?php echo htmlspecialchars($movie['country']); ?></p>
                            <p><strong>Режиссер:</strong> <?php echo htmlspecialchars($movie['director_name']); ?></p>
                            <p><strong>Сценарий:</strong> <?php echo htmlspecialchars($movie['director_name']); // Предполагаем, что режиссёр также сценарист ?></p>
                            <p><strong>Композитор:</strong> Дэвид А. Хьюз, Джон Мерфи</p> <!-- Статические данные, так как нет в базе -->
                            <p><strong>Продолжительность:</strong> <?php echo $movie['duration']; ?> мин</p>
                        </div>
                    </div>
                    <div class="movie-description">
                        <h2>О фильме</h2>
                        <p><?php echo htmlspecialchars($movie['description'] ?: "Описание отсутствует"); ?></p>
                    </div>
                    <div class="movie-cast">
                        <h2>В главных ролях:</h2>
                        <?php if (empty($actors)): ?>
                            <p>Актёры не указаны</p>
                        <?php else: ?>
                            <ul>
                                <?php foreach ($actors as $actor): ?>
                                    <li><?php echo htmlspecialchars($actor); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="footer">
        <div class="footer-logo">
            <div class="footer-logo-container">
                <a href="main.php" class="logo">MoviePortal</a>
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