# 🚀 Деплой MoviePortal на собственный сервер (VPS)

## 📋 Что вам понадобится

- ✅ Арендованный сервер (VPS)
- ✅ SSH доступ (логин, пароль или ключ)
- ✅ Доменное имя (опционально, можно по IP)
- ✅ Ubuntu/Debian Linux (рекомендуется)

---

## 🎯 Быстрый старт (5 команд)

```bash
# 1. Подключитесь к серверу
ssh root@your-server-ip

# 2. Установите Docker
curl -fsSL https://get.docker.com -o get-docker.sh && sh get-docker.sh

# 3. Клонируйте проект
git clone https://github.com/YOUR_USERNAME/movieportal.git && cd movieportal

# 4. Запустите
docker-compose up -d

# 5. Откройте в браузере
# http://your-server-ip
```

---

## 📝 Пошаговая инструкция

### Шаг 1: Получите данные от хостинга

После аренды сервера вы получите:
```
IP адрес: 123.45.67.89
Логин: root (или ubuntu)
Пароль: ваш_пароль
```

Или SSH ключ (файл `.pem` или `.ppk`)

---

### Шаг 2: Подключитесь к серверу

#### Способ 1: Через PowerShell (Windows)

```powershell
# Если есть пароль:
ssh root@123.45.67.89
# Введите пароль

# Если есть SSH ключ:
ssh -i "путь\к\ключу.pem" root@123.45.67.89
```

#### Способ 2: Через PuTTY (Windows)

1. Скачайте PuTTY: https://www.putty.org/
2. Откройте PuTTY
3. Host Name: `root@123.45.67.89`
4. Port: `22`
5. Connection type: SSH
6. Нажмите "Open"
7. Введите пароль

---

### Шаг 3: Обновите систему

```bash
# Обновление пакетов
apt update && apt upgrade -y

# Установка базовых утилит
apt install -y curl git nano ufw
```

---

### Шаг 4: Установите Docker и Docker Compose

```bash
# Установка Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sh get-docker.sh

# Проверка установки
docker --version
# Должно вывести: Docker version 24.x.x

# Установка Docker Compose (v2)
apt install -y docker-compose-plugin

# Проверка
docker compose version
# Должно вывести: Docker Compose version v2.x.x
```

---

### Шаг 5: Загрузите проект на сервер

#### Вариант А: Через Git (РЕКОМЕНДУЕТСЯ)

```bash
# Клонируйте ваш репозиторий
cd /var/www
git clone https://github.com/YOUR_USERNAME/movieportal.git
cd movieportal

# Если репозиторий приватный, введите токен при запросе
```

#### Вариант Б: Через SCP (если нет Git репозитория)

**На вашем локальном компьютере (Windows PowerShell):**

```powershell
# Из папки C:\labs
scp -r C:\labs root@123.45.67.89:/var/www/movieportal
```

---

### Шаг 6: Настройте переменные окружения

```bash
cd /var/www/movieportal

# Создайте .env файл (опционально)
nano .env
```

Содержимое `.env`:
```env
DB_HOST=postgres
DB_NAME=movies_db
DB_USER=postgres
DB_PASSWORD=your_secure_password_here
```

**Сохраните:** `Ctrl+O`, Enter, `Ctrl+X`

---

### Шаг 7: Обновите docker-compose.yml для продакшена

```bash
nano docker-compose.yml
```

Добавьте порты для nginx:
```yaml
services:
  nginx:
    build: ./nginx
    ports:
      - "80:80"
      - "443:443"  # Для SSL
    depends_on:
      - php
    restart: always
    
  php:
    build: ./php
    volumes:
      - ./public:/var/www/html/public
    restart: always
    
  postgres:
    image: postgres:15
    environment:
      POSTGRES_DB: movies_db
      POSTGRES_USER: postgres
      POSTGRES_PASSWORD: your_secure_password_here
    volumes:
      - postgres_data:/var/lib/postgresql/data
    restart: always

volumes:
  postgres_data:
```

**Сохраните:** `Ctrl+O`, Enter, `Ctrl+X`

---

### Шаг 8: Настройте фаервол

```bash
# Разрешаем SSH, HTTP и HTTPS
ufw allow 22/tcp
ufw allow 80/tcp
ufw allow 443/tcp

# Включаем фаервол
ufw enable

# Проверяем статус
ufw status
```

---

### Шаг 9: Запустите приложение

```bash
cd /var/www/movieportal

# Сборка и запуск
docker compose up -d --build

# Проверка статуса
docker compose ps

# Должны увидеть 3 контейнера: nginx, php, postgres
```

---

### Шаг 10: Проверьте работу

```bash
# Откройте в браузере
http://123.45.67.89

# Проверьте миграции
http://123.45.67.89/check_migrations.php

# Проверьте логи при ошибках
docker compose logs -f php
docker compose logs -f nginx
```

---

## 🌐 Настройка доменного имени (опционально)

### Шаг 1: Настройте DNS у регистратора домена

Зайдите в панель управления доменом и добавьте A-запись:

```
Type: A
Name: @
Value: 123.45.67.89
TTL: 3600

Type: A
Name: www
Value: 123.45.67.89
TTL: 3600
```

Подождите 10-30 минут для распространения DNS.

### Шаг 2: Обновите nginx.conf

```bash
nano /var/www/movieportal/nginx/nginx.conf
```

Замените `server_name localhost;` на ваш домен:
```nginx
server {
    listen 80;
    server_name movieportal.ru www.movieportal.ru;
    
    # ... остальная конфигурация
}
```

### Шаг 3: Перезапустите nginx

```bash
docker compose restart nginx
```

### Шаг 4: Проверьте

```
http://movieportal.ru
```

---

## 🔒 Настройка SSL (HTTPS) с Let's Encrypt

### Шаг 1: Установите Certbot

```bash
apt install -y certbot python3-certbot-nginx

# Остановите nginx в Docker (временно)
docker compose stop nginx
```

### Шаг 2: Получите сертификат

```bash
# Замените на ваш домен и email
certbot certonly --standalone \
  -d movieportal.ru \
  -d www.movieportal.ru \
  --email your@email.com \
  --agree-tos \
  --no-eff-email
```

Сертификаты сохранятся в:
```
/etc/letsencrypt/live/movieportal.ru/fullchain.pem
/etc/letsencrypt/live/movieportal.ru/privkey.pem
```

### Шаг 3: Обновите nginx.conf для SSL

```bash
nano /var/www/movieportal/nginx/nginx.conf
```

Содержимое:
```nginx
# Редирект с HTTP на HTTPS
server {
    listen 80;
    server_name movieportal.ru www.movieportal.ru;
    return 301 https://$server_name$request_uri;
}

# HTTPS сервер
server {
    listen 443 ssl http2;
    server_name movieportal.ru www.movieportal.ru;
    
    # SSL сертификаты
    ssl_certificate /etc/letsencrypt/live/movieportal.ru/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/movieportal.ru/privkey.pem;
    
    # SSL настройки
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;
    
    root /var/www/html/public;
    index main.php;

    # Gzip
    gzip on;
    gzip_types text/css application/javascript text/xml;
    
    location / {
        try_files $uri $uri/ =404;
    }

    # Кэш статики
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

### Шаг 4: Монтируйте сертификаты в Docker

```bash
nano /var/www/movieportal/docker-compose.yml
```

Добавьте volume для nginx:
```yaml
services:
  nginx:
    build: ./nginx
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - /etc/letsencrypt:/etc/letsencrypt:ro
    depends_on:
      - php
    restart: always
```

### Шаг 5: Перезапустите

```bash
docker compose up -d --force-recreate nginx
```

### Шаг 6: Настройте автообновление SSL

```bash
# Создаём cron задачу
crontab -e

# Добавьте строку (обновление каждую ночь в 3:00):
0 3 * * * certbot renew --quiet && docker compose -f /var/www/movieportal/docker-compose.yml restart nginx
```

---

## 📊 Обновление sitemap и robots.txt

После настройки домена обновите файлы:

```bash
cd /var/www/movieportal

# Обновите sitemap.xml
nano public/sitemap.xml
```

Замените `http://localhost/` на `https://movieportal.ru/`

```bash
# Обновите robots.txt
nano public/robots.txt
```

Замените Sitemap URL:
```
Sitemap: https://movieportal.ru/sitemap.xml
```

---

## 🔄 Обновление приложения

### Через Git:

```bash
cd /var/www/movieportal

# Получите изменения
git pull origin main

# Перезапустите контейнеры
docker compose down
docker compose up -d --build
```

### Вручную:

```bash
# Загрузите новые файлы через SCP
scp -r C:\labs\public\* root@123.45.67.89:/var/www/movieportal/public/

# Перезапустите
docker compose restart php
```

---

## 📝 Полезные команды

### Управление Docker:

```bash
# Просмотр контейнеров
docker compose ps

# Логи всех сервисов
docker compose logs -f

# Логи конкретного сервиса
docker compose logs -f php
docker compose logs -f nginx
docker compose logs -f postgres

# Перезапуск
docker compose restart

# Остановка
docker compose down

# Полная пересборка
docker compose down
docker compose up -d --build
```

### Мониторинг:

```bash
# Использование ресурсов
docker stats

# Дисковое пространство
df -h

# Память
free -h

# Процессы
htop
```

### База данных:

```bash
# Подключение к PostgreSQL
docker compose exec postgres psql -U postgres -d movies_db

# Внутри psql:
\dt              # Список таблиц
\d movies        # Структура таблицы
SELECT COUNT(*) FROM movies;  # Количество фильмов
\q               # Выход
```

---

## 🐛 Troubleshooting

### Проблема: Порт 80 занят

```bash
# Проверьте, что занимает порт
netstat -tulpn | grep :80

# Если Apache:
systemctl stop apache2
systemctl disable apache2
```

### Проблема: Docker не запускается

```bash
# Перезапустите Docker
systemctl restart docker

# Проверьте статус
systemctl status docker
```

### Проблема: База данных не подключается

```bash
# Проверьте логи PostgreSQL
docker compose logs postgres

# Проверьте переменные окружения
docker compose exec php env | grep DB
```

### Проблема: Миграции не запускаются

```bash
# Проверьте логи PHP
docker compose logs php | grep Migration

# Запустите вручную
docker compose exec php php /var/www/html/public/check_migrations.php
```

---

## 📊 Мониторинг и аналитика

После деплоя:

1. **Зарегистрируйте домен в Яндекс.Метрике:**
   - https://metrika.yandex.ru/
   - Добавьте сайт: `movieportal.ru`
   - Получите ID счетчика
   - Обновите `public/includes/analytics.php`

2. **Google Analytics:**
   - https://analytics.google.com/
   - Создайте ресурс
   - Получите ID
   - Обновите `public/includes/analytics.php`

3. **Google Search Console:**
   - https://search.google.com/search-console
   - Добавьте сайт
   - Отправьте sitemap: `https://movieportal.ru/sitemap.xml`

4. **Яндекс.Вебмастер:**
   - https://webmaster.yandex.ru/
   - Добавьте сайт
   - Отправьте sitemap

---

## ✅ Чеклист деплоя

- [ ] Подключились к серверу по SSH
- [ ] Установили Docker и Docker Compose
- [ ] Загрузили проект (git clone или scp)
- [ ] Настроили переменные окружения
- [ ] Настроили фаервол (80, 443, 22)
- [ ] Запустили docker-compose up -d
- [ ] Проверили миграции (`/check_migrations.php`)
- [ ] Настроили DNS для домена (A-запись)
- [ ] Получили SSL сертификат (Let's Encrypt)
- [ ] Обновили nginx.conf для HTTPS
- [ ] Обновили sitemap.xml и robots.txt
- [ ] Зарегистрировали в счетчиках аналитики
- [ ] Добавили в Search Console и Вебмастер
- [ ] Настроили автообновление SSL (cron)

---

## 🎯 Итоговая проверка

### Откройте в браузере:

```
✅ https://movieportal.ru - главная
✅ https://movieportal.ru/check_migrations.php - миграции
✅ https://movieportal.ru/robots.txt - SEO
✅ https://movieportal.ru/sitemap.xml - карта сайта
✅ https://movieportal.ru/films.php - каталог
✅ https://movieportal.ru/admin/index.php - админка
```

### Проверьте SSL:

```
https://www.ssllabs.com/ssltest/analyze.html?d=movieportal.ru
```

Должна быть оценка **A** или **A+**

---

## 🚀 Готово!

Ваш MoviePortal задеплоен на собственном сервере с:
- ✅ Docker контейнеризацией
- ✅ HTTPS (SSL сертификат)
- ✅ Автоматическими миграциями
- ✅ SEO оптимизацией
- ✅ Готовностью к продакшену

**Теперь можете защищать проект! 🎓**

---

**Нужна помощь на конкретном этапе? Спрашивайте!**
