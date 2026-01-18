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
    <div id="bg"></div>v>

	<!-- Forgot Password Modal -->
	<div class="modal active" id="forgotPasswordModal">
		<div class="modal-content">
			<span class="close" onclick="redirectTo('index')" style="position: absolute; top: 20px; right: 20px;">✕</span>
			<h2 style="margin-bottom: 30px;"><span>🔑</span> Reset Password</h2>
			
			<p style="color: rgba(232, 232, 240, 0.7); margin-bottom: 25px;">
				Enter your email address and we'll send you a link to reset your password.
			</p>
			
			<form>
				<div class="form-group">
					<label>Email</label>
					<input type="email" placeholder="your.email@campus.com">
				</div>
				
				<button type="submit" class="btn-primary" style="width: 100%; margin-bottom: 15px;">Send Reset Link</button>
				<p style="text-align: center; margin-top: 15px;">
					<a href="#" onclick="redirectTo('login')" style="color: #6366f1; text-decoration: none; font-size: 0.95em;">Back to Login</a>
				</p>
			</form>
		</div>
	</div>
</body>