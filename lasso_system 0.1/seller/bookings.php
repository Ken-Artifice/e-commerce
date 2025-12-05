<?php
require_once '../config/database.php';
require_once '../config/paths.php';
require_once '../config/auth.php';
requireRole('seller');

$user_id = getCurrentUserId();
$conn = getDBConnection();

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $booking_id = intval($_POST['booking_id']);
    $status = $_POST['status'];
    $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ? AND service_id IN (SELECT id FROM services WHERE seller_id = ?)");
    $stmt->bind_param("sii", $status, $booking_id, $user_id);
    $stmt->execute();
    $stmt->close();
    header('Location: ' . url('seller/bookings.php'));
    exit();
}

$bookings_query = "SELECT b.*, s.name as service_name, s.price, u.full_name as buyer_name, u.email as buyer_email, u.phone as buyer_phone
                   FROM bookings b
                   JOIN services s ON b.service_id = s.id
                   JOIN users u ON b.buyer_id = u.id
                   WHERE s.seller_id = ? 
                   ORDER BY b.booking_date DESC";
$stmt = $conn->prepare($bookings_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$bookings_result = $stmt->get_result();

$pageTitle = 'Manage Bookings';
include '../includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6 text-gray-800">Manage Bookings</h1>
    
    <?php if ($bookings_result->num_rows === 0): ?>
        <div class="bg-white rounded-lg shadow-md p-12 text-center">
            <p class="text-gray-500 text-xl">No bookings yet</p>
        </div>
    <?php else: ?>
        <div class="space-y-4">
            <?php while ($booking = $bookings_result->fetch_assoc()): ?>
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="font-bold text-lg"><?php echo htmlspecialchars($booking['service_name']); ?></h3>
                            <p class="text-sm text-gray-500">Booked by: <?php echo htmlspecialchars($booking['buyer_name']); ?></p>
                            <p class="text-sm text-gray-500">Email: <?php echo htmlspecialchars($booking['buyer_email']); ?></p>
                            <?php if ($booking['buyer_phone']): ?>
                                <p class="text-sm text-gray-500">Phone: <?php echo htmlspecialchars($booking['buyer_phone']); ?></p>
                            <?php endif; ?>
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
                    </div>
                    
                    <?php if ($booking['status'] !== 'completed' && $booking['status'] !== 'cancelled'): ?>
                        <form method="POST" class="flex gap-2">
                            <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                            <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg">
                                <option value="pending" <?php echo $booking['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="confirmed" <?php echo $booking['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                <option value="completed" <?php echo $booking['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                            <button type="submit" name="update_status" class="bg-orange-500 text-white px-6 py-2 rounded-lg hover:bg-orange-600">
                                Update Status
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
</div>

<?php 
$stmt->close();
$conn->close();
include '../includes/footer.php'; 
?>

