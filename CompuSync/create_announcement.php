<?php
session_start();
require_once 'shared/db.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Vérifier si l'utilisateur est admin
$stmt = $pdo->prepare("SELECT role FROM utilisateurs WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || $user['role'] !== 'admin') {
    $_SESSION['error'] = "Accès refusé. Seuls les administrateurs peuvent créer des annonces.";
    header("Location: index.php");
    exit();
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = trim($_POST['titre'] ?? '');
    $contenu = trim($_POST['contenu'] ?? '');
    $categorie = trim($_POST['categorie'] ?? '');
    $date_expiration = !empty($_POST['date_expiration']) ? $_POST['date_expiration'] : null;
    
    if (empty($titre) || empty($contenu)) {
        $error = "Le titre et le contenu sont obligatoires.";
    } else {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO annonces (created_by, titre, contenu, categorie, date_expiration) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $_SESSION['user_id'],
                $titre,
                $contenu,
                $categorie,
                $date_expiration
            ]);
            
            $_SESSION['success'] = "Annonce créée avec succès !";
            header("Location: announcements.php");
            exit();
        } catch (PDOException $e) {
            $error = "Erreur lors de la création de l'annonce.";
        }
    }
}
?>
<!DOCTYPE HTML>
<html>
<head>
    <title>CampuSync - Créer une annonce</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
    <link rel="stylesheet" href="files/styles.css">
    <script src="files/scripts.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: rgba(232, 232, 240, 0.9);
            font-weight: 500;
        }
        
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px;
            background: rgba(30, 30, 50, 0.6);
            border: 1px solid rgba(99, 102, 241, 0.3);
            border-radius: 8px;
            color: rgba(232, 232, 240, 0.9);
            font-family: 'Inter', sans-serif;
            font-size: 1em;
        }
        
        .form-group textarea {
            min-height: 120px;
            resize: vertical;
        }
        
        .btn-submit {
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            font-size: 1em;
            transition: all 0.3s ease;
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(99, 102, 241, 0.3);
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-error {
            background: rgba(239, 68, 68, 0.2);
            border: 1px solid rgba(239, 68, 68, 0.4);
            color: #fca5a5;
        }
        
        .alert-success {
            background: rgba(16, 185, 129, 0.2);
            border: 1px solid rgba(16, 185, 129, 0.4);
            color: #6ee7b7;
        }
    </style>
</head>
<body>
    <div id="bg"></div>
    <div id="wrapper">
        <div class="content-wrapper">
            <div id="main" class="active">
                <article style="display: block;">
                    <span class="close" onclick="redirectTo('announcements')">✕</span>
                    <h2><span>📝</span> Créer une annonce</h2>
                    
                    <?php if (isset($error)): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" style="margin-top: 30px;">
                        <div class="form-group">
                            <label for="titre">Titre de l'annonce *</label>
                            <input type="text" id="titre" name="titre" required maxlength="200" 
                                   placeholder="Ex: URGENT: Final Exams Schedule">
                        </div>
                        
                        <div class="form-group">
                            <label for="categorie">Catégorie</label>
                            <select id="categorie" name="categorie">
                                <option value="">Sélectionner...</option>
                                <option value="urgent">🚨 Urgent</option>
                                <option value="event">🎉 Event</option>
                                <option value="info">ℹ️ Info</option>
                                <option value="academic">📚 Academic</option>
                                <option value="administrative">📋 Administrative</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="contenu">Contenu de l'annonce *</label>
                            <textarea id="contenu" name="contenu" required 
                                      placeholder="Détails de l'annonce..."></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="date_expiration">Date d'expiration (optionnel)</label>
                            <input type="datetime-local" id="date_expiration" name="date_expiration">
                            <small style="color: rgba(232, 232, 240, 0.5); display: block; margin-top: 5px;">
                                Laisser vide pour une annonce permanente
                            </small>
                        </div>
                        
                        <div style="display: flex; gap: 15px; margin-top: 30px;">
                            <button type="submit" class="btn-submit">Publier l'annonce</button>
                            <button type="button" class="btn-submit" 
                                    style="background: rgba(100, 100, 120, 0.4);"
                                    onclick="redirectTo('announcements')">
                                Annuler
                            </button>
                        </div>
                    </form>
                </article>
            </div>
        </div>
    </div>
</body>
</html>