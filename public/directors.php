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
    <title><?= htmlspecialchars($pageTitle) ?></title>
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
                <li><a href="main.php">Главная</a></li>
                <li><a href="films.php">Фильмы</a></li>
                <li><a href="genres.php">Жанры</a></li>
                <li><a href="directors.php" class="active">Режиссёры</a></li>
                <li><a href="admin/index.php" style="color: #ff6b6b; font-weight: bold;">Админ-панель</a></li>
            </ul>
        </div>
        <div class="main-content">
            <h1>Популярные режиссеры</h1>
            <div class="genre-banners">
                <?php foreach ($directors as $director): ?>
                <a href="films_by_directors.php?director_id=<?= $director['id'] ?>" class="genre-card">
                    <img src="<?= htmlspecialchars($director['photo_url']) ?>" 
                         alt="<?= htmlspecialchars($director['name']) ?>"
                         onerror="this.src='https://via.placeholder.com/111x111?text=<?= urlencode(substr($director['name'], 0, 1)) ?>'">
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