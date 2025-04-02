document.addEventListener('DOMContentLoaded', () => {
    // تابع کمکی برای چک کردن وجود المان
    const getElement = (id) => {
        const element = document.getElementById(id);
        if (!element) console.warn(`Element with ID "${id}" not found!`);
        return element;
    };

    // =============== مدیریت تم تاریک ===============
    const darkModeCheckbox = getElement('dark-mode-checkbox');
    const body = document.body;

    const applyDarkMode = (isEnabled) => {
        body.classList.toggle('dark-mode', isEnabled);
        if (darkModeCheckbox) darkModeCheckbox.checked = isEnabled;
        localStorage.setItem('darkMode', isEnabled ? 'enabled' : 'disabled');
    };

    if (darkModeCheckbox) {
        const savedDarkMode = localStorage.getItem('darkMode') === 'enabled';
        applyDarkMode(savedDarkMode);

darkModeCheckbox.addEventListener('change', () => {
            applyDarkMode(darkModeCheckbox.checked);
        });
    }

    // =============== مدیریت زیرمنوها ===============
    document.querySelectorAll('.menu-item, .user-info, .user-info-mobile').forEach(item => {
        item.addEventListener('click', (e) => {
            const subMenu = item.querySelector('.sub-menu');
            if (subMenu) {
                e.stopPropagation();
                // بستن همه زیرمنوهای باز
                document.querySelectorAll('.sub-menu.show').forEach(openMenu => {
                    if (openMenu !== subMenu) {
                        openMenu.classList.remove('show');
                        const icon = openMenu.parentElement.querySelector('.fa-chevron-up');
                        if (icon) icon.classList.remove('fa-chevron-up');
                    }
                });
                // باز/بسته کردن زیرمنوی فعلی
                subMenu.classList.toggle('show');
                const icon = item.querySelector('.fa-chevron-down');
                if (icon) icon.classList.toggle('fa-chevron-up');
            }
        });
    });

    // لینک‌های زیرمنوها
    document.querySelectorAll('.sub-menu a').forEach(link => {
        link.addEventListener('click', (e) => {
            e.stopPropagation();
        });
    });

    // =============== مدیریت منوی همبرگری ===============
    const toggleMobileMenu = () => {
        const sidebar = getElement('sidebar');
        if (sidebar) sidebar.classList.toggle('active');
    };

    const hamburgerBtn = document.querySelector('.hamburger-btn');
    if (hamburgerBtn) hamburgerBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        toggleMobileMenu();
    });

    // بستن منوها با کلیک خارج از آن‌ها
    document.addEventListener('click', (e) => {
        const sidebar = getElement('sidebar');
        const subMenus = document.querySelectorAll('.sub-menu.show');
        
        // بستن زیرمنوها
        subMenus.forEach(subMenu => {
            subMenu.classList.remove('show');
            const icon = subMenu.parentElement.querySelector('.fa-chevron-up');
            if (icon) icon.classList.remove('fa-chevron-up');
        });

        // بستن منوی همبرگری
        if (sidebar && sidebar.classList.contains('active') && !sidebar.contains(e.target) && !hamburgerBtn.contains(e.target)) {
            sidebar.classList.remove('active');
        }
    });
});