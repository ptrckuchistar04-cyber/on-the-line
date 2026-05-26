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
    
    $sql = "SELECT rc.id as cart_id, p.*
            FROM reservation_carts rc
            JOIN products p ON rc.product_id = p.id
            WHERE rc.user_id = :user_id";
    
    $stmt = $db->prepare($sql);
    $stmt->execute([':user_id' => $userId]);
    $cartItems = $stmt->fetchAll();
    
    if (empty($cartItems)) {
        header('Location: cart.php');
        exit;
    }
    
    $totalReservation = 0;
    foreach ($cartItems as $item) {
        $totalReservation += $item['reservation_fee'];
    }
    
} catch (PDOException $e) {
    die('Error: ' . $e->getMessage());
}

// Process checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db->beginTransaction();
        
        $orderNumber = 'OTL-' . strtoupper(uniqid());
        
        // Create order
        $stmt = $db->prepare("INSERT INTO orders (user_id, order_number, total_reservation_fee, status) 
                             VALUES (:user_id, :order_number, :total, 'pending')");
        $stmt->execute([
            ':user_id' => $userId,
            ':order_number' => $orderNumber,
            ':total' => $totalReservation
        ]);
        
        $orderId = $db->lastInsertId();
        
        // Add order items
        $stmt = $db->prepare("INSERT INTO order_items (order_id, product_id, reservation_fee) 
                             VALUES (:order_id, :product_id, :fee)");
        
        foreach ($cartItems as $item) {
            $stmt->execute([
                ':order_id' => $orderId,
                ':product_id' => $item['id'],
                ':fee' => $item['reservation_fee']
            ]);
        }
        
        // Clear cart
        $stmt = $db->prepare("DELETE FROM reservation_carts WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $userId]);
        
        // Simulate payment success (for test mode)
        $stmt = $db->prepare("UPDATE orders SET status = 'paid', xendit_invoice_id = :invoice_id WHERE id = :order_id");
        $stmt->execute([
            ':invoice_id' => 'TEST-' . uniqid(),
            ':order_id' => $orderId
        ]);
        
        // Update product status
        $stmt = $db->prepare("UPDATE products p 
                             JOIN order_items oi ON p.id = oi.product_id 
                             SET p.status = 'reserved' 
                             WHERE oi.order_id = :order_id");
        $stmt->execute([':order_id' => $orderId]);
        
        $db->commit();
        
        header('Location: order-confirmation.php?order=' . $orderNumber);
        exit;
        
    } catch (Exception $e) {
        $db->rollBack();
        $error = 'Checkout failed: ' . $e->getMessage();
    }
}

include '../includes/header.php';
?>

<div class="container" style="padding: 2rem 0;">
    <h1 style="color: #191970; margin-bottom: 2rem;">💳 Checkout</h1>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
        <div style="background: white; padding: 2rem; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <h2 style="color: #191970; margin-bottom: 1.5rem;">Order Summary</h2>
            
            <?php foreach ($cartItems as $item): ?>
                <div style="display: flex; justify-content: space-between; padding: 1rem 0; border-bottom: 1px solid #eee;">
                    <div>
                        <h4><?php echo htmlspecialchars($item['title']); ?></h4>
                        <p style="color: #888;"><?php echo ucfirst($item['product_type']); ?></p>
                    </div>
                    <div style="font-weight: bold; color: #FF8C00;">
                        ₱<?php echo number_format($item['reservation_fee'], 2); ?>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <div style="font-size: 1.2rem; font-weight: bold; margin-top: 1.5rem; padding-top: 1.5rem; border-top: 2px solid #eee;">
                Total: ₱<?php echo number_format($totalReservation, 2); ?>
            </div>
        </div>
        
        <div style="background: white; padding: 2rem; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <h2 style="color: #191970; margin-bottom: 1.5rem;">Payment</h2>
            
            <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 10px; margin-bottom: 1.5rem;">
                <p><strong>Payment Method:</strong> Xendit Test Mode</p>
                <p style="color: #888; font-size: 0.9rem;">This is a test environment. No real payment will be processed.</p>
            </div>
            
            <form method="POST">
                <button type="submit" style="width: 100%; background: #FF8C00; color: white; border: none; padding: 1rem; border-radius: 10px; font-size: 1.1rem; font-weight: bold; cursor: pointer;">
                    Complete Reservation - ₱<?php echo number_format($totalReservation, 2); ?>
                </button>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>