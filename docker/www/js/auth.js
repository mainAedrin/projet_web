/**
 * js/auth.js — Appels aux APIs d'authentification
 */

// ─── Helper générique pour appeler une API JSON ──────────────────
async function postJSON(url, data) {
    const res = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data),
    });
    const json = await res.json();
    return { ok: res.ok, ...json };
}

// ─── Afficher un message dans une zone dédiée ────────────────────
function afficherMessage(elementId, texte, type = 'erreur') {
    const zone = document.getElementById(elementId);
    if (!zone) return;
    zone.textContent = texte;
    zone.className = 'message message-' + type;
}

// ─── INSCRIPTION ─────────────────────────────────────────────────
function initInscription() {
    const form = document.getElementById('form-inscription');
    if (!form) return;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const data = {
            nom:          document.getElementById('nom').value.trim(),
            prenom:       document.getElementById('prenom').value.trim(),
            email:        document.getElementById('email').value.trim(),
            password:     document.getElementById('password').value,
            consentement: document.getElementById('consentement').checked,
        };

        // Validation côté client (confort — le serveur revérifie tout)
        if (data.password.length < 8) {
            afficherMessage('msg', 'Le mot de passe doit faire au moins 8 caractères.');
            return;
        }
        if (!data.consentement) {
            afficherMessage('msg', 'Vous devez accepter la politique de confidentialité.');
            return;
        }

        const r = await postJSON('/api/register.php', data);

        if (r.success) {
            afficherMessage('msg', 'Compte créé ! Redirection vers la connexion…', 'succes');
            setTimeout(() => window.location.href = '/pages/connexion.php', 1500);
        } else {
            afficherMessage('msg', r.error || 'Une erreur est survenue.');
        }
    });
}

// ─── CONNEXION ───────────────────────────────────────────────────
function initConnexion() {
    const form = document.getElementById('form-connexion');
    if (!form) return;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const data = {
            email:    document.getElementById('email').value.trim(),
            password: document.getElementById('password').value,
        };

        const r = await postJSON('/api/login.php', data);

        if (r.success) {
            window.location.href = r.redirect;   // redirige selon le rôle
        } else {
            afficherMessage('msg', r.error || 'Connexion impossible.');
        }
    });
}

// ─── Initialisation automatique selon la page ───────────────────
document.addEventListener('DOMContentLoaded', () => {
    initInscription();
    initConnexion();
});
