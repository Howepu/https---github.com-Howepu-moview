-- Добавление поддержки Telegram OAuth в таблицу admin_users
ALTER TABLE admin_users ADD COLUMN IF NOT EXISTS telegram_id BIGINT UNIQUE;
ALTER TABLE admin_users ADD COLUMN IF NOT EXISTS telegram_username VARCHAR(255);
ALTER TABLE admin_users ADD COLUMN IF NOT EXISTS telegram_first_name VARCHAR(255);
ALTER TABLE admin_users ADD COLUMN IF NOT EXISTS telegram_last_name VARCHAR(255);
ALTER TABLE admin_users ADD COLUMN IF NOT EXISTS telegram_photo_url VARCHAR(500);

-- Создание индекса для быстрого поиска по Telegram ID
CREATE INDEX IF NOT EXISTS idx_admin_telegram_id ON admin_users(telegram_id);

-- Комментарий: 
-- telegram_id - уникальный ID пользователя Telegram
-- telegram_username - имя пользователя в Telegram (@username)
-- telegram_first_name - имя пользователя
-- telegram_last_name - фамилия пользователя
-- telegram_photo_url - URL фото профиля пользователя
