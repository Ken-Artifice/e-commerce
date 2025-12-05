<?php
require_once 'config/database.php';
require_once 'config/paths.php';
require_once 'config/auth.php';
requireRole('buyer');

$user_id = getCurrentUserId();
$conn = getDBConnection();

// Handle remove from cart
if (isset($_GET['remove'])) {
    $cart_id = intval($_GET['remove']);
    $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND buyer_id = ?");
    $stmt->bind_param("ii", $cart_id, $user_id);
    $stmt->execute();
    $stmt->close();
    header('Location: ' . url('cart.php'));
    exit();
}

// Handle update quantity
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_quantity'])) {
    $cart_id = intval($_POST['cart_id']);
    $quantity = intval($_POST['quantity']);
    
    if ($quantity > 0) {
        $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND buyer_id = ?");
        $stmt->bind_param("iii", $quantity, $cart_id, $user_id);
        $stmt->execute();
        $stmt->close();
    }
    header('Location: ' . url('cart.php'));
    exit();
}

// Get cart items
$cart_query = "SELECT c.id, c.quantity, 
                p.id as product_id, p.name as product_name, p.price as product_price, p.image as product_image, p.stock,
                s.id as service_id, s.name as service_name, s.price as service_price, s.image as service_image
                FROM cart c
                LEFT JOIN products p ON c.product_id = p.id
                LEFT JOIN services s ON c.service_id = s.id
                WHERE c.buyer_id = ?";
$stmt = $conn->prepare($cart_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cart_result = $stmt->get_result();

$total = 0;
$cart_items = [];

while ($item = $cart_result->fetch_assoc()) {
    if ($item['product_id']) {
        $item['type'] = 'product';
        $item['name'] = $item['product_name'];
        $item['price'] = $item['product_price'];
        $item['image'] = $item['product_image'];
        $item['link'] = "/product_detail.php?id=" . $item['product_id'];
    } else {
        $item['type'] = 'service';
        $item['name'] = $item['service_name'];
        $item['price'] = $item['service_price'];
        $item['image'] = $item['service_image'];
        $item['link'] = "/service_detail.php?id=" . $item['service_id'];
    }
    $item['subtotal'] = $item['price'] * $item['quantity'];
    $total += $item['subtotal'];
    $cart_items[] = $item;
}

$pageTitle = 'Shopping Cart';
include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6 text-lazada-black">Shopping Cart</h1>
    
    <?php if (empty($cart_items)): ?>
        <div class="bg-white rounded-lg shadow-md p-12 text-center">
            <p class="text-xl mb-4 text-lazada-gray">Your cart is empty</p>
            <a href="<?php echo url('products.php'); ?>" class="btn-lazada">Continue Shopping →</a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-4">
                <?php foreach ($cart_items as $item): ?>
                    <div class="bg-white rounded-lg shadow-md p-4 flex flex-col md:flex-row gap-4">
                        <a href="<?php echo $item['link']; ?>" class="flex-shrink-0">
                            <div class="w-32 h-32 bg-gray-200 flex items-center justify-center rounded">
                                <?php if ($item['image']): ?>
                                    <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="w-full h-full object-cover rounded">
                                <?php else: ?>
                                    <span class="text-gray-400">No Image</span>
                                <?php endif; ?>
                            </div>
                        </a>
                        
                        <div class="flex-1">
                            <a href="<?php echo $item['link']; ?>">
                                <h3 class="font-semibold text-lg hover:opacity-70 text-lazada-black"><?php echo htmlspecialchars($item['name']); ?></h3>
                            </a>
                            <p class="text-sm mb-2 text-lazada-gray"><?php echo ucfirst($item['type']); ?></p>
                            <p class="font-bold text-xl mb-2 price-lazada">₱<?php echo number_format($item['price'], 2); ?></p>
                            
                            <div class="mt-4 flex items-center gap-4">
                                <?php if ($item['type'] === 'product'): ?>
                                    <form method="POST" class="flex items-center gap-2">
                                        <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                                        <label class="text-sm text-lazada-black">Qty:</label>
                                        <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $item['stock'] ?? 999; ?>"
                                               class="w-20 input-lazada">
                                        <button type="submit" name="update_quantity" class="text-sm link-lazada">Update</button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-sm text-lazada-gray">Quantity: <?php echo $item['quantity']; ?></span>
                                <?php endif; ?>
                                
                                <a href="<?php echo url('cart.php?remove=' . $item['id']); ?>" 
                                   class="text-sm text-lazada-red" 
                                   style="text-decoration: underline;"
                                   onclick="return confirm('Remove this item from cart?')">Remove</a>
                            </div>
                            
                            <p class="mt-2 text-lazada-black">Subtotal: <span class="font-bold">₱<?php echo number_format($item['subtotal'], 2); ?></span></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-md p-6 sticky top-4">
                    <h2 class="text-2xl font-bold mb-4 text-lazada-black">Order Summary</h2>
                    <div class="space-y-2 mb-4">
                        <div class="flex justify-between">
                            <span class="text-lazada-gray">Subtotal:</span>
                            <span class="font-semibold text-lazada-black">₱<?php echo number_format($total, 2); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-lazada-gray">Shipping:</span>
                            <span class="font-semibold text-lazada-black">₱0.00</span>
                        </div>
                        <div class="border-t pt-2 flex justify-between">
                            <span class="text-xl font-bold text-lazada-black">Total:</span>
                            <span class="text-xl font-bold price-lazada">₱<?php echo number_format($total, 2); ?></span>
                        </div>
                    </div>
                    
                    <a href="<?php echo url('checkout.php'); ?>" class="btn-lazada w-full text-center">
                        Proceed to Checkout
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php 
$stmt->close();
$conn->close();
include 'includes/footer.php'; 
?>

