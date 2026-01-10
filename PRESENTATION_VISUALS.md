# 📸 Визуальные материалы для презентации

## 🎨 Что нужно подготовить для слайдов

### Слайд 1: Титульный
```
┌─────────────────────────────────┐
│                                 │
│        🎬 MoviePortal           │
│   Веб-каталог фильмов с OAuth   │
│                                 │
│   Выполнил: [ФИО]              │
│   Группа: [Номер]              │
│   Дата: 10.01.2026             │
│                                 │
└─────────────────────────────────┘
```

### Слайд 2: Цель и задачи
```
Цель: Разработка веб-приложения для каталогизации фильмов

Задачи:
✓ База данных (PostgreSQL)
✓ Поиск и фильтрация
✓ Админ-панель (CRUD)
✓ OAuth (Telegram, VK, Yandex)
✓ SEO оптимизация
```

### Слайд 3: Технологический стек

**ДИАГРАММА архитектуры:**
```
    ┌──────────┐
    │  Client  │
    │ Browser  │
    └────┬─────┘
         │ HTTP
    ┌────▼─────┐
    │  Nginx   │ ← Reverse Proxy
    └────┬─────┘
         │
    ┌────▼─────┐
    │ PHP-FPM  │ ← Business Logic
    │  PHP 8   │
    └────┬─────┘
         │
    ┌────▼─────┐
    │PostgreSQL│ ← Data Storage
    └──────────┘
```

**Таблица технологий:**
```
┌──────────┬────────────────────┐
│ Backend  │ PHP 8, PostgreSQL  │
│ Frontend │ HTML5, CSS3, JS    │
│ DevOps   │ Docker, Nginx      │
│ Auth     │ OAuth 2.0          │
└──────────┴────────────────────┘
```

### Слайд 4: База данных

**ER-диаграмма (для PowerPoint/Draw.io):**
```
┌─────────────────┐
│   DIRECTORS     │
│─────────────────│
│ • id (PK)       │
│ • name          │
│ • bio           │
└────────┬────────┘
         │
         │ 1:N
         │
┌────────▼────────┐          ┌──────────────┐
│    MOVIES       │ N:M      │ MOVIE_GENRES │
│─────────────────│◄─────────┤──────────────│
│ • id (PK)       │          │ • movie_id   │
│ • title         │          │ • genre_id   │
│ • year          │          └──────┬───────┘
│ • director_id(FK)│                │ N:M
│ • rating        │                 │
│ • poster_url    │          ┌──────▼───────┐
└─────────────────┘          │   GENRES     │
                             │──────────────│
                             │ • id (PK)    │
                             │ • name       │
                             └──────────────┘
```

**Код для слайда:**
```sql
CREATE TABLE movies (
    id SERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    year INTEGER,
    director_id INTEGER REFERENCES directors(id),
    rating DECIMAL(3,1)
);

CREATE INDEX idx_movies_title ON movies(title);
```

### Слайд 5: Backend - Безопасность и поиск

**Код 1: Безопасное подключение**
```php
// PDO с prepared statements
$pdo = new PDO(
    "pgsql:host=$host;dbname=$dbname",
    $user, 
    $password,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

// Параметризованный запрос
$stmt = $pdo->prepare("SELECT * FROM movies WHERE id = ?");
$stmt->execute([$id]);
```

**Код 2: AJAX поиск**
```php
// search.php - Real-time поиск
$query = $_GET['q'] ?? '';

$stmt = $pdo->prepare("
    SELECT m.*, d.name AS director
    FROM movies m
    JOIN directors d ON m.director_id = d.id
    WHERE m.title ILIKE ? OR d.name ILIKE ?
    LIMIT 10
");

$stmt->execute(["%$query%", "%$query%"]);
echo json_encode($stmt->fetchAll());
```

**Код 3: OAuth**
```php
// Telegram OAuth callback
if (checkTelegramAuthorization($_GET)) {
    $_SESSION['user'] = [
        'id' => $_GET['id'],
        'name' => $_GET['first_name']
    ];
}
```

### Слайд 6: Frontend - JavaScript

**Код 1: Живой поиск с debounce**
```javascript
// search.js
let searchTimeout;

searchInput.addEventListener('input', (e) => {
    clearTimeout(searchTimeout);
    
    searchTimeout = setTimeout(() => {
        fetch(`search.php?q=${e.target.value}`)
            .then(res => res.json())
            .then(data => displayResults(data));
    }, 300); // Debounce 300ms
});
```

**Код 2: Адаптивность**
```css
/* Mobile First */
.movie-card {
    width: 100%;
}

@media (min-width: 768px) {
    .movie-card { width: 48%; }
}

@media (min-width: 1200px) {
    .movie-card { width: 23%; }
}
```

### Слайд 7: Админ-панель

**Код 1: Защита доступа**
```php
// admin/auth.php
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

// CSRF защита
if ($_POST['token'] !== $_SESSION['csrf_token']) {
    die('Invalid CSRF token');
}
```

**Код 2: CRUD операции**
```php
// Добавление фильма
if ($_POST['action'] === 'add') {
    $stmt = $pdo->prepare("
        INSERT INTO movies 
        (title, year, director_id, rating)
        VALUES (?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $_POST['title'],
        $_POST['year'],
        $_POST['director_id'],
        $_POST['rating']
    ]);
}
```

### Слайд 8: Docker

**docker-compose.yml:**
```yaml
version: '3.8'

services:
  nginx:
    build: ./nginx
    ports:
      - "80:80"
    depends_on:
      - php
    
  php:
    build: ./php
    volumes:
      - ./public:/var/www/html/public
    
  postgres:
    image: postgres:15
    environment:
      POSTGRES_DB: movies_db
    volumes:
      - ./postgres/init.sql:/docker-entrypoint-initdb.d/
```

**Команды:**
```bash
# Запуск всей инфраструктуры
docker-compose up -d

# Просмотр логов
docker-compose logs -f
```

### Слайд 9: Оптимизация

**nginx.conf:**
```nginx
# Gzip сжатие
gzip on;
gzip_types text/css application/javascript text/xml;

# Кэширование статики
location ~* \.(css|js|jpg|png|svg)$ {
    expires 1y;
    add_header Cache-Control "public, immutable";
}
```

**Список SEO:**
```
✓ robots.txt
✓ sitemap.xml  
✓ Meta теги (title, description)
✓ Open Graph
✓ Favicon
✓ Semantic HTML5
```

### Слайд 10: Результаты

**Метрики проекта:**
```
┌─────────────────────┬──────┐
│ PHP файлы           │ 20+  │
│ JavaScript файлы    │ 4    │
│ CSS файлы           │ 8    │
│ Таблицы БД          │ 4    │
│ Страницы            │ 15+  │
│ OAuth провайдеры    │ 3    │
└─────────────────────┴──────┘
```

**Функционал:**
```
✓ Real-time поиск
✓ CRUD операции
✓ OAuth авторизация
✓ Адаптивный дизайн
✓ SEO оптимизация
✓ Docker контейнеризация
```

### Слайд 11: Демонстрация

**Скриншоты для вставки:**

1. **Главная страница**
   - Должно быть видно: шапку, поиск, карточки фильмов
   - Аннотация: "Главная страница с каталогом"

2. **Живой поиск**
   - Поле поиска с введенным текстом
   - Выпадающий список результатов
   - Аннотация: "Real-time поиск без перезагрузки"

3. **Страница фильма**
   - Постер, описание, рейтинг
   - Аннотация: "Детальная информация о фильме"

4. **Админ-панель**
   - Таблица с фильмами
   - Кнопки редактирования
   - Аннотация: "Административная панель (CRUD)"

5. **Мобильная версия**
   - На смартфоне (или DevTools)
   - Аннотация: "Адаптивный дизайн"

### Слайд 12: Заключение

```
Достигнутые цели:
✓ Полнофункциональная система
✓ Безопасная работа с БД
✓ Современные технологии
✓ Готовность к продакшену

Возможности развития:
→ REST API
→ Рекомендательная система (ML)
→ Интеграция с IMDB/Kinopoisk
→ Система комментариев
→ Мобильное приложение
```

---

## 🎨 Рекомендации по дизайну

### Цветовая схема:
```
Фон:       #FFFFFF (белый)
Заголовки: #2C3E50 (темно-синий)
Акценты:   #3498DB (синий)
Код:       #F8F9FA (светло-серый фон)
Текст кода:#2ECC71 (зеленый)
```

### Шрифты:
- **Заголовки:** Arial Bold, 32-40pt
- **Текст:** Arial Regular, 18-24pt
- **Код:** Consolas/Monaco, 14-16pt

### Структура слайда:
```
┌────────────────────────────────┐
│ [Заголовок]                    │
│                                │
│ [Код или диаграмма]            │
│                                │
│ [Ключевые пункты]              │
└────────────────────────────────┘
```

---

## 📊 Инструменты для создания

### Презентация:
- **PowerPoint** - классика
- **Google Slides** - онлайн
- **Canva** - красивые шаблоны
- **Keynote** - для Mac

### Диаграммы:
- **Draw.io** (https://app.diagrams.net/)
- **Lucidchart**
- **dbdiagram.io** - для БД
- **Mermaid** - код в markdown

### Скриншоты:
- **Снимок экрана** (Win + Shift + S)
- **Lightshot** - удобный редактор
- **ShareX** - мощный инструмент

### Код с подсветкой:
- **Carbon** (https://carbon.now.sh/)
- **Ray.so** (https://ray.so/)
- Встроенная в IDE

---

## ✅ Чеклист подготовки слайдов

**Слайд 1:**
- [ ] Название проекта
- [ ] ФИО и группа
- [ ] Логотип/иконка (опционально)

**Слайд 2:**
- [ ] Четкая цель
- [ ] 5 основных задач

**Слайд 3:**
- [ ] Схема архитектуры
- [ ] Список технологий

**Слайд 4:**
- [ ] ER-диаграмма
- [ ] SQL код

**Слайд 5-7:**
- [ ] Подсвеченный код PHP
- [ ] Комментарии к коду
- [ ] Акцент на безопасности

**Слайд 8:**
- [ ] docker-compose.yml
- [ ] Схема контейнеров

**Слайд 9:**
- [ ] nginx.conf
- [ ] Список SEO

**Слайд 10:**
- [ ] Цифры и метрики
- [ ] Таблица результатов

**Слайд 11:**
- [ ] 5 скриншотов
- [ ] Аннотации к ним

**Слайд 12:**
- [ ] Выводы
- [ ] Возможности развития
- [ ] "Спасибо за внимание!"

---

## 🚀 Готовые шаблоны

### Шаблон слайда с кодом:
```
┌────────────────────────────────────────┐
│  Заголовок: Backend - Безопасность     │
│                                        │
│  ╔════════════════════════════╗       │
│  ║ // PDO prepared statements ║       │
│  ║ $stmt = $pdo->prepare(...);║       │
│  ║ $stmt->execute([$param]);  ║       │
│  ╚════════════════════════════╝       │
│                                        │
│  ✓ Защита от SQL-injection             │
│  ✓ Параметризованные запросы           │
│  ✓ PDO::ERRMODE_EXCEPTION              │
└────────────────────────────────────────┘
```

### Шаблон слайда с метриками:
```
┌────────────────────────────────────────┐
│  Результаты проекта                    │
│                                        │
│  ┌──────────┐  ┌──────────┐          │
│  │   20+    │  │    4     │          │
│  │ PHP files│  │ Tables   │          │
│  └──────────┘  └──────────┘          │
│                                        │
│  ┌──────────┐  ┌──────────┐          │
│  │    3     │  │   15+    │          │
│  │  OAuth   │  │  Pages   │          │
│  └──────────┘  └──────────┘          │
└────────────────────────────────────────┘
```

---

**Презентация готова к созданию! 🎨**
