<?php
require_once 'config/paths.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>CSS Test</title>
    <link rel="stylesheet" href="<?php echo url('assets/css/style.css'); ?>?v=<?php echo time(); ?>">
    <style>
        body { padding: 20px; font-family: Arial; }
        .test-box { padding: 20px; margin: 10px 0; border: 2px solid #000; }
    </style>
</head>
<body>
    <h1>CSS Loading Test</h1>
    <p><strong>CSS File Path:</strong> <?php echo url('assets/css/style.css'); ?></p>
    <p><strong>Full URL:</strong> http://localhost<?php echo url('assets/css/style.css'); ?></p>
    
    <div class="test-box" style="background-color: #FF6A00; color: white;">
        <h2>Lazada Orange Test (#FF6A00)</h2>
        <p>This box should be orange if CSS variables work.</p>
    </div>
    
    <div class="test-box nav-lazada" style="color: white;">
        <h2>Navigation Test (.nav-lazada)</h2>
        <p>This should have orange background from CSS class.</p>
    </div>
    
    <button class="btn-lazada">Test Button (.btn-lazada)</button>
    
    <p class="price-lazada">Price Test: ₱1,234.56</p>
    
    <script>
        // Check if CSS loaded
        const styleSheet = Array.from(document.styleSheets).find(sheet => 
            sheet.href && sheet.href.includes('style.css')
        );
        
        if (styleSheet) {
            document.write('<p style="color: green;"><strong>✓ CSS File Loaded Successfully!</strong></p>');
            document.write('<p>CSS URL: ' + styleSheet.href + '</p>');
        } else {
            document.write('<p style="color: red;"><strong>✗ CSS File NOT Loaded!</strong></p>');
        }
    </script>
</body>
</html>

