<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Проверка миграций - MoviePortal</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 800px;
            width: 100%;
            padding: 40px;
        }
        h1 { 
            color: #2d3748; 
            margin-bottom: 10px;
            font-size: 2.5em;
        }
        .subtitle {
            color: #718096;
            margin-bottom: 30px;
            font-size: 1.1em;
        }
        .status {
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            border-left: 5px solid;
        }
        .success { 
            background: #f0fdf4; 
            border-color: #22c55e;
            color: #166534;
        }
        .error { 
            background: #fef2f2; 
            border-color: #ef4444;
            color: #991b1b;
        }
        .info { 
            background: #eff6ff; 
            border-color: #3b82f6;
            color: #1e40af;
        }
        .warning {
            background: #fffbeb;
            border-color: #f59e0b;
            color: #92400e;
        }
        .icon {
            font-size: 1.5em;
            margin-right: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background: white;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        th {
            background: #f9fafb;
            font-weight: 600;
            color: #374151;
        }
        tr:hover {
            background: #f9fafb;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #3b82f6;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            font-size: 1em;
        }
        .btn:hover {
            background: #2563eb;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(59, 130, 246, 0.3);
        }
        .btn-secondary {
            background: #6b7280;
        }
        .btn-secondary:hover {
            background: #4b5563;
        }
        .btn-success {
            background: #22c55e;
        }
        .btn-success:hover {
            background: #16a34a;
        }
        .actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        code {
            background: #f1f5f9;
            padding: 2px 6px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            color: #e11d48;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }
        .stat-number {
            font-size: 2.5em;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .stat-label {
            font-size: 0.9em;
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎬 MoviePortal</h1>
        <p class="subtitle">Проверка состояния базы данных</p>

        <?php
        require_once 'config.php';
        
        // Проверяем таблицы
        $stmt = $pdo->query("
            SELECT table_name 
            FROM information_schema.tables 
            WHERE table_schema = 'public' 
            ORDER BY table_name
        ");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $requiredTables = ['directors', 'genres', 'movies', 'movie_genres', 'actors', 'movie_actors'];
        $missingTables = array_diff($requiredTables, $tables);
        
        if (empty($missingTables)) {
            echo '<div class="status success">';
            echo '<span class="icon">✅</span>';
            echo '<strong>Миграции выполнены успешно!</strong><br>';
            echo 'Все необходимые таблицы созданы.';
            echo '</div>';
            
            // Статистика
            $stats = [];
            $stats['movies'] = $pdo->query("SELECT COUNT(*) FROM movies")->fetchColumn();
            $stats['directors'] = $pdo->query("SELECT COUNT(*) FROM directors")->fetchColumn();
            $stats['genres'] = $pdo->query("SELECT COUNT(*) FROM genres")->fetchColumn();
            $stats['actors'] = $pdo->query("SELECT COUNT(*) FROM actors")->fetchColumn();
            
            echo '<div class="stats">';
            echo '<div class="stat-card"><div class="stat-number">' . $stats['movies'] . '</div><div class="stat-label">Фильмов</div></div>';
            echo '<div class="stat-card"><div class="stat-number">' . $stats['directors'] . '</div><div class="stat-label">Режиссёров</div></div>';
            echo '<div class="stat-card"><div class="stat-number">' . $stats['genres'] . '</div><div class="stat-label">Жанров</div></div>';
            echo '<div class="stat-card"><div class="stat-number">' . $stats['actors'] . '</div><div class="stat-label">Актёров</div></div>';
            echo '</div>';
            
            // Список таблиц
            echo '<h3 style="margin-top: 30px; color: #374151;">Созданные таблицы:</h3>';
            echo '<table>';
            echo '<thead><tr><th>Таблица</th><th>Записей</th></tr></thead>';
            echo '<tbody>';
            foreach ($tables as $table) {
                try {
                    $count = $pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn();
                    echo "<tr><td><code>$table</code></td><td>$count</td></tr>";
                } catch (Exception $e) {
                    echo "<tr><td><code>$table</code></td><td>-</td></tr>";
                }
            }
            echo '</tbody></table>';
            
            echo '<div class="actions">';
            echo '<a href="main.php" class="btn btn-success">🏠 Перейти на главную</a>';
            echo '<a href="films.php" class="btn">🎬 Каталог фильмов</a>';
            echo '<a href="admin/index.php" class="btn btn-secondary">⚙️ Админ-панель</a>';
            echo '</div>';
            
        } else {
            echo '<div class="status error">';
            echo '<span class="icon">❌</span>';
            echo '<strong>Миграции не выполнены!</strong><br>';
            echo 'Отсутствуют таблицы: ' . implode(', ', array_map(function($t) { 
                return "<code>$t</code>"; 
            }, $missingTables));
            echo '</div>';
            
            echo '<div class="status info">';
            echo '<span class="icon">ℹ️</span>';
            echo '<strong>Что делать:</strong><br>';
            echo '1. Миграции должны запуститься автоматически при загрузке любой страницы<br>';
            echo '2. Проверьте логи: <code>docker-compose logs php</code><br>';
            echo '3. Проверьте подключение к БД<br>';
            echo '4. Попробуйте перезагрузить страницу';
            echo '</div>';
            
            echo '<div class="actions">';
            echo '<a href="check_migrations.php" class="btn">🔄 Обновить страницу</a>';
            echo '</div>';
        }
        
        // Информация о подключении
        echo '<div class="status info" style="margin-top: 30px;">';
        echo '<span class="icon">🔌</span>';
        echo '<strong>Информация о подключении:</strong><br>';
        echo 'Host: <code>' . DB_HOST . '</code><br>';
        echo 'Database: <code>' . DB_NAME . '</code><br>';
        echo 'User: <code>' . DB_USER . '</code>';
        echo '</div>';
        ?>
    </div>
</body>
</html>
