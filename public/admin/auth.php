<?php
// Файл для проверки авторизации админа
// Подключается в начале каждой админ-страницы

// Запускаем сессию только если она еще не запущена
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Обработка выхода из системы (должно быть в самом начале, до любого вывода)
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
}

// Проверяем, авторизован ли пользователь
function checkAdminAuth() {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        // Если не авторизован, перенаправляем на страницу входа
        header('Location: login.php');
        exit;
    }
}

// Функция для выхода из системы
function adminLogout() {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
}

// Функция для получения информации о текущем админе
function getCurrentAdmin() {
    if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
        return [
            'id' => $_SESSION['admin_id'] ?? null,
            'username' => $_SESSION['admin_username'] ?? null
        ];
    }
    return null;
}

// Автоматическая проверка авторизации при подключении файла
checkAdminAuth();
