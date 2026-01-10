<?php
require_once 'config.php';

// Проверяем, передан ли параметр жанра
$genre_id = isset($_GET['genre_id']) ? (int)$_GET['genre_id'] : null;
$selected_genre_name = '';

// Параметры сортировки
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'year';
$order = isset($_GET['order']) ? $_GET['order'] : 'desc';

// Валидация параметров сортировки
$allowed_sorts = ['title', 'year'];
$allowed_orders = ['asc', 'desc'];

if (!in_array($sort, $allowed_sorts)) {
    $sort = 'year';
}
if (!in_array($order, $allowed_orders)) {
    $order = 'desc';
}

// Формируем ORDER BY для SQL
$order_column = match($sort) {
    'title' => 'm.title',
    default => 'm.year'
};
$order_direction = strtoupper($order);
$order_clause = "ORDER BY {$order_column} {$order_direction}";

// Если передан genre_id, получаем название жанра
if ($genre_id) {
    $genre_stmt = $pdo->prepare("SELECT name FROM genres WHERE id = ?");
    $genre_stmt->execute([$genre_id]);
    $genre_result = $genre_stmt->fetch();
    $selected_genre_name = $genre_result ? $genre_result['name'] : '';
}

// Получаем список фильмов с информацией о режиссерах и жанрах
if ($genre_id) {
    // Фильтруем по конкретному жанру
    $stmt = $pdo->prepare("
        SELECT 
            m.id,
            m.title,
            m.year,
            m.duration,
            m.country,
            m.poster_url,
            m.rating,
            d.name AS director,
            STRING_AGG(g.name, ', ') AS genres
        FROM movies m
        JOIN directors d ON m.director_id = d.id
        JOIN movie_genres mg ON m.id = mg.movie_id
        JOIN genres g ON mg.genre_id = g.id
        WHERE m.id IN (
            SELECT DISTINCT mg2.movie_id 
            FROM movie_genres mg2 
            WHERE mg2.genre_id = ?
        )
        GROUP BY m.id, m.title, m.year, m.duration, m.country, m.poster_url, m.rating, d.name
        {$order_clause}
    ");
    $stmt->execute([$genre_id]);
} else {
    // Показываем все фильмы
    $stmt = $pdo->query("
        SELECT 
            m.id,
            m.title,
            m.year,
            m.duration,
            m.country,
            m.poster_url,
            m.rating,
            d.name AS director,
            STRING_AGG(g.name, ', ') AS genres
        FROM movies m
        JOIN directors d ON m.director_id = d.id
        JOIN movie_genres mg ON m.id = mg.movie_id
        JOIN genres g ON mg.genre_id = g.id
        GROUP BY m.id, m.title, m.year, m.duration, m.country, m.poster_url, m.rating, d.name
        {$order_clause}
    ");
}
$movies = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Функция для формирования URL с сохранением параметров
function buildSortUrl($newSort, $newOrder, $genre_id) {
    $params = ['sort' => $newSort, 'order' => $newOrder];
    if ($genre_id) {
        $params['genre_id'] = $genre_id;
    }
    return 'films.php?' . http_build_query($params);
}

// Формируем заголовок страницы
if ($genre_id && $selected_genre_name) {
    $pageTitle = "MoviePortal - Фильмы жанра: " . $selected_genre_name;
} else {
    $pageTitle = "MoviePortal - Все фильмы";
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Каталог фильмов - смотрите полный список фильмов с рейтингами, жанрами и информацией о режиссерах.">
    <meta name="keywords" content="каталог фильмов, список фильмов, рейтинг фильмов, кино">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <link rel="stylesheet" href="styles.css">
    
    <!-- Open Graph -->
    <meta property="og:title" content="<?= htmlspecialchars($pageTitle) ?>">
    <meta property="og:description" content="Каталог фильмов - смотрите полный список фильмов с рейтингами и жанрами.">
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
                <li><a href="films.php" class="active">Фильмы</a></li>
                <li><a href="genres.php">Жанры</a></li>
                <li><a href="directors.php">Режиссёры</a></li>
                <li><a href="help.php">Помощь</a></li>
                <li><a href="admin/index.php" style="color: #ff6b6b; font-weight: bold;">Админ-панель</a></li>
            </ul>
        </div>
        <div class="main-content">
            <div class="category-toggle">
                <a href="films.php" class="category-btn active">ФИЛЬМЫ</a>
                <a href="genres.php" class="category-btn">ЖАНРЫ</a>
            </div>
            
            <div class="films-toolbar">
                <?php if ($genre_id && $selected_genre_name): ?>
                    <div class="filter-info">
                        <span class="filter-label">Жанр: <strong><?= htmlspecialchars($selected_genre_name) ?></strong></span>
                        <span class="results-count">Найдено фильмов: <strong><?= count($movies) ?></strong></span>
                        <a href="films.php" class="btn-clear-filter">✕ Сбросить фильтр</a>
                    </div>
                <?php else: ?>
                    <div class="results-count-simple">Всего фильмов: <strong><?= count($movies) ?></strong></div>
                <?php endif; ?>
                
                <div class="sort-controls">
                    <span class="sort-label">Сортировка:</span>
                    <div class="sort-buttons">
                        <a href="<?= buildSortUrl('title', $sort === 'title' && $order === 'asc' ? 'desc' : 'asc', $genre_id) ?>" 
                           class="sort-btn <?= $sort === 'title' ? 'active' : '' ?>">
                            Название
                            <?php if ($sort === 'title'): ?>
                                <span class="sort-arrow"><?= $order === 'asc' ? '↑' : '↓' ?></span>
                            <?php endif; ?>
                        </a>
                        <a href="<?= buildSortUrl('year', $sort === 'year' && $order === 'desc' ? 'asc' : 'desc', $genre_id) ?>" 
                           class="sort-btn <?= $sort === 'year' ? 'active' : '' ?>">
                            Год
                            <?php if ($sort === 'year'): ?>
                                <span class="sort-arrow"><?= $order === 'asc' ? '↑' : '↓' ?></span>
                            <?php endif; ?>
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="movie-grid">
                <?php if (empty($movies)): ?>
                    <div class="no-movies-found">
                        <div class="no-movies-icon">🎬</div>
                        <h3>Фильмы не найдены</h3>
                        <?php if ($genre_id && $selected_genre_name): ?>
                            <p>В жанре "<?= htmlspecialchars($selected_genre_name) ?>" пока нет фильмов.</p>
                            <a href="films.php" class="btn-show-all">Посмотреть все фильмы</a>
                        <?php else: ?>
                            <p>В базе данных пока нет фильмов.</p>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <?php foreach ($movies as $movie): ?>
                    <div class="movie-card">
                        <a href="film_page.php?movie_id=<?= $movie['id'] ?>">
                            <?php if ($movie['rating']): ?>
                                <div class="movie-rating-badge">★ <?= number_format($movie['rating'], 1) ?></div>
                            <?php endif; ?>
                            <img src="<?= htmlspecialchars($movie['poster_url']) ?>" 
                                 alt="<?= htmlspecialchars($movie['title']) ?>" 
                                 width="120" height="180">
                            <div class="movie-info">
                                <h3><?= htmlspecialchars($movie['title']) ?></h3>
                                <p><?= htmlspecialchars($movie['year']) ?> | <?= htmlspecialchars($movie['duration']) ?> мин</p>
                                <p><?= htmlspecialchars($movie['country']) ?>, <?= htmlspecialchars($movie['genres']) ?></p>
                                <p>Режиссер: <?= htmlspecialchars($movie['director']) ?></p>
                            </div>
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