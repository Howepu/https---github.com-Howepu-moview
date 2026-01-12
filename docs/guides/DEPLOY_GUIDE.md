# 🚀 Инструкция по деплою MoviePortal

## 🎯 Самый простой способ - Railway.app (РЕКОМЕНДУЮ)

### Преимущества:
- ✅ Бесплатно (500 часов/месяц)
- ✅ Автоматический деплой из GitHub
- ✅ Поддержка Docker Compose
- ✅ Бесплатный домен (*.up.railway.app)
- ✅ Автоматический SSL сертификат
- ✅ PostgreSQL из коробки

---

## 📝 Шаг 1: Подготовка проекта

### 1.1 Создайте файл .dockerignore

```bash
# В C:\labs\ создайте файл .dockerignore
node_modules
.git
.env
*.md
*.log
```

### 1.2 Проверьте docker-compose.yml

Убедитесь, что PostgreSQL использует volume для данных:

```yaml
services:
  postgres:
    image: postgres:15
    environment:
      POSTGRES_DB: movies_db
      POSTGRES_USER: postgres
      POSTGRES_PASSWORD: ${POSTGRES_PASSWORD:-postgres}
    volumes:
      - postgres_data:/var/lib/postgresql/data
      - ./postgres/init.sql:/docker-entrypoint-initdb.d/init.sql

volumes:
  postgres_data:
```

---

## 📝 Шаг 2: Создание GitHub репозитория

### 2.1 Инициализируйте Git (если еще не сделано)

```powershell
cd C:\labs

# Инициализация
git init

# Добавить все файлы
git add .

# Первый коммит
git commit -m "Initial commit: MoviePortal"
```

### 2.2 Создайте репозиторий на GitHub

1. Зайдите на https://github.com
2. Нажмите "New repository"
3. Название: `movieportal`
4. Выберите "Public" или "Private"
5. НЕ создавайте README
6. Нажмите "Create repository"

### 2.3 Свяжите локальный проект с GitHub

```powershell
# Замените YOUR_USERNAME на ваш username
git remote add origin https://github.com/YOUR_USERNAME/movieportal.git

# Отправьте код
git branch -M main
git push -u origin main
```

---

## 📝 Шаг 3: Деплой на Railway.app

### 3.1 Регистрация

1. Откройте https://railway.app/
2. Нажмите "Start a New Project"
3. Войдите через GitHub

### 3.2 Создание проекта

1. Нажмите "New Project"
2. Выберите "Deploy from GitHub repo"
3. Выберите свой репозиторий `movieportal`
4. Railway автоматически найдет Docker Compose

### 3.3 Настройка сервисов

Railway создаст 3 сервиса автоматически:
- `nginx`
- `php`
- `postgres`

**Для каждого сервиса:**

#### Сервис `postgres`:
1. Перейдите в настройки
2. Добавьте переменные окружения:
   ```
   POSTGRES_DB=movies_db
   POSTGRES_USER=postgres
   POSTGRES_PASSWORD=your_secure_password_here
   ```
3. Railway автоматически создаст внутренний URL

#### Сервис `php`:
1. Настройки → Variables
2. Добавьте:
   ```
   DB_HOST=postgres.railway.internal
   DB_NAME=movies_db
   DB_USER=postgres
   DB_PASSWORD=your_secure_password_here
   ```

#### Сервис `nginx`:
1. Settings → Networking
2. Нажмите "Generate Domain"
3. Получите URL типа: `movieportal-production.up.railway.app`

### 3.4 Деплой

1. Railway автоматически начнет деплой
2. Следите за логами в разделе "Deployments"
3. Дождитесь статуса "Success"

---

## 📝 Шаг 4: Обновление конфигурации для продакшена

### 4.1 Обновите config.php для Railway

Создайте версию для продакшена:

```php
<?php
// public/config.php
$host = getenv('DB_HOST') ?: 'postgres';
$dbname = getenv('DB_NAME') ?: 'movies_db';
$user = getenv('DB_USER') ?: 'postgres';
$password = getenv('DB_PASSWORD') ?: 'postgres';

try {
    $pdo = new PDO(
        "pgsql:host=$host;dbname=$dbname",
        $user,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("Database connection failed");
}
?>
```

### 4.2 Обновите nginx.conf для продакшена

```nginx
server {
    listen 80;
    server_name _;
    
    root /var/www/html/public;
    index main.php;

    # Gzip
    gzip on;
    gzip_types text/css application/javascript text/xml application/xml;
    
    location / {
        try_files $uri $uri/ =404;
    }

    # Cache static
    location ~* \.(css|js|jpg|png|svg|ico)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    location ~ \.php$ {
        fastcgi_pass php:9000;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
    
    error_page 404 /404.php;
}
```

### 4.3 Закоммитьте изменения

```powershell
git add .
git commit -m "Update config for production"
git push origin main
```

Railway автоматически задеплоит новую версию!

---

## 📝 Шаг 5: Настройка аналитики

### 5.1 Зарегистрируйте в Яндекс.Метрике

1. Откройте https://metrika.yandex.ru/
2. Добавьте сайт: `your-app.up.railway.app`
3. Получите ID счетчика (например: 94832001)

### 5.2 Зарегистрируйте в Google Analytics

1. Откройте https://analytics.google.com/
2. Создайте ресурс
3. Получите ID (например: G-ABC123DEF4)

### 5.3 Обновите analytics.php

```php
<?php
// public/includes/analytics.php
?>
<!-- Яндекс.Метрика -->
<script type="text/javascript">
   (function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
   m[i].l=1*new Date();
   for (var j = 0; j < document.scripts.length; j++) {if (document.scripts[j].src === r) { return; }}
   k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)})
   (window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym");

   ym(94832001, "init", {
        clickmap:true,
        trackLinks:true,
        accurateTrackBounce:true,
        webvisor:true
   });
</script>
<noscript><div><img src="https://mc.yandex.ru/watch/94832001" style="position:absolute; left:-9999px;" alt="" /></div></noscript>

<!-- Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-ABC123DEF4"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', 'G-ABC123DEF4');
</script>
```

### 5.4 Обновите sitemap.xml

Замените localhost на ваш домен:

```xml
<loc>https://your-app.up.railway.app/main.php</loc>
```

### 5.5 Закоммитьте

```powershell
git add .
git commit -m "Add analytics IDs"
git push origin main
```

---

## ✅ Шаг 6: Проверка деплоя и миграций

### 6.1 Откройте ваш сайт

```
https://your-app.up.railway.app
```

### 6.2 Проверьте миграции

**Миграции запускаются автоматически!** При первом обращении к любой странице:

1. Откройте: `https://your-app.up.railway.app/check_migrations.php`
2. Вы увидите статус миграций:
   - ✅ Успешно - все таблицы созданы, данные загружены
   - ❌ Ошибка - проверьте логи Railway

### 6.3 Проверьте работу приложения:

- [ ] Главная страница: `/main.php`
- [ ] Каталог фильмов: `/films.php`
- [ ] Страница фильма: `/film_page.php?id=1`
- [ ] Поиск работает
- [ ] Админ-панель: `/admin/index.php`

### 6.4 Проверьте SEO файлы:

```
https://your-app.up.railway.app/robots.txt
https://your-app.up.railway.app/sitemap.xml
https://your-app.up.railway.app/favicon.svg
```

### 6.5 Проверьте логи Railway (если есть проблемы):

1. Dashboard → сервис `php` → Logs
2. Ищите строки:
   ```
   MoviePortal: Запуск миграций базы данных...
   MoviePortal: Миграции выполнены успешно! Фильмов: 18, Режиссёров: 13
   ```

---

## 🔧 Альтернатива: Render.com

Если Railway не подходит:

### 1. Регистрация
https://render.com/

### 2. Создание проекта
- New → Web Service
- Connect repository
- Build command: `docker-compose build`
- Start command: `docker-compose up`

### 3. База данных
- New → PostgreSQL
- Скопируйте Internal Database URL
- Добавьте как переменную окружения

---

## 🔧 Альтернатива: Heroku

### 1. Установка CLI

```powershell
# Скачайте с https://devcenter.heroku.com/articles/heroku-cli
```

### 2. Деплой

```powershell
heroku login
heroku create movieportal-app
heroku addons:create heroku-postgresql:mini
git push heroku main
```

---

## 📊 После деплоя

### Добавьте в Google Search Console:
1. https://search.google.com/search-console
2. Добавьте свойство
3. Отправьте sitemap.xml

### Добавьте в Яндекс.Вебмастер:
1. https://webmaster.yandex.ru/
2. Добавьте сайт
3. Отправьте sitemap

---

## 🐛 Troubleshooting

### Проблема: База данных не подключается
**Решение:**
```powershell
# Проверьте логи в Railway
# Settings → Logs
```

### Проблема: PHP ошибки
**Решение:**
Добавьте в nginx.conf:
```nginx
fastcgi_param PHP_VALUE "display_errors=On";
```

### Проблема: Медленная загрузка
**Решение:**
- Проверьте gzip в nginx
- Оптимизируйте изображения
- Добавьте кэширование

---

## 💰 Стоимость

### Railway.app:
- ✅ **Бесплатно**: 500 часов/месяц
- ✅ После этого: ~$5/месяц

### Render.com:
- ✅ **Бесплатно**: базовый tier
- Ограничение: спит после 15 мин неактивности

### Heroku:
- ⚠️ **Платно**: от $5/месяц (PostgreSQL)

---

## 🎯 Быстрый старт (5 минут)

```powershell
# 1. Git
git init
git add .
git commit -m "Initial commit"

# 2. GitHub
# Создать репозиторий на github.com
git remote add origin https://github.com/USERNAME/movieportal.git
git push -u origin main

# 3. Railway
# Зайти на railway.app
# Deploy from GitHub → выбрать репозиторий
# Дождаться деплоя
# Generate Domain

# 4. Получить URL
# your-app.up.railway.app

# 5. Зарегистрировать счетчики
# Яндекс.Метрика + Google Analytics

# ГОТОВО! 🚀
```

---

## ✅ Чеклист деплоя

- [ ] Git репозиторий создан
- [ ] Код загружен на GitHub
- [ ] Railway аккаунт создан
- [ ] Проект задеплоен
- [ ] Домен получен
- [ ] База данных работает
- [ ] Яндекс.Метрика настроена
- [ ] Google Analytics настроен
- [ ] sitemap.xml обновлен
- [ ] Search Console настроен
- [ ] Вебмастер настроен

---

**Начинайте с Railway - это проще всего! 🚀**

**Если нужна помощь на конкретном шаге - спрашивайте!**
