<?php
require_once 'config/database.php';
require_once 'config/paths.php';
require_once 'config/auth.php';
requireRole('buyer');

$user_id = getCurrentUserId();
$conn = getDBConnection();

// Handle order cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_order'])) {
    $order_id = intval($_POST['order_id']);
    
    // Verify order belongs to user and can be cancelled
    $check_stmt = $conn->prepare("SELECT id, status, payment_method FROM orders WHERE id = ? AND buyer_id = ?");
    $check_stmt->bind_param("ii", $order_id, $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($order = $check_result->fetch_assoc()) {
        // Can cancel if status is Pending or Preparing
        if (in_array($order['status'], ['Pending', 'Preparing'])) {
            $cancel_stmt = $conn->prepare("UPDATE orders SET status = 'Cancelled', cancelled_at = NOW() WHERE id = ?");
            $cancel_stmt->bind_param("i", $order_id);
            $cancel_stmt->execute();
            $cancel_stmt->close();
        }
    }
    $check_stmt->close();
    
    header('Location: ' . url('orders.php'));
    exit();
}

$orders_query = "SELECT * FROM orders WHERE buyer_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($orders_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orders_result = $stmt->get_result();

$pageTitle = 'My Orders';
include 'includes/header.php';
?>

<div class="container mx-auto px-4" style="padding-top: 32px; padding-bottom: 32px;">
    <h1 class="text-3xl font-bold mb-6 text-lazada-black">My Orders</h1>
    
    <?php if ($orders_result->num_rows === 0): ?>
        <div class="bg-white rounded-lg shadow-md p-12 text-center">
            <p class="text-lazada-gray text-xl mb-4">You have no orders yet</p>
            <a href="<?php echo url('products.php'); ?>" class="link-lazada font-semibold">Start Shopping →</a>
        </div>
    <?php else: ?>
        <div class="space-y-4">
            <?php while ($order = $orders_result->fetch_assoc()): ?>
                <?php
                // Get order items
                $items_query = "SELECT oi.*, p.name as product_name, s.name as service_name 
                               FROM order_items oi
                               LEFT JOIN products p ON oi.product_id = p.id
                               LEFT JOIN services s ON oi.service_id = s.id
                               WHERE oi.order_id = ?";
                $items_stmt = $conn->prepare($items_query);
                $items_stmt->bind_param("i", $order['id']);
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
                    
                    <!-- Order Status Timeline -->
                    <div class="mb-4 p-4 bg-gray-50 rounded-lg">
                        <h4 class="font-semibold mb-3 text-lazada-black">Order Status</h4>
                        <div class="flex items-center space-x-4 text-sm">
                            <?php
                            $statuses = ['Pending', 'Preparing', 'Out for Delivery', 'Delivered', 'Cancelled'];
                            $current_status = $order['status'];
                            $current_index = array_search($current_status, $statuses);
                            
                            foreach ($statuses as $index => $status):
                                $is_active = $index <= $current_index;
                                $is_current = $index === $current_index;
                            ?>
                                <div class="flex items-center">
                                    <div class="flex flex-col items-center">
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center <?php echo $is_active ? 'bg-orange-500 text-white' : 'bg-gray-300 text-gray-600'; ?>">
                                            <?php if ($is_active): ?>
                                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                </svg>
                                            <?php else: ?>
                                                <?php echo $index + 1; ?>
                                            <?php endif; ?>
                                        </div>
                                        <span class="mt-1 text-xs <?php echo $is_current ? 'font-bold text-orange-500' : 'text-gray-600'; ?>">
                                            <?php echo htmlspecialchars($status); ?>
                                        </span>
                                    </div>
                                    <?php if ($index < count($statuses) - 1): ?>
                                        <div class="w-12 h-1 <?php echo $is_active ? 'bg-orange-500' : 'bg-gray-300'; ?>"></div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
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
                    
                    <div class="border-t pt-4">
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
                    
                    <!-- Cancel Button -->
                    <?php
                    $can_cancel = in_array($order['status'], ['Pending', 'Preparing']);
                    if ($can_cancel):
                    ?>
                        <div class="mt-4 pt-4 border-t">
                            <form method="POST" onsubmit="return confirm('Are you sure you want to cancel this order? This action cannot be undone.');">
                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                <button type="submit" name="cancel_order" class="btn-lazada-secondary bg-red-500 hover:bg-red-600 text-white border-red-500">
                                    Cancel Order
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>
                    
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
include 'includes/footer.php'; 
?>

