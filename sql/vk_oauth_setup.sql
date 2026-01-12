-- Добавление поддержки VK OAuth в таблицу admin_users
ALTER TABLE admin_users ADD COLUMN IF NOT EXISTS vk_id BIGINT UNIQUE;
ALTER TABLE admin_users ADD COLUMN IF NOT EXISTS vk_access_token VARCHAR(255);
ALTER TABLE admin_users ADD COLUMN IF NOT EXISTS vk_avatar_url VARCHAR(500);

-- Создание индекса для быстрого поиска по VK ID
CREATE INDEX IF NOT EXISTS idx_admin_vk_id ON admin_users(vk_id);

-- Комментарий: 
-- vk_id - уникальный ID пользователя ВКонтакте
-- vk_access_token - токен доступа для API ВК (опционально)
-- vk_avatar_url - URL аватара пользователя из ВК
