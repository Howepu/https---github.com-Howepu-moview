<?php
require_once 'config.php';

// Получение 4 случайных фильмов
$query = "
    SELECT m.id, m.title, m.poster_url
    FROM movies m
    ORDER BY RANDOM()
    LIMIT 4
";
$stmt = $pdo->prepare($query);
$stmt->execute();
$movies = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MoviePortal</title>
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
        </div>
    </div>
    <div class="container">
        <div class="nav">
            <ul>
                <li><a href="main.php" class="active">Главная</a></li>
                <li><a href="films.php">Фильмы</a></li>
                <li><a href="genres.php">Жанры</a></li>
                <li><a href="directors.php">Режиссёры</a></li>
                <li><a href="admin/index.php" style="color: #ff6b6b; font-weight: bold;">Админ-панель</a></li>
            </ul>
        </div>
        <div class="main-content">
            <div class="banner">
                <div class="banner-text">
                    <h2>Онлайн-кинематер</h2>
                </div>
                <div class="banner-image">
                    <img src="https://avatars.mds.yandex.net/i?id=621a460638ec6acddeaae88ce185205b_l-4011414-images-thumbs&n=13" height="200" width="500">
                </div>
            </div>
            <div class="movie-grid">
                <?php if (empty($movies)): ?>
                    <p>Фильмы не найдены в базе данных.</p>
                <?php else: ?>
                    <?php foreach ($movies as $movie): ?>
                        <div class="movie-card">
                            <a href="film_page.php?movie_id=<?php echo $movie['id']; ?>">
                                <img src="<?php echo htmlspecialchars($movie['poster_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($movie['title']); ?>" 
                                     height="150" width="200"
                                     onerror="this.src='https://via.placeholder.com/200x300'">
                                <p><?php echo htmlspecialchars($movie['title']); ?></p>
                            </a>
                        </div>
                    <?php endforeach; ?>
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