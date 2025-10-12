-- Создание таблицы для админов
CREATE TABLE IF NOT EXISTS admin_users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP
);

-- Создание индекса для быстрого поиска по username
CREATE INDEX IF NOT EXISTS idx_admin_username ON admin_users(username);

-- Вставка тестового админа (логин: admin, пароль: admin123)
-- Пароль захеширован с помощью password_hash() в PHP
INSERT INTO admin_users (username, password_hash, email) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@movieportal.com')
ON CONFLICT (username) DO NOTHING;

-- Комментарий: Пароль для тестового админа - "admin123"
-- В реальном проекте пароль должен быть более сложным
