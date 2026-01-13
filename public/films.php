<?php
require_once 'config.php';

// Установка Last-Modified заголовка
header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime(__FILE__)) . ' GMT');

// Проверяем, передан ли параметр жанра
$genre_id = isset($_GET['genre_id']) ? (int)$_GET['genre_id'] : null;
$selected_genre_name = '';

// Параметры пагинации
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 5;
$offset = ($page - 1) * $per_page;

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

// Подсчёт общего количества фильмов для пагинации
if ($genre_id) {
    $count_stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT m.id) as total
        FROM movies m
        JOIN movie_genres mg ON m.id = mg.movie_id
        WHERE mg.genre_id = ?
    ");
    $count_stmt->execute([$genre_id]);
} else {
    $count_stmt = $pdo->query("SELECT COUNT(*) as total FROM movies");
}
$total_movies = $count_stmt->fetch()['total'];
$total_pages = ceil($total_movies / $per_page);

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
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$genre_id, $per_page, $offset]);
} else {
    // Показываем все фильмы с пагинацией
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
        GROUP BY m.id, m.title, m.year, m.duration, m.country, m.poster_url, m.rating, d.name
        {$order_clause}
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$per_page, $offset]);
}
$movies = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Функция для формирования URL с сохранением параметров
function buildSortUrl($newSort, $newOrder, $genre_id, $page) {
    $params = ['sort' => $newSort, 'order' => $newOrder];
    if ($genre_id) {
        $params['genre_id'] = $genre_id;
    }
    if ($page > 1) {
        $params['page'] = $page;
    }
    return 'films.php?' . http_build_query($params);
}

// Функция для формирования URL пагинации
function buildPageUrl($newPage, $sort, $order, $genre_id) {
    $params = [];
    if ($newPage > 1) {
        $params['page'] = $newPage;
    }
    if ($sort !== 'year') {
        $params['sort'] = $sort;
    }
    if ($order !== 'desc') {
        $params['order'] = $order;
    }
    if ($genre_id) {
        $params['genre_id'] = $genre_id;
    }
    return 'films.php' . ($params ? '?' . http_build_query($params) : '');
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
    <!-- Preconnect для внешних ресурсов -->
    <link rel="preconnect" href="https://mc.yandex.ru" crossorigin>
    <link rel="dns-prefetch" href="https://mc.yandex.ru">
    <link rel="icon" type="image/svg+xml" href="static/favicon.svg">
    <link rel="stylesheet" href="assets/css/styles.css">
    
    <!-- Yandex.Metrika counter -->
    <script type="text/javascript">
        (function(m,e,t,r,i,k,a){
            m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
            m[i].l=1*new Date();
            for (var j = 0; j < document.scripts.length; j++) {if (document.scripts[j].src === r) { return; }}
            k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)
        })(window, document,'script','https://mc.yandex.ru/metrika/tag.js?id=106226950', 'ym');

        ym(106226950, 'init', {ssr:true, webvisor:true, clickmap:true, ecommerce:"dataLayer", accurateTrackBounce:true, trackLinks:true});
    </script>
    <noscript><div><img src="https://mc.yandex.ru/watch/106226950" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
    <!-- /Yandex.Metrika counter -->
    
    <!-- Open Graph -->
    <meta property="og:title" content="<?= htmlspecialchars($pageTitle) ?>">
    <meta property="og:description" content="Каталог фильмов - смотрите полный список фильмов с рейтингами и жанрами.">
    <meta property="og:type" content="website">
</head>
<body>
    <div class="header">
        <div class="logo-container">
            <a href="main.php" class="logo" title="Вернуться на главную страницу">MoviePortal</a>
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
        <nav class="nav" aria-label="Основная навигация">
            <ul>
                <li><a href="main.php" title="Главная страница">Главная</a></li>
                <li><a href="films.php" class="active" title="Каталог всех фильмов">Фильмы</a></li>
                <li><a href="genres.php" title="Просмотр фильмов по жанрам">Жанры</a></li>
                <li><a href="directors.php" title="Список режиссёров">Режиссёры</a></li>
                <li><a href="help.php" title="Справка и помощь">Помощь</a></li>
                <li><a href="admin/index.php" style="color: #ff4444; font-weight: bold;" title="Панель администратора">Админ-панель</a></li>
            </ul>
        </nav>
        <main class="main-content">
            <div class="category-toggle">
                <a href="films.php" class="category-btn active">ФИЛЬМЫ</a>
                <a href="genres.php" class="category-btn">ЖАНРЫ</a>
            </div>
            
            <div class="films-toolbar">
                <?php if ($genre_id && $selected_genre_name): ?>
                    <div class="filter-info">
                        <span class="filter-label">Жанр: <strong><?= htmlspecialchars($selected_genre_name) ?></strong></span>
                        <span class="results-count">Найдено фильмов: <strong><?= $total_movies ?></strong></span>
                        <a href="films.php" class="btn-clear-filter">✕ Сбросить фильтр</a>
                    </div>
                <?php else: ?>
                    <div class="results-count-simple">Всего фильмов: <strong><?= $total_movies ?></strong></div>
                <?php endif; ?>
                
                <div class="sort-controls">
                    <span class="sort-label">Сортировка:</span>
                    <div class="sort-buttons">
                        <a href="<?= buildSortUrl('title', $sort === 'title' && $order === 'asc' ? 'desc' : 'asc', $genre_id, $page) ?>" 
                           class="sort-btn <?= $sort === 'title' ? 'active' : '' ?>">
                            Название
                            <?php if ($sort === 'title'): ?>
                                <span class="sort-arrow"><?= $order === 'asc' ? '↑' : '↓' ?></span>
                            <?php endif; ?>
                        </a>
                        <a href="<?= buildSortUrl('year', $sort === 'year' && $order === 'desc' ? 'asc' : 'desc', $genre_id, $page) ?>" 
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
                    <article class="movie-card">
                        <a href="film_page.php?movie_id=<?= $movie['id'] ?>" 
                           title="Смотреть информацию о фильме <?= htmlspecialchars($movie['title']) ?>">
                            <?php if ($movie['rating']): ?>
                                <div class="movie-rating-badge">★ <?= number_format($movie['rating'], 1) ?></div>
                            <?php endif; ?>
                            <img src="<?= htmlspecialchars($movie['poster_url']) ?>" 
                                 alt="Постер фильма <?= htmlspecialchars($movie['title']) ?>" 
                                 width="200" 
                                 height="300"
                                 loading="lazy"
                                 decoding="async"
                            <div class="movie-info">
                                <h3><?= htmlspecialchars($movie['title']) ?></h3>
                                <p><?= htmlspecialchars($movie['year']) ?> | <?= htmlspecialchars($movie['duration']) ?> мин</p>
                                <p><?= htmlspecialchars($movie['country']) ?>, <?= htmlspecialchars($movie['genres']) ?></p>
                                <p>Режиссер: <?= htmlspecialchars($movie['director']) ?></p>
                            </div>
                        </a>
                    </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <div class="pagination-info">
                    <span class="pagination-stats">
                        <strong>Страница <?= $page ?></strong> из <strong><?= $total_pages ?></strong>
                    </span>
                    <span class="pagination-separator">•</span>
                    <span class="pagination-count">
                        Показано <strong><?= count($movies) ?></strong> из <strong><?= $total_movies ?></strong> фильмов
                    </span>
                </div>
                <div class="pagination-controls">
                    <?php if ($page > 1): ?>
                        <a href="<?= buildPageUrl(1, $sort, $order, $genre_id) ?>" 
                           class="pagination-btn pagination-nav" title="Первая страница">
                            <span>⟨⟨</span>
                        </a>
                        <a href="<?= buildPageUrl($page - 1, $sort, $order, $genre_id) ?>" 
                           class="pagination-btn pagination-nav" title="Предыдущая">
                            <span>⟨</span>
                        </a>
                    <?php else: ?>
                        <span class="pagination-btn pagination-nav disabled">⟨⟨</span>
                        <span class="pagination-btn pagination-nav disabled">⟨</span>
                    <?php endif; ?>
                    
                    <?php
                    // Показываем до 5 страниц вокруг текущей
                    $start = max(1, $page - 2);
                    $end = min($total_pages, $page + 2);
                    
                    if ($start > 1): ?>
                        <a href="<?= buildPageUrl(1, $sort, $order, $genre_id) ?>" 
                           class="pagination-btn">1</a>
                        <?php if ($start > 2): ?>
                            <span class="pagination-dots">⋯</span>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <?php for ($i = $start; $i <= $end; $i++): ?>
                        <?php if ($i == $page): ?>
                            <span class="pagination-btn active"><?= $i ?></span>
                        <?php else: ?>
                            <a href="<?= buildPageUrl($i, $sort, $order, $genre_id) ?>" 
                               class="pagination-btn"><?= $i ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($end < $total_pages): ?>
                        <?php if ($end < $total_pages - 1): ?>
                            <span class="pagination-dots">⋯</span>
                        <?php endif; ?>
                        <a href="<?= buildPageUrl($total_pages, $sort, $order, $genre_id) ?>" 
                           class="pagination-btn"><?= $total_pages ?></a>
                    <?php endif; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="<?= buildPageUrl($page + 1, $sort, $order, $genre_id) ?>" 
                           class="pagination-btn pagination-nav" title="Следующая">
                            <span>⟩</span>
                        </a>
                        <a href="<?= buildPageUrl($total_pages, $sort, $order, $genre_id) ?>" 
                           class="pagination-btn pagination-nav" title="Последняя страница">
                            <span>⟩⟩</span>
                        </a>
                    <?php else: ?>
                        <span class="pagination-btn pagination-nav disabled">⟩</span>
                        <span class="pagination-btn pagination-nav disabled">⟩⟩</span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </main>
    </div>
    <div class="footer">
        <div class="footer-logo">
            <div class="footer-logo-container">
                <a href="main.php" class="logo">MoviePortal</a>
            </div>
        </div>
    </div>
    <script src="assets/js/search.js"></script>
    <script src="assets/js/loader.js"></script>
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