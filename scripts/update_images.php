<?php
/**
 * Скрипт для обновления ссылок на изображения
 * Запускать вручную после деплоя: php scripts/update_images.php
 */

require_once __DIR__ . '/../public/config.php';

try {
    echo "Начинаем обновление изображений...\n";
    
    // Пример: обновляем poster_url для конкретных фильмов
    $updates = [
        [
            'title' => 'Название фильма',
            'poster_url' => 'https://новая-ссылка-на-постер.jpg'
        ],
        // Добавьте сюда нужные обновления
    ];
    
    foreach ($updates as $update) {
        $stmt = $pdo->prepare("UPDATE movies SET poster_url = :poster WHERE title = :title");
        $stmt->execute([
            'poster' => $update['poster_url'],
            'title' => $update['title']
        ]);
        echo "✓ Обновлено: {$update['title']}\n";
    }
    
    echo "\nГотово! Обновлено " . count($updates) . " записей.\n";
    
} catch (Exception $e) {
    echo "Ошибка: " . $e->getMessage() . "\n";
    exit(1);
}
