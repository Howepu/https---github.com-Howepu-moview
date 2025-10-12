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

-- Комментарий: 
-- Админ - логин: "admin", пароль: "admin123", роль: "admin"
-- Пользователь - логин: "user", пароль: "user123", роль: "user"
-- В реальном проекте пароли должны быть более сложными
