<?php
session_start();
require_once '../config/database.php';

$orderNumber = $_GET['order'] ?? '';

if (!$orderNumber) {
    header('Location: ../index.php');
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    $stmt = $db->prepare("SELECT * FROM orders WHERE order_number = :order_number");
    $stmt->execute([':order_number' => $orderNumber]);
    $order = $stmt->fetch();
    
    if (!$order) {
        header('Location: ../index.php');
        exit;
    }
    
    $stmt = $db->prepare("SELECT oi.*, p.title, p.product_type 
                          FROM order_items oi 
                          JOIN products p ON oi.product_id = p.id 
                          WHERE oi.order_id = :order_id");
    $stmt->execute([':order_id' => $order['id']]);
    $orderItems = $stmt->fetchAll();
    
} catch (PDOException $e) {
    die('Error: ' . $e->getMessage());
}

include '../includes/header.php';
?>

<div class="container" style="padding: 2rem 0; text-align: center;">
    <div style="font-size: 5rem; margin-bottom: 1rem;">✅</div>
    <h1 style="color: #191970;">Reservation Confirmed!</h1>
    <p style="color: #666; margin-bottom: 2rem;">Your reservation has been successfully processed.</p>
    
    <div style="max-width: 600px; margin: 0 auto; background: white; padding: 2rem; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); text-align: left;">
        <h2 style="color: #191970; margin-bottom: 1.5rem;">Order Details</h2>
        
        <p><strong>Order Number:</strong> <?php echo $order['order_number']; ?></p>
        <p><strong>Date:</strong> <?php echo date('F j, Y \a\t g:i A', strtotime($order['created_at'])); ?></p>
        <p><strong>Status:</strong> 
            <span style="background: #d4edda; color: #155724; padding: 4px 12px; border-radius: 20px; font-weight: bold;">
                <?php echo ucfirst($order['status']); ?>
            </span>
        </p>
        
        <h3 style="color: #191970; margin: 1.5rem 0 1rem;">Items Reserved</h3>
        
        <?php foreach ($orderItems as $item): ?>
            <div style="display: flex; justify-content: space-between; padding: 0.75rem 0; border-bottom: 1px solid #eee;">
                <div>
                    <strong><?php echo htmlspecialchars($item['title']); ?></strong>
                    <p style="color: #888; font-size: 0.85rem;"><?php echo ucfirst($item['product_type']); ?></p>
                </div>
                <div style="font-weight: bold; color: #FF8C00;">
                    ₱<?php echo number_format($item['reservation_fee'], 2); ?>
                </div>
            </div>
        <?php endforeach; ?>
        
        <div style="font-size: 1.2rem; font-weight: bold; margin-top: 1.5rem; padding-top: 1rem; border-top: 2px solid #eee;">
            Total: ₱<?php echo number_format($order['total_reservation_fee'], 2); ?>
        </div>
    </div>
    
    <div style="margin-top: 2rem;">
        <a href="../index.php" style="background: #FF8C00; color: white; padding: 12px 30px; border-radius: 10px; text-decoration: none; font-weight: bold; margin-right: 1rem;">
            Continue Browsing
        </a>
        <a href="orders.php" style="color: #191970; text-decoration: none; font-weight: bold;">
            View My Orders →
        </a>
    </div>
</div>

<?php include '../includes/footer.php'; ?>