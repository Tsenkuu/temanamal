<?php
// Menentukan halaman aktif untuk navigasi bawah
$current_page_footer = basename($_SERVER['PHP_SELF']);
$nav_items = [
    'index.php' => ['icon' => 'bi-house-heart-fill', 'label' => 'Beranda'],
    'program.php' => ['icon' => 'bi-grid-1x2-fill', 'label' => 'Program'],
    'kalkulator_zakat.php' => ['icon' => 'bi-calculator-fill', 'label' => 'Zakat', 'action' => true],
    'berita.php' => ['icon' => 'bi-newspaper', 'label' => 'Berita'],
    'dashboard.php' => ['icon' => 'bi-person-fill', 'label' => 'Akun'],
];
?>
</main> <!-- End of main content from header -->

<!-- Bottom Navigation for Mobile -->
<nav class="fixed bottom-0 left-0 right-0 h-20 bg-white border-t border-gray-200 flex justify-around items-center z-50 md:hidden shadow-[0_-2px_10px_rgba(0,0,0,0.05)]">
    <?php foreach ($nav_items as $page => $item):
        $is_active = ($current_page_footer == $page);
    ?>
        <a href="<?php echo $page; ?>" class="flex flex-col items-center justify-center text-center w-full h-full transition-colors duration-200 <?php echo $is_active ? 'text-primary-orange' : 'text-gray-500'; ?> <?php echo isset($item['action']) ? ' -mt-8' : ''; ?>">
            <?php if (isset($item['action'])): ?>
                <div class="w-16 h-16 rounded-full bg-primary-orange text-white flex items-center justify-center shadow-lg border-4 border-white">
                    <i class="bi <?php echo $item['icon']; ?> text-3xl"></i>
                </div>
            <?php else: ?>
                <i class="bi <?php echo $item['icon']; ?> text-2xl"></i>
                <span class="text-xs font-medium mt-1"><?php echo $item['label']; ?></span>
            <?php endif; ?>
        </a>
    <?php endforeach; ?>
</nav>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const userMenuButton = document.getElementById('user-menu-button');
    const userMenu = document.getElementById('user-menu');

    if (userMenuButton && userMenu) {
        userMenuButton.addEventListener('click', function () {
            const isExpanded = userMenuButton.getAttribute('aria-expanded') === 'true';
            userMenuButton.setAttribute('aria-expanded', !isExpanded);
            userMenu.classList.toggle('hidden');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function (event) {
            if (!userMenuButton.contains(event.target) && !userMenu.contains(event.target)) {
                userMenu.classList.add('hidden');
                userMenuButton.setAttribute('aria-expanded', 'false');
            }
        });
    }
});
</script>

</body>
</html>

