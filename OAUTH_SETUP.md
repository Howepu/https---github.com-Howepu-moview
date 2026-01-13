# Настройка OAuth для домена movieportal-utbt.onrender.com

## Яндекс ID OAuth

**Callback URL для добавления:**
```
https://movieportal-utbt.onrender.com/admin/yandex_callback.php
```

**Инструкция:**
1. Зайдите на https://oauth.yandex.ru/
2. Найдите приложение с ID `0a05754ab8594f6a97437159055427ee`
3. В разделе **"Callback URI"** (или "Redirect URI") добавьте:
   ```
   https://movieportal-utbt.onrender.com/admin/yandex_callback.php
   ```
4. Нажмите **"Сохранить"**

## Telegram Bot OAuth

**Домен для добавления:**
```
movieportal-utbt.onrender.com
```

**Инструкция:**
1. Откройте Telegram и найдите @BotFather
2. Отправьте команду: `/mybots`
3. Выберите бота: `@oauth_barrier_bot`
4. Нажмите **Bot Settings** → **Domain**
5. Введите домен: `movieportal-utbt.onrender.com`
6. Подтвердите

## Проверка после настройки

1. Запушьте обновленный код:
   ```bash
   git add public/admin/yandex_config.php
   git commit -m "Update OAuth config for Render domain"
   git push
   ```

2. Дождитесь деплоя на Render (2-5 минут)

3. Зайдите на https://movieportal-utbt.onrender.com/admin/login.php

4. Попробуйте авторизоваться через Яндекс или Telegram

## Если не работает

Проверьте логи в Render Dashboard → вашprofile Web Service → Logs

Возможные проблемы:
- Не добавили callback URL в настройках Яндекс
- Не настроили домен в BotFather
- Render еще не задеплоил новый код
