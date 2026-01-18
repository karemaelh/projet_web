<?php
session_start();

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
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

    <!-- Top Navigation -->
    <div class="top-nav">
        <div class="logo-nav">
            <div class="logo-icon"></div>
            <span>CampuSync</span>
        </div>

        <?php if (!isset($_SESSION['user_id'])): ?>
            <button class="login-btn" onclick="redirectTo('login')">Login / Register</button>
        <?php endif; ?>
        <?php if (isset($_SESSION['user_id'])): ?>
            <button class="login-btn" onclick="window.location.href='index.php?logout=1'">Logout</button>
        <?php endif; ?>
    </div>

    <!-- Wrapper -->
    <div id="wrapper">
        <!-- Header -->
        <header id="header">
            <!-- Logo with decorative lines -->
            <div class="logo-section">
                <div class="hero-logo"></div>
            </div>

            <h1>CampuSync</h1>
            <p class="tagline">You never thought you need CAMPUSYNC in your life</p>

            <!-- Navigation with decorative line -->
            <div class="nav-section">
                <nav>
                    <ul>
                        <li>
                            <a href="dashboard.php">
                                <span>🏠</span><span>Dashboard</span>
                            </a>
                        </li>
                        <li>
                            <a href="complaints.php">
                                <span>📢</span><span>Complaints</span>
                            </a>
                        </li>
                        <li>
                            <a href="lostandfound.php">
                                <span>📦</span><span>Lost & Found</span>
                            </a>
                        </li>
                        <li>
                            <a href="chat.php">
                                <span>💬</span><span>Chat</span>
                            </a>
                        </li>
                        <li>
                            <a href="announcements.php">
                                <span>📣</span><span>Announcements</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </header>

        <!-- Main Content Wrapper -->
        <div class="content-wrapper">
        </div>

        <!-- Footer -->
        <footer>
            <p>&copy; 2025 CampuSync Platform</p>
        </footer>
    </div>
</body>
</html>