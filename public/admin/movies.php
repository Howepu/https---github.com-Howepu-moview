<?php
require_once '../config.php';
require_once 'auth.php';

// Проверяем права администратора
checkAdminRole();

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;
$message = '';
$messageType = '';

// Обработка POST запросов
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_movie'])) {
        // Создание нового фильма
        try {
            $stmt = $pdo->prepare("
                INSERT INTO movies (title, year, duration, country, poster_url, description, director_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $_POST['title'],
                $_POST['year'],
                $_POST['duration'],
                $_POST['country'],
                $_POST['poster_url'],
                $_POST['description'],
                $_POST['director_id']
            ]);
            
            $movie_id = $pdo->lastInsertId();
            
            // Добавляем жанры
            if (!empty($_POST['genres'])) {
                $stmt = $pdo->prepare("INSERT INTO movie_genres (movie_id, genre_id) VALUES (?, ?)");
                foreach ($_POST['genres'] as $genre_id) {
                    $stmt->execute([$movie_id, $genre_id]);
                }
            }
            
            $message = "Фильм успешно добавлен!";
            $messageType = "success";
            $action = 'list';
        } catch (PDOException $e) {
            $message = "Ошибка при добавлении фильма: " . $e->getMessage();
            $messageType = "danger";
        }
    }
    
    if (isset($_POST['update_movie'])) {
        // Обновление фильма
        try {
            $stmt = $pdo->prepare("
                UPDATE movies 
                SET title = ?, year = ?, duration = ?, country = ?, poster_url = ?, description = ?, director_id = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $_POST['title'],
                $_POST['year'],
                $_POST['duration'],
                $_POST['country'],
                $_POST['poster_url'],
                $_POST['description'],
                $_POST['director_id'],
                $_POST['id']
            ]);
            
            // Удаляем старые жанры и добавляем новые
            $stmt = $pdo->prepare("DELETE FROM movie_genres WHERE movie_id = ?");
            $stmt->execute([$_POST['id']]);
            
            if (!empty($_POST['genres'])) {
                $stmt = $pdo->prepare("INSERT INTO movie_genres (movie_id, genre_id) VALUES (?, ?)");
                foreach ($_POST['genres'] as $genre_id) {
                    $stmt->execute([$_POST['id'], $genre_id]);
                }
            }
            
            $message = "Фильм успешно обновлен!";
            $messageType = "success";
            $action = 'list';
        } catch (PDOException $e) {
            $message = "Ошибка при обновлении фильма: " . $e->getMessage();
            $messageType = "danger";
        }
    }
    
    if (isset($_POST['delete_movie'])) {
        // Удаление фильма
        try {
            // Сначала удаляем связи с жанрами
            $stmt = $pdo->prepare("DELETE FROM movie_genres WHERE movie_id = ?");
            $stmt->execute([$_POST['id']]);
            
            // Затем удаляем сам фильм
            $stmt = $pdo->prepare("DELETE FROM movies WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            
            $message = "Фильм успешно удален!";
            $messageType = "success";
            $action = 'list';
        } catch (PDOException $e) {
            $message = "Ошибка при удалении фильма: " . $e->getMessage();
            $messageType = "danger";
        }
    }
}

// Получаем данные для форм
$directors = $pdo->query("SELECT id, name FROM directors ORDER BY name")->fetchAll();
$genres = $pdo->query("SELECT id, name FROM genres ORDER BY name")->fetchAll();

// Получаем данные фильма для редактирования
$movie = null;
$movie_genres = [];
if ($action === 'edit' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM movies WHERE id = ?");
    $stmt->execute([$id]);
    $movie = $stmt->fetch();
    
    if ($movie) {
        $stmt = $pdo->prepare("SELECT genre_id FROM movie_genres WHERE movie_id = ?");
        $stmt->execute([$id]);
        $movie_genres = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}

$pageTitle = "Управление фильмами - Админ-панель";
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="admin-styles.css">
</head>
<body>
    <div class="admin-header">
        <div class="admin-logo">
            <h1>Управление фильмами</h1>
        </div>
        <div class="admin-nav">
            <span class="admin-user">Добро пожаловать, <?= htmlspecialchars($_SESSION['admin_username']) ?>!</span>
            <a href="index.php" class="btn btn-secondary">Назад к панели</a>
            <a href="../main.php" class="btn btn-secondary">На сайт</a>
            <a href="?action=logout" class="btn btn-danger">Выйти</a>
        </div>
    </div>

    <div class="admin-container">
        <div class="admin-sidebar">
            <nav class="admin-menu">
                <ul>
                    <li><a href="index.php">Главная</a></li>
                    <li><a href="movies.php" class="active">Управление фильмами</a></li>
                    <li><a href="directors.php">Управление режиссерами</a></li>
                    <li><a href="genres.php">Управление жанрами</a></li>
                </ul>
            </nav>
        </div>

        <div class="admin-content">
            <?php if ($message): ?>
                <div class="alert alert-<?= $messageType ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <?php if ($action === 'list'): ?>
                <!-- Список фильмов -->
                <div class="admin-header-actions">
                    <h2>Список фильмов</h2>
                    <a href="?action=create" class="btn btn-primary">Добавить фильм</a>
                </div>

                <?php
                $stmt = $pdo->query("
                    SELECT 
                        m.id,
                        m.title,
                        m.year,
                        m.duration,
                        m.country,
                        d.name AS director,
                        STRING_AGG(g.name, ', ') AS genres
                    FROM movies m
                    JOIN directors d ON m.director_id = d.id
                    LEFT JOIN movie_genres mg ON m.id = mg.movie_id
                    LEFT JOIN genres g ON mg.genre_id = g.id
                    GROUP BY m.id, d.name
                    ORDER BY m.id
                ");
                $movies = $stmt->fetchAll();
                ?>

                <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Название</th>
                            <th>Год</th>
                            <th>Длительность</th>
                            <th>Страна</th>
                            <th>Режиссер</th>
                            <th>Жанры</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($movies as $movie): ?>
                        <tr>
                            <td><?= $movie['id'] ?></td>
                            <td><?= htmlspecialchars($movie['title']) ?></td>
                            <td><?= $movie['year'] ?></td>
                            <td><?= $movie['duration'] ?> мин</td>
                            <td><?= htmlspecialchars($movie['country']) ?></td>
                            <td><?= htmlspecialchars($movie['director']) ?></td>
                            <td><?= htmlspecialchars($movie['genres'] ?? 'Нет жанров') ?></td>
                            <td>
                                <div class="table-actions">
                                    <a href="?action=edit&id=<?= $movie['id'] ?>" class="btn btn-warning">Редактировать</a>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Вы уверены, что хотите удалить этот фильм?')">
                                        <input type="hidden" name="id" value="<?= $movie['id'] ?>">
                                        <button type="submit" name="delete_movie" class="btn btn-danger">Удалить</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                </div>

            <?php elseif ($action === 'create' || $action === 'edit'): ?>
                <!-- Форма создания/редактирования -->
                <h2><?= $action === 'create' ? 'Добавить фильм' : 'Редактировать фильм' ?></h2>
                
                <form method="POST" class="admin-form">
                    <?php if ($action === 'edit'): ?>
                        <input type="hidden" name="id" value="<?= $movie['id'] ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="title">Название фильма:</label>
                        <input type="text" id="title" name="title" class="form-control" 
                               value="<?= htmlspecialchars($movie['title'] ?? '') ?>" 
                               placeholder="Например: Начало" required>
                        <small class="form-hint">Полное название фильма на русском языке</small>
                    </div>

                    <div class="form-group">
                        <label for="year">Год выпуска:</label>
                        <input type="number" id="year" name="year" class="form-control" 
                               value="<?= $movie['year'] ?? '' ?>" min="1900" max="2030" 
                               placeholder="2024" required>
                        <small class="form-hint">Год от 1900 до 2030</small>
                    </div>

                    <div class="form-group">
                        <label for="duration">Длительность (минуты):</label>
                        <input type="number" id="duration" name="duration" class="form-control" 
                               value="<?= $movie['duration'] ?? '' ?>" min="1" max="600"
                               placeholder="120" required>
                        <small class="form-hint">Продолжительность в минутах (например: 120)</small>
                    </div>

                    <div class="form-group">
                        <label for="country">Страна:</label>
                        <input type="text" id="country" name="country" class="form-control" 
                               value="<?= htmlspecialchars($movie['country'] ?? '') ?>" 
                               placeholder="США, Великобритания" required>
                        <small class="form-hint">Страна производства (можно несколько через запятую)</small>
                    </div>

                    <div class="form-group">
                        <label for="director_id">Режиссер:</label>
                        <select id="director_id" name="director_id" class="form-control" required>
                            <option value="">Выберите режиссера</option>
                            <?php foreach ($directors as $director): ?>
                                <option value="<?= $director['id'] ?>" 
                                        <?= ($movie['director_id'] ?? '') == $director['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($director['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="genres">Жанры:</label>
                        <small class="form-hint" style="display: block; margin-bottom: 0.5rem;">Выберите один или несколько жанров</small>
                        <div style="max-height: 150px; overflow-y: auto; border: 1px solid #ced4da; padding: 0.5rem; border-radius: 6px;">
                            <?php foreach ($genres as $genre): ?>
                                <label style="display: block; margin-bottom: 0.25rem;">
                                    <input type="checkbox" name="genres[]" value="<?= $genre['id'] ?>"
                                           <?= in_array($genre['id'], $movie_genres) ? 'checked' : '' ?>>
                                    <?= htmlspecialchars($genre['name']) ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="poster_url">URL постера:</label>
                        <input type="url" id="poster_url" name="poster_url" class="form-control" 
                               value="<?= htmlspecialchars($movie['poster_url'] ?? '') ?>"
                               placeholder="https://example.com/poster.jpg">
                        <small class="form-hint">Прямая ссылка на изображение (jpg, png, webp)</small>
                    </div>

                    <div class="form-group">
                        <label for="description">Описание:</label>
                        <textarea id="description" name="description" class="form-control" rows="4" 
                                  placeholder="Краткое описание сюжета фильма..."><?= htmlspecialchars($movie['description'] ?? '') ?></textarea>
                        <small class="form-hint">Краткий синопсис фильма (2-3 предложения)</small>
                    </div>

                    <div class="form-actions">
                        <button type="submit" name="<?= $action === 'create' ? 'create_movie' : 'update_movie' ?>" 
                                class="btn btn-success">
                            <?= $action === 'create' ? 'Добавить фильм' : 'Сохранить изменения' ?>
                        </button>
                        <a href="?action=list" class="btn btn-secondary">Отмена</a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <style>
    .admin-header-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
    }
    
    .admin-header-actions h2 {
        margin: 0;
    }
    </style>
</body>
</html>
