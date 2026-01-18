<?php
session_start();
require_once 'shared/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Traiter la soumission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("INSERT INTO objets_perdus (user_id, nom_objet, description, lieu_perte, date_perte, statut) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $user_id,
        $_POST['nom_objet'],
        $_POST['description'],
        $_POST['lieu'],
        $_POST['date_perte'],
        $_POST['statut']
    ]);
    header("Location: lostandfound.php");
    exit();
}

// Récupérer les objets perdus/trouvés
$stmt = $pdo->query("
    SELECT o.*, u.nom, u.prenom 
    FROM objets_perdus o
    LEFT JOIN utilisateurs u ON o.user_id = u.id
    WHERE o.statut IN ('lost', 'found')
    ORDER BY o.date_creation DESC
    LIMIT 20
");
$objets = $stmt->fetchAll(PDO::FETCH_ASSOC);
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

	<!-- Wrapper -->
	<div id="wrapper">
		<!-- Main Content Wrapper -->
		<div class="content-wrapper">
			<div id="main" class="active">
				<!-- Lost & Found -->
				<article id="lostandfound" style="display: block;">
					<span class="close" onclick="redirectTo('index')">✕</span>
					<h2><span>📦</span> Lost & Found</h2>

					<form method="POST"
						style="background: rgba(99, 102, 241, 0.05); border: 1px solid rgba(99, 102, 241, 0.2); padding: 30px; border-radius: 16px;">
						<div class="form-group">
							<label>Item Status</label>
							<select name="statut">
								<option value="lost">🔴 Lost</option>
								<option value="found">🟢 Found</option>
							</select>
						</div>

						<div class="form-group">
							<label>Item Name</label>
							<input type="text" name="nom_objet" placeholder="e.g., Blue notebook, iPhone 13..." required>
						</div>

						<div class="form-group">
							<label>Location</label>
							<input type="text" name="lieu" placeholder="Where was it lost/found?">
						</div>

						<div class="form-group">
							<label>Date</label>
							<input type="date" name="date_perte" value="<?php echo date('Y-m-d'); ?>">
						</div>

						<div class="form-group">
							<label>Description</label>
							<textarea name="description" placeholder="Additional details..."></textarea>
						</div>

						<button type="submit" class="btn-primary">Post Item</button>
					</form>

					<h3>Recent Items</h3>
					<?php foreach ($objets as $o): ?>
					<div class="stat-card" style="text-align: left; margin-top: 15px;">
						<p>
							<strong><?php echo $o['statut'] == 'lost' ? '🔴 LOST' : '🟢 FOUND'; ?> - <?php echo htmlspecialchars($o['nom_objet']); ?></strong>
						</p>
						<p style="margin: 10px 0; color: rgba(232, 232, 240, 0.8);">
							<?php echo htmlspecialchars($o['description']); ?>
						</p>
						<p style="color: rgba(232, 232, 240, 0.6);">
							📍 <?php echo htmlspecialchars($o['lieu_perte']); ?> • 
							<?php echo $o['prenom'] . ' ' . substr($o['nom'], 0, 1); ?>. • 
							<?php echo date('M d, Y', strtotime($o['date_creation'])); ?>
						</p>
					</div>
					<?php endforeach; ?>
				</article>
			</div>
		</div>
	</div>
</body>
</html>