<?php
require_once 'config/database.php';
require_once 'config/paths.php';
require_once 'config/auth.php';

$product_id = $_GET['id'] ?? 0;
$conn = getDBConnection();

$stmt = $conn->prepare("SELECT p.*, u.full_name as seller_name, c.name as category_name 
                        FROM products p 
                        LEFT JOIN users u ON p.seller_id = u.id 
                        LEFT JOIN categories c ON p.category_id = c.id 
                        WHERE p.id = ? AND p.status = 'active'");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    require_once 'config/paths.php';
    header('Location: ' . url('products.php'));
    exit();
}

$added_to_cart = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isLoggedIn() && hasRole('buyer')) {
    $quantity = intval($_POST['quantity'] ?? 1);
    $user_id = getCurrentUserId();
    
    // Check if already in cart
    $check_stmt = $conn->prepare("SELECT id, quantity FROM cart WHERE buyer_id = ? AND product_id = ?");
    $check_stmt->bind_param("ii", $user_id, $product_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        $cart_item = $check_result->fetch_assoc();
        $new_quantity = $cart_item['quantity'] + $quantity;
        $update_stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
        $update_stmt->bind_param("ii", $new_quantity, $cart_item['id']);
        $update_stmt->execute();
        $update_stmt->close();
    } else {
        $insert_stmt = $conn->prepare("INSERT INTO cart (buyer_id, product_id, quantity) VALUES (?, ?, ?)");
        $insert_stmt->bind_param("iii", $user_id, $product_id, $quantity);
        $insert_stmt->execute();
        $insert_stmt->close();
    }
    
    $check_stmt->close();
    $added_to_cart = true;
}

$pageTitle = $product['name'];
include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <?php if ($added_to_cart): ?>
        <div class="alert-success border border-green-400 px-4 py-3 rounded mb-4">
            Product added to cart successfully!
        </div>
    <?php endif; ?>
    
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="md:flex">
            <div class="md:w-1/2">
                <div class="h-96 bg-gray-200 flex items-center justify-center">
                    <?php if ($product['image']): ?>
                        <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-full h-full object-cover">
                    <?php else: ?>
                        <span class="text-gray-400 text-xl">No Image Available</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="md:w-1/2 p-8">
                <span class="text-sm text-lazada-gray"><?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?></span>
                <h1 class="text-3xl font-bold mb-4 text-lazada-black"><?php echo htmlspecialchars($product['name']); ?></h1>
                <p class="mb-4 text-lazada-gray">Sold by: <?php echo htmlspecialchars($product['seller_name']); ?></p>
                <p class="text-4xl font-bold mb-6 price-lazada">₱<?php echo number_format($product['price'], 2); ?></p>
                
                <div class="mb-6">
                    <?php if ($product['stock'] > 0): ?>
                        <span class="font-semibold stock-in">In Stock (<?php echo $product['stock']; ?> available)</span>
                    <?php else: ?>
                        <span class="font-semibold stock-out">Out of Stock</span>
                    <?php endif; ?>
                </div>
                
                <div class="mb-6">
                    <h3 class="font-semibold mb-2 text-lazada-black">Description</h3>
                    <p class="text-lazada-black"><?php echo nl2br(htmlspecialchars($product['description'] ?? 'No description available.')); ?></p>
                </div>
                
                <?php if (isLoggedIn() && hasRole('buyer') && $product['stock'] > 0): ?>
                    <form method="POST" class="flex items-center gap-4">
                        <label class="font-semibold text-lazada-black">Quantity:</label>
                        <input type="number" name="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>" 
                               class="w-20 input-lazada">
                        <button type="submit" class="btn-lazada">
                            Add to Cart
                        </button>
                    </form>
                <?php elseif (!isLoggedIn()): ?>
                    <p class="text-lazada-black">Please <a href="<?php echo url('login.php'); ?>" class="link-lazada">login</a> to add items to cart.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php 
$stmt->close();
$conn->close();
include 'includes/footer.php'; 
?>

