<?php
session_start();
require_once 'shared/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Récupérer les annonces actives
$stmt = $pdo->query("
    SELECT * 
    FROM annonces 
    WHERE date_expiration IS NULL OR date_expiration > NOW()
    ORDER BY date_creation DESC
    LIMIT 20
");
$annonces = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fonction pour calculer le temps écoulé
function timeAgo($date) {
    $diff = time() - strtotime($date);
    if ($diff < 3600) return floor($diff / 60) . ' minutes ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    return floor($diff / 86400) . ' days ago';
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

	<!-- Wrapper -->
	<div id="wrapper">
		<!-- Main Content Wrapper -->
		<div class="content-wrapper">
			<div id="main" class="active">
				<!-- Announcements -->
				<article id="announcements" style="display: block;">
					<span class="close" onclick="redirectTo('index')">✕</span>
					<h2><span>📣</span> Announcements</h2>

					<?php if (empty($annonces)): ?>
					<div class="stat-card" style="text-align: center;">
						<p style="color: rgba(232, 232, 240, 0.6);">No announcements at the moment.</p>
					</div>
					<?php else: ?>
						<?php foreach ($annonces as $a): ?>
						<div class="stat-card" style="text-align: left; margin-bottom: 20px;">
							<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
								<strong style="font-size: 1.1em;"><?php echo htmlspecialchars($a['titre']); ?></strong>
								<?php if ($a['categorie']): ?>
								<span style="background: linear-gradient(135deg, 
									<?php 
										if (stripos($a['categorie'], 'urgent') !== false) echo '#ef4444, #dc2626';
										elseif (stripos($a['categorie'], 'event') !== false) echo '#10b981, #059669';
										else echo '#6366f1, #4f46e5';
									?>); padding: 6px 14px; border-radius: 8px; font-size: 0.85em; font-weight: 600;">
									<?php echo strtoupper(htmlspecialchars($a['categorie'])); ?>
								</span>
								<?php endif; ?>
							</div>
							<p style="margin: 15px 0; color: rgba(232, 232, 240, 0.8);">
								<?php echo htmlspecialchars($a['contenu']); ?>
							</p>
							<p style="margin-top: 15px; color: rgba(232, 232, 240, 0.5); font-size: 0.9em;">
								Posted <?php echo timeAgo($a['date_creation']); ?>
							</p>
						</div>
						<?php endforeach; ?>
					<?php endif; ?>
				</article>
			</div>
		</div>
	</div>
</body>
</html>