<?php
require_once '../config.php';

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;
$message = '';
$messageType = '';

// Обработка POST запросов
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_genre'])) {
        // Создание нового жанра
        try {
            $stmt = $pdo->prepare("INSERT INTO genres (name, description) VALUES (?, ?)");
            $stmt->execute([
                $_POST['name'],
                $_POST['description']
            ]);
            
            $message = "Жанр успешно добавлен!";
            $messageType = "success";
            $action = 'list';
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'UNIQUE constraint failed') !== false || 
                strpos($e->getMessage(), 'duplicate key') !== false) {
                $message = "Жанр с таким названием уже существует!";
            } else {
                $message = "Ошибка при добавлении жанра: " . $e->getMessage();
            }
            $messageType = "danger";
        }
    }
    
    if (isset($_POST['update_genre'])) {
        // Обновление жанра
        try {
            $stmt = $pdo->prepare("UPDATE genres SET name = ?, description = ? WHERE id = ?");
            $stmt->execute([
                $_POST['name'],
                $_POST['description'],
                $_POST['id']
            ]);
            
            $message = "Жанр успешно обновлен!";
            $messageType = "success";
            $action = 'list';
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'UNIQUE constraint failed') !== false || 
                strpos($e->getMessage(), 'duplicate key') !== false) {
                $message = "Жанр с таким названием уже существует!";
            } else {
                $message = "Ошибка при обновлении жанра: " . $e->getMessage();
            }
            $messageType = "danger";
        }
    }
    
    if (isset($_POST['delete_genre'])) {
        // Проверяем, есть ли фильмы с этим жанром
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM movie_genres WHERE genre_id = ?");
        $stmt->execute([$_POST['id']]);
        $movieCount = $stmt->fetch()['count'];
        
        if ($movieCount > 0) {
            $message = "Невозможно удалить жанр. К нему привязано {$movieCount} фильм(ов). Сначала удалите связи с фильмами.";
            $messageType = "danger";
        } else {
            // Удаление жанра
            try {
                $stmt = $pdo->prepare("DELETE FROM genres WHERE id = ?");
                $stmt->execute([$_POST['id']]);
                
                $message = "Жанр успешно удален!";
                $messageType = "success";
                $action = 'list';
            } catch (PDOException $e) {
                $message = "Ошибка при удалении жанра: " . $e->getMessage();
                $messageType = "danger";
            }
        }
    }
}

// Получаем данные жанра для редактирования
$genre = null;
if ($action === 'edit' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM genres WHERE id = ?");
    $stmt->execute([$id]);
    $genre = $stmt->fetch();
}

$pageTitle = "Управление жанрами - Админ-панель";
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
            <h1>Управление жанрами</h1>
        </div>
        <div class="admin-nav">
            <a href="index.php" class="btn btn-secondary">Назад к панели</a>
            <a href="../main.php" class="btn btn-secondary">На сайт</a>
        </div>
    </div>

    <div class="admin-container">
        <div class="admin-sidebar">
            <nav class="admin-menu">
                <ul>
                    <li><a href="index.php">Главная</a></li>
                    <li><a href="movies.php">Управление фильмами</a></li>
                    <li><a href="directors.php">Управление режиссерами</a></li>
                    <li><a href="genres.php" class="active">Управление жанрами</a></li>
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
                <!-- Список жанров -->
                <div class="admin-header-actions">
                    <h2>Список жанров</h2>
                    <a href="?action=create" class="btn btn-primary">Добавить жанр</a>
                </div>

                <?php
                $stmt = $pdo->query("
                    SELECT 
                        g.id,
                        g.name,
                        g.description,
                        COUNT(mg.movie_id) as movies_count
                    FROM genres g
                    LEFT JOIN movie_genres mg ON g.id = mg.genre_id
                    GROUP BY g.id, g.name, g.description
                    ORDER BY g.name
                ");
                $genres = $stmt->fetchAll();
                ?>

                <div class="genres-grid">
                    <?php foreach ($genres as $genre): ?>
                    <div class="genre-card">
                        <div class="genre-header">
                            <h3><?= htmlspecialchars($genre['name']) ?></h3>
                            <span class="genre-count"><?= $genre['movies_count'] ?> фильм(ов)</span>
                        </div>
                        
                        <div class="genre-description">
                            <?php if ($genre['description']): ?>
                                <p><?= htmlspecialchars($genre['description']) ?></p>
                            <?php else: ?>
                                <p style="color: #6c757d; font-style: italic;">Описание не добавлено</p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="genre-actions">
                            <a href="?action=edit&id=<?= $genre['id'] ?>" class="btn btn-warning btn-sm">Редактировать</a>
                            <?php if ($genre['movies_count'] > 0): ?>
                                <a href="movies.php?genre_id=<?= $genre['id'] ?>" class="btn btn-info btn-sm">Фильмы</a>
                            <?php endif; ?>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Вы уверены, что хотите удалить этот жанр?<?= $genre['movies_count'] > 0 ? ' К нему привязано ' . $genre['movies_count'] . ' фильм(ов)!' : '' ?>')">
                                <input type="hidden" name="id" value="<?= $genre['id'] ?>">
                                <button type="submit" name="delete_genre" class="btn btn-danger btn-sm" 
                                        <?= $genre['movies_count'] > 0 ? 'title="К жанру привязаны фильмы"' : '' ?>>
                                    Удалить
                                </button>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <?php if (empty($genres)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">🎭</div>
                        <h3>Жанры не найдены</h3>
                        <p>Добавьте первый жанр для начала работы</p>
                        <a href="?action=create" class="btn btn-primary">Добавить жанр</a>
                    </div>
                <?php endif; ?>

            <?php elseif ($action === 'create' || $action === 'edit'): ?>
                <!-- Форма создания/редактирования -->
                <h2><?= $action === 'create' ? 'Добавить жанр' : 'Редактировать жанр' ?></h2>
                
                <form method="POST" class="admin-form">
                    <?php if ($action === 'edit'): ?>
                        <input type="hidden" name="id" value="<?= $genre['id'] ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="name">Название жанра:</label>
                        <input type="text" id="name" name="name" class="form-control" 
                               value="<?= htmlspecialchars($genre['name'] ?? '') ?>" 
                               placeholder="Например: Боевик, Комедия, Драма..." required>
                    </div>

                    <div class="form-group">
                        <label for="description">Описание жанра:</label>
                        <textarea id="description" name="description" class="form-control" rows="4" 
                                  placeholder="Краткое описание жанра и его особенностей..."><?= htmlspecialchars($genre['description'] ?? '') ?></textarea>
                        <small style="color: #6c757d; font-size: 0.875em;">Необязательное поле. Добавьте описание для лучшего понимания жанра.</small>
                    </div>

                    <div class="form-actions">
                        <button type="submit" name="<?= $action === 'create' ? 'create_genre' : 'update_genre' ?>" 
                                class="btn btn-success">
                            <?= $action === 'create' ? 'Добавить жанр' : 'Сохранить изменения' ?>
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
    
    .genres-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .genre-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        border: 1px solid #e9ecef;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .genre-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }
    
    .genre-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid #e9ecef;
    }
    
    .genre-header h3 {
        margin: 0;
        color: #343a40;
        font-size: 1.25rem;
        font-weight: 600;
    }
    
    .genre-count {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .genre-description {
        margin-bottom: 1.5rem;
        min-height: 60px;
    }
    
    .genre-description p {
        margin: 0;
        color: #6c757d;
        line-height: 1.5;
    }
    
    .genre-actions {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }
    
    .btn-sm {
        padding: 0.375rem 0.75rem;
        font-size: 0.875rem;
    }
    
    .btn-info {
        background: #17a2b8;
        color: white;
    }
    
    .btn-info:hover {
        background: #138496;
    }
    
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        border: 1px solid #e9ecef;
    }
    
    .empty-icon {
        font-size: 4rem;
        margin-bottom: 1rem;
    }
    
    .empty-state h3 {
        color: #343a40;
        margin-bottom: 0.5rem;
    }
    
    .empty-state p {
        color: #6c757d;
        margin-bottom: 2rem;
    }
    
    @media (max-width: 768px) {
        .genres-grid {
            grid-template-columns: 1fr;
        }
        
        .genre-actions {
            justify-content: center;
        }
    }
    </style>
</body>
</html>
