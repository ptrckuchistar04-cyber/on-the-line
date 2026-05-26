<?php
require_once 'config/config.php';
require_once 'includes/functions.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = sanitize($_POST['full_name']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    
    // Validation
    if (empty($fullName) || empty($email) || empty($password)) {
        $error = 'Please fill in all required fields';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters';
    } else {
        $db = getDB();
        
        // Check if email exists
        $stmt = $db->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        
        if ($stmt->fetch()) {
            $error = 'Email already registered';
        } else {
            // Create user
            $hashedPassword = hashPassword($password);
            $stmt = $db->prepare("INSERT INTO users (full_name, email, password_hash, role) VALUES (:name, :email, :password, 'customer')");
            
            if ($stmt->execute([
                ':name' => $fullName,
                ':email' => $email,
                ':password' => $hashedPassword
            ])) {
                $success = 'Registration successful! You can now <a href="login.php">sign in</a>';
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}

include 'includes/header.php';
?>

<div class="auth-container">
    <div class="auth-box">
        <h2>Join <span style="color: <?php echo COLOR_ORANGE; ?>">On The Line</span></h2>
        <p class="auth-subtitle">Start reserving your dream property today</p>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form method="POST" class="auth-form">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="full_name" required class="form-control">
            </div>
            
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" required class="form-control">
            </div>
            
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required class="form-control" minlength="8">
            </div>
            
            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" required class="form-control" minlength="8">
            </div>
            
            <button type="submit" class="btn btn-primary btn-block">Create Account</button>
        </form>
        
        <p class="auth-link">Already have an account? <a href="login.php">Sign in</a></p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>