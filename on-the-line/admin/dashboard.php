<?php
require_once '../config/config.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit;
}

$db = getDB();

// Get statistics
$stats = [];

// Total sales (reservation fees collected)
$stmt = $db->query("SELECT COALESCE(SUM(total_reservation_fee), 0) as total_sales 
                    FROM orders WHERE status IN ('paid', 'processing', 'completed')");
$stats['total_sales'] = $stmt->fetch()['total_sales'];

// Number of orders
$stmt = $db->query("SELECT COUNT(*) as order_count FROM orders");
$stats['order_count'] = $stmt->fetch()['order_count'];

// Product inventory
$stmt = $db->query("SELECT COUNT(*) as product_count FROM products WHERE status = 'available'");
$stats['product_count'] = $stmt->fetch()['product_count'];

// Registered users
$stmt = $db->query("SELECT COUNT(*) as user_count FROM users WHERE role = 'customer'");
$stats['user_count'] = $stmt->fetch()['user_count'];

// Recent orders
$stmt = $db->query("SELECT o.*, u.full_name as customer_name 
                    FROM orders o 
                    JOIN users u ON o.user_id = u.id 
                    ORDER BY o.created_at DESC LIMIT 5");
$recentOrders = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="admin-layout">
    <?php include 'sidebar.php'; ?>
    
    <div class="admin-content">
        <h1>Admin Dashboard</h1>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">💰</div>
                <div class="stat-info">
                    <h3>Total Sales</h3>
                    <p class="stat-value">₱<?php echo number_format($stats['total_sales'], 2); ?></p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">📋</div>
                <div class="stat-info">
                    <h3>Total Orders</h3>
                    <p class="stat-value"><?php echo $stats['order_count']; ?></p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">🏷️</div>
                <div class="stat-info">
                    <h3>Active Products</h3>
                    <p class="stat-value"><?php echo $stats['product_count']; ?></p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <div class="stat-info">
                    <h3>Customers</h3>
                    <p class="stat-value"><?php echo $stats['user_count']; ?></p>
                </div>
            </div>
        </div>
        
        <div class="dashboard-section">
            <h2>Recent Orders</h2>
            
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentOrders as $order): ?>
                        <tr>
                            <td><?php echo $order['order_number']; ?></td>
                            <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                            <td>₱<?php echo number_format($order['total_reservation_fee'], 2); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo $order['status']; ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>