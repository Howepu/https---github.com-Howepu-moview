# MoviePortal - Структура проекта

##  Структура директорий

\\\
c:\labs\
 docs/                      # Документация проекта
    guides/               # Руководства по настройке (OAuth, Deploy, Analytics)
    presentations/        # Презентации для защиты проекта
    reports/              # Отчёты, аудиты, чек-листы

 sql/                      # SQL скрипты миграций и настройки
    admin_setup.sql
    telegram_oauth_setup.sql
    vk_oauth_setup.sql

 scripts/                  # Утилитные скрипты (init, test, fix)
    init_admin.php
    init_telegram_system.php
    init_vk_system.php
    fix_admin_password.php
    test_db.php
    check_migrations.php

 docker/                   # Docker конфигурация
    nginx/               # Nginx конфигурация
    php/                 # PHP-FPM Dockerfile
    postgres/            # PostgreSQL init скрипты

 public/                   # Публичные файлы веб-сайта
     admin/               # Админ-панель
     assets/              # Статические ресурсы (CSS, JS, images)
     includes/            # Переиспользуемые компоненты
     config.php           # Конфигурация БД
     *.php                # Страницы сайта
     robots.txt           # SEO
     sitemap_dynamic.php  # Динамическая карта сайта
     favicon.svg          # Иконка сайта
\\\

##  Быстрый старт

\\\ash
# Запуск проекта
docker-compose up -d

# Инициализация БД и админа (выполнить один раз)
http://localhost/scripts/init_admin.php

# Проверка подключения к БД
http://localhost/scripts/test_db.php
\\\

##  Документация

- **Гайды:** \docs/guides/\ - настройка OAuth, деплой на VPS, аналитика
- **Презентации:** \docs/presentations/\ - материалы для защиты проекта
- **Отчёты:** \docs/reports/\ - UX аудит, чек-листы, итоговые отчёты

##  Утилиты

Все служебные скрипты находятся в \scripts/\ и доступны по URL:
- http://localhost/scripts/init_admin.php - инициализация администратора
- http://localhost/scripts/test_db.php - тест подключения к БД
- http://localhost/scripts/fix_admin_password.php - сброс пароля админа



---

#  Структура public/

\\\
public/
 admin/                    # Админ-панель
    *.php                # Страницы админки
    admin-styles.css     # Стили админки

 assets/                   # Статические ресурсы
    css/                 # Все CSS файлы
       styles.css       # Основные стили
       films_style.css
       directors_style.css
       ...              # Остальные стили страниц
    js/                  # Все JS файлы
       loader.js
       search.js
       script.js
    images/              # Изображения

 includes/                 # Переиспользуемые компоненты
    header.php
    analytics.php
    migrations.php

 errors/                   # Страницы ошибок
    403.php              # Доступ запрещён
    404.php              # Страница не найдена

 static/                   # SEO и статика
    robots.txt           # Индексация
    sitemap.xml          # Статичная карта
    sitemap_dynamic.php  # Динамическая карта
    favicon.svg          # Иконка сайта

 templates/                # Шаблоны (если есть)

 config.php               # Конфигурация БД
 index.php                # Редирект на main.php
 main.php                 # Главная страница
 films.php                # Каталог фильмов
 film_page.php            # Страница фильма
 genres.php               # Жанры
 directors.php            # Режиссёры
 films_by_genres.php      # Фильмы по жанру
 films_by_directors.php   # Фильмы режиссёра
 search.php               # Поиск (AJAX)
 help.php                 # Справка
\\\

##  Улучшения структуры

- **CSS**  \ssets/css/\ (вместо корня public/)
- **JS**  \ssets/js/\ (вместо корня public/)
- **Ошибки**  \errors/\ (403.php, 404.php)
- **SEO**  \static/\ (robots.txt, sitemap, favicon)
- **Админка**  \dmin/\ (изолирована)
- **PHP страницы**  в корне public/ (для удобства URL)
