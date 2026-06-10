-- ================================================================
--  JUNIA CV Platform — Schéma de base de données
--  Architecture Web AP3 — Projet final
-- ================================================================

SET NAMES utf8mb4;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;

-- ────────────────────────────────────────────────────────────────
--  Base
-- ────────────────────────────────────────────────────────────────
CREATE DATABASE IF NOT EXISTS cv_platform
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE cv_platform;

-- ────────────────────────────────────────────────────────────────
--  1. users  (table centrale — 3 rôles)
-- ────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS users (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email         VARCHAR(191) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role          ENUM('etudiant','entreprise','admin') NOT NULL DEFAULT 'etudiant',
    is_active     TINYINT(1) NOT NULL DEFAULT 1,
    created_at    DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────────────────────────
--  2. etudiants  (profil CV)
-- ────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS etudiants (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         INT UNSIGNED NOT NULL UNIQUE,
    nom             VARCHAR(100) NOT NULL,
    prenom          VARCHAR(100) NOT NULL,
    date_naissance  DATE,
    telephone       VARCHAR(20),
    adresse         VARCHAR(255),
    ville           VARCHAR(100),
    code_postal     VARCHAR(10),
    photo           VARCHAR(255),           -- chemin relatif dans uploads/
    biographie      TEXT,                   -- bio / lettre de motivation
    promotion       VARCHAR(100),           -- ex: "Ingénieur 3e année"
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_etudiant_user FOREIGN KEY (user_id)
        REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────────────────────────
--  3. domaines_recherche  (cases à cocher étudiant)
-- ────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS domaines_recherche (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    etudiant_id  INT UNSIGNED NOT NULL,
    type         ENUM(
                    'stage_1a',
                    'stage_2a',
                    'alternance_apprentissage',
                    'alternance_professionnalisation',
                    'mobilite_internationale',
                    'cdi'
                 ) NOT NULL,
    UNIQUE KEY uq_etudiant_domaine (etudiant_id, type),
    CONSTRAINT fk_domaine_etudiant FOREIGN KEY (etudiant_id)
        REFERENCES etudiants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────────────────────────
--  4. formations  (parcours académique — ajout dynamique)
-- ────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS formations (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    etudiant_id  INT UNSIGNED NOT NULL,
    etablissement VARCHAR(200) NOT NULL,
    diplome       VARCHAR(200) NOT NULL,
    domaine       VARCHAR(200),
    date_debut    YEAR         NOT NULL,
    date_fin      YEAR,                     -- NULL si en cours
    description   TEXT,
    ordre         TINYINT UNSIGNED NOT NULL DEFAULT 0,
    CONSTRAINT fk_formation_etudiant FOREIGN KEY (etudiant_id)
        REFERENCES etudiants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────────────────────────
--  5. experiences  (expériences pro — ajout dynamique)
-- ────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS experiences (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    etudiant_id  INT UNSIGNED NOT NULL,
    poste        VARCHAR(200) NOT NULL,
    entreprise   VARCHAR(200) NOT NULL,
    lieu         VARCHAR(200),
    date_debut   DATE         NOT NULL,
    date_fin     DATE,                      -- NULL si en cours
    description  TEXT,
    ordre        TINYINT UNSIGNED NOT NULL DEFAULT 0,
    CONSTRAINT fk_experience_etudiant FOREIGN KEY (etudiant_id)
        REFERENCES etudiants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────────────────────────
--  6. competences_techniques
-- ────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS competences_techniques (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    etudiant_id  INT UNSIGNED NOT NULL,
    libelle      VARCHAR(100) NOT NULL,
    niveau       ENUM('debutant','intermediaire','avance','expert') NOT NULL DEFAULT 'intermediaire',
    CONSTRAINT fk_competence_etudiant FOREIGN KEY (etudiant_id)
        REFERENCES etudiants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────────────────────────
--  7. competences_linguistiques
-- ────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS competences_linguistiques (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    etudiant_id  INT UNSIGNED NOT NULL,
    langue       VARCHAR(100) NOT NULL,
    niveau       ENUM('A1','A2','B1','B2','C1','C2','natif') NOT NULL,
    CONSTRAINT fk_langue_etudiant FOREIGN KEY (etudiant_id)
        REFERENCES etudiants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────────────────────────
--  8. entreprises
-- ────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS entreprises (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id      INT UNSIGNED NOT NULL UNIQUE,
    nom          VARCHAR(200) NOT NULL,
    secteur      VARCHAR(200),
    description  TEXT,
    site_web     VARCHAR(255),
    logo         VARCHAR(255),
    contact_nom  VARCHAR(200),
    contact_tel  VARCHAR(20),
    created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_entreprise_user FOREIGN KEY (user_id)
        REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────────────────────────
--  9. convocations
-- ────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS convocations (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    entreprise_id INT UNSIGNED NOT NULL,
    etudiant_id   INT UNSIGNED NOT NULL,
    date_entretien DATETIME    NOT NULL,
    lieu          VARCHAR(255),
    message       TEXT,
    statut        ENUM('en_attente','acceptee','refusee') NOT NULL DEFAULT 'en_attente',
    created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_convoc_entreprise FOREIGN KEY (entreprise_id)
        REFERENCES entreprises(id) ON DELETE CASCADE,
    CONSTRAINT fk_convoc_etudiant   FOREIGN KEY (etudiant_id)
        REFERENCES etudiants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────────────────────────
--  10. demandes_partenariat  (page contact entreprises externes)
-- ────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS demandes_partenariat (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom_entreprise VARCHAR(200) NOT NULL,
    contact_nom    VARCHAR(200) NOT NULL,
    email          VARCHAR(191) NOT NULL,
    message        TEXT,
    traite         TINYINT(1)  NOT NULL DEFAULT 0,
    created_at     DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────────────────────────
--  DONNÉES DE TEST
-- ────────────────────────────────────────────────────────────────
-- Mot de passe : "Admin1234!" (hash bcrypt)
INSERT INTO users (email, password_hash, role) VALUES
('admin@junia.com',
 '$2y$10$iBW.lIab9HLXff5WPoph5.56cokNlTCsSSiDyHpENG/sIBy.qPT5m',
 'admin');

SET foreign_key_checks = 1;
