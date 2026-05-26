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
    
    $sql = "SELECT rc.id as cart_id, p.*, 
                   rd.square_meters, rd.bedrooms, rd.bathrooms,
                   vd.make, vd.model, vd.year, vd.mileage
            FROM reservation_carts rc
            JOIN products p ON rc.product_id = p.id
            LEFT JOIN real_estate_details rd ON p.id = rd.product_id AND p.product_type = 'real_estate'
            LEFT JOIN vehicle_details vd ON p.id = vd.product_id AND p.product_type = 'vehicle'
            WHERE rc.user_id = :user_id
            ORDER BY rc.added_at DESC";
    
    $stmt = $db->prepare($sql);
    $stmt->execute([':user_id' => $userId]);
    $cartItems = $stmt->fetchAll();
    
    $totalReservation = 0;
    foreach ($cartItems as $item) {
        $totalReservation += $item['reservation_fee'];
    }
    
} catch (PDOException $e) {
    $cartItems = [];
    $totalReservation = 0;
}

include '../includes/header.php';
?>

<div class="container" style="padding: 2rem 0;">
    <h1 style="color: #191970; margin-bottom: 2rem;">🛒 My Reservation Cart</h1>
    
    <?php if (isset($_SESSION['message'])): ?>
        <div style="background: #d4edda; color: #155724; padding: 1rem; border-radius: 10px; margin-bottom: 1rem;">
            <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 1rem; border-radius: 10px; margin-bottom: 1rem;">
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>
    
    <?php if (empty($cartItems)): ?>
        <div style="text-align: center; padding: 4rem 2rem; background: white; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <div style="font-size: 4rem; margin-bottom: 1rem;">🛒</div>
            <h3 style="color: #191970; margin-bottom: 1rem;">Your cart is empty</h3>
            <p style="color: #666; margin-bottom: 2rem;">Browse our listings and add properties or vehicles to your reservation cart.</p>
            <a href="../index.php" style="background: #FF8C00; color: white; padding: 12px 30px; border-radius: 10px; text-decoration: none; font-weight: bold;">
                Browse Listings
            </a>
        </div>
    <?php else: ?>
        <?php foreach ($cartItems as $item): ?>
            <div style="background: white; border-radius: 15px; padding: 1.5rem; margin-bottom: 1rem; display: flex; gap: 1.5rem; align-items: center; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                <div style="width: 120px; height: 90px; background: #f0f0f0; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 2rem;">
                    <?php echo $item['product_type'] === 'real_estate' ? '🏠' : '🚗'; ?>
                </div>
                
                <div style="flex: 1;">
                    <h3 style="color: #191970; margin-bottom: 0.5rem;"><?php echo htmlspecialchars($item['title']); ?></h3>
                    <p style="color: #888; font-size: 0.9rem;">
                        <?php if ($item['product_type'] === 'real_estate'): ?>
                            <?php echo $item['square_meters']; ?> sqm | <?php echo $item['bedrooms']; ?> beds
                        <?php else: ?>
                            <?php echo $item['make'] . ' ' . $item['model']; ?> | <?php echo $item['year']; ?>
                        <?php endif; ?>
                    </p>
                </div>
                
                <div style="text-align: center;">
                    <div style="font-size: 1.3rem; font-weight: bold; color: #FF8C00;">
                        ₱<?php echo number_format($item['reservation_fee'], 2); ?>
                    </div>
                    <div style="font-size: 0.8rem; color: #888;">Reservation Fee</div>
                </div>
                
                <form method="POST" action="remove-from-cart.php" onsubmit="return confirm('Remove this item?');">
                    <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                    <button type="submit" style="background: #dc3545; color: white; border: none; padding: 8px 16px; border-radius: 8px; cursor: pointer;">
                        Remove
                    </button>
                </form>
            </div>
        <?php endforeach; ?>
        
        <div style="background: white; padding: 2rem; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); margin-top: 2rem; border: 2px solid #191970;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 1rem;">
                <span>Total Items:</span>
                <span style="font-weight: bold;"><?php echo count($cartItems); ?></span>
            </div>
            <div style="display: flex; justify-content: space-between; font-size: 1.3rem; font-weight: bold; border-top: 2px solid #eee; padding-top: 1rem;">
                <span>Total Reservation Fees:</span>
                <span style="color: #FF8C00;">₱<?php echo number_format($totalReservation, 2); ?></span>
            </div>
            
            <a href="checkout.php" style="display: block; text-align: center; background: #FF8C00; color: white; padding: 1rem; border-radius: 10px; text-decoration: none; font-weight: bold; font-size: 1.1rem; margin-top: 1.5rem;">
                Proceed to Checkout
            </a>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>