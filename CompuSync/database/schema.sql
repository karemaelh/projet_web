CREATE DATABASE IF NOT EXISTS campusync CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE campusync;

-- 2. Table des utilisateurs (devrait déjà exister)
CREATE TABLE IF NOT EXISTS utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    derniere_connexion TIMESTAMP NULL,
    INDEX idx_email (email)
);

-- 3. Table pour le chaos meter
CREATE TABLE IF NOT EXISTS chaos_meter (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    changement INT NOT NULL,
    date_enregistrement TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    INDEX idx_date (date_enregistrement)
);

-- 4. Table des réclamations
CREATE TABLE IF NOT EXISTS reclamations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    categorie ENUM('Administration', 'Teachers', 'Facilities', 'Exams', 'Cafeteria') NOT NULL,
    description TEXT NOT NULL,
    mood ENUM('angry', 'sad', 'laugh') NOT NULL,
    anonyme BOOLEAN DEFAULT FALSE,
    statut ENUM('active', 'resolved', 'archived') DEFAULT 'active',
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    INDEX idx_statut (statut),
    INDEX idx_date (date_creation)
);

-- 5. Table des objets perdus
CREATE TABLE IF NOT EXISTS objets_perdus (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    nom_objet VARCHAR(200) NOT NULL,
    description TEXT,
    lieu_perte VARCHAR(200),
    date_perte DATE,
    statut ENUM('lost', 'found', 'claimed') DEFAULT 'lost',
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    INDEX idx_statut (statut)
);

-- 6. Table des annonces
CREATE TABLE IF NOT EXISTS annonces (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(200) NOT NULL,
    contenu TEXT NOT NULL,
    categorie VARCHAR(100),
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_expiration TIMESTAMP NULL,
    INDEX idx_date (date_creation)
);

-- 7. Table des messages de chat
CREATE TABLE IF NOT EXISTS messages_chat (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    date_envoi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    INDEX idx_date (date_envoi)
);