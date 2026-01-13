-- Создание таблиц для основного функционала MoviePortal

-- Таблица жанров
CREATE TABLE IF NOT EXISTS genres (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Таблица режиссеров
CREATE TABLE IF NOT EXISTS directors (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    birth_date DATE,
    biography TEXT,
    photo_url VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Таблица фильмов
CREATE TABLE IF NOT EXISTS movies (
    id SERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    release_date DATE,
    duration INTEGER, -- продолжительность в минутах
    rating DECIMAL(3,1) CHECK (rating >= 0 AND rating <= 10),
    poster_url VARCHAR(500),
    trailer_url VARCHAR(500),
    director_id INTEGER REFERENCES directors(id) ON DELETE SET NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Связующая таблица фильмы-жанры (многие ко многим)
CREATE TABLE IF NOT EXISTS movie_genres (
    movie_id INTEGER REFERENCES movies(id) ON DELETE CASCADE,
    genre_id INTEGER REFERENCES genres(id) ON DELETE CASCADE,
    PRIMARY KEY (movie_id, genre_id)
);

-- Создание индексов для оптимизации
CREATE INDEX IF NOT EXISTS idx_movies_director ON movies(director_id);
CREATE INDEX IF NOT EXISTS idx_movies_title ON movies(title);
CREATE INDEX IF NOT EXISTS idx_movies_year ON movies(year);
CREATE INDEX IF NOT EXISTS idx_directors_name ON directors(name);
CREATE INDEX IF NOT EXISTS idx_genres_name ON genres(name);

-- Тестовые данные уже добавлены в основном init.sql файле

-- Создание таблицы для пользователей системы
CREATE TABLE IF NOT EXISTS admin_users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    role VARCHAR(20) DEFAULT 'user' CHECK (role IN ('admin', 'user')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP
);

-- Создание индекса для быстрого поиска по username
CREATE INDEX IF NOT EXISTS idx_admin_username ON admin_users(username);

-- Создание индекса для поиска по роли
CREATE INDEX IF NOT EXISTS idx_admin_role ON admin_users(role);

-- Удаление старых записей (если есть)
DELETE FROM admin_users WHERE username IN ('admin', 'user');

-- Вставка предустановленных пользователей
-- Админ (логин: admin, пароль: admin123)
INSERT INTO admin_users (username, password_hash, email, role) 
VALUES ('admin', '$2y$10$keY0E7kqwVWQXO/w7xsOiunvVmw6L9lWAh8u4dVbcSg3c0xMVRZR6', 'admin@movieportal.com', 'admin');

-- Обычный пользователь (логин: user, пароль: user123)  
INSERT INTO admin_users (username, password_hash, email, role) 
VALUES ('user', '$2y$10$zupCEm.zBTHH1J6Iem4GTu4nQZO5L9lWAh8u4dVbcSg3c0xMVRZR6', 'user@movieportal.com', 'user');

-- Добавление поддержки Яндекс ID OAuth
ALTER TABLE admin_users ADD COLUMN IF NOT EXISTS yandex_id VARCHAR(255) UNIQUE;
ALTER TABLE admin_users ADD COLUMN IF NOT EXISTS yandex_login VARCHAR(255);
ALTER TABLE admin_users ADD COLUMN IF NOT EXISTS yandex_first_name VARCHAR(255);
ALTER TABLE admin_users ADD COLUMN IF NOT EXISTS yandex_last_name VARCHAR(255);
ALTER TABLE admin_users ADD COLUMN IF NOT EXISTS yandex_display_name VARCHAR(255);
ALTER TABLE admin_users ADD COLUMN IF NOT EXISTS yandex_real_name VARCHAR(255);
ALTER TABLE admin_users ADD COLUMN IF NOT EXISTS yandex_email VARCHAR(255);
ALTER TABLE admin_users ADD COLUMN IF NOT EXISTS yandex_avatar_url VARCHAR(500);
ALTER TABLE admin_users ADD COLUMN IF NOT EXISTS yandex_access_token VARCHAR(255);

-- Создание индекса для быстрого поиска по Yandex ID
CREATE INDEX IF NOT EXISTS idx_admin_yandex_id ON admin_users(yandex_id);

-- Комментарий: 
-- Админ - логин: "admin", пароль: "admin123", роль: "admin"
-- Пользователь - логин: "user", пароль: "user123", роль: "user"
-- В реальном проекте пароли должны быть более сложными

-- Поля для Яндекс ID OAuth:
-- yandex_id - уникальный ID пользователя Яндекс
-- yandex_login - логин пользователя в Яндекс
-- yandex_first_name - имя пользователя
-- yandex_last_name - фамилия пользователя
-- yandex_display_name - отображаемое имя
-- yandex_real_name - реальное имя
-- yandex_email - email пользователя
-- yandex_avatar_url - URL аватара пользователя
-- yandex_access_token - токен доступа для API Яндекс
-- ok_avatar_url - URL аватара пользователя из OK
-- ok_access_token - токен доступа для API OK
-- yandex_id - уникальный ID пользователя Яндекс
-- yandex_login - логин пользователя в Яндекс
-- yandex_first_name - имя пользователя в Яндекс
-- yandex_last_name - фамилия пользователя в Яндекс
-- yandex_display_name - отображаемое имя пользователя в Яндекс
-- yandex_real_name - реальное имя пользователя в Яндекс
-- yandex_email - email пользователя в Яндекс
-- yandex_avatar_url - URL аватара пользователя из Яндекс
-- yandex_access_token - токен доступа для API Яндекс
