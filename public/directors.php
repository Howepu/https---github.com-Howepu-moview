<?php
require_once 'config.php';

// Установка Last-Modified заголовка
header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime(__FILE__)) . ' GMT');

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
    <!-- Preconnect для внешних ресурсов -->
    <link rel="preconnect" href="https://mc.yandex.ru" crossorigin>
    <link rel="dns-prefetch" href="https://mc.yandex.ru">
    <link rel="icon" type="image/svg+xml" href="static/favicon.svg">
    <link rel="stylesheet" href="/assets/css/styles.css">
    
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
    <meta property="og:description" content="Список режиссеров - изучайте фильмографию известных кинорежиссеров.">
    <meta property="og:type" content="website">
</head>
<body>
    <div class="nav-overlay" id="navOverlay"></div>
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
                <li><a href="films.php" title="Каталог всех фильмов">Фильмы</a></li>
                <li><a href="genres.php" title="Просмотр фильмов по жанрам">Жанры</a></li>
                <li><a href="directors.php" class="active" title="Список режиссёров">Режиссёры</a></li>
                <li><a href="help.php" title="Справка и помощь">Помощь</a></li>
                <li><a href="admin/index.php" style="color: #ff4444; font-weight: bold;" title="Панель администратора">Админ-панель</a></li>
            </ul>
        </nav>
        <main class="main-content">
            <h1>Популярные режиссеры</h1>
            <div class="results-count-simple">Всего режиссеров: <strong><?= count($directors) ?></strong></div>
            <div class="genre-banners">
                <?php foreach ($directors as $director): ?>
                <a href="/director/<?= $director['id'] ?>.html" class="genre-card"
                   title="Смотреть фильмы режиссёра <?= htmlspecialchars($director['name']) ?>">
                    <img src="<?= htmlspecialchars($director['photo_url']) ?>" 
                         alt="<?= htmlspecialchars($director['name']) ?>"
                         width="150" height="150"
                         onerror="this.src='https://via.placeholder.com/150x150?text=<?= urlencode(substr($director['name'], 0, 1)) ?>'">
                    <p><?= htmlspecialchars($director['name']) ?></p>
                </a>
                <?php endforeach; ?>
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