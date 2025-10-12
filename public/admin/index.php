<?php
require_once '../config.php';
require_once 'auth.php';

// Получаем статистику
$stats = [];

// Количество фильмов
$stmt = $pdo->query("SELECT COUNT(*) as count FROM movies");
$stats['movies'] = $stmt->fetch()['count'];

// Количество режиссеров
$stmt = $pdo->query("SELECT COUNT(*) as count FROM directors");
$stats['directors'] = $stmt->fetch()['count'];

// Количество жанров
$stmt = $pdo->query("SELECT COUNT(*) as count FROM genres");
$stats['genres'] = $stmt->fetch()['count'];

$pageTitle = "Админ-панель - MoviePortal";
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="admin-styles.css">
</head>
<body>
    <div class="admin-header">
        <div class="admin-logo">
            <h1>Админ-панель MoviePortal</h1>
        </div>
        <div class="admin-nav">
            <span class="admin-user">Добро пожаловать, <?= htmlspecialchars($_SESSION['admin_username']) ?>!</span>
            <a href="../main.php" class="btn btn-secondary">Вернуться на сайт</a>
            <a href="?action=logout" class="btn btn-danger">Выйти</a>
        </div>
    </div>

    <div class="admin-container">
        <div class="admin-sidebar">
            <nav class="admin-menu">
                <ul>
                    <li><a href="index.php" class="active">Главная</a></li>
                    <li><a href="movies.php">Управление фильмами</a></li>
                    <li><a href="directors.php">Управление режиссерами</a></li>
                    <li><a href="genres.php">Управление жанрами</a></li>
                </ul>
            </nav>
        </div>

        <div class="admin-content">
            <div class="admin-dashboard">
                <h2>Панель управления</h2>
                
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">🎬</div>
                        <div class="stat-info">
                            <h3><?= $stats['movies'] ?></h3>
                            <p>Фильмов</p>
                        </div>
                        <a href="movies.php" class="stat-link">Управлять</a>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">🎭</div>
                        <div class="stat-info">
                            <h3><?= $stats['directors'] ?></h3>
                            <p>Режиссеров</p>
                        </div>
                        <a href="directors.php" class="stat-link">Управлять</a>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">🎪</div>
                        <div class="stat-info">
                            <h3><?= $stats['genres'] ?></h3>
                            <p>Жанров</p>
                        </div>
                        <a href="genres.php" class="stat-link">Управлять</a>
                    </div>
                </div>

                <div class="quick-actions">
                    <h3>Быстрые действия</h3>
                    <div class="action-buttons">
                        <a href="movies.php?action=create" class="btn btn-primary">Добавить фильм</a>
                        <a href="directors.php?action=create" class="btn btn-primary">Добавить режиссера</a>
                        <a href="genres.php?action=create" class="btn btn-primary">Добавить жанр</a>
                    </div>
                </div>

                <div class="recent-activity">
                    <h3>Последние добавленные фильмы</h3>
                    <?php
                    $stmt = $pdo->query("
                        SELECT m.id, m.title, m.year, d.name as director
                        FROM movies m
                        JOIN directors d ON m.director_id = d.id
                        ORDER BY m.id DESC
                        LIMIT 5
                    ");
                    $recent_movies = $stmt->fetchAll();
                    ?>
                    
                    <div class="recent-list">
                        <?php foreach ($recent_movies as $movie): ?>
                        <div class="recent-item">
                            <span class="movie-title"><?= htmlspecialchars($movie['title']) ?></span>
                            <span class="movie-year">(<?= $movie['year'] ?>)</span>
                            <span class="movie-director">Режиссер: <?= htmlspecialchars($movie['director']) ?></span>
                            <a href="movies.php?action=edit&id=<?= $movie['id'] ?>" class="edit-link">Редактировать</a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
