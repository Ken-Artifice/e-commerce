<?php
// Include paths if not already included
if (!function_exists('url')) {
    if (file_exists(__DIR__ . '/../config/paths.php')) {
        require_once __DIR__ . '/../config/paths.php';
    } elseif (file_exists(__DIR__ . '/config/paths.php')) {
        require_once __DIR__ . '/config/paths.php';
    }
}
?>
    <footer class="bg-gray-800 text-white py-8" style="background-color: #1f2937 !important;">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <h3 class="text-xl font-bold mb-4">Lasso.cs</h3>
                    <p class="text-gray-400">Your one-stop shop for products and services.</p>
                </div>
                <div>
                    <h4 class="font-bold mb-4">Quick Links</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="<?php echo function_exists('url') ? url('products.php') : '/products.php'; ?>" class="hover:text-white">Products</a></li>
                        <li><a href="<?php echo function_exists('url') ? url('services.php') : '/services.php'; ?>" class="hover:text-white">Services</a></li>
                        <li><a href="<?php echo function_exists('url') ? url('index.php') : '/index.php'; ?>" class="hover:text-white">About Us</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold mb-4">Contact</h4>
                    <p class="text-gray-400">Email: support@lasso.cs</p>
                    <p class="text-gray-400">Phone: +63 962 624 3997</p>
                </div>
            </div>
            <div class="mt-8 pt-8 border-t border-gray-700 text-center text-gray-400">
                <p>&copy; 2025 Lasso.cs. All rights reserved.</p>
            </div>
        </div>
    </footer>
    
    <script>
        // Mobile menu toggle
        document.getElementById('mobile-menu-btn')?.addEventListener('click', function() {
            const menu = document.getElementById('mobile-menu');
            menu.classList.toggle('active');
        });
    </script>
    </div><!-- End of main-content wrapper -->
</body>
</html>

