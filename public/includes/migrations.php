<?php
/**
 * Автоматическая инициализация базы данных
 * Запускается один раз при первом подключении к БД
 */

function runDatabaseMigrations($pdo) {
    try {
        // Проверяем, существуют ли основные таблицы
        $stmt = $pdo->query("
            SELECT COUNT(*) 
            FROM information_schema.tables 
            WHERE table_schema = 'public' 
            AND table_name IN ('movies', 'directors', 'genres', 'movie_genres', 'actors', 'movie_actors')
        ");
        
        $existingTables = $stmt->fetchColumn();
        $mainTablesExist = ($existingTables >= 6);
        
        // Отдельно проверяем таблицу admin_users
        $stmt = $pdo->query("
            SELECT COUNT(*) 
            FROM information_schema.tables 
            WHERE table_schema = 'public' 
            AND table_name = 'admin_users'
        ");
        $adminTableExists = ($stmt->fetchColumn() > 0);
        
        // Проверяем наличие OAuth колонок в admin_users
        $oauthColumnsExist = false;
        if ($adminTableExists) {
            $stmt = $pdo->query("
                SELECT column_name 
                FROM information_schema.columns 
                WHERE table_schema = 'public' 
                AND table_name = 'admin_users' 
                AND column_name IN ('yandex_id', 'telegram_id', 'yandex_login')
            ");
            $oauthColumnsExist = ($stmt->rowCount() >= 3);
        }
        
        if ($mainTablesExist && $adminTableExists && $oauthColumnsExist) {
            return ['success' => true, 'message' => 'База данных уже инициализирована', 'skipped' => true];
        }
        
        error_log("MoviePortal: Запуск миграций базы данных...");
        
        // Начинаем транзакцию
        $pdo->beginTransaction();
        
        // 0. Создаем таблицу администраторов (если её нет)
        if (!$adminTableExists) {
            error_log("MoviePortal: Создаем таблицу admin_users...");
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS admin_users (
                    id SERIAL PRIMARY KEY,
                    username VARCHAR(50) UNIQUE NOT NULL,
                    password_hash VARCHAR(255) NOT NULL,
                    email VARCHAR(100),
                    role VARCHAR(20) DEFAULT 'user' CHECK (role IN ('admin', 'user')),
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    last_login TIMESTAMP,
                    telegram_id BIGINT UNIQUE,
                    telegram_username VARCHAR(255),
                    telegram_first_name VARCHAR(255),
                    telegram_last_name VARCHAR(255),
                    telegram_photo_url VARCHAR(500),
                    yandex_id VARCHAR(255) UNIQUE,
                    yandex_login VARCHAR(255),
                    yandex_first_name VARCHAR(255),
                    yandex_last_name VARCHAR(255),
                    yandex_display_name VARCHAR(255),
                    yandex_avatar_url VARCHAR(500),
                    yandex_access_token VARCHAR(255),
                    vk_id BIGINT UNIQUE,
                    vk_access_token VARCHAR(255),
                    vk_avatar_url VARCHAR(500)
                )
            ");
            
            // Создаем индексы
            $pdo->exec("CREATE INDEX IF NOT EXISTS idx_admin_username ON admin_users(username)");
            $pdo->exec("CREATE INDEX IF NOT EXISTS idx_admin_telegram_id ON admin_users(telegram_id)");
            $pdo->exec("CREATE INDEX IF NOT EXISTS idx_admin_yandex_id ON admin_users(yandex_id)");
            
            // Вставляем админа (логин: admin, пароль: admin123)
            $pdo->exec("
                INSERT INTO admin_users (username, password_hash, email, role) 
                VALUES ('admin', '\$2y\$10\$keY0E7kqwVWQXO/w7xsOiunvVmw6L9lWAh8u4dVbcSg3c0xMVRZR6', 'admin@movieportal.com', 'admin')
                ON CONFLICT (username) DO NOTHING
            ");
            error_log("MoviePortal: Таблица admin_users создана!");
        } elseif ($adminTableExists && !$oauthColumnsExist) {
            // Таблица существует, но без OAuth колонок - добавляем их
            error_log("MoviePortal: Добавляем OAuth колонки в admin_users...");
            
            $pdo->exec("ALTER TABLE admin_users ADD COLUMN IF NOT EXISTS telegram_id BIGINT UNIQUE");
            $pdo->exec("ALTER TABLE admin_users ADD COLUMN IF NOT EXISTS telegram_username VARCHAR(255)");
            $pdo->exec("ALTER TABLE admin_users ADD COLUMN IF NOT EXISTS telegram_first_name VARCHAR(255)");
            $pdo->exec("ALTER TABLE admin_users ADD COLUMN IF NOT EXISTS telegram_last_name VARCHAR(255)");
            $pdo->exec("ALTER TABLE admin_users ADD COLUMN IF NOT EXISTS telegram_photo_url VARCHAR(500)");
            
            $pdo->exec("ALTER TABLE admin_users ADD COLUMN IF NOT EXISTS yandex_id VARCHAR(255) UNIQUE");
            $pdo->exec("ALTER TABLE admin_users ADD COLUMN IF NOT EXISTS yandex_login VARCHAR(255)");
            $pdo->exec("ALTER TABLE admin_users ADD COLUMN IF NOT EXISTS yandex_first_name VARCHAR(255)");
            $pdo->exec("ALTER TABLE admin_users ADD COLUMN IF NOT EXISTS yandex_last_name VARCHAR(255)");
            $pdo->exec("ALTER TABLE admin_users ADD COLUMN IF NOT EXISTS yandex_display_name VARCHAR(255)");
            $pdo->exec("ALTER TABLE admin_users ADD COLUMN IF NOT EXISTS yandex_avatar_url VARCHAR(500)");
            $pdo->exec("ALTER TABLE admin_users ADD COLUMN IF NOT EXISTS yandex_access_token VARCHAR(255)");
            
            $pdo->exec("ALTER TABLE admin_users ADD COLUMN IF NOT EXISTS vk_id BIGINT UNIQUE");
            $pdo->exec("ALTER TABLE admin_users ADD COLUMN IF NOT EXISTS vk_access_token VARCHAR(255)");
            $pdo->exec("ALTER TABLE admin_users ADD COLUMN IF NOT EXISTS vk_avatar_url VARCHAR(500)");
            
            // Создаем индексы
            $pdo->exec("CREATE INDEX IF NOT EXISTS idx_admin_telegram_id ON admin_users(telegram_id)");
            $pdo->exec("CREATE INDEX IF NOT EXISTS idx_admin_yandex_id ON admin_users(yandex_id)");
            
            error_log("MoviePortal: OAuth колонки добавлены!");
        }
        
        // Если основные таблицы уже есть, пропускаем их создание
        if ($mainTablesExist) {
            $pdo->commit();
            return ['success' => true, 'message' => 'Таблица admin_users добавлена', 'skipped' => false];
        }
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS directors (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                bio TEXT,
                photo_url VARCHAR(255)
            )
        ");
        
        // 2. Создаем таблицу жанров
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS genres (
                id SERIAL PRIMARY KEY,
                name VARCHAR(100) NOT NULL UNIQUE,
                icon_url VARCHAR(255),
                description TEXT
            )
        ");
        
        // 3. Создаем таблицу фильмов
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS movies (
                id SERIAL PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                year INT NOT NULL,
                duration INT NOT NULL,
                country VARCHAR(100),
                director_id INTEGER REFERENCES directors(id),
                poster_url VARCHAR(255),
                description TEXT,
                rating FLOAT
            )
        ");
        
        // 4. Создаем связующую таблицу для жанров
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS movie_genres (
                movie_id INTEGER REFERENCES movies(id) ON DELETE CASCADE,
                genre_id INTEGER REFERENCES genres(id) ON DELETE CASCADE,
                PRIMARY KEY (movie_id, genre_id)
            )
        ");
        
        // 5. Создаем таблицу актёров
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS actors (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                photo_url VARCHAR(255),
                bio TEXT
            )
        ");
        
        // 6. Создаем связующую таблицу для актёров
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS movie_actors (
                movie_id INTEGER REFERENCES movies(id) ON DELETE CASCADE,
                actor_id INTEGER REFERENCES actors(id) ON DELETE CASCADE,
                PRIMARY KEY (movie_id, actor_id)
            )
        ");
        
        error_log("MoviePortal: Таблицы созданы, заполняем данными...");
        
        // 7. Заполняем режиссеров
        $pdo->exec("
            INSERT INTO directors (name, photo_url, bio) VALUES
            ('Гай Ричи', 'https://avatars.mds.yandex.net/get-entity_search/2321801/952259934/SUx182_2x', 'Британский режиссер, известный своими криминальными комедиями'),
            ('Кристофер Нолан', 'https://cybersport-img.cdnvideo.ru/images/og-jpg/plain/97/973d27e2-c7a3-4a29-a49b-49c1c8921d6a.webp', 'Американский режиссер, мастер интеллектуального кино'),
            ('Оливье Накаш и Эрик Толедано', 'https://avatars.mds.yandex.net/i?id=2475fd85992cf577d7a0d6698aeefaedb4dc7af3016ef1d7-6917316-images-thumbs&n=13', 'Французский режиссерский дуэт'),
            ('Фрэнк Дарабонт', 'https://avatars.mds.yandex.net/get-entity_search/42097/1132264037/SUx182_2x', 'Американский режиссер и сценарист'),
            ('Квентин Тарантино', 'https://avatars.mds.yandex.net/get-entity_search/4739159/791923121/SUx182', 'Американский режиссер, культовая фигура'),
            ('Роберт Земекис', 'https://avatars.mds.yandex.net/get-entity_search/5535095/785130599/S600xU_2x', 'Американский режиссер и продюсер'),
            ('Фрэнсис Форд Коппола', 'https://avatars.mds.yandex.net/get-entity_search/1579191/857228259/SUx182_2x', 'Легендарный американский режиссер'),
            ('Мартин Скорсезе', 'https://avatars.mds.yandex.net/get-entity_search/5098055/1131945373/SUx182_2x', 'Американский кинорежиссёр'),
            ('Дэвид Финчер', 'https://avatars.mds.yandex.net/get-kinopoisk-image/1773646/b7916266-133e-4c17-b95e-e0c219943fda/3840x', 'Американский режиссер, мастер триллеров'),
            ('Питер Джексон', 'https://avatars.mds.yandex.net/get-entity_search/2048976/954049122/SUx182_2x', 'Новозеландский режиссер, создатель Властелина Колец'),
            ('Ридли Скотт', 'https://avatars.mds.yandex.net/get-entity_search/1879189/1133266099/SUx182_2x', 'Британский режиссер, мастер эпического кино'),
            ('Джеймс Кэмерон', 'https://avatars.mds.yandex.net/get-entity_search/5505928/953154934/SUx182_2x', 'Канадский режиссер, создатель Титаника и Аватара'),
            ('Лана и Лилли Вачовски', 'https://avatars.mds.yandex.net/get-entity_search/1579191/857228259/SUx182_2x', 'Американские режиссеры, создатели Матрицы')
        ");
        
        // 8. Заполняем жанры
        $pdo->exec("
            INSERT INTO genres (name, icon_url, description) VALUES
            ('криминал', 'https://i.pinimg.com/736x/0d/b3/14/0db3145dad7888f428ed4ce24a4c7836.jpg', 'Фильмы о преступном мире и мафии'),
            ('драма', 'https://avatars.mds.yandex.net/get-ott/1648503/2a00000170151228609cebf3f79fc0812bdb/256x384', 'Эмоциональные фильмы о человеческих отношениях'),
            ('фантастика', 'https://static.kinoafisha.info/upload/articles/599507571934.jpg', 'Научная фантастика и футуристические миры'),
            ('боевик', 'https://avatars.mds.yandex.net/get-ott/1652588/2a000001867933df86991a0a7ae0ed78ee98/256x384', 'Динамичные фильмы с экшеном'),
            ('фэнтези', 'https://avatars.mds.yandex.net/i?id=cbd5c0e8f866a09a7c6a1f70865ac8c2_l-5185705-images-thumbs&n=13', 'Волшебные миры и магия'),
            ('исторический', 'https://avatars.mds.yandex.net/get-ott/2419418/2a00000178db661f735e8c376668da7f5993/256x384', 'Фильмы о реальных исторических событиях')
        ");
        
        // 9. Заполняем актёров
        $pdo->exec("
            INSERT INTO actors (name, photo_url, bio) VALUES
            ('Джейсон Флеминг', 'https://example.com/actors/flemyng.jpg', 'Британский актёр'),
            ('Декстер Флетчер', 'https://example.com/actors/fletcher.jpg', 'Британский актёр и режиссёр'),
            ('Ник Моран', 'https://example.com/actors/moran.jpg', 'Британский актёр'),
            ('Джейсон Стэйтем', 'https://example.com/actors/statham.jpg', 'Британский актёр, звезда боевиков'),
            ('Стивен Макинтош', 'https://example.com/actors/mackintosh.jpg', 'Британский актёр'),
            ('Винни Джонс', 'https://example.com/actors/jones.jpg', 'Бывший футболист, актёр'),
            ('Ленни Маклин', 'https://example.com/actors/maclean.jpg', 'Британский боксёр и актёр')
        ");
        
        // 10. Заполняем фильмы
        $pdo->exec("
            INSERT INTO movies (title, year, duration, country, director_id, poster_url, description, rating) VALUES
            ('Карты, деньги, два ствола', 1998, 107, 'Великобритания', 1, 
             'https://img.ixbt.site/live/images/original/24/96/61/2024/04/03/564608fb8b.jpg',
             'Четверо молодых парней накопили каждый по 25 тысяч фунтов', 8.2),
            ('Джентельмены', 2019, 113, 'Великобритания', 1,
             'https://avatars.mds.yandex.net/i?id=f9b4eaa815cb05b92e4ca0313981d26f96f20e8d-13083617-images-thumbs&n=13', NULL, 8.5),
            ('Большой куш', 2000, 104, 'Великобритания', 1,
             'https://i.pinimg.com/736x/0d/b3/14/0db3145dad7888f428ed4ce24a4c7836.jpg', NULL, 8.2),
            ('Intouchables', 2011, 112, 'Франция', 3,
             'https://avatars.mds.yandex.net/i?id=4a025e8a5426ed931b6194e08cfff2c5b78458e8-5234044-images-thumbs&n=13', NULL, 8.7),
            ('Interstellar', 2014, 169, 'США', 2,
             'https://static.kinoafisha.info/upload/articles/599507571934.jpg', NULL, 8.6),
            ('The Shawshank Redemption', 1994, 142, 'США', 4,
             'https://avatars.mds.yandex.net/get-ott/1648503/2a00000170151228609cebf3f79fc0812bdb/256x384', NULL, 9.3),
            ('Inception', 2010, 148, 'США', 2,
             'https://avatars.mds.yandex.net/i?id=10a416634a2e58f2c873a622f39ef0c5_l-6994641-images-thumbs&n=13', NULL, 8.8),
            ('The Dark Knight', 2008, 152, 'США', 2,
             'https://avatars.mds.yandex.net/get-ott/200035/2a000001673fe7e7c4e6a78ffd6079d74062/256x384', NULL, 9.0),
            ('Pulp Fiction', 1994, 154, 'США', 5,
             'https://avatars.mds.yandex.net/get-ott/2419418/2a0000017015126106578a9dc498a0625f06/256x384', NULL, 8.9),
            ('Forrest Gump', 1994, 142, 'США', 6,
             'https://avatars.mds.yandex.net/get-ott/224348/2a0000016126cd92137d5218e4d0c7c8d798/256x3840', NULL, 8.8),
            ('The Godfather', 1972, 175, 'США', 7,
             'https://avatars.mds.yandex.net/i?id=060528744b47d45c843231e925718d01_l-5222073-images-thumbs&n=13', NULL, 9.2),
            ('The Matrix', 1999, 136, 'США', 13,
             'https://avatars.mds.yandex.net/get-ott/1652588/2a000001867933df86991a0a7ae0ed78ee98/256x384', NULL, 8.7),
            ('Fight Club', 1999, 139, 'США', 9,
             'https://avatars.mds.yandex.net/get-ott/1534341/2a0000017924bbfa9fc3a82b229c4346a31c/256x384', NULL, 8.8),
            ('The Lord of the Rings', 2001, 178, 'США', 10,
             'https://avatars.mds.yandex.net/i?id=cbd5c0e8f866a09a7c6a1f70865ac8c2_l-5185705-images-thumbs&n=13', NULL, 8.8),
            ('Gladiator', 2000, 155, 'США', 11,
             'https://avatars.mds.yandex.net/i?id=819c406b6465e0b35948cd5944ef9d89_l-5234092-images-thumbs&n=13', NULL, 8.5),
            ('Titanic', 1997, 194, 'США', 12,
             'https://avatars.mds.yandex.net/get-ott/2419418/2a00000178db661f735e8c376668da7f5993/256x384', NULL, 7.8),
            ('Гнев человеческий', 2021, 118, 'США', 1,
             'https://avatars.mds.yandex.net/i?id=f58be0cef8d34d06d7b31b25daeecd5dd16eceb9-3071260-images-thumbs&n=13', NULL, 7.1),
            ('Агенты А.Н.К.Л.', 2015, 116, 'США', 1,
             'https://avatars.mds.yandex.net/get-ott/1652588/2a00000167a6be08aef125bc7cf6fab6dc8b/256x384', NULL, 7.3)
        ");
        
        // 11. Связываем фильмы и жанры
        $pdo->exec("
            INSERT INTO movie_genres (movie_id, genre_id) VALUES
            (1, 1), (2, 1), (3, 1), (9, 1), (11, 1),
            (4, 2), (6, 2), (10, 2), (13, 2), (16, 2),
            (5, 3), (7, 3), (12, 3),
            (8, 4), (17, 4), (18, 4),
            (14, 5),
            (15, 6)
        ");
        
        // 12. Связываем актёров с фильмом
        $pdo->exec("
            INSERT INTO movie_actors (movie_id, actor_id) VALUES
            (1, 1), (1, 2), (1, 3), (1, 4), (1, 5), (1, 6), (1, 7)
        ");
        
        $pdo->commit();
        
        // Считаем результаты
        $stmt = $pdo->query("SELECT COUNT(*) FROM movies");
        $moviesCount = $stmt->fetchColumn();
        
        $stmt = $pdo->query("SELECT COUNT(*) FROM directors");
        $directorsCount = $stmt->fetchColumn();
        
        error_log("MoviePortal: Миграции выполнены успешно! Фильмов: $moviesCount, Режиссёров: $directorsCount");
        
        return [
            'success' => true, 
            'message' => "База данных инициализирована: $moviesCount фильмов, $directorsCount режиссёров",
            'skipped' => false
        ];
        
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("MoviePortal: Ошибка миграций - " . $e->getMessage());
        return ['success' => false, 'message' => 'Ошибка инициализации БД: ' . $e->getMessage()];
    }
}
?>
