<?php
require_once '../config/database.php';
require_once '../config/paths.php';
require_once '../config/auth.php';
requireRole('admin');

$conn = getDBConnection();

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $booking_id = intval($_POST['booking_id']);
    $status = $_POST['status'];
    $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $booking_id);
    $stmt->execute();
    $stmt->close();
    require_once '../config/paths.php';
    header('Location: ' . url('admin/bookings.php'));
    exit();
}

// Get all bookings
$bookings_query = "SELECT b.*, s.name as service_name, s.price, 
                   u1.full_name as buyer_name, u1.email as buyer_email,
                   u2.full_name as seller_name
                   FROM bookings b
                   JOIN services s ON b.service_id = s.id
                   JOIN users u1 ON b.buyer_id = u1.id
                   JOIN users u2 ON s.seller_id = u2.id
                   ORDER BY b.booking_date DESC";
$bookings_result = $conn->query($bookings_query);

$pageTitle = 'All Bookings';
include '../includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6 text-gray-800">All Bookings</h1>
    
    <div class="space-y-4">
        <?php while ($booking = $bookings_result->fetch_assoc()): ?>
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="font-bold text-lg"><?php echo htmlspecialchars($booking['service_name']); ?></h3>
                        <p class="text-sm text-gray-500">Buyer: <?php echo htmlspecialchars($booking['buyer_name']); ?> (<?php echo htmlspecialchars($booking['buyer_email']); ?>)</p>
                        <p class="text-sm text-gray-500">Seller: <?php echo htmlspecialchars($booking['seller_name']); ?></p>
                    </div>
                    <form method="POST" class="flex items-center gap-2">
                        <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                        <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg">
                            <option value="pending" <?php echo $booking['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="confirmed" <?php echo $booking['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                            <option value="completed" <?php echo $booking['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                            <option value="cancelled" <?php echo $booking['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                        <button type="submit" name="update_status" class="bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600">
                            Update
                        </button>
                    </form>
                </div>
                
                <div class="space-y-2">
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
            </div>
        <?php endwhile; ?>
    </div>
</div>

<?php 
$conn->close();
include '../includes/footer.php'; 
?>

