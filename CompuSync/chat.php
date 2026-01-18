<?php
session_start();
require_once 'shared/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Traiter l'envoi de message
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("INSERT INTO messages_chat (user_id, message) VALUES (?, ?)");
    $stmt->execute([$user_id, $_POST['message']]);
    header("Location: chat.php");
    exit();
}

// Récupérer les messages
$stmt = $pdo->query("
    SELECT m.*, u.nom, u.prenom 
    FROM messages_chat m
    LEFT JOIN utilisateurs u ON m.user_id = u.id
    ORDER BY m.date_envoi DESC
    LIMIT 50
");
$messages = array_reverse($stmt->fetchAll(PDO::FETCH_ASSOC));
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
				<!-- Chat -->
				<article id="chat" style="display: block;">
					<span class="close" onclick="redirectTo('index')">✕</span>
					<h2><span>💬</span> Campus Chat Room</h2>
					<p style="color: rgba(232, 232, 240, 0.7); margin-bottom: 25px;">Connect with your fellow students!</p>

					<div id="chatBox"
						style="background: rgba(0, 0, 0, 0.3); padding: 25px; border-radius: 16px; height: 400px; overflow-y: auto; margin: 20px 0; border: 1px solid rgba(99, 102, 241, 0.2);">
						<?php foreach ($messages as $m): ?>
						<div style="margin-bottom: 20px;">
							<strong style="color: <?php echo ($m['user_id'] % 2 == 0) ? '#6366f1' : '#8b5cf6'; ?>;">
								<?php echo htmlspecialchars($m['prenom'] . ' ' . substr($m['nom'], 0, 1)); ?>.
							</strong>
							<p style="background: rgba(<?php echo ($m['user_id'] % 2 == 0) ? '99, 102, 241' : '139, 92, 246'; ?>, 0.1); padding: 12px 16px; border-radius: 12px; margin-top: 8px; border-left: 3px solid <?php echo ($m['user_id'] % 2 == 0) ? '#6366f1' : '#8b5cf6'; ?>;">
								<?php echo htmlspecialchars($m['message']); ?>
							</p>
							<small style="color: rgba(232, 232, 240, 0.5); font-size: 0.85em;">
								<?php echo date('H:i', strtotime($m['date_envoi'])); ?>
							</small>
						</div>
						<?php endforeach; ?>
					</div>

					<form method="POST" style="display: flex; gap: 12px;">
						<input type="text" name="message" placeholder="Type your message..." style="flex: 1;" required>
						<button type="submit" class="btn-primary" style="padding: 14px 30px;">Send</button>
					</form>
				</article>
			</div>
		</div>
	</div>

	<script>
		// Auto-scroll vers le bas
		const chatBox = document.getElementById('chatBox');
		chatBox.scrollTop = chatBox.scrollHeight;
	</script>
</body>
</html>