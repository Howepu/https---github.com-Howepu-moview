<?php
require_once 'config.php';
$pageTitle = "MoviePortal - Помощь";
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="assets/css/styles.css">
    
    <!-- Yandex.Metrika counter -->
    <script type="text/javascript">
        (function(m,e,t,r,i,k,a){
            m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
            m[i].l=1*new Date();
            for (var j = 0; j < document.scripts.length; j++) {if (document.scripts[j].src === r) { return; }}
            k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)
        })(window, document,'script','https://mc.yandex.ru/metrika/tag.js?id=106226950', 'ym');

        ym(106226950, 'init', {ssr:true, webvisor:true, clickmap:true, ecommerce:"dataLayer", accurateTrackBounce:true, trackLinks:true});
    </script>
    <noscript><div><img src="https://mc.yandex.ru/watch/106226950" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
    <!-- /Yandex.Metrika counter -->
</head>
<body>
    <div class="header">
        <div class="logo-container">
            <a href="main.php" class="logo">MoviePortal</a>
        </div>
        <div class="search-container">
            <span class="search-icon">🔍</span>
            <input type="search" id="globalSearch" placeholder="Поиск фильмов, режиссеров..." autocomplete="off">
            <div id="searchResults" class="search-results"></div>
        </div>
        <button class="search-toggle" id="searchToggle">🔍</button>
        <div class="menu-toggle">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </div>
    <div class="container">
        <div class="nav">
            <ul>
                <li><a href="main.php">Главная</a></li>
                <li><a href="films.php">Фильмы</a></li>
                <li><a href="genres.php">Жанры</a></li>
                <li><a href="directors.php">Режиссёры</a></li>
                <li><a href="help.php" class="active">Помощь</a></li>
                <li><a href="admin/index.php" style="color: #ff6b6b; font-weight: bold;">Админ-панель</a></li>
            </ul>
        </div>
        <div class="main-content">
            <div class="help-page">
                <h1>📚 Справка и помощь</h1>
                
                <div class="help-section">
                    <h2>🔍 Как найти фильм?</h2>
                    <div class="help-content">
                        <p><strong>Способ 1: Глобальный поиск</strong></p>
                        <ul>
                            <li>Используйте поле поиска в верхней части страницы</li>
                            <li>Начните вводить название фильма или имя режиссера</li>
                            <li>Результаты появятся автоматически</li>
                            <li>Горячая клавиша: <kbd>Ctrl+K</kbd> или <kbd>Cmd+K</kbd></li>
                        </ul>
                        
                        <p><strong>Способ 2: Просмотр по категориям</strong></p>
                        <ul>
                            <li><strong>Фильмы</strong> - полный список всех фильмов</li>
                            <li><strong>Жанры</strong> - фильмы по жанрам (боевик, драма, комедия и т.д.)</li>
                            <li><strong>Режиссеры</strong> - фильмы конкретного режиссера</li>
                        </ul>
                    </div>
                </div>

                <div class="help-section">
                    <h2>🎬 Информация о фильме</h2>
                    <div class="help-content">
                        <p>На странице фильма вы найдете:</p>
                        <ul>
                            <li>📅 Год производства</li>
                            <li>🌍 Страна производства</li>
                            <li>🎬 Режиссер</li>
                            <li>⏱️ Продолжительность</li>
                            <li>📝 Описание фильма</li>
                            <li>🎭 Список актеров</li>
                        </ul>
                        <p>Используйте кнопку <strong>"← Назад к списку"</strong> для возврата.</p>
                    </div>
                </div>

                <div class="help-section">
                    <h2>🎯 Фильтрация фильмов</h2>
                    <div class="help-content">
                        <p>Чтобы найти фильмы определенного жанра:</p>
                        <ol>
                            <li>Перейдите в раздел <strong>"Жанры"</strong></li>
                            <li>Выберите интересующий жанр</li>
                            <li>Просмотрите список фильмов этого жанра</li>
                            <li>Используйте кнопку <strong>"✕ Сбросить фильтр"</strong> для возврата ко всем фильмам</li>
                        </ol>
                    </div>
                </div>

                <div class="help-section">
                    <h2>⌨️ Горячие клавиши</h2>
                    <div class="help-content">
                        <table class="help-table">
                            <tr>
                                <td><kbd>Ctrl+K</kbd> / <kbd>Cmd+K</kbd></td>
                                <td>Открыть поиск</td>
                            </tr>
                            <tr>
                                <td><kbd>Esc</kbd></td>
                                <td>Закрыть результаты поиска</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="help-section">
                    <h2>🔐 Админ-панель</h2>
                    <div class="help-content">
                        <p>Для доступа к административной панели необходима авторизация.</p>
                        <p><strong>Возможности админ-панели:</strong></p>
                        <ul>
                            <li>Добавление новых фильмов</li>
                            <li>Редактирование информации о фильмах</li>
                            <li>Управление режиссерами</li>
                            <li>Управление жанрами</li>
                        </ul>
                        <p>Для получения доступа обратитесь к администратору сайта.</p>
                    </div>
                </div>

                <div class="help-section">
                    <h2>❓ Часто задаваемые вопросы (FAQ)</h2>
                    <div class="help-content">
                        <details class="faq-item">
                            <summary>Почему не отображается постер фильма?</summary>
                            <p>Если постер не загружается, вместо него отображается заглушка. Это может происходить, если изображение недоступно или ссылка на него устарела.</p>
                        </details>
                        
                        <details class="faq-item">
                            <summary>Как быстро найти фильм, если я помню только часть названия?</summary>
                            <p>Используйте глобальный поиск (Ctrl+K). Он работает по частичному совпадению - достаточно ввести несколько букв из названия.</p>
                        </details>
                        
                        <details class="faq-item">
                            <summary>Можно ли посмотреть все фильмы одного режиссера?</summary>
                            <p>Да! Перейдите в раздел "Режиссеры" и выберите интересующего режиссера. Откроется список всех его фильмов в базе.</p>
                        </details>
                        
                        <details class="faq-item">
                            <summary>Как вернуться на главную страницу?</summary>
                            <p>Нажмите на логотип "MoviePortal" в левом верхнем углу или используйте навигационное меню.</p>
                        </details>
                    </div>
                </div>

                <div class="help-section">
                    <h2>📧 Контакты</h2>
                    <div class="help-content">
                        <p>Если вы не нашли ответ на свой вопрос, свяжитесь с нами:</p>
                        <ul>
                            <li>📧 Email: support@movieportal.ru</li>
                            <li>💬 Telegram: @movieportal_support</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="footer">
        <div class="footer-logo">
            <div class="footer-logo-container">
                <a href="main.php" class="logo">MoviePortal</a>
            </div>
        </div>
    </div>
    <script src="assets/js/search.js"></script>
    <script src="assets/js/loader.js"></script>
    <script>
        const menuToggle = document.querySelector('.menu-toggle');
        const nav = document.querySelector('.nav');

        menuToggle.addEventListener('click', () => {
            nav.classList.toggle('active');
            menuToggle.classList.toggle('active');
        });
    </script>
</body>
</html>
