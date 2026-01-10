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
        <div class="search-container">
            <span class="search-icon">🔍</span>
            <input type="search" id="globalSearch" placeholder="Поиск фильмов, режиссеров..." autocomplete="off">
            <div id="searchResults" class="search-results"></div>
        </div>
        <button class="search-toggle" id="searchToggle">🔍</button>
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
                <li><a href="genres.php">Жанры</a></li>
                <li><a href="directors.php">Режиссёры</a></li>
                <li><a href="help.php">Помощь</a></li>
                <li><a href="admin/index.php" style="color: #ff6b6b; font-weight: bold;">Админ-панель</a></li>
            </ul>
        </div>
        <div class="main-content">
            <?php if (!$movie): ?>
                <div class="error-page">
                    <div class="error-icon">🎬</div>
                    <h2>Фильм не найден</h2>
                    <p>К сожалению, запрашиваемый фильм не найден в нашей базе данных.</p>
                    <div class="error-actions">
                        <a href="films.php" class="btn btn-primary">Посмотреть все фильмы</a>
                        <a href="main.php" class="btn btn-secondary">На главную</a>
                    </div>
                </div>
            <?php else: ?>
                <nav class="breadcrumbs" aria-label="Навигация">
                    <ol style="list-style: none; padding-left: 0;">
                        <li><a href="main.php">Главная</a></li>
                        <li><a href="films.php">Фильмы</a></li>
                        <li aria-current="page"><?= htmlspecialchars($movie['title']) ?></li>
                    </ol>
                </nav>
                
                <a href="javascript:history.back()" class="btn-back">← Назад к списку</a>
                
                <div class="movie-page">
                    <h1><?php echo htmlspecialchars($movie['title'] . " (" . $movie['year'] . ")"); ?></h1>
                    <div class="movie-info">
                        <div class="movie-poster">
                            <img src="<?php echo htmlspecialchars($movie['poster_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($movie['title']); ?>" 
                                 width="300" height="450"
                                 onerror="this.src='https://via.placeholder.com/300x450?text=Нет+постера'">
                        </div>
                        <div class="movie-details">
                            <div class="detail-item">
                                <span class="detail-icon">📅</span>
                                <span class="detail-label">Год производства:</span>
                                <span class="detail-value"><?php echo $movie['year']; ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-icon">🌍</span>
                                <span class="detail-label">Страна:</span>
                                <span class="detail-value"><?php echo htmlspecialchars($movie['country']); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-icon">🎬</span>
                                <span class="detail-label">Режиссер:</span>
                                <span class="detail-value"><?php echo htmlspecialchars($movie['director_name']); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-icon">⏱️</span>
                                <span class="detail-label">Продолжительность:</span>
                                <span class="detail-value"><?php echo $movie['duration']; ?> мин</span>
                            </div>
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
                </div>
            <?php endif; ?>
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
    <script src="search.js"></script>
    <script src="loader.js"></script>
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