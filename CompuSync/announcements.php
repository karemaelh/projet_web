<?php
session_start();
require_once 'shared/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Vérifier si l'utilisateur est admin
$stmt = $pdo->prepare("SELECT role FROM utilisateurs WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$is_admin = ($user && $user['role'] === 'admin');

// Traitement de la suppression (pour les admins)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_announcement']) && $is_admin) {
    $announcement_id = intval($_POST['announcement_id']);
    try {
        $stmt = $pdo->prepare("DELETE FROM annonces WHERE id = ?");
        $stmt->execute([$announcement_id]);
        $_SESSION['success'] = "Annonce supprimée avec succès !";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur lors de la suppression de l'annonce.";
    }
    header("Location: announcements.php");
    exit();
}

// Récupérer les annonces actives
$stmt = $pdo->query("
    SELECT a.*, u.nom, u.prenom 
    FROM annonces a
    LEFT JOIN utilisateurs u ON a.created_by = u.id
    WHERE a.date_expiration IS NULL OR a.date_expiration > NOW()
    ORDER BY a.date_creation DESC
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
		
		.admin-controls {
			display: flex;
			justify-content: space-between;
			align-items: center;
			margin-bottom: 25px;
			padding: 15px;
			background: rgba(99, 102, 241, 0.1);
			border-radius: 10px;
			border: 1px solid rgba(99, 102, 241, 0.3);
		}
		
		.btn-create {
			background: linear-gradient(135deg, #6366f1, #4f46e5);
			color: white;
			padding: 10px 20px;
			border: none;
			border-radius: 8px;
			font-weight: 600;
			cursor: pointer;
			text-decoration: none;
			display: inline-flex;
			align-items: center;
			gap: 8px;
			transition: all 0.3s ease;
		}
		
		.btn-create:hover {
			transform: translateY(-2px);
			box-shadow: 0 10px 20px rgba(99, 102, 241, 0.3);
		}
		
		.btn-delete {
			background: rgba(239, 68, 68, 0.2);
			color: #fca5a5;
			border: 1px solid rgba(239, 68, 68, 0.4);
			padding: 6px 12px;
			border-radius: 6px;
			cursor: pointer;
			font-size: 0.85em;
			font-weight: 600;
			transition: all 0.2s ease;
		}
		
		.btn-delete:hover {
			background: rgba(239, 68, 68, 0.3);
		}
		
		.alert {
			padding: 15px;
			border-radius: 8px;
			margin-bottom: 20px;
		}
		
		.alert-success {
			background: rgba(16, 185, 129, 0.2);
			border: 1px solid rgba(16, 185, 129, 0.4);
			color: #6ee7b7;
		}
		
		.alert-error {
			background: rgba(239, 68, 68, 0.2);
			border: 1px solid rgba(239, 68, 68, 0.4);
			color: #fca5a5;
		}
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

					<?php if (isset($_SESSION['success'])): ?>
					<div class="alert alert-success">
						<?php 
						echo htmlspecialchars($_SESSION['success']); 
						unset($_SESSION['success']);
						?>
					</div>
					<?php endif; ?>

					<?php if (isset($_SESSION['error'])): ?>
					<div class="alert alert-error">
						<?php 
						echo htmlspecialchars($_SESSION['error']); 
						unset($_SESSION['error']);
						?>
					</div>
					<?php endif; ?>

					<?php if ($is_admin): ?>
					<div class="admin-controls">
						<span style="color: rgba(232, 232, 240, 0.7);">
							👑 Mode Administrateur
						</span>
						<a href="create_announcement.php" class="btn-create">
							<span>➕</span> Créer une annonce
						</a>
					</div>
					<?php endif; ?>

					<?php if (empty($annonces)): ?>
					<div class="stat-card" style="text-align: center;">
						<p style="color: rgba(232, 232, 240, 0.6);">No announcements at the moment.</p>
					</div>
					<?php else: ?>
						<?php foreach ($annonces as $a): ?>
						<div class="stat-card" style="text-align: left; margin-bottom: 20px;">
							<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
								<strong style="font-size: 1.1em;"><?php echo htmlspecialchars($a['titre']); ?></strong>
								<div style="display: flex; gap: 10px; align-items: center;">
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
									
									<?php if ($is_admin): ?>
									<form method="POST" style="margin: 0;" 
										  onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette annonce ?');">
										<input type="hidden" name="announcement_id" value="<?php echo $a['id']; ?>">
										<input type="hidden" name="delete_announcement" value="1">
										<button type="submit" class="btn-delete">🗑️ Supprimer</button>
									</form>
									<?php endif; ?>
								</div>
							</div>
							<p style="margin: 15px 0; color: rgba(232, 232, 240, 0.8);">
								<?php echo htmlspecialchars($a['contenu']); ?>
							</p>
							<div style="display: flex; justify-content: space-between; align-items: center; margin-top: 15px;">
								<p style="color: rgba(232, 232, 240, 0.5); font-size: 0.9em; margin: 0;">
									Posted <?php echo timeAgo($a['date_creation']); ?>
									<?php if ($a['nom']): ?>
									by <?php echo htmlspecialchars($a['prenom'] . ' ' . $a['nom']); ?>
									<?php endif; ?>
								</p>
								<?php if ($a['date_expiration']): ?>
								<p style="color: rgba(239, 68, 68, 0.7); font-size: 0.85em; margin: 0;">
									⏰ Expire le <?php echo date('d/m/Y H:i', strtotime($a['date_expiration'])); ?>
								</p>
								<?php endif; ?>
							</div>
						</div>
						<?php endforeach; ?>
					<?php endif; ?>
				</article>
			</div>
		</div>
	</div>
</body>
</html>