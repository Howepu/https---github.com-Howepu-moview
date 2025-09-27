<?php
require_once 'config.php';

// Получаем ID режиссёра из GET-параметра, если не указан - показываем сообщение
$director_id = isset($_GET['director_id']) ? (int)$_GET['director_id'] : null;

if (!$director_id) {
    $movies = [];
    $director_name = "Не указан режиссёр";
} else {
    // Получение имени режиссёра
    $director_query = "SELECT name FROM directors WHERE id = :director_id";
    $director_stmt = $pdo->prepare($director_query);
    $director_stmt->execute(['director_id' => $director_id]);
    $director = $director_stmt->fetch(PDO::FETCH_ASSOC);
    $director_name = $director ? $director['name'] : "Режиссёр не найден";

    // Получение списка фильмов конкретного режиссёра
    $query = "
        SELECT m.title, m.year, m.duration, m.country, m.poster_url, d.name as director_name
        FROM movies m
        JOIN directors d ON m.director_id = d.id
        WHERE m.director_id = :director_id
        ORDER BY m.year DESC
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['director_id' => $director_id]);
    $movies = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MoviePortal - Фильмы режиссёра <?php echo htmlspecialchars($director_name); ?></title>
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
                <li><a href="films.php">Фильмы</a></li>
                <li><a href="directors.html">Режиссёры</a></li>
            </ul>
        </div>
        <div class="main-content">
            <h2>Фильмы режиссёра: <?php echo htmlspecialchars($director_name); ?></h2>
            <?php if (empty($movies)): ?>
                <p>Фильмы не найдены или режиссёр не указан. Укажите director_id в URL, например: films_by_directors.php?director_id=1</p>
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
                                <p><?php echo htmlspecialchars($movie['country']); ?></p>
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