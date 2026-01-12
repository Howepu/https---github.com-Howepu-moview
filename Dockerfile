# Используем официальный PHP образ с Apache
FROM php:8.2-apache

# Устанавливаем расширения PostgreSQL для PHP
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql pgsql \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Включаем mod_rewrite для Apache
RUN a2enmod rewrite

# Копируем конфигурацию Apache
COPY docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf

# Копируем файлы приложения
COPY public /var/www/html/
COPY sql /var/www/sql/
COPY scripts /var/www/scripts/

# Устанавливаем права доступа
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Открываем порт
EXPOSE 80

# Запускаем Apache
CMD ["apache2-foreground"]
