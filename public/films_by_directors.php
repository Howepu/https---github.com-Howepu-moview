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
        SELECT m.id, m.title, m.year, m.duration, m.country, m.poster_url, d.name as director_name
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
    <link rel="stylesheet" href="assets/css/styles.css">
    
    <!-- Yandex.Metrika counter -->
    <script type="text/javascript">
        (function(m,e,t,r,i,k,a){
            m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
            m[i].l=1*new Date();
            for (var j = 0; j < document.scripts.length; j++) {if (document.scripts[j].src === r) { return; }}
            k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)
        })(window, document,'script','https://mc.yandex.ru/metrika/tag.js?id=106218457', 'ym');

        ym(106218457, 'init', {ssr:true, webvisor:true, clickmap:true, ecommerce:"dataLayer", accurateTrackBounce:true, trackLinks:true});
    </script>
    <noscript><div><img src="https://mc.yandex.ru/watch/106218457" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
    <!-- /Yandex.Metrika counter -->
</head>
<body>
    <div class="header">
        <div class="logo-container">
            <a href="main.php" class="logo">MoviePortal</a>
        </div>
        <div class="menu-toggle">
            <span></span>
        </div>
    </div>
    <div class="container">
        <div class="nav">
            <ul>
                <li><a href="main.php">Главная</a></li>
                <li><a href="films.php">Фильмы</a></li>
                <li><a href="genres.php">Жанры</a></li>
                <li><a href="directors.php">Режиссёры</a></li>
                <li><a href="admin/index.php" style="color: #ff6b6b; font-weight: bold;">Админ-панель</a></li>
            </ul>
        </div>
        <div class="main-content">
            <nav class="breadcrumbs" aria-label="Навигация">
                <ol style="list-style: none; padding-left: 0;">
                    <li><a href="main.php" title="Главная страница">Главная</a></li>
                    <li><a href="directors.php" title="Все режиссёры">Режиссёры</a></li>
                    <li aria-current="page"><?= htmlspecialchars($director_name) ?></li>
                </ol>
            </nav>
            
            <h2>Фильмы режиссёра: <?php echo htmlspecialchars($director_name); ?></h2>
            <?php if (empty($movies)): ?>
            <?php else: ?>
                <div class="movie-grid">
                    <?php foreach ($movies as $movie): ?>
                        <div class="movie-card">
                            <a href="film_page.php?movie_id=<?php echo $movie['id']; ?>">
                                <img src="<?php echo htmlspecialchars($movie['poster_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($movie['title']); ?>" 
                                     width="120" height="180"
                                     onerror="this.src='https://via.placeholder.com/120x180?text=Нет+постера'">
                                <div class="movie-info">
                                    <h3><?php echo htmlspecialchars($movie['title']); ?></h3>
                                    <p><?php echo $movie['year'] . ' | ' . $movie['duration'] . ' мин'; ?></p>
                                    <p><?php echo htmlspecialchars($movie['country']); ?></p>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="footer">
        <div class="footer-logo">
            <div class="footer-logo-container">
                <a href="main.php" class="logo">MoviePortal</a>
            </div>
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