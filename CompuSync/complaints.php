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
    $stmt = $pdo->prepare("INSERT INTO reclamations (user_id, categorie, description, mood, anonyme) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        $user_id,
        $_POST['categorie'],
        $_POST['description'],
        $_POST['mood'],
        isset($_POST['anonyme']) ? 1 : 0
    ]);
    header("Location: complaints.php");
    exit();
}

// Récupérer les réclamations
$stmt = $pdo->query("
    SELECT r.*, u.nom, u.prenom 
    FROM reclamations r
    LEFT JOIN utilisateurs u ON r.user_id = u.id
    WHERE r.statut = 'active'
    ORDER BY r.date_creation DESC
    LIMIT 10
");
$reclamations = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                <!-- Complaints -->
                <article id="complaints" style="display: block;">
                    <span class="close" onclick="redirectTo('index')">✕</span>
                    <h2><span>📢</span> Complaint Wall</h2>
                    <p style="color: rgba(232, 232, 240, 0.7); margin-bottom: 30px;">Vent your frustrations. Your voice
                        matters!</p>

                    <form method="POST"
                        style="background: rgba(99, 102, 241, 0.05); border: 1px solid rgba(99, 102, 241, 0.2); padding: 30px; border-radius: 16px;">
                        <div class="form-group">
                            <label>Category</label>
                            <select name="categorie">
                                <option>Administration</option>
                                <option>Teachers</option>
                                <option>Facilities</option>
                                <option>Exams</option>
                                <option>Cafeteria</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Your Complaint</label>
                            <textarea name="description" placeholder="What's bothering you today?"></textarea>
                        </div>

                        <div class="form-group">
                            <label>Choose Your Mood</label>
                            <input type="hidden" name="mood" id="mood">
                            <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                                <button type="button" onclick="setMood('angry')"
                                    style="font-size: 2em; padding: 15px 25px; width: auto;">😡</button>
                                <button type="button" onclick="setMood('sad')"
                                    style="font-size: 2em; padding: 15px 25px; width: auto;">😭</button>
                                <button type="button" onclick="setMood('laugh')"
                                    style="font-size: 2em; padding: 15px 25px; width: auto;">😂</button>
                            </div>
                        </div>

                        <div class="form-group">
                            <label style="display: flex; align-items: center; cursor: pointer;">
                                <input type="checkbox" name="anonyme" style="width: auto; margin-right: 10px;">
                                Post Anonymously
                            </label>
                        </div>

                        <button type="submit" class="btn-primary">Submit Complaint</button>
                    </form>

                    <h3>Recent Complaints</h3>
                    <?php foreach ($reclamations as $r): ?>
                    <div class="stat-card" style="text-align: left; margin-top: 15px;">
                        <p><strong style="color: #fbbf24;">
                            <?php echo $r['mood'] == 'angry' ? '😡' : ($r['mood'] == 'sad' ? '😭' : '😂'); ?> 
                            <?php echo ucfirst($r['mood']); ?> | <?php echo $r['categorie']; ?>
                        </strong></p>
                        <p style="margin: 15px 0; color: rgba(232, 232, 240, 0.8);">
                            <em>"<?php echo htmlspecialchars($r['description']); ?>"</em>
                        </p>
                        <p style="color: rgba(232, 232, 240, 0.6);">
                            <?php if (!$r['anonyme']) echo $r['prenom'] . ' ' . substr($r['nom'], 0, 1) . '. • '; ?>
                            <?php echo date('M d, Y', strtotime($r['date_creation'])); ?>
                        </p>
                    </div>
                    <?php endforeach; ?>
                </article>
            </div>
        </div>
    </div>

    <script>
        function setMood(m) {
            document.getElementById('mood').value = m;
        }
    </script>
</body>
</html>