/**
 * Funciones del Sidebar
 */

document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.querySelector('.sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const menuToggle = document.getElementById('menuToggle');
    const mainContent = document.querySelector('.main-content');

    // Toggle sidebar en mobile
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
        });
    }

    if (menuToggle) {
        menuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
        });
    }

    // Cerrar sidebar cuando se hace clic fuera
    document.addEventListener('click', function(e) {
        const isSidebarClick = sidebar && sidebar.contains(e.target);
        const isToggleClick = sidebarToggle && sidebarToggle.contains(e.target);
        const isMenuClick = menuToggle && menuToggle.contains(e.target);

        if (!isSidebarClick && !isToggleClick && !isMenuClick && sidebar) {
            sidebar.classList.remove('active');
        }
    });

    // Prevenir que los clicks dentro del sidebar cierren el sidebar
    if (sidebar) {
        sidebar.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }

    // Cerrar sidebar cuando se hace clic en un link
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth < 768) {
                sidebar.classList.remove('active');
            }
        });
    });

    // Manejar redimensión de ventana
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            if (window.innerWidth >= 768) {
                sidebar.classList.remove('active');
            }
        }, 250);
    });
});
