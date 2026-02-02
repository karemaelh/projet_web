<?php
session_start();
require_once 'shared/db.php';

$message = '';
$error = '';

// Vérifier si on vient de forgotPassword.php
if (!isset($_SESSION['reset_email'])) {
    header("Location: forgotPassword.php");
    exit();
}

$email = $_SESSION['reset_email'];

// Traiter le formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($new_password)) {
        $error = "Please enter a new password.";
    } elseif (strlen($new_password) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif ($new_password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Mettre à jour le mot de passe
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE utilisateurs SET mot_de_passe = ? WHERE email = ?");
        $stmt->execute([$hashed_password, $email]);
        
        // Nettoyer la session
        unset($_SESSION['reset_email']);
        
        $message = "Password successfully reset! You can now login.";
    }
}
?>
<!DOCTYPE HTML>
<html>
<head>
    <title>CampuSync - Reset Password</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
    <link rel="stylesheet" href="files/styles.css">
    <script src="files/scripts.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
    </style>
</head>
<body>
    <!-- Background -->
    <div id="bg"></div>

    <!-- Reset Password Modal -->
    <div class="modal active" id="resetPasswordModal">
        <div class="modal-content">
            <span class="close" onclick="redirectTo('index')" style="position: absolute; top: 20px; right: 20px;">✕</span>
            <h2 style="margin-bottom: 30px;"><span>🔐</span> Reset Password</h2>
            
            <?php if (!empty($message)): ?>
                <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); color: #10b981; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    ✓ <?php echo htmlspecialchars($message); ?>
                </div>
                <button onclick="redirectTo('login')" class="btn-primary" style="width: 100%;">Go to Login</button>
            <?php else: ?>
                <?php if (!empty($error)): ?>
                    <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); color: #ef4444; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                        ✕ <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <p style="color: rgba(232, 232, 240, 0.7); margin-bottom: 25px;">
                    Enter your new password for <strong><?php echo htmlspecialchars($email); ?></strong>
                </p>
                
                <form method="POST">
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="password" placeholder="Enter new password" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Confirm Password</label>
                        <input type="password" name="confirm_password" placeholder="Confirm new password" required>
                    </div>
                    
                    <button type="submit" class="btn-primary" style="width: 100%; margin-bottom: 15px;">Reset Password</button>
                    <p style="text-align: center; margin-top: 15px;">
                        <a href="#" onclick="redirectTo('login')" style="color: #6366f1; text-decoration: none; font-size: 0.95em;">Back to Login</a>
                    </p>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>