<?php
require_once __DIR__ . '/../config/paths.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>URL Test</title>
  <style>body{font-family:Inter,Arial,Helvetica,sans-serif;padding:20px}li{margin:6px 0}</style>
</head>
<body>
  <h1>URL Helper Test</h1>
  <p><strong>BASE_PATH:</strong> <?php echo defined('BASE_PATH') ? BASE_PATH : '(not defined)'; ?></p>
  <ul>
    <?php
    $tests = [
        'admin/sales_monitoring.php',
        'admin/sales_report.php',
        'admin/inventory_monitoring.php',
        'admin/create_seller.php',
        'admin/users.php',
        'admin/categories.php',
        'admin/products.php',
        'admin/services.php',
        'admin/orders.php',
        'admin/bookings.php',
        'index.php',
        'assets/css/style.css',
    ];

    foreach ($tests as $p) {
        echo '<li><code>' . htmlspecialchars($p) . '</code> &rarr; <strong>' . htmlspecialchars(url($p)) . '</strong></li>';
    }
    ?>
  </ul>
  <p>Open this page via Apache at <code>http://localhost/clone_system/tools/url_test.php</code> and confirm the generated links match your expectations.</p>
  <p>Remove this file after testing if you want: <code>tools/url_test.php</code>.</p>
</body>
</html>
