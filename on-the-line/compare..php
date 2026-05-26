<?php
require_once 'config/config.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$compareIds = $_SESSION['compare_list'] ?? [];
$compareItems = getComparisonItems($compareIds);

// Handle remove action
if (isset($_GET['remove'])) {
    $removeId = (int)$_GET['remove'];
    if (($key = array_search($removeId, $_SESSION['compare_list'])) !== false) {
        unset($_SESSION['compare_list'][$key]);
        $_SESSION['compare_list'] = array_values($_SESSION['compare_list']);
    }
    header('Location: compare.php');
    exit;
}

include 'includes/header.php';
?>

<div class="container">
    <h1>Compare Products</h1>
    
    <?php if (count($compareItems) < 2 && !empty($_SESSION['compare_list'])): ?>
        <div class="alert alert-info">
            Add one more item to see the comparison
        </div>
    <?php endif; ?>
    
    <?php if (empty($compareItems)): ?>
        <div class="empty-compare">
            <h3>No items to compare</h3>
            <p>Browse our listings and add products to compare their features.</p>
            <a href="index.php" class="btn btn-primary">Browse Listings</a>
        </div>
    <?php else: ?>
        <div class="comparison-table-wrapper">
            <table class="comparison-table">
                <thead>
                    <tr>
                        <th>Feature</th>
                        <?php foreach ($compareItems as $item): ?>
                            <th>
                                <div class="compare-header">
                                    <img src="<?php echo $item['main_image'] ?: 'assets/images/no-image.jpg'; ?>" 
                                         alt="<?php echo htmlspecialchars($item['title']); ?>">
                                    <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                                    <a href="compare.php?remove=<?php echo $item['id']; ?>" 
                                       class="btn btn-danger btn-sm">Remove</a>
                                </div>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <!-- Common Features -->
                    <tr>
                        <td><strong>Type</strong></td>
                        <?php foreach ($compareItems as $item): ?>
                            <td>
                                <?php echo $item['product_type'] === 'real_estate' ? '🏠 Real Estate' : '🚗 Vehicle'; ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                    
                    <tr>
                        <td><strong>Price</strong></td>
                        <?php foreach ($compareItems as $item): ?>
                            <td>₱<?php echo number_format($item['price'], 2); ?></td>
                        <?php endforeach; ?>
                    </tr>
                    
                    <tr>
                        <td><strong>Reservation Fee</strong></td>
                        <?php foreach ($compareItems as $item): ?>
                            <td>₱<?php echo number_format($item['reservation_fee'], 2); ?></td>
                        <?php endforeach; ?>
                    </tr>
                    
                    <!-- Real Estate Specific Features -->
                    <?php if ($compareItems[0]['product_type'] === 'real_estate'): ?>
                        <tr>
                            <td><strong>Square Meters</strong></td>
                            <?php foreach ($compareItems as $item): ?>
                                <td><?php echo $item['square_meters']; ?> sqm</td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <td><strong>Bedrooms</strong></td>
                            <?php foreach ($compareItems as $item): ?>
                                <td><?php echo $item['bedrooms']; ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <td><strong>Bathrooms</strong></td>
                            <?php foreach ($compareItems as $item): ?>
                                <td><?php echo $item['bathrooms']; ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <td><strong>Amenities</strong></td>
                            <?php foreach ($compareItems as $item): ?>
                                <td><?php echo htmlspecialchars($item['amenities']); ?></td>
                            <?php endforeach; ?>
                        </tr>
                        
                    <!-- Vehicle Specific Features -->
                    <?php elseif ($compareItems[0]['product_type'] === 'vehicle'): ?>
                        <tr>
                            <td><strong>Make & Model</strong></td>
                            <?php foreach ($compareItems as $item): ?>
                                <td><?php echo $item['make'] . ' ' . $item['model']; ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <td><strong>Year</strong></td>
                            <?php foreach ($compareItems as $item): ?>
                                <td><?php echo $item['year']; ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <td><strong>Mileage</strong></td>
                            <?php foreach ($compareItems as $item): ?>
                                <td><?php echo number_format($item['mileage']); ?> km</td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <td><strong>Transmission</strong></td>
                            <?php foreach ($compareItems as $item): ?>
                                <td><?php echo ucfirst($item['transmission']); ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <td><strong>Modifications</strong></td>
                            <?php foreach ($compareItems as $item): ?>
                                <td><?php echo $item['modifications'] ?: 'None'; ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endif; ?>
                    
                    <tr>
                        <td><strong>Description</strong></td>
                        <?php foreach ($compareItems as $item): ?>
                            <td><?php echo htmlspecialchars(substr($item['description'], 0, 150)) . '...'; ?></td>
                        <?php endforeach; ?>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div class="compare-actions">
            <a href="index.php" class="btn btn-outline">Continue Browsing</a>
            <a href="compare.php?clear=1" class="btn btn-danger">Clear Comparison</a>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>