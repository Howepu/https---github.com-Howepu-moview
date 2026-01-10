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
    <meta name="description" content="MoviePortal - ваш путеводитель в мире кино. Каталог фильмов, режиссёров и жанров.">
    <meta name="keywords" content="фильмы, кино, режиссёры, жанры, каталог фильмов">
    <title>MoviePortal - Главная страница</title>
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <link rel="stylesheet" href="styles.css">
    
    <!-- Open Graph -->
    <meta property="og:title" content="MoviePortal - Главная страница">
    <meta property="og:description" content="MoviePortal - ваш путеводитель в мире кино. Каталог фильмов, режиссёров и жанров.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="http://localhost/main.php">
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
                <li><a href="main.php" class="active">Главная</a></li>
                <li><a href="films.php">Фильмы</a></li>
                <li><a href="genres.php">Жанры</a></li>
                <li><a href="directors.php">Режиссёры</a></li>
                <li><a href="help.php">Помощь</a></li>
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
                                     width="200" height="300"
                                     onerror="this.src='https://via.placeholder.com/200x300?text=Нет+постера'">
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
    <script src="loader.js"></script>
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
                                         alt="${movie.title}" 
                                         class="search-result-poster"
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

    <?php include 'includes/analytics.php'; ?>
</body>
</html>