<?php
require_once '../config/database.php';
require_once '../config/paths.php';
require_once '../config/auth.php';
requireRole('seller');

$seller_id = getCurrentUserId();
$conn = getDBConnection();

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $status = trim($_POST['status']);
    
    // Verify order belongs to this seller
    $verify_stmt = $conn->prepare("SELECT o.id FROM orders o
                                   JOIN order_items oi ON o.id = oi.order_id
                                   LEFT JOIN products p ON oi.product_id = p.id
                                   LEFT JOIN services s ON oi.service_id = s.id
                                   WHERE o.id = ? AND (p.seller_id = ? OR s.seller_id = ?)
                                   LIMIT 1");
    $verify_stmt->bind_param("iii", $order_id, $seller_id, $seller_id);
    $verify_stmt->execute();
    $verify_result = $verify_stmt->get_result();
    
    if ($verify_result->num_rows > 0) {
        $allowed_statuses = ['Pending', 'Preparing', 'Out for Delivery', 'Delivered', 'Cancelled'];
        if (in_array($status, $allowed_statuses)) {
            $update_stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
            $update_stmt->bind_param("si", $status, $order_id);
            $update_stmt->execute();
            $update_stmt->close();
        }
    }
    $verify_stmt->close();
    
    header('Location: ' . url('seller/orders.php'));
    exit();
}

// Get all orders for this seller's products/services
$orders_query = "SELECT DISTINCT o.*, u.full_name as buyer_name, u.email as buyer_email, u.phone as buyer_phone
                 FROM orders o
                 JOIN users u ON o.buyer_id = u.id
                 JOIN order_items oi ON o.id = oi.order_id
                 LEFT JOIN products p ON oi.product_id = p.id
                 LEFT JOIN services s ON oi.service_id = s.id
                 WHERE (p.seller_id = ? OR s.seller_id = ?)
                 ORDER BY o.created_at DESC";
$stmt = $conn->prepare($orders_query);
$stmt->bind_param("ii", $seller_id, $seller_id);
$stmt->execute();
$orders_result = $stmt->get_result();

$pageTitle = 'My Orders';
include '../includes/header.php';
?>

<div class="container mx-auto px-4" style="padding-top: 32px; padding-bottom: 32px;">
    <h1 class="text-3xl font-bold mb-6 text-lazada-black">My Orders</h1>
    
    <?php if ($orders_result->num_rows === 0): ?>
        <div class="bg-white rounded-lg shadow-md p-12 text-center">
            <p class="text-lazada-gray text-xl mb-4">You have no orders yet</p>
        </div>
    <?php else: ?>
        <div class="space-y-4">
            <?php while ($order = $orders_result->fetch_assoc()): ?>
                <?php
                // Get order items for this seller
                $items_query = "SELECT oi.*, p.name as product_name, p.seller_id as product_seller_id,
                               s.name as service_name, s.seller_id as service_seller_id
                               FROM order_items oi
                               LEFT JOIN products p ON oi.product_id = p.id
                               LEFT JOIN services s ON oi.service_id = s.id
                               WHERE oi.order_id = ? AND (p.seller_id = ? OR s.seller_id = ?)";
                $items_stmt = $conn->prepare($items_query);
                $items_stmt->bind_param("iii", $order['id'], $seller_id, $seller_id);
                $items_stmt->execute();
                $items_result = $items_stmt->get_result();
                ?>
                
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="font-bold text-lg text-lazada-black">Order #<?php echo $order['id']; ?></h3>
                            <p class="text-sm text-lazada-gray">Placed on <?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?></p>
                        </div>
                        <span class="px-4 py-2 rounded-full text-sm font-semibold order-status-badge status-<?php echo strtolower(str_replace(' ', '-', $order['status'])); ?>">
                            <?php echo htmlspecialchars($order['status']); ?>
                        </span>
                    </div>
                    
                    <!-- Buyer Information -->
                    <div class="mb-4 p-4 bg-gray-50 rounded-lg">
                        <h4 class="font-semibold mb-2 text-lazada-black">Buyer Information</h4>
                        <p class="text-sm text-lazada-black"><strong>Name:</strong> <?php echo htmlspecialchars($order['buyer_name']); ?></p>
                        <p class="text-sm text-lazada-black"><strong>Email:</strong> <?php echo htmlspecialchars($order['buyer_email']); ?></p>
                        <?php if (!empty($order['buyer_phone'])): ?>
                            <p class="text-sm text-lazada-black"><strong>Phone:</strong> <?php echo htmlspecialchars($order['buyer_phone']); ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Payment Information -->
                    <div class="mb-4 p-4 bg-blue-50 rounded-lg">
                        <h4 class="font-semibold mb-2 text-lazada-black">Payment Information</h4>
                        <p class="text-sm text-lazada-black"><strong>Payment Method:</strong> <?php echo htmlspecialchars($order['payment_method'] ?? 'COD'); ?></p>
                        <?php if (!empty($order['payment_receipt'])): ?>
                            <p class="text-sm text-lazada-black mt-2">
                                <strong>Receipt:</strong> 
                                <a href="<?php echo url($order['payment_receipt']); ?>" target="_blank" class="link-lazada">
                                    View Receipt
                                </a>
                            </p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-4">
                        <p class="text-sm text-lazada-gray"><strong>Shipping Address:</strong></p>
                        <p class="text-lazada-black"><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
                    </div>
                    
                    <div class="border-t pt-4 mb-4">
                        <h4 class="font-semibold mb-2 text-lazada-black">Items:</h4>
                        <ul class="space-y-2">
                            <?php while ($item = $items_result->fetch_assoc()): ?>
                                <li class="flex justify-between text-sm">
                                    <span class="text-lazada-black">
                                        <?php echo htmlspecialchars($item['product_name'] ?? $item['service_name']); ?> 
                                        (x<?php echo $item['quantity']; ?>)
                                    </span>
                                    <span class="text-lazada-black">₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                        <div class="mt-4 flex justify-between font-bold text-lg">
                            <span class="text-lazada-black">Total:</span>
                            <span class="price-lazada">₱<?php echo number_format($order['total_amount'], 2); ?></span>
                        </div>
                    </div>
                    
                    <!-- Status Update Form -->
                    <div class="border-t pt-4">
                        <form method="POST" class="flex items-center gap-4">
                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                            <label class="font-semibold text-lazada-black">Update Status:</label>
                            <select name="status" class="input-lazada focus:ring-2 focus:ring-orange-500">
                                <option value="Pending" <?php echo $order['status'] === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="Preparing" <?php echo $order['status'] === 'Preparing' ? 'selected' : ''; ?>>Preparing</option>
                                <option value="Out for Delivery" <?php echo $order['status'] === 'Out for Delivery' ? 'selected' : ''; ?>>Out for Delivery</option>
                                <option value="Delivered" <?php echo $order['status'] === 'Delivered' ? 'selected' : ''; ?>>Delivered</option>
                                <option value="Cancelled" <?php echo $order['status'] === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                            <button type="submit" name="update_status" class="btn-lazada">
                                Update Status
                            </button>
                        </form>
                    </div>
                    
                    <?php if ($order['status'] === 'Cancelled' && !empty($order['cancelled_at'])): ?>
                        <div class="mt-4 pt-4 border-t">
                            <p class="text-sm text-red-600">
                                <strong>Cancelled on:</strong> <?php echo date('M d, Y H:i', strtotime($order['cancelled_at'])); ?>
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <?php $items_stmt->close(); ?>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
</div>

<?php 
$stmt->close();
$conn->close();
include '../includes/footer.php'; 
?>

