<?php
require_once 'config.php';

// Установка Last-Modified заголовка
header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime(__FILE__)) . ' GMT');

// Получаем ID фильма из GET-параметра (поддержка и id и movie_id для обратной совместимости)
$movie_id = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_GET['movie_id']) ? (int)$_GET['movie_id'] : null);

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
    <meta name="description" content="<?php echo $movie ? htmlspecialchars($movie['title'] . ' (' . $movie['year'] . ') - ' . ($movie['description'] ?? 'Информация о фильме')) : 'Фильм не найден'; ?>">
    <title>MoviePortal - <?php echo $movie ? htmlspecialchars($movie['title'] . " (" . $movie['year'] . ")") : "Фильм не найден"; ?></title>
    
    <!-- Preconnect для внешних ресурсов -->
    <link rel="preconnect" href="https://mc.yandex.ru" crossorigin>
    <link rel="dns-prefetch" href="https://mc.yandex.ru">
    
    <link rel="stylesheet" href="/assets/css/styles.css">
    <link rel="stylesheet" href="/assets/css/film_page_style.css">
    
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
    
    <?php if ($movie): ?>
    <!-- Schema.org микроразметка для Google Rich Snippets -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Movie",
        "name": "<?= htmlspecialchars($movie['title'], ENT_QUOTES) ?>",
        "datePublished": "<?= $movie['year'] ?>",
        "duration": "PT<?= $movie['duration'] ?>M",
        "director": {
            "@type": "Person",
            "name": "<?= htmlspecialchars($movie['director_name'], ENT_QUOTES) ?>"
        },
        "countryOfOrigin": {
            "@type": "Country",
            "name": "<?= htmlspecialchars($movie['country'], ENT_QUOTES) ?>"
        },
        "description": "<?= htmlspecialchars($movie['description'] ?? 'Информация о фильме', ENT_QUOTES) ?>",
        "image": "<?= htmlspecialchars($movie['poster_url'], ENT_QUOTES) ?>"<?php if (!empty($actors)): ?>,
        "actor": [
            <?php foreach ($actors as $index => $actor): ?>
            {
                "@type": "Person",
                "name": "<?= htmlspecialchars($actor, ENT_QUOTES) ?>"
            }<?= $index < count($actors) - 1 ? ',' : '' ?>
            <?php endforeach; ?>
        ]
        <?php endif; ?>
    }
    </script>
    
    <!-- Open Graph для соцсетей -->
    <meta property="og:type" content="video.movie">
    <meta property="og:title" content="<?= htmlspecialchars($movie['title']) ?> (<?= $movie['year'] ?>)">
    <meta property="og:description" content="Режиссёр: <?= htmlspecialchars($movie['director_name']) ?>. <?= htmlspecialchars($movie['description'] ?? '') ?>">
    <meta property="og:image" content="<?= htmlspecialchars($movie['poster_url']) ?>">
    <meta property="og:url" content="https://movieportal-utbt.onrender.com<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>">
    <link rel="canonical" href="https://movieportal-utbt.onrender.com<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>">
    <?php endif; ?>
</head>
<body>
    <div class="nav-overlay" id="navOverlay"></div>
    <div class="header">
        <div class="logo-container">
            <a href="/main.php" class="logo" title="Вернуться на главную страницу">MoviePortal</a>
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
                <li><a href="/main.php" title="Главная страница">Главная</a></li>
                <li><a href="/films.php" title="Каталог всех фильмов">Фильмы</a></li>
                <li><a href="/genres.php" title="Просмотр фильмов по жанрам">Жанры</a></li>
                <li><a href="/directors.php" title="Список режиссёров">Режиссёры</a></li>
                <li><a href="/help.php" title="Справка и помощь">Помощь</a></li>
                <li><a href="/admin/index.php" style="color: #ff4444; font-weight: bold;" title="Панель администратора">Админ-панель</a></li>
            </ul>
        </nav>
        <main class="main-content">
            <?php if (!$movie): ?>
                <div class="error-page">
                    <div class="error-icon">🎬</div>
                    <h2>Фильм не найден</h2>
                    <p>К сожалению, запрашиваемый фильм не найден в нашей базе данных.</p>
                    <div class="error-actions">
                        <a href="/films.php" class="btn btn-primary">Посмотреть все фильмы</a>
                        <a href="/main.php" class="btn btn-secondary">На главную</a>
                    </div>
                </div>
            <?php else: ?>
                <nav class="breadcrumbs" aria-label="Навигация">
                    <ol style="list-style: none; padding-left: 0;">
                        <li><a href="/main.php" title="Главная страница">Главная</a></li>
                        <li><a href="/films.php" title="Каталог фильмов">Фильмы</a></li>
                        <li aria-current="page"><?= htmlspecialchars($movie['title']) ?></li>
                    </ol>
                </nav>
                
                <a href="javascript:history.back()" class="btn-back">← Назад к списку</a>
                
                <div class="movie-page">
                    <h1><?php echo htmlspecialchars($movie['title'] . " (" . $movie['year'] . ")"); ?></h1>
                    <div class="movie-info">
                        <div class="movie-poster">
                            <img src="<?php echo htmlspecialchars($movie['poster_url']); ?>" 
                                 alt="Постер фильма <?php echo htmlspecialchars($movie['title']); ?>" 
                                 width="300" 
                                 height="450"
                                 fetchpriority="high"
                                 decoding="async"
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
        </main>
    </div>
    <div class="footer">
        <div class="footer-logo">
            <div class="footer-logo-container">
                <a href="main.php" class="logo">MoviePortal</a>
            </div>
        </div>
    </div>
    <script src="/assets/js/search.js"></script>
    <script src="/assets/js/loader.js"></script>
    <script>
        const menuToggle = document.querySelector('.menu-toggle');
        const nav = document.querySelector('.nav');
        const navOverlay = document.getElementById('navOverlay');

        // Открытие/закрытие меню
        menuToggle.addEventListener('click', () => {
            nav.classList.toggle('active');
            menuToggle.classList.toggle('active');
            navOverlay.classList.toggle('active');
        });

        // Закрытие меню при клике на overlay
        navOverlay.addEventListener('click', () => {
            nav.classList.remove('active');
            menuToggle.classList.remove('active');
            navOverlay.classList.remove('active');
        });
    </script>
</body>
</html>