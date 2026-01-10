<?php
require_once 'config.php';

// Получаем список режиссеров из базы данных
$stmt = $pdo->query("SELECT * FROM directors ORDER BY name");
$directors = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = "MoviePortal - Режиссеры";
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Список режиссеров - изучайте фильмографию известных кинорежиссеров.">
    <meta name="keywords" content="режиссеры, кинорежиссеры, фильмография">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <link rel="stylesheet" href="styles.css">
    
    <!-- Open Graph -->
    <meta property="og:title" content="<?= htmlspecialchars($pageTitle) ?>">
    <meta property="og:description" content="Список режиссеров - изучайте фильмографию известных кинорежиссеров.">
    <meta property="og:type" content="website">
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
                <li><a href="directors.php" class="active">Режиссёры</a></li>
                <li><a href="help.php">Помощь</a></li>
                <li><a href="admin/index.php" style="color: #ff6b6b; font-weight: bold;">Админ-панель</a></li>
            </ul>
        </div>
        <div class="main-content">
            <h1>Популярные режиссеры</h1>
            <div class="results-count-simple">Всего режиссеров: <strong><?= count($directors) ?></strong></div>
            <div class="genre-banners">
                <?php foreach ($directors as $director): ?>
                <a href="films_by_directors.php?director_id=<?= $director['id'] ?>" class="genre-card">
                    <img src="<?= htmlspecialchars($director['photo_url']) ?>" 
                         alt="<?= htmlspecialchars($director['name']) ?>"
                         width="150" height="150"
                         onerror="this.src='https://via.placeholder.com/150x150?text=<?= urlencode(substr($director['name'], 0, 1)) ?>'">
                    <p><?= htmlspecialchars($director['name']) ?></p>
                </a>
                <?php endforeach; ?>
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