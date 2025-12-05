<?php
require_once 'config/database.php';
require_once 'config/paths.php';
require_once 'config/auth.php';

$service_id = $_GET['id'] ?? 0;
$conn = getDBConnection();

$stmt = $conn->prepare("SELECT s.*, u.full_name as seller_name, c.name as category_name 
                        FROM services s 
                        LEFT JOIN users u ON s.seller_id = u.id 
                        LEFT JOIN categories c ON s.category_id = c.id 
                        WHERE s.id = ? AND s.status = 'active'");
$stmt->bind_param("i", $service_id);
$stmt->execute();
$result = $stmt->get_result();
$service = $result->fetch_assoc();

if (!$service) {
    require_once 'config/paths.php';
    header('Location: ' . url('services.php'));
    exit();
}

$booking_success = false;
$booking_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isLoggedIn() && hasRole('buyer')) {
    $booking_date = $_POST['booking_date'] ?? '';
    $booking_time = $_POST['booking_time'] ?? '';
    $notes = trim($_POST['notes'] ?? '');
    
    if (empty($booking_date) || empty($booking_time)) {
        $booking_error = 'Please select date and time';
    } else {
        $datetime = $booking_date . ' ' . $booking_time;
        $user_id = getCurrentUserId();
        
        $insert_stmt = $conn->prepare("INSERT INTO bookings (buyer_id, service_id, booking_date, notes) VALUES (?, ?, ?, ?)");
        $insert_stmt->bind_param("iiss", $user_id, $service_id, $datetime, $notes);
        
        if ($insert_stmt->execute()) {
            $booking_success = true;
        } else {
            $booking_error = 'Failed to create booking. Please try again.';
        }
        
        $insert_stmt->close();
    }
}

$pageTitle = $service['name'];
include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <?php if ($booking_success): ?>
        <div class="alert-success border border-green-400 px-4 py-3 rounded mb-4">
            Booking created successfully! Check your bookings page for details.
        </div>
    <?php endif; ?>
    
    <?php if ($booking_error): ?>
        <div class="alert-error border border-red-400 px-4 py-3 rounded mb-4">
            <?php echo htmlspecialchars($booking_error); ?>
        </div>
    <?php endif; ?>
    
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="md:flex">
            <div class="md:w-1/2">
                <div class="h-96 bg-gray-200 flex items-center justify-center">
                    <?php if ($service['image']): ?>
                        <img src="<?php echo htmlspecialchars($service['image']); ?>" alt="<?php echo htmlspecialchars($service['name']); ?>" class="w-full h-full object-cover">
                    <?php else: ?>
                        <span class="text-gray-400 text-xl">No Image Available</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="md:w-1/2 p-8">
                <span class="text-sm text-lazada-gray"><?php echo htmlspecialchars($service['category_name'] ?? 'Uncategorized'); ?></span>
                <h1 class="text-3xl font-bold mb-4 text-lazada-black"><?php echo htmlspecialchars($service['name']); ?></h1>
                <p class="mb-4 text-lazada-gray">Service provider: <?php echo htmlspecialchars($service['seller_name']); ?></p>
                <p class="text-4xl font-bold mb-6 price-lazada">₱<?php echo number_format($service['price'], 2); ?></p>
                
                <div class="mb-6">
                    <p class="text-lazada-black"><span class="font-semibold">Duration:</span> <?php echo $service['duration']; ?> minutes</p>
                </div>
                
                <div class="mb-6">
                    <h3 class="font-semibold mb-2 text-lazada-black">Description</h3>
                    <p class="text-lazada-black"><?php echo nl2br(htmlspecialchars($service['description'] ?? 'No description available.')); ?></p>
                </div>
                
                <?php if (isLoggedIn() && hasRole('buyer')): ?>
                    <form method="POST" class="space-y-4">
                        <div>
                            <label class="block font-semibold mb-2 text-lazada-black">Booking Date</label>
                            <input type="date" name="booking_date" required min="<?php echo date('Y-m-d'); ?>"
                                   class="w-full input-lazada focus:ring-2 focus:ring-orange-500">
                        </div>
                        
                        <div>
                            <label class="block font-semibold mb-2 text-lazada-black">Booking Time</label>
                            <input type="time" name="booking_time" required
                                   class="w-full input-lazada focus:ring-2 focus:ring-orange-500">
                        </div>
                        
                        <div>
                            <label class="block font-semibold mb-2 text-lazada-black">Additional Notes (Optional)</label>
                            <textarea name="notes" rows="3" 
                                      class="w-full input-lazada focus:ring-2 focus:ring-orange-500"></textarea>
                        </div>
                        
                        <button type="submit" class="btn-lazada w-full">
                            Book Service
                        </button>
                    </form>
                <?php elseif (!isLoggedIn()): ?>
                    <p class="text-lazada-black">Please <a href="<?php echo url('login.php'); ?>" class="link-lazada">login</a> to book this service.</p>
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

