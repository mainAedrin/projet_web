/**
 * js/profil.js — Page profil étudiant : suppression de compte (RGPD)
 */
document.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById('btn-supprimer-compte');
    if (!btn) return;

    btn.addEventListener('click', async () => {
        const ok = confirm(
            'Êtes-vous sûr de vouloir supprimer définitivement votre compte ?\n\n' +
            'Toutes vos données (CV, photo, candidatures) seront effacées. ' +
            'Cette action est irréversible.'
        );
        if (!ok) return;

        const res = await fetch('/api/supprimer-compte.php', { method: 'POST' });
        const d = await res.json();
        if (d.success) {
            alert('Votre compte a été supprimé. À bientôt !');
            window.location.href = '/index.php';
        } else {
            alert(d.error || 'Erreur lors de la suppression.');
        }
    });
});
