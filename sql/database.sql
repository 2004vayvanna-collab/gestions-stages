-- =============================================================
-- database.sql
-- Base de données : gestion_stage
-- ENSI 2025/2026 — Partie IV
-- Tables basées sur les objets JS de la Partie III
-- =============================================================

CREATE DATABASE IF NOT EXISTS gestion_stage
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE gestion_stage;

-- -------------------------------------------------------------
-- TABLE 1 : candidature
-- Correspond à l'objet JS : Candidature(id, poste, entreprise,
--   date, statut, ville, domaine)
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS candidature (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  poste       VARCHAR(150) NOT NULL,
  entreprise  VARCHAR(150) NOT NULL,
  date_candidature DATE NOT NULL,
  statut      ENUM('En cours','Accepté','Refusé') DEFAULT 'En cours',
  ville       VARCHAR(100) NOT NULL,
  domaine     VARCHAR(100) NOT NULL
);

-- -------------------------------------------------------------
-- TABLE 2 : etudiant
-- Correspond aux données saisies dans espace-etudiant.html
-- Contient une photo (champ obligatoire selon le sujet)
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS etudiant (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  nom         VARCHAR(100) NOT NULL,
  prenom      VARCHAR(100) NOT NULL,
  email       VARCHAR(150) NOT NULL UNIQUE,
  telephone   VARCHAR(20),
  ville       VARCHAR(100),
  domaine     VARCHAR(100),
  photo       VARCHAR(255) DEFAULT 'default.jpg',
  date_inscription DATE DEFAULT (CURRENT_DATE)
);

-- -------------------------------------------------------------
-- TABLE 3 : entreprise
-- Correspond à l'espace-entreprise, offres de stage
-- Contient un logo (photo)
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS entreprise (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  nom         VARCHAR(150) NOT NULL,
  secteur     VARCHAR(100) NOT NULL,
  ville       VARCHAR(100) NOT NULL,
  email       VARCHAR(150),
  telephone   VARCHAR(20),
  logo        VARCHAR(255) DEFAULT 'default-logo.png',
  description TEXT
);

-- -------------------------------------------------------------
-- DONNÉES : 5 enregistrements par table
-- -------------------------------------------------------------

INSERT INTO candidature (poste, entreprise, date_candidature, statut, ville, domaine) VALUES
('Développeur Web Front-End', 'TechVision SARL',  '2026-03-01', 'En cours', 'Tunis',  'Informatique'),
('Designer UX/UI',            'PixelStudio',      '2026-02-25', 'Accepté',  'Tunis',  'Design'),
('Analyste Marketing',        'MediaPro',         '2026-02-18', 'Refusé',   'Sousse', 'Marketing'),
('Développeur Back-End',      'InnoSoft',         '2026-03-10', 'En cours', 'Sfax',   'Informatique'),
('Data Analyst',              'DataCore TN',      '2026-03-05', 'Accepté',  'Tunis',  'Informatique');

INSERT INTO etudiant (nom, prenom, email, telephone, ville, domaine, photo) VALUES
('Ben Ali',   'Ahmed',  'ahmed.benali@ensi.tn',   '22334455', 'Tunis',    'Informatique', 'ahmed.jpg'),
('Trabelsi',  'Sara',   'sara.trabelsi@ensi.tn',  '55667788', 'Sousse',   'Design',       'sara.jpg'),
('Mansour',   'Karim',  'karim.mansour@ensi.tn',  '33445566', 'Sfax',     'Informatique', 'karim.jpg'),
('Bouazizi',  'Fatma',  'fatma.bouazizi@ensi.tn', '44556677', 'Tunis',    'Marketing',    'fatma.jpg'),
('Haddad',    'Omar',   'omar.haddad@ensi.tn',    '66778899', 'Bizerte',  'Informatique', 'omar.jpg');

INSERT INTO entreprise (nom, secteur, ville, email, telephone, logo, description) VALUES
('TechVision SARL', 'Informatique', 'Tunis',   'rh@techvision.tn',  '71234567', 'techvision.png', 'Entreprise spécialisée en développement web et mobile.'),
('PixelStudio',     'Design',       'Tunis',   'jobs@pixel.tn',     '71345678', 'pixel.png',      'Studio créatif spécialisé en UI/UX et branding.'),
('MediaPro',        'Marketing',    'Sousse',  'stage@mediapro.tn', '73456789', 'mediapro.png',   'Agence de communication digitale et marketing.'),
('InnoSoft',        'Informatique', 'Sfax',    'rh@innosoft.tn',    '74567890', 'innosoft.png',   'Éditeur de logiciels ERP pour PME tunisiennes.'),
('DataCore TN',     'Informatique', 'Tunis',   'hr@datacore.tn',    '71678901', 'datacore.png',   'Cabinet de conseil en data science et BI.');
