// Глобальный поиск
(function() {
    let searchTimeout;
    const searchInput = document.getElementById('globalSearch');
    const searchResults = document.getElementById('searchResults');

    if (!searchInput || !searchResults) return;

    searchInput.addEventListener('input', function(e) {
        clearTimeout(searchTimeout);
        const query = e.target.value.trim();
        
        if (query.length < 2) {
            searchResults.innerHTML = '';
            searchResults.classList.remove('show');
            return;
        }
        
        searchResults.innerHTML = '<div class="search-loading">Поиск...</div>';
        searchResults.classList.add('show');
        
        searchTimeout = setTimeout(() => {
            fetch(`search.php?q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.results && data.results.length > 0) {
                        searchResults.innerHTML = data.results.map(movie => `
                            <a href="film_page.php?movie_id=${movie.id}" class="search-result-item">
                                <img src="${movie.poster_url}" 
                                     alt="${movie.title}" 
                                     class="search-result-poster"
                                     onerror="this.src='https://via.placeholder.com/50x75?text=No+Image'">
                                <div class="search-result-info">
                                    <div class="search-result-title">${movie.title}</div>
                                    <div class="search-result-meta">${movie.year} • ${movie.director}</div>
                                </div>
                            </a>
                        `).join('');
                    } else {
                        searchResults.innerHTML = '<div class="search-no-results">Ничего не найдено</div>';
                    }
                })
                .catch(error => {
                    searchResults.innerHTML = '<div class="search-no-results">Ошибка поиска</div>';
                });
        }, 300);
    });

    // Закрытие результатов при клике вне поиска
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.search-container')) {
            searchResults.classList.remove('show');
        }
    });

    // Мобильный поиск
    const searchToggle = document.getElementById('searchToggle');
    const searchContainer = document.querySelector('.search-container');
    
    if (searchToggle && searchContainer) {
        searchToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            searchContainer.classList.toggle('active');
            searchToggle.classList.toggle('active');
            
            // Фокус на поле поиска при открытии
            if (searchContainer.classList.contains('active')) {
                setTimeout(() => searchInput.focus(), 100);
            }
        });
        
        // Закрытие мобильного поиска при клике вне
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.search-container') && !e.target.closest('.search-toggle')) {
                searchContainer.classList.remove('active');
                searchToggle.classList.remove('active');
            }
        });
    }

    // Горячие клавиши
    document.addEventListener('keydown', function(e) {
        // Ctrl+K или Cmd+K для фокуса на поиске
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            searchInput.focus();
        }
        
        // Escape для закрытия результатов и мобильного поиска
        if (e.key === 'Escape') {
            searchResults.classList.remove('show');
            searchInput.blur();
            if (searchContainer) searchContainer.classList.remove('active');
            if (searchToggle) searchToggle.classList.remove('active');
        }
    });
})();
