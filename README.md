# JUNIA CV — Plateforme de centralisation des CV étudiants

Projet final du module **Architecture Web AP3** — JUNIA Grande École d'Ingénieurs.

La plateforme permet aux étudiants de créer un CV standardisé, aux entreprises partenaires de consulter les profils et de convoquer des candidats, et aux administrateurs JUNIA de gérer l'ensemble des comptes.

---

## Stack technique

| Couche | Technologie |
|--------|-------------|
| Serveur | PHP 8.2 + Apache (Docker) |
| Base de données | MySQL 8.0 |
| Frontend | HTML5 sémantique · CSS3 vanilla · JavaScript vanilla |
| Typographie | Montserrat (titres) · Open Sans (corps) — Google Fonts |
| Conteneurs | Docker Compose |
| Administration BDD | phpMyAdmin |

---

## Installation et lancement

### Prérequis

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) (ou Docker Engine + Compose)

### Démarrer le projet

```bash
# 1. Cloner le dépôt
git clone <url-du-repo>
cd projet-plateforme-cv-junia

# 2. Configurer les variables d'environnement
cp docker/.env.example docker/.env
# (les valeurs par défaut fonctionnent telles quelles en développement)

# 3. Lancer les conteneurs
cd docker
docker compose up -d --build
```

L'application est disponible sur :

| Service | URL |
|---------|-----|
| Application | http://localhost:8080 |
| phpMyAdmin | http://localhost:8081 |

Le schéma SQL (`sql/junia_cv.sql`) est importé automatiquement au premier démarrage.

### Arrêter le projet

```bash
docker compose down          # arrête les conteneurs (données conservées)
docker compose down -v       # arrête + supprime le volume MySQL (repart de zéro)
```

---

## Identifiants de test

| Rôle | Email | Mot de passe |
|------|-------|--------------|
| Administrateur | admin@junia.com | Admin1234! |
| Entreprise | *(créer via l'admin)* | *(généré par l'admin)* |
| Étudiant | *(s'inscrire avec @junia.com)* | *(choisi à l'inscription)* |

> Les étudiants doivent obligatoirement utiliser une adresse `@junia.com`.

---

## Fonctionnalités

### Côté étudiant
- Inscription avec adresse `@junia.com` et consentement RGPD explicite
- Formulaire CV complet : données personnelles, photo de profil, biographie, parcours académique, expériences professionnelles, compétences techniques et linguistiques
- Sélection des domaines de recherche (stage, alternance, CDI, mobilité)
- Consultation et modification du profil à tout moment
- Suppression du compte et de toutes les données associées (RGPD)

### Côté entreprise
- Accès au catalogue des profils étudiants
- Filtres dynamiques : domaine de recherche, compétence, promotion, recherche par nom
- Consultation de la fiche CV complète de chaque étudiant
- Convocation à un entretien (date, lieu, message) avec envoi d'e-mail automatique
- Historique des convocations envoyées

### Administration
- Tableau de bord avec statistiques (étudiants, entreprises, convocations, demandes)
- Création de comptes entreprise (identifiants générés et affichés)
- Gestion des comptes : suspension / réactivation / suppression
- Traitement des demandes de partenariat (page Contact publique)

### Pages légales
- Mentions légales
- Politique de confidentialité (RGPD)

---

## Structure du projet

```
projet-plateforme-cv-junia/
├── README.md
└── docker/
    ├── docker-compose.yml
    ├── .env                       ← variables d'environnement (non commité)
    ├── .env.example               ← modèle à copier
    ├── .gitignore
    ├── docker/
    │   └── php/
    │       └── Dockerfile         ← PHP 8.2 + Apache + PDO MySQL
    ├── sql/
    │   └── junia_cv.sql           ← schéma BDD + compte admin de test
    └── www/                       ← racine de l'application
        ├── index.php              ← page d'accueil
        ├── .htaccess              ← réécriture d'URL Apache
        ├── css/
        │   └── style.css          ← feuille de style unique (charte JUNIA)
        ├── js/
        │   ├── auth.js            ← inscription / connexion
        │   ├── form-cv.js         ← formulaire CV dynamique
        │   ├── catalogue.js       ← catalogue entreprise + filtres
        │   ├── fiche-profil.js    ← CV détaillé + modale convocation
        │   ├── mes-convocations.js
        │   ├── profil.js          ← suppression de compte
        │   └── admin.js           ← tableau de bord admin
        ├── inc/
        │   ├── db.php             ← connexion PDO MySQL
        │   ├── session.php        ← démarrage session + helpers
        │   ├── auth.php           ← contrôle d'accès par rôle
        │   ├── header.php         ← header commun + Google Fonts
        │   └── footer.php         ← footer commun + JS menu burger
        ├── pages/
        │   ├── connexion.php
        │   ├── inscription.php
        │   ├── profil.php         ← CV de l'étudiant connecté
        │   ├── formulaire-cv.php  ← édition du CV
        │   ├── catalogue.php      ← catalogue (entreprise)
        │   ├── fiche-profil.php   ← CV complet (entreprise)
        │   ├── mes-convocations.php
        │   ├── contact.php        ← demande de partenariat
        │   ├── mentions-legales.php
        │   ├── confidentialite.php
        │   └── admin/
        │       └── index.php      ← tableau de bord admin
        ├── api/
        │   ├── login.php
        │   ├── register.php
        │   ├── logout.php
        │   ├── enregistrer-cv.php
        │   ├── recuperer-cv.php
        │   ├── profils.php
        │   ├── profil-detail.php
        │   ├── convoquer.php
        │   ├── mes-convocations.php
        │   ├── supprimer-compte.php
        │   ├── contact.php
        │   └── admin/
        │       ├── stats.php
        │       ├── comptes.php
        │       └── creer-entreprise.php
        └── uploads/               ← photos de profil (non commités)
```

---

## Schéma de la base de données

```
users ──────────────┬── etudiants ──┬── domaines_recherche
(id, email, role,   │               ├── formations
 password_hash,     │               ├── experiences
 is_active)         │               ├── competences_techniques
                    │               └── competences_linguistiques
                    │
                    └── entreprises ── convocations ── (etudiants)

demandes_partenariat  (table indépendante — page Contact)
```

Toutes les suppressions sont en cascade (`ON DELETE CASCADE`).

---

## Sécurité & RGPD

- Mots de passe hachés avec `password_hash()` (bcrypt)
- Toutes les requêtes SQL utilisent des **requêtes préparées PDO**
- Toutes les sorties HTML sont échappées avec `htmlspecialchars()`
- Upload photo : validation du type MIME réel (pas l'extension), limite 2 Mo
- Sessions sécurisées : `httponly`, `SameSite=Lax`, regénération d'ID à la connexion
- Contrôle d'accès par rôle sur chaque page et endpoint API
- Consentement explicite (case non pré-cochée) à l'inscription
- Suppression du compte avec effacement complet des données (RGPD)

---

## Équipe

| Membre | Rôle |
|--------|------|
| ... | Front-end Lead |
| ... | Back-end Lead |
| ... | Full-stack / Chef de projet |
