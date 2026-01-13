<?php
require_once 'config.php';

// Установка Last-Modified заголовка
header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime(__FILE__)) . ' GMT');

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
    <meta name="description" content="MoviePortal - ваш путеводитель в мире кино. Каталог фильмов, режиссёров и жанров.">
    <meta name="keywords" content="фильмы, кино, режиссёры, жанры, каталог фильмов">
    <title>MoviePortal - Главная страница</title>
    <link rel="icon" type="image/svg+xml" href="static/favicon.svg">
    
    <!-- Preconnect для внешних ресурсов -->
    <link rel="preconnect" href="https://mc.yandex.ru" crossorigin>
    <link rel="preconnect" href="https://avatars.mds.yandex.net" crossorigin>
    <link rel="dns-prefetch" href="https://mc.yandex.ru">
    <link rel="dns-prefetch" href="https://avatars.mds.yandex.net">
    
    <link rel="stylesheet" href="assets/css/styles.css">
    
    <!-- Yandex.Metrika counter - deferred loading -->
    <script type="text/javascript">
        // Отложенная загрузка Яндекс.Метрики для улучшения производительности
        window.addEventListener('load', function() {
            setTimeout(function() {
                (function(m,e,t,r,i,k,a){
                    m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
                    m[i].l=1*new Date();
                    k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.defer=1,k.src=r,a.parentNode.insertBefore(k,a)
                })(window, document,'script','https://mc.yandex.ru/metrika/tag.js?id=106226950', 'ym');
                ym(106226950, 'init', {ssr:true, webvisor:false, clickmap:true, trackLinks:true});
            }, 2000);
        });
    </script>
    <noscript><div><img src="https://mc.yandex.ru/watch/106226950" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
    <!-- /Yandex.Metrika counter -->
    
    <!-- Open Graph -->
    <meta property="og:title" content="MoviePortal - Главная страница">
    <meta property="og:description" content="MoviePortal - ваш путеводитель в мире кино. Каталог фильмов, режиссёров и жанров.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://movieportal-utbt.onrender.com/main.php">
    <meta property="og:image" content="https://movieportal-utbt.onrender.com/assets/images/og-image.jpg">
    <link rel="canonical" href="https://movieportal-utbt.onrender.com/main.php">
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
                <li><a href="main.php" class="active" title="Главная страница">Главная</a></li>
                <li><a href="films.php" title="Каталог всех фильмов">Фильмы</a></li>
                <li><a href="genres.php" title="Просмотр фильмов по жанрам">Жанры</a></li>
                <li><a href="directors.php" title="Список режиссёров">Режиссёры</a></li>
                <li><a href="help.php" title="Справка и помощь">Помощь</a></li>
                <li><a href="admin/index.php" style="color: #ff4444; font-weight: bold;" title="Панель администратора">Админ-панель</a></li>
            </ul>
        </nav>
        <main class="main-content">
            <div class="banner">
                <div class="banner-text">
                    <h2>Онлайн-кинематер</h2>
                </div>
                <div class="banner-image">
                    <img src="https://avatars.mds.yandex.net/i?id=621a460638ec6acddeaae88ce185205b_l-4011414-images-thumbs&n=13" 
                         alt="Баннер MoviePortal" 
                         width="500" 
                         height="200"
                         fetchpriority="high"
                         decoding="async">
                </div>
            </div>
            <div class="movie-grid">
                <?php if (empty($movies)): ?>
                    <p>Фильмы не найдены в базе данных.</p>
                <?php else: ?>
                    <?php foreach ($movies as $movie): ?>
                        <article class="movie-card">
                            <a href="film_page.php?movie_id=<?php echo $movie['id']; ?>" 
                               title="Смотреть информацию о фильме <?php echo htmlspecialchars($movie['title']); ?>">
                                <img src="<?php echo htmlspecialchars($movie['poster_url']); ?>" 
                                     alt="Постер фильма <?php echo htmlspecialchars($movie['title']); ?>" 
                                     width="200" 
                                     height="300"
                                     loading="lazy"
                                     decoding="async"
                                     onerror="this.src='https://via.placeholder.com/200x300?text=Нет+постера'">
                                <p><?php echo htmlspecialchars($movie['title']); ?></p>
                            </a>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>
    <div class="footer">
        <div class="footer-logo">
            <div class="footer-logo-container">
                <a href="main.php" class="logo">MoviePortal</a>
            </div>
        </div>
    </div>
    <script src="assets/js/loader.js"></script>
    <script>
        const menuToggle = document.querySelector('.menu-toggle');
        const nav = document.querySelector('.nav');

        menuToggle.addEventListener('click', () => {
            nav.classList.toggle('active');
            menuToggle.classList.toggle('active');
        });

        // Поиск
        let searchTimeout;
        const searchInput = document.getElementById('globalSearch');
        const searchResults = document.getElementById('searchResults');

        searchInput.addEventListener('input', function(e) {
            clearTimeout(searchTimeout);
            const query = e.target.value.trim();
            
            if (query.length < 2) {
                searchResults.innerHTML = '';
                searchResults.classList.remove('show');
                return;
            }
            
            searchResults.innerHTML = '<div class="search-loading">Поиск...</div>';
            searchResults.classList.add('show');
            
            searchTimeout = setTimeout(() => {
                fetch(`search.php?q=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.results && data.results.length > 0) {
                            searchResults.innerHTML = data.results.map(movie => `
                                <a href="film_page.php?movie_id=${movie.id}" class="search-result-item">
                                    <img src="${movie.poster_url}" 
                                         alt="Постер фильма ${movie.title}" 
                                         class="search-result-poster"
                                         width="50"
                                         height="75"
                                         loading="lazy"
                                         decoding="async"
                                         onerror="this.src='https://via.placeholder.com/50x75?text=No+Image'">
                                    <div class="search-result-info">
                                        <div class="search-result-title">${movie.title}</div>
                                        <div class="search-result-meta">${movie.year} • ${movie.director}</div>
                                    </div>
                                </a>
                            `).join('');
                        } else {
                            searchResults.innerHTML = '<div class="search-no-results">Ничего не найдено</div>';
                        }
                    })
                    .catch(error => {
                        searchResults.innerHTML = '<div class="search-no-results">Ошибка поиска</div>';
                    });
            }, 300);
        });

        // Закрытие результатов при клике вне поиска
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.search-container')) {
                searchResults.classList.remove('show');
            }
        });

        // Мобильный поиск
        const searchToggle = document.getElementById('searchToggle');
        const searchContainer = document.querySelector('.search-container');
        
        if (searchToggle) {
            searchToggle.addEventListener('click', (e) => {
                e.stopPropagation();
                searchContainer.classList.toggle('active');
                searchToggle.classList.toggle('active');
                
                // Фокус на поле поиска при открытии
                if (searchContainer.classList.contains('active')) {
                    setTimeout(() => searchInput.focus(), 100);
                }
            });
            
            // Закрытие поиска при клике вне
            document.addEventListener('click', (e) => {
                if (!e.target.closest('.search-container') && !e.target.closest('.search-toggle')) {
                    searchContainer.classList.remove('active');
                    searchToggle.classList.remove('active');
                }
            });
        }
    </script>
</body>
</html>