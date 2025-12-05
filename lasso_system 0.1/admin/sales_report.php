<?php
require_once '../config/database.php';
require_once '../config/paths.php';
require_once '../config/auth.php';
requireRole('admin');

$conn = getDBConnection();

// Get date range filter
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');
$report_type = $_GET['report_type'] ?? 'summary';

// Generate report based on type
if ($report_type === 'detailed') {
    // Detailed report with all orders
    $orders_query = "SELECT o.*, u.full_name as buyer_name, u.email as buyer_email 
                     FROM orders o 
                     JOIN users u ON o.buyer_id = u.id 
                     WHERE o.created_at BETWEEN '$start_date' AND '$end_date 23:59:59'
                     ORDER BY o.created_at DESC";
    $orders_result = $conn->query($orders_query);
}

// Summary statistics
$summary = [
    'total_sales' => $conn->query("SELECT SUM(total_amount) as total FROM orders WHERE status != 'cancelled' AND created_at BETWEEN '$start_date' AND '$end_date 23:59:59'")->fetch_assoc()['total'] ?? 0,
    'total_orders' => $conn->query("SELECT COUNT(*) as count FROM orders WHERE created_at BETWEEN '$start_date' AND '$end_date 23:59:59'")->fetch_assoc()['count'],
    'delivered' => $conn->query("SELECT COUNT(*) as count, SUM(total_amount) as total FROM orders WHERE status = 'delivered' AND created_at BETWEEN '$start_date' AND '$end_date 23:59:59'")->fetch_assoc(),
    'cancelled' => $conn->query("SELECT COUNT(*) as count, SUM(total_amount) as total FROM orders WHERE status = 'cancelled' AND created_at BETWEEN '$start_date' AND '$end_date 23:59:59'")->fetch_assoc(),
    'pending' => $conn->query("SELECT COUNT(*) as count, SUM(total_amount) as total FROM orders WHERE status IN ('pending', 'processing', 'shipped') AND created_at BETWEEN '$start_date' AND '$end_date 23:59:59'")->fetch_assoc(),
];

$pageTitle = 'Sales Report';
include '../includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-lazada-black">Sales Report</h1>
        <button onclick="window.print()" class="btn-lazada">Print Report</button>
    </div>
    
    <!-- Date Filter -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <form method="GET" class="flex flex-col md:flex-row gap-4">
            <div>
                <label class="block font-semibold mb-2 text-lazada-black">Start Date</label>
                <input type="date" name="start_date" value="<?php echo $start_date; ?>" 
                       class="input-lazada">
            </div>
            <div>
                <label class="block font-semibold mb-2 text-lazada-black">End Date</label>
                <input type="date" name="end_date" value="<?php echo $end_date; ?>" 
                       class="input-lazada">
            </div>
            <div>
                <label class="block font-semibold mb-2 text-lazada-black">Report Type</label>
                <select name="report_type" class="input-lazada">
                    <option value="summary" <?php echo $report_type === 'summary' ? 'selected' : ''; ?>>Summary</option>
                    <option value="detailed" <?php echo $report_type === 'detailed' ? 'selected' : ''; ?>>Detailed</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="btn-lazada">Generate Report</button>
            </div>
        </form>
    </div>
    
    <!-- Summary Report -->
    <?php if ($report_type === 'summary'): ?>
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-2xl font-bold mb-4 text-lazada-black">Sales Summary</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="font-semibold mb-2 text-lazada-black">Total Sales</h3>
                    <p class="text-2xl font-bold price-lazada">₱<?php echo number_format($summary['total_sales'], 2); ?></p>
                </div>
                <div>
                    <h3 class="font-semibold mb-2 text-lazada-black">Total Orders</h3>
                    <p class="text-2xl font-bold stat-value"><?php echo $summary['total_orders']; ?></p>
                </div>
                <div>
                    <h3 class="font-semibold mb-2 text-lazada-black">Delivered Orders</h3>
                    <p class="text-xl text-lazada-black"><?php echo $summary['delivered']['count']; ?> orders</p>
                    <p class="text-lg text-lazada-green">₱<?php echo number_format($summary['delivered']['total'] ?? 0, 2); ?></p>
                </div>
                <div>
                    <h3 class="font-semibold mb-2" style="color: #000000;">Pending Orders</h3>
                    <p class="text-xl" style="color: #000000;"><?php echo $summary['pending']['count']; ?> orders</p>
                    <p class="text-lg" style="color: #ffc107;">₱<?php echo number_format($summary['pending']['total'] ?? 0, 2); ?></p>
                </div>
                <div>
                    <h3 class="font-semibold mb-2" style="color: #000000;">Cancelled Orders</h3>
                    <p class="text-xl" style="color: #000000;"><?php echo $summary['cancelled']['count']; ?> orders</p>
                    <p class="text-lg" style="color: #E31E24;">₱<?php echo number_format($summary['cancelled']['total'] ?? 0, 2); ?></p>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Detailed Report -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-bold mb-4" style="color: #000000;">Detailed Sales Report</h2>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b">
                            <th class="text-left py-2" style="color: #000000;">Order ID</th>
                            <th class="text-left py-2" style="color: #000000;">Buyer</th>
                            <th class="text-left py-2" style="color: #000000;">Date</th>
                            <th class="text-right py-2" style="color: #000000;">Amount</th>
                            <th class="text-left py-2" style="color: #000000;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($order = $orders_result->fetch_assoc()): ?>
                            <tr class="border-b">
                                <td class="py-2" style="color: #000000;">#<?php echo $order['id']; ?></td>
                                <td class="py-2" style="color: #000000;"><?php echo htmlspecialchars($order['buyer_name']); ?></td>
                                <td class="py-2" style="color: #000000;"><?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?></td>
                                <td class="text-right py-2" style="color: #000000;">₱<?php echo number_format($order['total_amount'], 2); ?></td>
                                <td class="py-2">
                                    <span class="px-2 py-1 rounded text-xs <?php 
                                        echo $order['status'] === 'delivered' ? 'bg-green-100 text-green-800' : 
                                            ($order['status'] === 'cancelled' ? 'bg-red-100 text-red-800' : 
                                            'bg-yellow-100 text-yellow-800'); 
                                    ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php 
$conn->close();
include '../includes/footer.php'; 
?>

