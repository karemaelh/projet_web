<?php
session_start();
require_once 'shared/db.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    
    // Vérifier si l'email existe
    $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        // Stocker l'email en session pour la réinitialisation
        $_SESSION['reset_email'] = $email;
        header("Location: resetPassword.php");
        exit();
    } else {
        $error = "This email is not registered.";
    }
}
?>
<!DOCTYPE HTML>
<html>
<head>
    <title>CampuSync</title>
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

	<!-- Forgot Password Modal -->
	<div class="modal active" id="forgotPasswordModal">
		<div class="modal-content">
			<span class="close" onclick="redirectTo('index')" style="position: absolute; top: 20px; right: 20px;">✕</span>
			<h2 style="margin-bottom: 30px;"><span>🔑</span> Reset Password</h2>
			
			<?php if (!empty($error)): ?>
				<div style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); color: #ef4444; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
					✕ <?php echo htmlspecialchars($error); ?>
				</div>
			<?php endif; ?>
			
			<p style="color: rgba(232, 232, 240, 0.7); margin-bottom: 25px;">
				Enter your email address to reset your password.
			</p>
			
			<form method="POST">
				<div class="form-group">
					<label>Email</label>
					<input type="email" name="email" placeholder="your.email@campus.com" required>
				</div>
				
				<button type="submit" class="btn-primary" style="width: 100%; margin-bottom: 15px;">Continue</button>
				<p style="text-align: center; margin-top: 15px;">
					<a href="#" onclick="redirectTo('login')" style="color: #6366f1; text-decoration: none; font-size: 0.95em;">Back to Login</a>
				</p>
			</form>
		</div>
	</div>
</body>
</html> 	