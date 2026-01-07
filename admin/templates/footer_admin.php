        </div> <!-- Penutup .flex-1 .flex .flex-col -->
        </div> <!-- Penutup .admin-layout -->
        <script>
document.addEventListener('DOMContentLoaded', function() {
    // Logika untuk Profile Dropdown
    const profileBtn = document.getElementById('profile-btn');
    const profileDropdown = document.getElementById('profile-dropdown');
    if (profileBtn && profileDropdown) {
        profileBtn.addEventListener('click', () => {
            profileDropdown.classList.toggle('hidden');
        });
        document.addEventListener('click', (e) => {
            if (!profileBtn.contains(e.target) && !profileDropdown.contains(e.target)) {
                profileDropdown.classList.add('hidden');
            }
        });
    }

    // Logika untuk Sidebar Mobile
    const sidebar = document.getElementById('sidebar');
    const hamburgerBtn = document.getElementById('hamburger-btn');
    const closeSidebarBtn = document.getElementById('close-sidebar-btn');
    if (sidebar && hamburgerBtn && closeSidebarBtn) {
        hamburgerBtn.addEventListener('click', () => sidebar.classList.add('is-open'));
        closeSidebarBtn.addEventListener('click', () => sidebar.classList.remove('is-open'));
    }

    // Logika untuk Accordion Sidebar
    const groupToggles = document.querySelectorAll('[data-group-toggle]');
    groupToggles.forEach(toggle => {
        toggle.addEventListener('click', () => {
            const groupKey = toggle.getAttribute('data-group-toggle');
            const submenu = document.getElementById(`submenu-${groupKey}`);
            const chevron = toggle.querySelector('.bi-chevron-down');

            // Tutup semua submenu lain
            document.querySelectorAll('.submenu.show').forEach(openSubmenu => {
                if (openSubmenu !== submenu) {
                    openSubmenu.classList.remove('show');
                    openSubmenu.style.maxHeight = null;
                    const otherToggle = document.querySelector(
                        `[data-group-toggle="${openSubmenu.id.replace('submenu-','')}"`
                        );
                    if (otherToggle) otherToggle.querySelector('.bi-chevron-down')
                        .classList.remove('rotate-180');
                }
            });

            // Buka atau tutup submenu yang diklik
            if (submenu.classList.contains('show')) {
                submenu.classList.remove('show');
                submenu.style.maxHeight = null;
                chevron.classList.remove('rotate-180');
            } else {
                submenu.classList.add('show');
                submenu.style.maxHeight = submenu.scrollHeight + "px";
                chevron.classList.add('rotate-180');
            }
        });
    });

    // Pastikan submenu yang aktif terbuka saat halaman dimuat
    const activeSubmenu = document.querySelector('.submenu.show');
    if (activeSubmenu) {
        activeSubmenu.style.maxHeight = activeSubmenu.scrollHeight + "px";
    }
});
        </script>
        </body>

        </html>