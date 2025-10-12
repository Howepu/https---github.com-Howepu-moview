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

// Функция для получения информации о текущем пользователе
function getCurrentUser() {
    if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
        return [
            'id' => $_SESSION['admin_id'] ?? null,
            'username' => $_SESSION['admin_username'] ?? null,
            'role' => $_SESSION['admin_role'] ?? 'user'
        ];
    }
    return null;
}

// Функция для проверки роли пользователя
function hasRole($required_role) {
    $user = getCurrentUser();
    if (!$user) {
        return false;
    }
    
    // Админ имеет доступ ко всему
    if ($user['role'] === 'admin') {
        return true;
    }
    
    // Проверяем конкретную роль
    return $user['role'] === $required_role;
}

// Функция для проверки админских прав
function checkAdminRole() {
    if (!hasRole('admin')) {
        // Если не админ, перенаправляем на страницу ошибки доступа
        header('Location: access_denied.php');
        exit;
    }
}

// Для обратной совместимости
function getCurrentAdmin() {
    return getCurrentUser();
}

// Автоматическая проверка авторизации при подключении файла
checkAdminAuth();
