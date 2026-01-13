<!-- Admin Header with Mobile Menu -->
<div class="admin-header">
    <div class="admin-logo">
        <h1><?= $pageTitle ?? 'MoviePortal' ?></h1>
    </div>
    <div class="menu-toggle" onclick="toggleMobileMenu()">
        <span></span>
        <span></span>
        <span></span>
    </div>
    <div class="admin-nav">
        <span class="admin-user">
            <?= htmlspecialchars($_SESSION['admin_username']) ?>
        </span>
        <?php if (basename($_SERVER['PHP_SELF']) !== 'index.php'): ?>
        <a href="index.php" class="btn btn-secondary">Панель</a>
        <?php endif; ?>
        <a href="../main.php" class="btn btn-secondary">Сайт</a>
        <a href="?action=logout" class="btn btn-danger">Выйти</a>
    </div>
</div>

<!-- Admin Sidebar with Mobile Support -->
<div class="admin-sidebar" id="mobileSidebar">
    <div class="mobile-menu-header">
        <h3 style="margin: 0; color: #667eea;">Меню</h3>
        <button class="mobile-menu-close" onclick="toggleMobileMenu()">✕</button>
    </div>
    <nav class="admin-menu">
        <ul>
            <li><a href="index.php" <?= basename($_SERVER['PHP_SELF']) === 'index.php' ? 'class="active"' : '' ?>>🏠 Главная</a></li>
            <li><a href="movies.php" <?= basename($_SERVER['PHP_SELF']) === 'movies.php' ? 'class="active"' : '' ?>>🎬 Фильмы</a></li>
            <li><a href="directors.php" <?= basename($_SERVER['PHP_SELF']) === 'directors.php' ? 'class="active"' : '' ?>>🎭 Режиссеры</a></li>
            <li><a href="genres.php" <?= basename($_SERVER['PHP_SELF']) === 'genres.php' ? 'class="active"' : '' ?>>🎪 Жанры</a></li>
        </ul>
    </nav>
</div>

<!-- Mobile Menu Toggle Script -->
<script>
function toggleMobileMenu() {
    const sidebar = document.getElementById('mobileSidebar');
    sidebar.classList.toggle('mobile-open');
    document.body.style.overflow = sidebar.classList.contains('mobile-open') ? 'hidden' : '';
}

// Close menu when clicking on links
document.querySelectorAll('.admin-menu a').forEach(link => {
    link.addEventListener('click', () => {
        if (window.innerWidth <= 768) {
            const sidebar = document.getElementById('mobileSidebar');
            sidebar.classList.remove('mobile-open');
            document.body.style.overflow = '';
        }
    });
});

// Close menu when clicking outside
document.addEventListener('click', (e) => {
    const sidebar = document.getElementById('mobileSidebar');
    const toggle = document.querySelector('.menu-toggle');
    if (sidebar.classList.contains('mobile-open') && 
        !sidebar.contains(e.target) && 
        !toggle.contains(e.target)) {
        sidebar.classList.remove('mobile-open');
        document.body.style.overflow = '';
    }
});
</script>
