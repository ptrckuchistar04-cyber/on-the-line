<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$userId = $_SESSION['user_id'];

try {
    $database = new Database();
    $db = $database->getConnection();
    
    $stmt = $db->prepare("SELECT * FROM orders WHERE user_id = :user_id ORDER BY created_at DESC");
    $stmt->execute([':user_id' => $userId]);
    $orders = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $orders = [];
}

include '../includes/header.php';
?>

<div class="container" style="padding: 2rem 0;">
    <h1 style="color: #191970; margin-bottom: 2rem;">📋 My Orders</h1>
    
    <?php if (empty($orders)): ?>
        <div style="text-align: center; padding: 4rem 2rem; background: white; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <div style="font-size: 4rem; margin-bottom: 1rem;">📋</div>
            <h3 style="color: #191970;">No orders yet</h3>
            <p style="color: #666; margin-bottom: 2rem;">Start browsing and reserve your dream property or vehicle!</p>
            <a href="../index.php" style="background: #FF8C00; color: white; padding: 12px 30px; border-radius: 10px; text-decoration: none; font-weight: bold;">
                Browse Listings
            </a>
        </div>
    <?php else: ?>
        <?php foreach ($orders as $order): ?>
            <div style="background: white; padding: 1.5rem; border-radius: 15px; margin-bottom: 1rem; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h3 style="color: #191970;">Order #<?php echo $order['order_number']; ?></h3>
                        <p style="color: #888;"><?php echo date('F j, Y', strtotime($order['created_at'])); ?></p>
                    </div>
                    <div style="font-size: 1.2rem; font-weight: bold; color: #FF8C00;">
                        ₱<?php echo number_format($order['total_reservation_fee'], 2); ?>
                    </div>
                    <span style="padding: 6px 16px; border-radius: 20px; font-weight: bold; background: <?php echo $order['status'] === 'paid' ? '#d4edda' : '#fff3cd'; ?>; color: <?php echo $order['status'] === 'paid' ? '#155724' : '#856404'; ?>;">
                        <?php echo ucfirst($order['status']); ?>
                    </span>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>