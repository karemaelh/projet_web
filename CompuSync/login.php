<?php
	session_start();
	require_once 'shared/db.php';

	$erreur = '';

	// Traitement du formulaire de connexion
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		$email = trim($_POST['email']);
		$mot_de_passe = $_POST['password'];

		if (empty($email) || empty($mot_de_passe)) {
			$erreur = "Veuillez remplir tous les champs.";
		} else {
			// Rechercher l'utilisateur par email
			$stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ?");
			$stmt->execute([$email]);
			$utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);

			if ($utilisateur && password_verify($mot_de_passe, $utilisateur['mot_de_passe'])) {
				// Connexion réussie
				$_SESSION['user_id'] = $utilisateur['id'];
				$_SESSION['user_nom'] = $utilisateur['nom'];
				$_SESSION['user_prenom'] = $utilisateur['prenom'];
				$_SESSION['user_email'] = $utilisateur['email'];

				// Mettre à jour la dernière connexion
				$update = $pdo->prepare("UPDATE utilisateurs SET derniere_connexion = NOW() WHERE id = ?");
				$update->execute([$utilisateur['id']]);

				header("Location: dashboard.php");
				exit();
			} else {
				$erreur = "Email ou mot de passe incorrect.";
			}
		}
	}
?>
<!DOCTYPE HTML>
<html>

<head>
	<title>CampuSync - Connexion</title>
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

	<!-- Login Modal -->
	<div class="modal active" id="loginModal">
		<div class="modal-content">
			<span class="close" onclick="redirectTo('index')"
				style="position: absolute; top: 20px; right: 20px;">✕</span>
			<h2 style="margin-bottom: 30px;"><span>🔐</span> Login</h2>

			<?php if (!empty($erreur)): ?>
				<div class="erreur">
					<?php echo $erreur; ?>
				</div>
			<?php endif; ?>

			<form method="POST" action="">
				<div class="form-group">
					<label>Email</label>
					<input type="email" name="email" placeholder="your.email@campus.com" required>
				</div>

				<div class="form-group">
					<label>Password</label>
					<input type="password" name="password" placeholder="Enter your password" required>
				</div>

				<div class="form-group">
					<label style="display: flex; align-items: center; cursor: pointer;">
						<input type="checkbox" name="remember" style="width: auto; margin-right: 10px;">
						Remember me
					</label>
				</div>

				<button type="submit" class="btn-primary" style="width: 100%; margin-bottom: 15px;">Login</button>
				<button type="button" style="width: 100%; margin-bottom: 15px;" onclick="redirectTo('register')">Create
					New Account</button>
				<p style="text-align: center; margin-top: 15px;">
					<a href="#" onclick="redirectTo('forgotPassword')"
						style="color: #6366f1; text-decoration: none; font-size: 0.95em;">Forgot Password?</a>
				</p>
			</form>
		</div>
	</div>
</body>

</html>