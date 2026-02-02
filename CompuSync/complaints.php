<?php
session_start();
require_once 'shared/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Traiter les réactions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['react'])) {
    $reclamation_id = intval($_POST['reclamation_id']);
    $reaction_type = $_POST['reaction_type'];
    
    // Vérifier si l'utilisateur a déjà réagi
    $stmt = $pdo->prepare("SELECT id FROM reclamation_reactions WHERE user_id = ? AND reclamation_id = ?");
    $stmt->execute([$user_id, $reclamation_id]);
    
    if ($stmt->fetch()) {
        // Mettre à jour la réaction
        $stmt = $pdo->prepare("UPDATE reclamation_reactions SET reaction_type = ? WHERE user_id = ? AND reclamation_id = ?");
        $stmt->execute([$reaction_type, $user_id, $reclamation_id]);
    } else {
        // Ajouter une nouvelle réaction
        $stmt = $pdo->prepare("INSERT INTO reclamation_reactions (user_id, reclamation_id, reaction_type) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $reclamation_id, $reaction_type]);
    }
    
    header("Location: complaints.php");
    exit();
}

// Traiter la soumission de réclamation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['react'])) {
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

// Récupérer les réclamations avec le nombre de réactions
$stmt = $pdo->query("
    SELECT r.*, u.nom, u.prenom,
    (SELECT COUNT(*) FROM reclamation_reactions WHERE reclamation_id = r.id AND reaction_type = 'agree') as agree_count,
    (SELECT COUNT(*) FROM reclamation_reactions WHERE reclamation_id = r.id AND reaction_type = 'laugh') as laugh_count,
    (SELECT COUNT(*) FROM reclamation_reactions WHERE reclamation_id = r.id AND reaction_type = 'sad') as sad_count
    FROM reclamations r
    LEFT JOIN utilisateurs u ON r.user_id = u.id
    WHERE r.statut = 'active'
    ORDER BY r.date_creation DESC
    LIMIT 10
");
$reclamations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les réactions de l'utilisateur actuel
$stmt = $pdo->prepare("SELECT reclamation_id, reaction_type FROM reclamation_reactions WHERE user_id = ?");
$stmt->execute([$user_id]);
$user_reactions = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $user_reactions[$row['reclamation_id']] = $row['reaction_type'];
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
        
        .reaction-btn {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 8px 15px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1.1em;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        
        .reaction-btn:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: scale(1.05);
        }
        
        .reaction-btn.active {
            background: rgba(99, 102, 241, 0.3);
            border-color: #6366f1;
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
                        <p style="color: rgba(232, 232, 240, 0.6); margin-bottom: 15px;">
                            <?php if (!$r['anonyme']) echo $r['prenom'] . ' ' . substr($r['nom'], 0, 1) . '. • '; ?>
                            <?php echo date('M d, Y', strtotime($r['date_creation'])); ?>
                        </p>
                        
                        <!-- Reactions -->
                        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="react" value="1">
                                <input type="hidden" name="reclamation_id" value="<?php echo $r['id']; ?>">
                                <input type="hidden" name="reaction_type" value="agree">
                                <button type="submit" class="reaction-btn <?php echo isset($user_reactions[$r['id']]) && $user_reactions[$r['id']] == 'agree' ? 'active' : ''; ?>">
                                    👍 <span><?php echo $r['agree_count']; ?></span>
                                </button>
                            </form>
                            
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="react" value="1">
                                <input type="hidden" name="reclamation_id" value="<?php echo $r['id']; ?>">
                                <input type="hidden" name="reaction_type" value="laugh">
                                <button type="submit" class="reaction-btn <?php echo isset($user_reactions[$r['id']]) && $user_reactions[$r['id']] == 'laugh' ? 'active' : ''; ?>">
                                    😂 <span><?php echo $r['laugh_count']; ?></span>
                                </button>
                            </form>
                            
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="react" value="1">
                                <input type="hidden" name="reclamation_id" value="<?php echo $r['id']; ?>">
                                <input type="hidden" name="reaction_type" value="sad">
                                <button type="submit" class="reaction-btn <?php echo isset($user_reactions[$r['id']]) && $user_reactions[$r['id']] == 'sad' ? 'active' : ''; ?>">
                                    😭 <span><?php echo $r['sad_count']; ?></span>
                                </button>
                            </form>
                        </div>
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