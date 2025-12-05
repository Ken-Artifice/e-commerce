<?php
require_once '../config/database.php';
require_once '../config/paths.php';
require_once '../config/auth.php';
requireRole('admin');

$conn = getDBConnection();

// Get date range filter
$start_date = $_GET['start_date'] ?? date('Y-m-01'); // First day of current month
$end_date = $_GET['end_date'] ?? date('Y-m-d'); // Today

// Sales Statistics
$total_sales = $conn->query("SELECT SUM(total_amount) as total FROM orders WHERE status != 'cancelled' AND created_at BETWEEN '$start_date' AND '$end_date 23:59:59'")->fetch_assoc()['total'] ?? 0;
$total_orders = $conn->query("SELECT COUNT(*) as count FROM orders WHERE created_at BETWEEN '$start_date' AND '$end_date 23:59:59'")->fetch_assoc()['count'];
$delivered_orders = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'delivered' AND created_at BETWEEN '$start_date' AND '$end_date 23:59:59'")->fetch_assoc()['count'];
$pending_orders = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status IN ('pending', 'processing', 'shipped') AND created_at BETWEEN '$start_date' AND '$end_date 23:59:59'")->fetch_assoc()['count'];

// Daily sales for chart
$daily_sales_query = "SELECT DATE(created_at) as date, SUM(total_amount) as daily_total, COUNT(*) as order_count 
                      FROM orders 
                      WHERE status != 'cancelled' AND created_at BETWEEN '$start_date' AND '$end_date 23:59:59'
                      GROUP BY DATE(created_at) 
                      ORDER BY date ASC";
$daily_sales = $conn->query($daily_sales_query);

// Top selling products
$top_products_query = "SELECT p.name, SUM(oi.quantity) as total_sold, SUM(oi.quantity * oi.price) as revenue
                       FROM order_items oi
                       JOIN products p ON oi.product_id = p.id
                       JOIN orders o ON oi.order_id = o.id
                       WHERE o.status != 'cancelled' AND o.created_at BETWEEN '$start_date' AND '$end_date 23:59:59'
                       GROUP BY p.id, p.name
                       ORDER BY total_sold DESC
                       LIMIT 10";
$top_products = $conn->query($top_products_query);

// Sales by seller
$sales_by_seller_query = "SELECT u.full_name as seller_name, COUNT(DISTINCT o.id) as order_count, SUM(oi.quantity * oi.price) as revenue
                          FROM order_items oi
                          JOIN products p ON oi.product_id = p.id
                          JOIN users u ON p.seller_id = u.id
                          JOIN orders o ON oi.order_id = o.id
                          WHERE o.status != 'cancelled' AND o.created_at BETWEEN '$start_date' AND '$end_date 23:59:59'
                          GROUP BY u.id, u.full_name
                          ORDER BY revenue DESC";
$sales_by_seller = $conn->query($sales_by_seller_query);

$pageTitle = 'Sales Monitoring';
include '../includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6 text-lazada-black">Sales Monitoring</h1>
    
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
            <div class="flex items-end">
                <button type="submit" class="btn-lazada">Filter</button>
            </div>
        </form>
    </div>
    
    <!-- Sales Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="dashboard-card">
            <h3 class="stat-label">Total Sales</h3>
            <p class="stat-value">₱<?php echo number_format($total_sales, 2); ?></p>
            <p class="text-sm text-lazada-gray mt-2">Period: <?php echo date('M d', strtotime($start_date)); ?> - <?php echo date('M d, Y', strtotime($end_date)); ?></p>
        </div>
        <div class="dashboard-card">
            <h3 class="stat-label">Total Orders</h3>
            <p class="stat-value"><?php echo $total_orders; ?></p>
        </div>
        <div class="dashboard-card">
            <h3 class="stat-label">Delivered</h3>
            <p class="text-3xl font-bold text-lazada-green"><?php echo $delivered_orders; ?></p>
        </div>
        <div class="dashboard-card">
            <h3 class="stat-label">Pending</h3>
            <p class="text-3xl font-bold text-lazada-yellow"><?php echo $pending_orders; ?></p>
        </div>
    </div>
    
    <!-- Daily Sales Chart -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-2xl font-bold mb-4 text-lazada-black">Daily Sales Trend</h2>
        <div class="overflow-x-auto">
            <table class="table-lazada">
                <thead>
                    <tr>
                        <th class="text-left py-2 text-lazada-black">Date</th>
                        <th class="text-right py-2 text-lazada-black">Orders</th>
                        <th class="text-right py-2 text-lazada-black">Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($day = $daily_sales->fetch_assoc()): ?>
                        <tr>
                            <td class="py-2 text-lazada-black"><?php echo date('M d, Y', strtotime($day['date'])); ?></td>
                            <td class="text-right py-2 text-lazada-black"><?php echo $day['order_count']; ?></td>
                            <td class="text-right py-2 text-lazada-black">₱<?php echo number_format($day['daily_total'], 2); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Top Products -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-2xl font-bold mb-4 text-lazada-black">Top Selling Products</h2>
        <div class="overflow-x-auto">
            <table class="table-lazada">
                <thead>
                    <tr>
                        <th class="text-left py-2 text-lazada-black">Product</th>
                        <th class="text-right py-2 text-lazada-black">Quantity Sold</th>
                        <th class="text-right py-2 text-lazada-black">Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($product = $top_products->fetch_assoc()): ?>
                        <tr>
                            <td class="py-2 text-lazada-black"><?php echo htmlspecialchars($product['name']); ?></td>
                            <td class="text-right py-2 text-lazada-black"><?php echo $product['total_sold']; ?></td>
                            <td class="text-right py-2 text-lazada-black">₱<?php echo number_format($product['revenue'], 2); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Sales by Seller -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-bold mb-4 text-lazada-black">Sales by Seller</h2>
        <div class="overflow-x-auto">
            <table class="table-lazada">
                <thead>
                    <tr>
                        <th class="text-left py-2 text-lazada-black">Seller</th>
                        <th class="text-right py-2 text-lazada-black">Orders</th>
                        <th class="text-right py-2 text-lazada-black">Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($seller = $sales_by_seller->fetch_assoc()): ?>
                        <tr>
                            <td class="py-2 text-lazada-black"><?php echo htmlspecialchars($seller['seller_name'] ?? 'Unknown'); ?></td>
                            <td class="text-right py-2 text-lazada-black"><?php echo $seller['order_count']; ?></td>
                            <td class="text-right py-2 text-lazada-black">₱<?php echo number_format($seller['revenue'], 2); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php 
$conn->close();
include '../includes/footer.php'; 
?>

