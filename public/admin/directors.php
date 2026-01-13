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
    if (isset($_POST['create_director'])) {
        // Создание нового режиссера
        try {
            $stmt = $pdo->prepare("
                INSERT INTO directors (name, bio, photo_url) 
                VALUES (?, ?, ?)
            ");
            $stmt->execute([
                $_POST['name'],
                $_POST['bio'],
                $_POST['photo_url']
            ]);
            
            $message = "Режиссер успешно добавлен!";
            $messageType = "success";
            $action = 'list';
        } catch (PDOException $e) {
            $message = "Ошибка при добавлении режиссера: " . $e->getMessage();
            $messageType = "danger";
        }
    }
    
    if (isset($_POST['update_director'])) {
        // Обновление режиссера
        try {
            $stmt = $pdo->prepare("
                UPDATE directors 
                SET name = ?, bio = ?, photo_url = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $_POST['name'],
                $_POST['bio'],
                $_POST['photo_url'],
                $_POST['id']
            ]);
            
            $message = "Режиссер успешно обновлен!";
            $messageType = "success";
            $action = 'list';
        } catch (PDOException $e) {
            $message = "Ошибка при обновлении режиссера: " . $e->getMessage();
            $messageType = "danger";
        }
    }
    
    if (isset($_POST['delete_director'])) {
        // Проверяем, есть ли фильмы у этого режиссера
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM movies WHERE director_id = ?");
        $stmt->execute([$_POST['id']]);
        $movieCount = $stmt->fetch()['count'];
        
        if ($movieCount > 0) {
            $message = "Невозможно удалить режиссера. У него есть {$movieCount} фильм(ов). Сначала удалите или переназначьте фильмы.";
            $messageType = "danger";
        } else {
            // Удаление режиссера
            try {
                $stmt = $pdo->prepare("DELETE FROM directors WHERE id = ?");
                $stmt->execute([$_POST['id']]);
                
                $message = "Режиссер успешно удален!";
                $messageType = "success";
                $action = 'list';
            } catch (PDOException $e) {
                $message = "Ошибка при удалении режиссера: " . $e->getMessage();
                $messageType = "danger";
            }
        }
    }
}

// Получаем данные режиссера для редактирования
$director = null;
if ($action === 'edit' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM directors WHERE id = ?");
    $stmt->execute([$id]);
    $director = $stmt->fetch();
}

$pageTitle = "Управление режиссерами - Админ-панель";
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
            <h1>Режиссеры</h1>
        </div>
        <div class="menu-toggle" onclick="toggleMobileMenu()">
            <span></span>
            <span></span>
            <span></span>
        </div>
        <div class="admin-nav">
            <span class="admin-user"><?= htmlspecialchars($_SESSION['admin_username']) ?></span>
            <a href="index.php" class="btn btn-secondary">Панель</a>
            <a href="../main.php" class="btn btn-secondary">Сайт</a>
            <a href="?action=logout" class="btn btn-danger">Выйти</a>
        </div>
    </div>

    <div class="admin-container">
        <div class="admin-sidebar" id="mobileSidebar">
            <div class="mobile-menu-header">
                <h3 style="margin: 0; color: #667eea;">Меню</h3>
                <button class="mobile-menu-close" onclick="toggleMobileMenu()">✕</button>
            </div>
            <nav class="admin-menu">
                <ul>
                    <li><a href="index.php">🏠 Главная</a></li>
                    <li><a href="movies.php">🎬 Фильмы</a></li>
                    <li><a href="directors.php" class="active">🎭 Режиссеры</a></li>
                    <li><a href="genres.php">🎪 Жанры</a></li>
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
                <!-- Список режиссеров -->
                <div class="admin-header-actions">
                    <h2>Список режиссеров</h2>
                    <a href="?action=create" class="btn btn-primary">Добавить режиссера</a>
                </div>

                <?php
                try {
                    $stmt = $pdo->query("
                        SELECT 
                            d.id,
                            d.name,
                            d.photo_url,
                            COUNT(m.id) as movies_count
                        FROM directors d
                        LEFT JOIN movies m ON d.id = m.director_id
                        GROUP BY d.id, d.name, d.photo_url
                        ORDER BY d.id
                    ");
                    $directors = $stmt->fetchAll();
                } catch (PDOException $e) {
                    echo "Ошибка SQL: " . $e->getMessage();
                    $directors = [];
                }
                ?>

                <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Фото</th>
                            <th>Имя</th>
                            <th>Количество фильмов</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($directors as $director): ?>
                        <tr>
                            <td data-label="ID"><?= $director['id'] ?></td>
                            <td data-label="Фото">
                                <?php if ($director['photo_url']): ?>
                                    <img src="<?= htmlspecialchars($director['photo_url']) ?>" 
                                         alt="<?= htmlspecialchars($director['name']) ?>" 
                                         style="width: 50px; height: 50px; object-fit: cover; border-radius: 50%;"
                                         onerror="this.style.display='none'">
                                <?php else: ?>
                                    <div style="width: 50px; height: 50px; background: #f0f0f0; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px;">👤</div>
                                <?php endif; ?>
                            </td>
                            <td data-label="Имя"><?= htmlspecialchars($director['name']) ?></td>
                            <td data-label="Фильмов">
                                <span class="badge"><?= $director['movies_count'] ?></span>
                                <?php if ($director['movies_count'] > 0): ?>
                                    <a href="movies.php?director_id=<?= $director['id'] ?>" style="font-size: 0.8em; color: #667eea;">Посмотреть</a>
                                <?php endif; ?>
                            </td>
                            <td data-label="Действия">
                                <div class="table-actions">
                                    <a href="?action=edit&id=<?= $director['id'] ?>" class="btn btn-warning">Редактировать</a>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Вы уверены, что хотите удалить этого режиссера?<?= $director['movies_count'] > 0 ? ' У него есть ' . $director['movies_count'] . ' фильм(ов)!' : '' ?>')">
                                        <input type="hidden" name="id" value="<?= $director['id'] ?>">
                                        <button type="submit" name="delete_director" class="btn btn-danger" 
                                                <?= $director['movies_count'] > 0 ? 'title="У режиссера есть фильмы"' : '' ?>>
                                            Удалить
                                        </button>
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
                <h2><?= $action === 'create' ? 'Добавить режиссера' : 'Редактировать режиссера' ?></h2>
                
                <form method="POST" class="admin-form">
                    <?php if ($action === 'edit'): ?>
                        <input type="hidden" name="id" value="<?= $director['id'] ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="name">Имя режиссера:</label>
                        <input type="text" id="name" name="name" class="form-control" 
                               value="<?= htmlspecialchars($director['name'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="photo_url">URL фотографии:</label>
                        <input type="url" id="photo_url" name="photo_url" class="form-control" 
                               value="<?= htmlspecialchars($director['photo_url'] ?? '') ?>"
                               placeholder="https://example.com/photo.jpg">
                        <small style="color: #6c757d; font-size: 0.875em;">Необязательное поле. Введите ссылку на фотографию режиссера.</small>
                    </div>

                    <div class="form-group">
                        <label for="bio">Биография:</label>
                        <textarea id="bio" name="bio" class="form-control" rows="6" 
                                  placeholder="Краткая биография режиссера..."><?= htmlspecialchars($director['bio'] ?? '') ?></textarea>
                    </div>

                    <?php if ($action === 'edit' && $director): ?>
                        <div class="form-group">
                            <label>Предварительный просмотр фото:</label>
                            <div id="photo-preview" style="margin-top: 0.5rem;">
                                <?php if ($director['photo_url']): ?>
                                    <img src="<?= htmlspecialchars($director['photo_url']) ?>" 
                                         alt="Предварительный просмотр" 
                                         style="max-width: 200px; max-height: 200px; object-fit: cover; border-radius: 8px; border: 1px solid #ddd;"
                                         onerror="this.style.display='none'; document.getElementById('no-photo').style.display='block';">
                                    <div id="no-photo" style="display: none; padding: 2rem; background: #f8f9fa; border-radius: 8px; text-align: center; color: #6c757d;">
                                        Фото не загружается
                                    </div>
                                <?php else: ?>
                                    <div style="padding: 2rem; background: #f8f9fa; border-radius: 8px; text-align: center; color: #6c757d;">
                                        Фото не добавлено
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="form-actions">
                        <button type="submit" name="<?= $action === 'create' ? 'create_director' : 'update_director' ?>" 
                                class="btn btn-success">
                            <?= $action === 'create' ? 'Добавить режиссера' : 'Сохранить изменения' ?>
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
    
    .badge {
        background: #667eea;
        color: white;
        padding: 0.25rem 0.5rem;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
        margin-right: 0.5rem;
    }
    
    #photo_url {
        margin-bottom: 0.5rem;
    }
    
    /* Живой предварительный просмотр фото */
    #photo-preview img {
        transition: all 0.3s ease;
    }
    </style>

    <script>
    // Живой предварительный просмотр фото
    document.getElementById('photo_url')?.addEventListener('input', function() {
        const url = this.value;
        const preview = document.getElementById('photo-preview');
        
        if (preview) {
            if (url) {
                preview.innerHTML = `
                    <img src="${url}" 
                         alt="Предварительный просмотр" 
                         style="max-width: 200px; max-height: 200px; object-fit: cover; border-radius: 8px; border: 1px solid #ddd;"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                    <div style="display: none; padding: 2rem; background: #f8f9fa; border-radius: 8px; text-align: center; color: #6c757d;">
                        Не удается загрузить изображение
                    </div>
                `;
            } else {
                preview.innerHTML = `
                    <div style="padding: 2rem; background: #f8f9fa; border-radius: 8px; text-align: center; color: #6c757d;">
                        Введите URL фотографии для предварительного просмотра
                    </div>
                `;
            }
        }
    });
    
    function toggleMobileMenu() {
        const sidebar = document.getElementById('mobileSidebar');
        sidebar.classList.toggle('mobile-open');
        document.body.style.overflow = sidebar.classList.contains('mobile-open') ? 'hidden' : '';
    }
    
    document.querySelectorAll('.admin-menu a').forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth <= 768) {
                const sidebar = document.getElementById('mobileSidebar');
                sidebar.classList.remove('mobile-open');
                document.body.style.overflow = '';
            }
        });
    });
    </script>
</body>
</html>
