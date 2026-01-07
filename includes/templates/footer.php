<!-- Footer Section -->
<footer id="kontak"
    class="bg-white text-dark-text pt-16 pb-8 px-6 md:px-12 rounded-t-3xl mt-8 relative overflow-hidden">
    <style>
    .footer-bg-img {
        position: absolute;
        right: 0;
        bottom: 0;
        width: 100vw;
        max-width: 4000px;
        min-width: 320px;
        opacity: 0.8;
        z-index: 0;
        pointer-events: none;
    }

    @media (max-width: 768px) {
        .footer-bg-img {
            width: 100vw;
            max-width: 100vw;
            min-width: 220px;
        }
    }

    .footer-content {
        position: relative;
        z-index: 1;
    }
    </style>
    <div class="footer-content max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-4 gap-8">
        <!-- Kolom Informasi Kontak -->
        <div class="md:col-span-2">
            <h5 class="text-xl font-bold mb-4 text-primary-orange">Lazismu Tulungagung</h5>
            <p class="text-black mb-2">Jl. Ade Irma Suryani No.16, Sembung, Kec. Tulungagung, Kabupaten
                Tulungagung, Jawa Timur 66219</p>
            <p class="text-black mb-2">Telepon: 0821-2599-1199 (admin Lazismu)</p>
            <p class="text-black mb-2">Telepon: 0822-4529-0863 (admin Teman Amal)</p>
            <p class="text-black">Email: lazismutag@gmail.com</p>
        </div>
        <!-- Kolom Navigasi Cepat -->
        <div>
            <h5 class="text-xl font-bold mb-4 text-primary-orange">Navigasi</h5>
            <ul class="space-y-2">
                <li><a href="<?php echo BASE_URL; ?>/program"
                        class="text-black hover:text-white transition duration-300">Program</a></li>
                <li><a href="<?php echo BASE_URL; ?>/berita"
                        class="ttext-black hover:text-white transition duration-300">Berita</a></li>
                <li><a href="<?php echo BASE_URL; ?>/donasi"
                        class="text-black hover:text-white transition duration-300">Cara Donasi</a></li>
                <li><a href="<?php echo BASE_URL; ?>/login"
                        class="text-black hover:text-white transition duration-300">Login</a></li>
            </ul>
        </div>
        <!-- Kolom Media Sosial -->
        <div>
            <h5 class="text-xl font-bold mb-4 text-primary-orange">Ikuti Kami</h5>
            <div class="flex space-x-4">
                <a href="https://www.facebook.com/lazismukabtulungagung/" class="text-black hover:text-white transition duration-300" aria-label="Facebook"><svg
                        class="w-7 h-7" fill="currentColor" viewBox="0 0 24 24">
                        <path fill-rule="evenodd"
                            d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.776-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33V22C17.34 21.291 22 16.991 22 12z"
                            clip-rule="evenodd"></path>
                    </svg></a>
                <a href="https://www.instagram.com/lazismu.tulungagung/" class="text-black hover:text-white transition duration-300" aria-label="Instagram"><svg
                        class="w-7 h-7" fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.85s-.011 3.585-.069 4.85c-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07s-3.585-.012-4.85-.07c-3.252-.148-4.771-1.691-4.919-4.919-.058-1.265-.07-1.645-.07-4.85s.011-3.585.07-4.85c.148-3.225 1.664-4.771 4.919-4.919 1.266-.058 1.644-.07 4.85-.07zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948s.014 3.667.072 4.947c.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072s3.667-.014 4.947-.072c4.358-.2 6.78-2.618 6.98-6.98.059-1.281.073-1.689.073-4.948s-.014-3.667-.072-4.947c-.2-4.358-2.618-6.78-6.98-6.98-1.281-.059-1.689-.073-4.948-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.162 6.162 6.162 6.162-2.759 6.162-6.162-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4s1.791-4 4-4 4 1.79 4 4-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44 1.441-.645 1.441-1.44-.645-1.44-1.441-1.44z">
                        </path>
                    </svg></a>
                <a href="https://www.youtube.com/@lazismutulungagung" class="text-black hover:text-white transition duration-300" aria-label="YouTube" target="_blank" rel="noopener noreferrer">
  <svg class="w-7 h-7" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
    <path d="M23.498 6.186a2.99 2.99 0 0 0-2.105-2.116C19.513 3.5 12 3.5 12 3.5s-7.513 0-9.393.57A2.99 2.99 0 0 0 .502 6.186 31.72 31.72 0 0 0 0 12a31.72 31.72 0 0 0 .502 5.814 2.99 2.99 0 0 0 2.105 2.116C4.487 20.5 12 20.5 12 20.5s7.513 0 9.393-.57a2.99 2.99 0 0 0 2.105-2.116A31.72 31.72 0 0 0 24 12a31.72 31.72 0 0 0-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
  </svg>
</a>

            </div>
        </div>
    </div>
    <div class="footer-content text-center text-gray-400 mt-8 pt-6">
        <p>&copy; <span id="current-year"></span> Lazismu Tulungagung.</p>
    </div>
    <!-- Ganti path di bawah dengan path PNG Anda -->
    <img src="<?php echo BASE_URL; ?>/assets/img/footer-bg.png" alt="Footer Background" class="footer-bg-img">
</footer>
</body>

</html>