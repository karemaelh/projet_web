<?php
	session_start();
	require_once 'shared/db.php';

	$erreur = '';
	$succes = '';

	// Traitement du formulaire d'inscription
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		$nom = trim($_POST['nom']);
		$prenom = trim($_POST['prenom']);
		$email = trim($_POST['email']);
		$mot_de_passe = $_POST['password'];
		$confirm_password = $_POST['confirm_password'];
		$accepte_conditions = isset($_POST['conditions']);

		// Validation
		if (empty($nom) || empty($prenom) || empty($email) || empty($mot_de_passe)) {
			$erreur = "Veuillez remplir tous les champs.";
		} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$erreur = "Veuillez entrer un email valide.";
		} elseif ($mot_de_passe !== $confirm_password) {
			$erreur = "Les mots de passe ne correspondent pas.";
		} elseif (strlen($mot_de_passe) < 6) {
			$erreur = "Le mot de passe doit contenir au moins 6 caractères.";
		} elseif (!$accepte_conditions) {
			$erreur = "Vous devez accepter les conditions d'utilisation.";
		} else {
			// Vérifier si l'email existe déjà
			$stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ?");
			$stmt->execute([$email]);

			if ($stmt->fetch()) {
				$erreur = "Cet email est déjà utilisé.";
			} else {
				// Hasher le mot de passe et insérer l'utilisateur
				$hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);

				$stmt = $pdo->prepare("INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe) VALUES (?, ?, ?, ?)");
				$stmt->execute([$nom, $prenom, $email, $hash]);

				$succes = "Compte créé avec succès ! Vous pouvez maintenant vous connecter.";
			}
		}
	}
?>
<!DOCTYPE HTML>
<html>
	<head>
		<title>CampuSync - Inscription</title>
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

		<!-- Register Modal -->
		<div class="modal active" id="registerModal">
			<div class="modal-content">
				<span class="close" onclick="redirectTo('index')"
					style="position: absolute; top: 20px; right: 20px;">✕</span>
				<h2 style="margin-bottom: 30px;"><span>✨</span> Create Account</h2>

				<?php if (!empty($erreur)): ?>
					<div class="erreur">
						<?php echo $erreur; ?>
					</div>
				<?php endif; ?>

				<?php if (!empty($succes)): ?>
					<div class="succes">
						<?php echo $succes; ?>
					</div>
				<?php endif; ?>

				<form method="POST" action="">
					<div class="form-group">
						<label>Nom</label>
						<input type="text" name="nom" placeholder="Doe"
							value="<?php echo isset($nom) ? htmlspecialchars($nom) : ''; ?>" required>
					</div>

					<div class="form-group">
						<label>Prénom</label>
						<input type="text" name="prenom" placeholder="John"
							value="<?php echo isset($prenom) ? htmlspecialchars($prenom) : ''; ?>" required>
					</div>

					<div class="form-group">
						<label>Email</label>
						<input type="email" name="email" placeholder="your.email@campus.com"
							value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
					</div>

					<div class="form-group">
						<label>Password</label>
						<input type="password" name="password" placeholder="Create a strong password" required>
					</div>

					<div class="form-group">
						<label>Confirm Password</label>
						<input type="password" name="confirm_password" placeholder="Re-enter your password" required>
					</div>

					<div class="form-group">
						<label style="display: flex; align-items: center; cursor: pointer;">
							<input type="checkbox" name="conditions" style="width: auto; margin-right: 10px;">
							I agree to the Terms & Conditions
						</label>
					</div>

					<button type="submit" class="btn-primary" style="width: 100%; margin-bottom: 15px;">Create
						Account</button>
					<p style="text-align: center; margin-top: 15px; color: rgba(232, 232, 240, 0.7);">
						Already have an account?
						<a href="#" onclick="redirectTo('login')" style="color: #6366f1; text-decoration: none;">Login</a>
					</p>
				</form>
			</div>
		</div>
	</body>
</html>