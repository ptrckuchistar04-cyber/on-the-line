<?php
require_once 'config/config.php';
require_once 'includes/functions.php';

$productId = $_GET['id'] ?? 0;
$product = getProductDetails($productId);

if (!$product) {
    header('Location: index.php');
    exit;
}

include 'includes/header.php';
?>

<div class="container">
    <nav class="breadcrumb">
        <a href="index.php">Home</a> /
        <a href="index.php?category=<?php echo $product['product_type']; ?>">
            <?php echo ucfirst(str_replace('_', ' ', $product['product_type'])); ?>
        </a> /
        <span><?php echo htmlspecialchars($product['title']); ?></span>
    </nav>
    
    <div class="product-detail">
        <div class="product-gallery">
            <img src="<?php echo $product['main_image'] ?: 'assets/images/no-image.jpg'; ?>" 
                 alt="<?php echo htmlspecialchars($product['title']); ?>" 
                 class="main-image">
            
            <!-- Additional images would go here -->
            <?php
            $db = getDB();
            $stmt = $db->prepare("SELECT image_url FROM product_images WHERE product_id = :id");
            $stmt->execute([':id' => $productId]);
            $images = $stmt->fetchAll();
            ?>
            
            <?php if (!empty($images)): ?>
                <div class="thumbnail-gallery">
                    <?php foreach ($images as $image): ?>
                        <img src="<?php echo $image['image_url']; ?>" 
                             alt="Product image" class="thumbnail">
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="product-info-detail">
            <h1><?php echo htmlspecialchars($product['title']); ?></h1>
            
            <div class="price-section">
                <span class="price-label">Full Price:</span>
                <span class="price-value">₱<?php echo number_format($product['price'], 2); ?></span>
            </div>
            
            <div class="reservation-section">
                <span class="reservation-label">Reservation Fee:</span>
                <span class="reservation-value">₱<?php echo number_format($product['reservation_fee'], 2); ?></span>
            </div>
            
            <div class="description">
                <h3>Description</h3>
                <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
            </div>
            
            <?php if ($product['product_type'] === 'real_estate'): ?>
                <div class="specifications">
                    <h3>Property Details</h3>
                    <table>
                        <tr>
                            <td>Property Type:</td>
                            <td><?php echo ucfirst($product['property_type']); ?></td>
                        </tr>
                        <tr>
                            <td>Square Meters:</td>
                            <td><?php echo $product['square_meters']; ?> sqm</td>
                        </tr>
                        <tr>
                            <td>Bedrooms:</td>
                            <td><?php echo $product['bedrooms']; ?></td>
                        </tr>
                        <tr>
                            <td>Bathrooms:</td>
                            <td><?php echo $product['bathrooms']; ?></td>
                        </tr>
                        <tr>
                            <td>Year Built:</td>
                            <td><?php echo $product['year_built']; ?></td>
                        </tr>
                        <tr>
                            <td>Amenities:</td>
                            <td><?php echo htmlspecialchars($product['amenities']); ?></td>
                        </tr>
                    </table>
                </div>
            <?php else: ?>
                <div class="specifications">
                    <h3>Vehicle Details</h3>
                    <table>
                        <tr>
                            <td>Make & Model:</td>
                            <td><?php echo $product['make'] . ' ' . $product['model']; ?></td>
                        </tr>
                        <tr>
                            <td>Year:</td>
                            <td><?php echo $product['year']; ?></td>
                        </tr>
                        <tr>
                            <td>Mileage:</td>
                            <td><?php echo number_format($product['mileage']); ?> km</td>
                        </tr>
                        <tr>
                            <td>Transmission:</td>
                            <td><?php echo ucfirst($product['transmission']); ?></td>
                        </tr>
                        <tr>
                            <td>Fuel Type:</td>
                            <td><?php echo $product['fuel_type']; ?></td>
                        </tr>
                        <?php if ($product['modifications']): ?>
                        <tr>
                            <td>Modifications:</td>
                            <td><?php echo htmlspecialchars($product['modifications']); ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($product['vin']): ?>
                        <tr>
                            <td>VIN:</td>
                            <td><?php echo htmlspecialchars($product['vin']); ?></td>
                        </tr>
                        <?php endif; ?>
                    </table>
                </div>
            <?php endif; ?>
            
            <div class="product-actions-detail">
                <?php if (isLoggedIn() && !isAdmin()): ?>
                    <form method="POST" action="customer/add-to-cart.php">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        <button type="submit" class="btn btn-primary btn-lg">
                            Reserve Now - ₱<?php echo number_format($product['reservation_fee'], 2); ?>
                        </button>
                    </form>
                    
                    <button onclick="addToCompare(<?php echo $product['id']; ?>)" 
                            class="btn btn-outline btn-lg">
                        ⇆ Add to Compare
                    </button>
                <?php elseif (!isLoggedIn()): ?>
                    <a href="login.php" class="btn btn-primary btn-lg">
                        Login to Reserve
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>