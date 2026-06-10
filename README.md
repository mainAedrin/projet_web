# 🎓 JUNIA CV Platform

Plateforme web de centralisation des CV étudiants JUNIA.
Projet final — Architecture Web AP3.

---

## 🚀 Lancer le projet en 3 commandes

```bash
git clone <url-du-repo>
cd projet-cv-junia
docker compose up -d --build
```

Puis ouvrir :
- **App** → http://localhost:8080
- **phpMyAdmin** → http://localhost:8081

---

## 🛑 Arrêter

```bash
docker compose down
```

Pour repartir de zéro (supprime la BDD) :
```bash
docker compose down -v
```

---

## 🔑 Identifiants de test

| Rôle | Email | Mot de passe |
|------|-------|-------------|
| Admin | admin@junia.com | Admin1234! |

---

## 🗂️ Structure du projet

```
projet-cv-junia/
├── docker-compose.yml
├── .env                  ← variables d'environnement (non commité)
├── .gitignore
├── docker/
│   └── php/
│       └── Dockerfile    ← PHP 8.2 + Apache + extensions
├── sql/
│   └── junia_cv.sql      ← schéma + données de test
└── www/                  ← code source de l'application
    ├── index.php
    ├── .htaccess
    ├── inc/
    │   ├── db.php
    │   ├── header.php
    │   └── footer.php
    ├── pages/
    ├── api/
    ├── css/
    ├── js/
    └── uploads/
```

---

## 👥 Équipe

| Membre | Rôle |
|--------|------|
| ... | Front-end Lead |
| ... | Back-end Lead |
| ... | Full-stack / Chef de projet |
