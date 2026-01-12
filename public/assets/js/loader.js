// Глобальный индикатор загрузки
(function() {
    // Создание элементов при загрузке страницы
    document.addEventListener('DOMContentLoaded', function() {
        // Создать loader bar
        const loader = document.createElement('div');
        loader.className = 'page-loader';
        loader.innerHTML = '<div class="page-loader-bar"></div>';
        document.body.insertBefore(loader, document.body.firstChild);

        // Создать spinner overlay
        const spinner = document.createElement('div');
        spinner.className = 'spinner-overlay';
        spinner.id = 'spinnerOverlay';
        spinner.innerHTML = '<div class="spinner"></div>';
        document.body.appendChild(spinner);

        // Показать loader при переходе по ссылкам
        document.querySelectorAll('a:not([href^="#"]):not([target="_blank"])').forEach(link => {
            link.addEventListener('click', function(e) {
                if (this.href && !this.href.includes('javascript:')) {
                    loader.classList.add('active');
                }
            });
        });

        // Показать loader при отправке форм
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function() {
                loader.classList.add('active');
            });
        });

        // Скрыть loader когда страница загружена
        window.addEventListener('load', function() {
            loader.classList.remove('active');
        });
    });

    // Функция для показа spinner
    window.showSpinner = function() {
        const spinner = document.getElementById('spinnerOverlay');
        if (spinner) spinner.classList.add('active');
    };

    // Функция для скрытия spinner
    window.hideSpinner = function() {
        const spinner = document.getElementById('spinnerOverlay');
        if (spinner) spinner.classList.remove('active');
    };
})();
