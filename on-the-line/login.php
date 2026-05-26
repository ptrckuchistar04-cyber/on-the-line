<?php
require_once 'config/config.php';
require_once 'includes/functions.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();
        
        if ($user && verifyPassword($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_role'] = $user['role'];
            
            if ($user['role'] === 'administrator') {
                header('Location: admin/dashboard.php');
            } else {
                header('Location: index.php');
            }
            exit;
        } else {
            $error = 'Invalid email or password';
        }
    }
}

include 'includes/header.php';
?>

<div class="auth-container">
    <div class="auth-box">
        <h2>Welcome Back to <span style="color: <?php echo COLOR_ORANGE; ?>">On The Line</span></h2>
        <p class="auth-subtitle">Sign in to manage your reservations</p>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" class="auth-form">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" required class="form-control">
            </div>
            
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required class="form-control">
            </div>
            
            <button type="submit" class="btn btn-primary btn-block">Sign In</button>
        </form>
        
        <p class="auth-link">Don't have an account? <a href="register.php">Create one here</a></p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>