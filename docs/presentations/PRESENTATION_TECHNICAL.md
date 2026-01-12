# MoviePortal - Техническая презентация
## Веб-приложение для каталогизации фильмов

---

## 📊 Слайд 1: Титульный
**MoviePortal**  
Полнофункциональная система управления каталогом фильмов

**Технологии:**
- PHP 8.2 + PostgreSQL 12
- Docker Compose
- OAuth 2.0 (Yandex, Telegram, VK)

---

## 💻 Слайд 2: Архитектура приложения

**Трёхуровневая архитектура:**

```
┌──────────────────────────────────────┐
│         Презентационный слой         │
│  HTML5 + CSS3 + Vanilla JavaScript   │
│      (Адаптивный интерфейс)          │
└─────────────────┬────────────────────┘
                  │ HTTP/AJAX
┌─────────────────▼────────────────────┐
│        Бизнес-логика (PHP 8.2)       │
│  • Роутинг и контроллеры             │
│  • Обработка запросов                │
│  • OAuth авторизация                 │
│  • Валидация данных                  │
└─────────────────┬────────────────────┘
                  │ PDO
┌─────────────────▼────────────────────┐
│      Слой данных (PostgreSQL 12)     │
│  • Реляционная модель (3НФ)          │
│  • Foreign Keys                       │
│  • Индексы для оптимизации           │
└──────────────────────────────────────┘
```

**Контейнеризация:**
- Nginx (reverse proxy, статика)
- PHP-FPM (FastCGI Process Manager)
- PostgreSQL (изолированная БД)

---

## 🗄️ Слайд 3: Модель данных

**Схема базы данных:**

```sql
-- Режиссёры
CREATE TABLE directors (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    bio TEXT,
    photo_url VARCHAR(255)
);

-- Фильмы
CREATE TABLE movies (
    id SERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    year INTEGER NOT NULL,
    duration INTEGER NOT NULL,
    country VARCHAR(100),
    director_id INTEGER REFERENCES directors(id),
    poster_url VARCHAR(255),
    description TEXT,
    rating FLOAT
);

-- Жанры
CREATE TABLE genres (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT
);

-- Связь многие-ко-многим
CREATE TABLE movie_genres (
    movie_id INTEGER REFERENCES movies(id) ON DELETE CASCADE,
    genre_id INTEGER REFERENCES genres(id) ON DELETE CASCADE,
    PRIMARY KEY (movie_id, genre_id)
);

-- Индексы для оптимизации
CREATE INDEX idx_movies_title ON movies(title);
CREATE INDEX idx_movies_year ON movies(year);
CREATE INDEX idx_directors_name ON directors(name);
```

**Ключевые решения:**
- ✅ Нормализация до 3НФ (избежание дублирования)
- ✅ Foreign Keys (целостность данных)
- ✅ Cascade удаление (автоматическая очистка связей)
- ✅ Индексы на полях поиска (быстрые запросы)

---

## 🔍 Слайд 4: Поиск и фильтрация

**Живой поиск (AJAX):**

```javascript
// public/assets/js/search.js
let searchTimeout;

searchInput.addEventListener('input', (e) => {
    clearTimeout(searchTimeout);
    
    searchTimeout = setTimeout(() => {
        const query = e.target.value.trim();
        
        if (query.length < 2) {
            hideResults();
            return;
        }
        
        fetch(`/search.php?q=${encodeURIComponent(query)}`)
            .then(res => res.json())
            .then(data => displayResults(data));
    }, 300); // Debouncing - 300ms задержка
});
```

**SQL запрос с полнотекстовым поиском:**

```php
// public/search.php
$stmt = $pdo->prepare("
    SELECT DISTINCT 
        m.id, 
        m.title, 
        m.year, 
        d.name as director
    FROM movies m
    JOIN directors d ON m.director_id = d.id
    LEFT JOIN movie_genres mg ON m.id = mg.movie_id
    LEFT JOIN genres g ON mg.genre_id = g.id
    WHERE 
        LOWER(m.title) LIKE LOWER(:search)
        OR LOWER(d.name) LIKE LOWER(:search)
        OR LOWER(g.name) LIKE LOWER(:search)
    ORDER BY m.title
    LIMIT 10
");

$stmt->execute(['search' => "%$query%"]);
```

**Особенности:**
- Debouncing (уменьшение нагрузки)
- LIKE поиск по нескольким таблицам
- JSON ответ для фронтенда
- Ограничение результатов (LIMIT 10)

---

## 📄 Слайд 5: Пагинация

**Серверная пагинация:**

```php
// public/films.php
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 5; // Фильмов на страницу
$offset = ($page - 1) * $perPage;

// Получение общего количества
$totalStmt = $pdo->query("SELECT COUNT(*) FROM movies");
$total = $totalStmt->fetchColumn();
$totalPages = ceil($total / $perPage);

// Получение фильмов текущей страницы
$stmt = $pdo->prepare("
    SELECT m.*, d.name as director
    FROM movies m
    JOIN directors d ON m.director_id = d.id
    ORDER BY m.id
    LIMIT :limit OFFSET :offset
");

$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
```

**UI пагинации:**
```php
// Умная навигация (показывает 5 страниц)
$startPage = max(1, $page - 2);
$endPage = min($totalPages, $page + 2);

echo "<div class='pagination'>";
echo "<a href='?page=1'>«</a>"; // Первая

for ($i = $startPage; $i <= $endPage; $i++) {
    $active = ($i == $page) ? 'active' : '';
    echo "<a href='?page=$i' class='$active'>$i</a>";
}

echo "<a href='?page=$totalPages'>»</a>"; // Последняя
echo "</div>";
```

---

## 🔐 Слайд 6: OAuth авторизация (Yandex ID)

**Процесс авторизации:**

```
┌─────────┐                    ┌──────────┐                ┌─────────┐
│ Браузер │                    │ MovieP   │                │ Yandex  │
└────┬────┘                    └────┬─────┘                └────┬────┘
     │                              │                            │
     │ 1. Клик "Войти через Яндекс" │                           │
     ├────────────────────────────►│                            │
     │                              │                            │
     │                              │ 2. Redirect с client_id    │
     │                              ├──────────────────────────►│
     │                              │                            │
     │ 3. Форма авторизации Yandex  │                           │
     │◄─────────────────────────────┼────────────────────────────┤
     │                              │                            │
     │ 4. Ввод логина/пароля        │                           │
     ├──────────────────────────────┼────────────────────────────►
     │                              │                            │
     │ 5. Redirect с code           │                            │
     │◄─────────────────────────────┼────────────────────────────┤
     │                              │                            │
     │ 6. code отправлен на сервер  │                            │
     ├────────────────────────────►│                            │
     │                              │                            │
     │                              │ 7. Обмен code на token     │
     │                              ├──────────────────────────►│
     │                              │                            │
     │                              │ 8. Access token            │
     │                              │◄──────────────────────────┤
     │                              │                            │
     │                              │ 9. Запрос данных user      │
     │                              ├──────────────────────────►│
     │                              │                            │
     │                              │ 10. user info (email, id)  │
     │                              │◄──────────────────────────┤
     │                              │                            │
     │ 11. Создание сессии          │                            │
     │◄────────────────────────────┤                            │
```

**Код обработки callback:**

```php
// public/admin/yandex_callback.php
session_start();

$code = $_GET['code'] ?? null;
if (!$code) {
    die('Ошибка: код авторизации не получен');
}

// Обмен code на access_token
$tokenUrl = 'https://oauth.yandex.ru/token';
$postData = [
    'grant_type' => 'authorization_code',
    'code' => $code,
    'client_id' => YANDEX_CLIENT_ID,
    'client_secret' => YANDEX_CLIENT_SECRET
];

$ch = curl_init($tokenUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$tokenData = json_decode($response, true);

$accessToken = $tokenData['access_token'];

// Получение данных пользователя
$userUrl = 'https://login.yandex.ru/info';
$ch = curl_init($userUrl);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: OAuth ' . $accessToken
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$userResponse = curl_exec($ch);
$userData = json_decode($userResponse, true);

// Проверка прав администратора
if ($userData['default_email'] === ADMIN_EMAIL) {
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['admin_username'] = $userData['display_name'];
    $_SESSION['admin_email'] = $userData['default_email'];
    
    header('Location: /admin/index.php');
} else {
    header('Location: /errors/403.php');
}
```

---

## 🛡️ Слайд 7: Безопасность

**Защита от SQL-инъекций (PDO Prepared Statements):**

```php
// ❌ НЕБЕЗОПАСНО
$query = "SELECT * FROM movies WHERE id = " . $_GET['id'];
$result = $pdo->query($query);

// ✅ БЕЗОПАСНО
$stmt = $pdo->prepare("SELECT * FROM movies WHERE id = :id");
$stmt->execute(['id' => $_GET['id']]);
```

**Защита от XSS:**

```php
// Всегда экранируем вывод
echo htmlspecialchars($movie['title'], ENT_QUOTES, 'UTF-8');

// В шаблонах
<h1><?= htmlspecialchars($movie['title']) ?></h1>
```

**CSRF защита (для форм):**

```php
// Генерация токена
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// В форме
<input type="hidden" name="csrf_token" 
       value="<?= $_SESSION['csrf_token'] ?>">

// Проверка
if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die('CSRF token mismatch');
}
```

**Контроль доступа:**

```php
// public/admin/auth.php
function checkAdminRole() {
    if (!isset($_SESSION['admin_logged_in']) || 
        !$_SESSION['admin_logged_in']) {
        header('Location: /errors/403.php');
        exit;
    }
}

// В каждой админ-странице
require_once 'auth.php';
checkAdminRole();
```

---

## 🎨 Слайд 8: Адаптивный интерфейс

**Responsive Design (Mobile-First):**

```css
/* Базовые стили для мобильных */
.container {
    max-width: 1200px;
    display: flex;
}

.nav {
    width: 200px;
}

/* Планшеты (≤768px) */
@media (max-width: 768px) {
    .nav {
        position: fixed;
        left: -100%;
        width: 250px;
        transition: left 0.3s ease;
    }
    
    .nav.active {
        left: 0;
    }
    
    .movie-card {
        flex-direction: column;
    }
    
    .banner {
        flex-direction: column;
    }
}

/* Мобильные (≤480px) */
@media (max-width: 480px) {
    .logo {
        font-size: 24px;
    }
    
    .search-container {
        display: none; /* Скрыть на маленьких экранах */
    }
    
    .nav {
        width: 220px;
    }
    
    .movie-card img {
        max-width: 150px;
    }
}
```

**Гамбургер-меню (JavaScript):**

```javascript
// public/assets/js/script.js
const menuToggle = document.querySelector('.menu-toggle');
const nav = document.querySelector('.nav');

menuToggle?.addEventListener('click', () => {
    nav.classList.toggle('active');
});

// Закрытие при клике вне меню
document.addEventListener('click', (e) => {
    if (!nav.contains(e.target) && 
        !menuToggle.contains(e.target)) {
        nav.classList.remove('active');
    }
});
```

---

## 🚀 Слайд 9: SEO оптимизация

**1. Семантическая разметка:**

```html
<article itemscope itemtype="https://schema.org/Movie">
    <h1 itemprop="name">Inception</h1>
    <meta itemprop="datePublished" content="2010">
    <span itemprop="director">Christopher Nolan</span>
    <div itemprop="aggregateRating" itemscope 
         itemtype="https://schema.org/AggregateRating">
        <meta itemprop="ratingValue" content="8.8">
    </div>
</article>
```

**2. JSON-LD микроразметка:**

```php
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Movie",
  "name": "<?= htmlspecialchars($movie['title']) ?>",
  "datePublished": "<?= $movie['year'] ?>",
  "director": {
    "@type": "Person",
    "name": "<?= htmlspecialchars($movie['director']) ?>"
  },
  "aggregateRating": {
    "@type": "AggregateRating",
    "ratingValue": "<?= $movie['rating'] ?>"
  }
}
</script>
```

**3. Динамический sitemap:**

```php
// public/static/sitemap_dynamic.php
header('Content-Type: application/xml; charset=utf-8');

echo '<?xml version="1.0" encoding="UTF-8"?>';
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

// Главные страницы
$pages = ['main.php', 'films.php', 'genres.php', 'directors.php'];
foreach ($pages as $page) {
    echo "<url>";
    echo "<loc>http://localhost/$page</loc>";
    echo "<priority>1.0</priority>";
    echo "</url>";
}

// Все фильмы
$stmt = $pdo->query("SELECT id FROM movies");
while ($row = $stmt->fetch()) {
    echo "<url>";
    echo "<loc>http://localhost/film_page.php?movie_id={$row['id']}</loc>";
    echo "<priority>0.8</priority>";
    echo "</url>";
}

echo '</urlset>';
```

**4. Meta теги:**

```php
<meta name="description" content="<?= htmlspecialchars($description) ?>">
<meta property="og:title" content="<?= htmlspecialchars($title) ?>">
<meta property="og:description" content="<?= htmlspecialchars($description) ?>">
<meta property="og:image" content="<?= htmlspecialchars($poster) ?>">
```

---

## 📊 Слайд 10: Производительность

**Кэширование статики (Nginx):**

```nginx
# docker/nginx/nginx.conf
location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2)$ {
    expires 1y;
    add_header Cache-Control "public, immutable";
    access_log off;
}

location /assets/ {
    expires max;
    access_log off;
}
```

**Gzip сжатие:**

```nginx
gzip on;
gzip_vary on;
gzip_min_length 1000;
gzip_types text/plain text/css application/json 
           application/javascript text/xml 
           application/xml text/javascript;
```

**Оптимизация SQL:**

```sql
-- Индексы на часто используемых полях
CREATE INDEX idx_movies_title ON movies(title);
CREATE INDEX idx_movies_year ON movies(year);
CREATE INDEX idx_directors_name ON directors(name);

-- Составной индекс для movie_genres
CREATE INDEX idx_movie_genres_movie ON movie_genres(movie_id);
CREATE INDEX idx_movie_genres_genre ON movie_genres(genre_id);
```

**Lazy loading изображений:**

```html
<img src="placeholder.jpg" 
     data-src="<?= $movie['poster_url'] ?>" 
     loading="lazy"
     alt="<?= htmlspecialchars($movie['title']) ?>">
```

---

## 🐳 Слайд 11: Docker инфраструктура

**docker-compose.yml:**

```yaml
version: '3.8'

services:
  nginx:
    build: ./docker/nginx
    ports:
      - "80:80"
    volumes:
      - ./public:/var/www/html/public
    depends_on:
      - php

  php:
    build: ./docker/php
    volumes:
      - ./public:/var/www/html/public
    environment:
      - DB_HOST=postgres
      - DB_PORT=5432

  postgres:
    image: postgres:12
    environment:
      POSTGRES_DB: movieportal
      POSTGRES_USER: admin
      POSTGRES_PASSWORD: secret
    volumes:
      - ./sql/admin_setup.sql:/docker-entrypoint-initdb.d/init.sql
    ports:
      - "5432:5432"
```

**Dockerfile (PHP):**

```dockerfile
FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

WORKDIR /var/www/html
```

**Запуск проекта:**

```bash
# Сборка и запуск
docker-compose up -d --build

# Остановка
docker-compose down

# Логи
docker-compose logs -f php
```

---

## 📱 Слайд 12: Административная панель

**Функционал:**
- ✅ CRUD операции для фильмов
- ✅ CRUD операции для режиссёров
- ✅ CRUD операции для жанров
- ✅ Связывание фильмов с жанрами (многие-ко-многим)
- ✅ OAuth авторизация (Yandex, Telegram, VK)
- ✅ Защита от несанкционированного доступа

**Пример CRUD (создание фильма):**

```php
// public/admin/movies.php
if (isset($_POST['create_movie'])) {
    try {
        $pdo->beginTransaction();
        
        // 1. Создаём фильм
        $stmt = $pdo->prepare("
            INSERT INTO movies 
            (title, year, duration, country, director_id, 
             poster_url, description, rating)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $_POST['title'],
            $_POST['year'],
            $_POST['duration'],
            $_POST['country'],
            $_POST['director_id'],
            $_POST['poster_url'],
            $_POST['description'],
            $_POST['rating']
        ]);
        
        $movieId = $pdo->lastInsertId();
        
        // 2. Добавляем жанры
        if (!empty($_POST['genres'])) {
            $genreStmt = $pdo->prepare("
                INSERT INTO movie_genres (movie_id, genre_id) 
                VALUES (?, ?)
            ");
            
            foreach ($_POST['genres'] as $genreId) {
                $genreStmt->execute([$movieId, $genreId]);
            }
        }
        
        $pdo->commit();
        $message = "Фильм успешно добавлен!";
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        $message = "Ошибка: " . $e->getMessage();
    }
}
```

---

## 📈 Слайд 13: Статистика проекта

**Код проекта:**
- 📄 **15+ PHP файлов** (backend логика)
- 🎨 **8 CSS файлов** (стили и адаптивность)
- ⚡ **4 JavaScript файла** (интерактивность)
- 🗄️ **4 таблицы БД** (реляционная модель)

**Функциональность:**
- 🎬 **Каталог фильмов** с поиском и пагинацией
- 🔍 **Живой поиск** (AJAX + debouncing)
- 👤 **3 OAuth провайдера** (Yandex, Telegram, VK)
- 🛡️ **Админ-панель** с полным CRUD
- 📱 **Responsive дизайн** (768px, 480px breakpoints)
- 🔐 **Безопасность**: PDO, XSS защита, CSRF токены

**SEO:**
- ✅ Schema.org микроразметка
- ✅ Динамический sitemap.xml
- ✅ Meta теги (Open Graph)
- ✅ Семантический HTML5
- ✅ robots.txt

---

## 🎯 Слайд 14: Демонстрация работы

**Основной функционал:**

1. **Главная страница** (main.php)
   - Баннеры популярных жанров
   - Последние добавленные фильмы
   - Навигация по разделам

2. **Каталог фильмов** (films.php)
   - Полный список с пагинацией (5 на страницу)
   - Сортировка по ID
   - Постеры, название, год, режиссёр

3. **Страница фильма** (film_page.php)
   - Детальная информация
   - Рейтинг, жанры, страна
   - Schema.org разметка

4. **Поиск**
   - Автодополнение при вводе
   - Поиск по названию/режиссёру/жанру
   - Debouncing 300ms

5. **Админ-панель** (/admin/)
   - OAuth авторизация
   - Управление фильмами/режиссёрами/жанрами
   - Таблицы с действиями (редактировать/удалить)

---

## ✨ Слайд 15: Выводы и перспективы

**Достижения проекта:**
- ✅ Полнофункциональное веб-приложение
- ✅ Современный технологический стек
- ✅ Безопасная работа с данными (PDO, OAuth)
- ✅ Оптимизация для SEO и производительности
- ✅ Адаптивный дизайн для всех устройств
- ✅ Контейнеризация (Docker Compose)

**Возможности для развития:**
- 🎯 REST API для мобильных приложений
- 🎯 Система рецензий и комментариев
- 🎯 Интеграция с внешними API (TMDB, Kinopoisk)
- 🎯 Рекомендательная система (ML)
- 🎯 Elasticsearch для полнотекстового поиска
- 🎯 Redis для кэширования запросов
- 🎯 CI/CD pipeline (GitHub Actions)

**Заключение:**
MoviePortal демонстрирует применение современных веб-технологий для создания масштабируемого и безопасного приложения с акцентом на качество кода и архитектуру.

---

**Спасибо за внимание!**

**Вопросы?**
