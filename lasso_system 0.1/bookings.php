<?php
require_once 'config/database.php';
require_once 'config/paths.php';
require_once 'config/auth.php';
requireRole('buyer');

$user_id = getCurrentUserId();
$conn = getDBConnection();

// Handle cancel booking
if (isset($_GET['cancel'])) {
    $booking_id = intval($_GET['cancel']);
    $stmt = $conn->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ? AND buyer_id = ?");
    $stmt->bind_param("ii", $booking_id, $user_id);
    $stmt->execute();
    $stmt->close();
    header('Location: ' . url('bookings.php'));
    exit();
}

$bookings_query = "SELECT b.*, s.name as service_name, s.price, u.full_name as seller_name 
                   FROM bookings b
                   JOIN services s ON b.service_id = s.id
                   JOIN users u ON s.seller_id = u.id
                   WHERE b.buyer_id = ? 
                   ORDER BY b.booking_date DESC";
$stmt = $conn->prepare($bookings_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$bookings_result = $stmt->get_result();

$pageTitle = 'My Bookings';
include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6 text-gray-800">My Bookings</h1>
    
    <?php if ($bookings_result->num_rows === 0): ?>
        <div class="bg-white rounded-lg shadow-md p-12 text-center">
            <p class="text-gray-500 text-xl mb-4">You have no bookings yet</p>
            <a href="<?php echo url('services.php'); ?>" class="text-orange-500 hover:underline font-semibold">Browse Services →</a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <?php while ($booking = $bookings_result->fetch_assoc()): ?>
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="font-bold text-lg"><?php echo htmlspecialchars($booking['service_name']); ?></h3>
                            <p class="text-sm text-gray-500">Provider: <?php echo htmlspecialchars($booking['seller_name']); ?></p>
                        </div>
                        <span class="px-4 py-2 rounded-full text-sm font-semibold 
                            <?php 
                            echo $booking['status'] === 'completed' ? 'bg-green-100 text-green-800' : 
                                ($booking['status'] === 'cancelled' ? 'bg-red-100 text-red-800' : 
                                ($booking['status'] === 'confirmed' ? 'bg-blue-100 text-blue-800' : 
                                'bg-yellow-100 text-yellow-800')); 
                            ?>">
                            <?php echo ucfirst($booking['status']); ?>
                        </span>
                    </div>
                    
                    <div class="space-y-2 mb-4">
                        <p class="text-gray-700">
                            <strong>Date & Time:</strong> 
                            <?php echo date('M d, Y H:i', strtotime($booking['booking_date'])); ?>
                        </p>
                        <p class="text-gray-700">
                            <strong>Price:</strong> 
                            <span class="text-orange-500 font-bold">₱<?php echo number_format($booking['price'], 2); ?></span>
                        </p>
                        <?php if ($booking['notes']): ?>
                            <p class="text-gray-700">
                                <strong>Notes:</strong> 
                                <?php echo nl2br(htmlspecialchars($booking['notes'])); ?>
                            </p>
                        <?php endif; ?>
                        <p class="text-sm text-gray-500">
                            Booked on <?php echo date('M d, Y', strtotime($booking['created_at'])); ?>
                        </p>
                    </div>
                    
                    <?php if ($booking['status'] === 'pending' || $booking['status'] === 'confirmed'): ?>
                        <a href="<?php echo url('bookings.php?cancel=' . $booking['id']); ?>" 
                           class="block text-center bg-red-500 text-white py-2 rounded-lg hover:bg-red-600 transition"
                           onclick="return confirm('Cancel this booking?')">
                            Cancel Booking
                        </a>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
</div>

<?php 
$stmt->close();
$conn->close();
include 'includes/footer.php'; 
?>

