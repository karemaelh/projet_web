<?php
session_start();
require_once 'shared/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$vote_message = '';

// Vérifier si l'utilisateur a déjà voté aujourd'hui
$stmt = $pdo->prepare("
    SELECT COUNT(*) as count 
    FROM chaos_meter 
    WHERE user_id = ? 
    AND DATE(date_enregistrement) = CURDATE()
");
$stmt->execute([$user_id]);
$has_voted_today = $stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0;

// Traiter la mise à jour du chaos meter
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_chaos'])) {
    if ($has_voted_today) {
        $vote_message = "You've already voted today! Come back tomorrow.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO chaos_meter (user_id, changement) VALUES (?, ?)");
        $stmt->execute([$user_id, intval($_POST['changement'])]);
        header("Location: dashboard.php");
        exit();
    }
}

// Statistiques
$stmt = $pdo->query("SELECT COUNT(*) as count FROM reclamations WHERE statut = 'active'");
$active_complaints = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

$stmt = $pdo->query("SELECT COUNT(*) as count FROM objets_perdus WHERE statut = 'lost'");
$lost_items = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

$stmt = $pdo->query("SELECT COUNT(*) as count FROM messages_chat WHERE date_envoi >= DATE_SUB(NOW(), INTERVAL 24 HOUR)");
$chat_messages = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

$stmt = $pdo->query("SELECT COUNT(*) as count FROM annonces WHERE date_creation >= DATE_SUB(NOW(), INTERVAL 7 DAY) AND (date_expiration IS NULL OR date_expiration > NOW())");
$new_announcements = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Chaos level
$stmt = $pdo->query("SELECT COALESCE(SUM(changement), 0) as total FROM chaos_meter WHERE date_enregistrement >= DATE_SUB(NOW(), INTERVAL 24 HOUR)");
$chaos_total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$chaos_level = max(0, min(100, 50 + $chaos_total));

if ($chaos_level < 30) {
    $chaosStatus = ['text' => 'Calm & Peaceful', 'color' => '#10b981'];
} elseif ($chaos_level < 60) {
    $chaosStatus = ['text' => 'Moderate Stress', 'color' => '#fbbf24'];
} else {
    $chaosStatus = ['text' => 'High Chaos', 'color' => '#ef4444'];
}

// Top complaint du jour (avec le plus de réactions)
$stmt = $pdo->query("
    SELECT r.categorie, r.description,
    (SELECT COUNT(*) FROM reclamation_reactions WHERE reclamation_id = r.id) as total_reactions
    FROM reclamations r
    WHERE r.statut = 'active' 
    AND DATE(r.date_creation) = CURDATE()
    ORDER BY total_reactions DESC, r.date_creation DESC
    LIMIT 1
");
$top_complaint = $stmt->fetch(PDO::FETCH_ASSOC);
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
                <!-- Dashboard -->
                <article id="dashboard" style="display: block;">
                    <span class="close" onclick="redirectTo('index')">✕</span>
                    <h2><span>🏠</span> Campus Dashboard</h2>

                    <!-- Chaos Meter -->
                    <div class="chaos-meter">
                        <h3>Campus Chaos Level</h3>
                        <div class="chaos-level"><?php echo round($chaos_level); ?>%</div>
                        <div class="chaos-bar">
                            <div class="chaos-fill" style="width: <?php echo $chaos_level; ?>%;"></div>
                        </div>
                        <p style="color: rgba(232, 232, 240, 0.7);">Current Status: <strong
                                style="color: <?php echo $chaosStatus['color']; ?>;"><?php echo $chaosStatus['text']; ?></strong></p>

                        <?php if (!empty($vote_message)): ?>
                        <div style="background: rgba(251, 191, 36, 0.1); border: 1px solid rgba(251, 191, 36, 0.3); color: #fbbf24; padding: 12px; border-radius: 8px; margin: 15px 0; text-align: center;">
                            ⚠️ <?php echo $vote_message; ?>
                        </div>
                        <?php endif; ?>

                        <?php if ($has_voted_today): ?>
                        <div style="background: rgba(99, 102, 241, 0.1); border: 1px solid rgba(99, 102, 241, 0.3); padding: 20px; border-radius: 12px; text-align: center; margin-top: 15px;">
                            <p style="color: rgba(232, 232, 240, 0.8); margin: 0;">
                                ✓ Thanks for voting today! Come back tomorrow to share your mood.
                            </p>
                        </div>
                        <?php else: ?>
                        <div class="mood-buttons">
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="update_chaos" value="1">
                                <input type="hidden" name="changement" value="5">
                                <button type="submit" class="mood-btn stressed">
                                    <span>😭</span> I'm stressed
                                </button>
                            </form>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="update_chaos" value="1">
                                <input type="hidden" name="changement" value="-5">
                                <button type="submit" class="mood-btn happy">
                                    <span>🙂</span> Today is fine
                                </button>
                            </form>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Stats Grid -->
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon">📢</div>
                            <div class="stat-value"><?php echo $active_complaints; ?></div>
                            <div style="color: rgba(232, 232, 240, 0.7);">Active Complaints</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">📦</div>
                            <div class="stat-value"><?php echo $lost_items; ?></div>
                            <div style="color: rgba(232, 232, 240, 0.7);">Lost Items</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">💬</div>
                            <div class="stat-value"><?php echo $chat_messages; ?></div>
                            <div style="color: rgba(232, 232, 240, 0.7);">Chat Messages</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">📣</div>
                            <div class="stat-value"><?php echo $new_announcements; ?></div>
                            <div style="color: rgba(232, 232, 240, 0.7);">New Announcements</div>
                        </div>
                    </div>

                    <h3>😭 Top Complaint of the Day</h3>
                    <?php if ($top_complaint): ?>
                    <div class="stat-card" style="text-align: left; margin-top: 15px;">
                        <p><strong>Category:</strong> <span style="color: #6366f1;"><?php echo htmlspecialchars($top_complaint['categorie']); ?></span></p>
                        <p style="margin: 15px 0; color: rgba(232, 232, 240, 0.8);"><em>"<?php echo htmlspecialchars($top_complaint['description']); ?>"</em></p>
                        <p style="color: rgba(232, 232, 240, 0.6);">🔥 <?php echo $top_complaint['total_reactions']; ?> Total Reactions</p>
                    </div>
                    <?php else: ?>
                    <div class="stat-card" style="text-align: left; margin-top: 15px;">
                        <p style="color: rgba(232, 232, 240, 0.6);">No complaints today! 🎉</p>
                    </div>
                    <?php endif; ?>
                </article>
            </div>
        </div>
    </div>
</body>
</html>