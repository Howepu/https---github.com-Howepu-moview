<?php
http_response_code(403);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Доступ запрещён - MoviePortal</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .error-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 60vh;
            text-align: center;
            padding: 2rem;
        }
        .error-code {
            font-size: 8rem;
            font-weight: bold;
            color: #e74c3c;
            margin: 0;
            line-height: 1;
        }
        .error-title {
            font-size: 2rem;
            color: #2c3e50;
            margin: 1rem 0;
        }
        .error-message {
            font-size: 1.2rem;
            color: #7f8c8d;
            margin: 1rem 0 2rem;
        }
        .error-button {
            display: inline-block;
            padding: 1rem 2rem;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 1.1rem;
            transition: background 0.3s;
        }
        .error-button:hover {
            background: #2980b9;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="error-container">
            <h1 class="error-code">403</h1>
            <h2 class="error-title">Доступ запрещён</h2>
            <p class="error-message">У вас нет прав для доступа к этой странице.</p>
            <a href="/main.php" class="error-button">Вернуться на главную</a>
        </div>
    </div>

    <script src="search.js"></script>
    <script src="loader.js"></script>
</body>
</html>
