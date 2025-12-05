<?php
require_once 'config/database.php';
require_once 'config/paths.php';
require_once 'config/auth.php';
requireRole('buyer');

$user_id = getCurrentUserId();
$conn = getDBConnection();

// Get user info
$user_stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();
$user_stmt->close();

// Get cart items
$cart_query = "SELECT c.id, c.quantity, 
                p.id as product_id, p.name as product_name, p.price as product_price,
                s.id as service_id, s.name as service_name, s.price as service_price
                FROM cart c
                LEFT JOIN products p ON c.product_id = p.id
                LEFT JOIN services s ON c.service_id = s.id
                WHERE c.buyer_id = ?";
$stmt = $conn->prepare($cart_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cart_result = $stmt->get_result();

$cart_items = [];
$total = 0;

while ($item = $cart_result->fetch_assoc()) {
    if ($item['product_id']) {
        $item['type'] = 'product';
        $item['name'] = $item['product_name'];
        $item['price'] = $item['product_price'];
        $item['item_id'] = $item['product_id'];
    } else {
        $item['type'] = 'service';
        $item['name'] = $item['service_name'];
        $item['price'] = $item['service_price'];
        $item['item_id'] = $item['service_id'];
    }
    $item['subtotal'] = $item['price'] * $item['quantity'];
    $total += $item['subtotal'];
    $cart_items[] = $item;
}

if (empty($cart_items)) {
    header('Location: ' . url('cart.php'));
    exit();
}

$order_success = false;
$order_error = '';

// Create uploads directory if it doesn't exist
$uploads_dir = __DIR__ . '/uploads/payment_receipts/';
if (!file_exists($uploads_dir)) {
    mkdir($uploads_dir, 0755, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shipping_address = trim($_POST['shipping_address'] ?? '');
    $payment_method = trim($_POST['payment_method'] ?? 'COD');
    
    // Validate payment method
    $allowed_methods = ['COD', 'GCash', 'PayMaya', 'Bank Transfer'];
    if (!in_array($payment_method, $allowed_methods)) {
        $order_error = 'Invalid payment method selected';
    } elseif (empty($shipping_address)) {
        $order_error = 'Shipping address is required';
    } else {
        $payment_receipt = null;
        
        // Handle receipt upload for digital payments
        if (in_array($payment_method, ['GCash', 'PayMaya', 'Bank Transfer'])) {
            if (isset($_FILES['payment_receipt']) && $_FILES['payment_receipt']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['payment_receipt'];
                $allowed_extensions = ['jpg', 'jpeg', 'png', 'pdf'];
                $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                
                if (!in_array($file_extension, $allowed_extensions)) {
                    $order_error = 'Invalid file type. Only JPG, PNG, and PDF files are allowed.';
                } elseif ($file['size'] > 5242880) { // 5MB limit
                    $order_error = 'File size exceeds 5MB limit.';
                } else {
                    $new_filename = 'receipt_' . time() . '_' . uniqid() . '.' . $file_extension;
                    $target_path = $uploads_dir . $new_filename;
                    
                    if (move_uploaded_file($file['tmp_name'], $target_path)) {
                        $payment_receipt = 'uploads/payment_receipts/' . $new_filename;
                    } else {
                        $order_error = 'Failed to upload receipt. Please try again.';
                    }
                }
            } else {
                $order_error = 'Payment receipt is required for ' . $payment_method . ' payments.';
            }
        }
        
        if (empty($order_error)) {
            // Create order with payment information
            $order_stmt = $conn->prepare("INSERT INTO orders (buyer_id, total_amount, shipping_address, payment_method, payment_receipt, status) VALUES (?, ?, ?, ?, ?, 'Pending')");
            $order_stmt->bind_param("idsss", $user_id, $total, $shipping_address, $payment_method, $payment_receipt);
            
            if ($order_stmt->execute()) {
                $order_id = $conn->insert_id;
                
                // Create order items
                $item_stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, service_id, quantity, price) VALUES (?, ?, ?, ?, ?)");
                
                foreach ($cart_items as $item) {
                    $product_id = $item['type'] === 'product' ? $item['item_id'] : null;
                    $service_id = $item['type'] === 'service' ? $item['item_id'] : null;
                    $item_stmt->bind_param("iiiid", $order_id, $product_id, $service_id, $item['quantity'], $item['price']);
                    $item_stmt->execute();
                }
                
                $item_stmt->close();
                
                // Clear cart
                $clear_stmt = $conn->prepare("DELETE FROM cart WHERE buyer_id = ?");
                $clear_stmt->bind_param("i", $user_id);
                $clear_stmt->execute();
                $clear_stmt->close();
                
                $order_success = true;
            } else {
                $order_error = 'Failed to create order. Please try again.';
            }
            
            $order_stmt->close();
        }
    }
}

$pageTitle = 'Checkout';
include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <?php if ($order_success): ?>
        <div class="alert-success border border-green-400 px-4 py-3 rounded mb-4">
            Order placed successfully! <a href="<?php echo url('orders.php'); ?>" class="underline link-lazada">View your orders</a>
        </div>
    <?php endif; ?>
    
    <?php if ($order_error): ?>
        <div class="alert-error border border-red-400 px-4 py-3 rounded mb-4">
            <?php echo htmlspecialchars($order_error); ?>
        </div>
    <?php endif; ?>
    
    <?php if (!$order_success): ?>
        <h1 class="text-3xl font-bold mb-6 text-lazada-black">Checkout</h1>
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <h2 class="text-2xl font-bold mb-4 text-lazada-black">Shipping Information</h2>
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-4">
                            <label class="block font-semibold mb-2 text-lazada-black">Full Name</label>
                            <input type="text" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" 
                                   class="w-full input-lazada" disabled>
                        </div>
                        
                        <div class="mb-4">
                            <label class="block font-semibold mb-2 text-lazada-black">Email</label>
                            <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" 
                                   class="w-full input-lazada" disabled>
                        </div>
                        
                        <div class="mb-4">
                            <label class="block font-semibold mb-2 text-lazada-black">Phone</label>
                            <input type="text" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" 
                                   class="w-full input-lazada" disabled>
                        </div>
                        
                        <div class="mb-4">
                            <label class="block font-semibold mb-2 text-lazada-black">Shipping Address *</label>
                            <textarea name="shipping_address" rows="4" required
                                      class="w-full input-lazada focus:ring-2 focus:ring-orange-500"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="mb-4">
                            <label class="block font-semibold mb-2 text-lazada-black">Payment Method *</label>
                            <select name="payment_method" id="payment_method" required
                                    class="w-full input-lazada focus:ring-2 focus:ring-orange-500">
                                <option value="COD">Cash on Delivery (COD)</option>
                                <option value="GCash">GCash</option>
                                <option value="PayMaya">PayMaya</option>
                                <option value="Bank Transfer">Bank Transfer</option>
                            </select>
                        </div>
                        
                        <div class="mb-4" id="receipt_upload_section" style="display: none;">
                            <label class="block font-semibold mb-2 text-lazada-black">Payment Receipt *</label>
                            <p class="text-sm text-lazada-gray mb-2">Upload proof of payment (JPG, PNG, or PDF, max 5MB)</p>
                            <input type="file" name="payment_receipt" id="payment_receipt" accept="image/*,.pdf"
                                   class="w-full input-lazada focus:ring-2 focus:ring-orange-500">
                        </div>
                        
                        <button type="submit" class="btn-lazada w-full">
                            Place Order
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-md p-6 sticky top-4">
                    <h2 class="text-2xl font-bold mb-4 text-lazada-black">Order Summary</h2>
                    <div class="space-y-2 mb-4">
                        <?php foreach ($cart_items as $item): ?>
                            <div class="flex justify-between text-sm">
                                <span class="text-lazada-black"><?php echo htmlspecialchars($item['name']); ?> (x<?php echo $item['quantity']; ?>)</span>
                                <span class="text-lazada-black">₱<?php echo number_format($item['subtotal'], 2); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="border-t pt-2 flex justify-between">
                        <span class="text-xl font-bold text-lazada-black">Total:</span>
                        <span class="text-xl font-bold price-lazada">₱<?php echo number_format($total, 2); ?></span>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
// Show/hide receipt upload based on payment method
document.getElementById('payment_method').addEventListener('change', function() {
    const receiptSection = document.getElementById('receipt_upload_section');
    const receiptInput = document.getElementById('payment_receipt');
    
    if (this.value === 'COD') {
        receiptSection.style.display = 'none';
        receiptInput.removeAttribute('required');
    } else {
        receiptSection.style.display = 'block';
        receiptInput.setAttribute('required', 'required');
    }
});
</script>

<?php 
$stmt->close();
$conn->close();
include 'includes/footer.php'; 
?>

