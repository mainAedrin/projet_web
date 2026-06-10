/**
 * js/mes-convocations.js — Historique des convocations (entreprise)
 */
function esc(v) {
    if (v === null || v === undefined) return '';
    return String(v).replace(/&/g, '&amp;').replace(/</g, '&lt;')
        .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

const STATUTS = {
    en_attente: 'En attente',
    acceptee:   'Acceptée',
    refusee:    'Refusée',
};

async function charger() {
    const res = await fetch('/api/mes-convocations.php');
    const d = await res.json();
    const conteneur = document.getElementById('liste-convocations');

    if (!d.success || d.convocations.length === 0) {
        conteneur.innerHTML = '<p class="vide-message">Vous n\'avez encore convoqué aucun étudiant.</p>';
        return;
    }

    let html = '<table class="tableau"><thead><tr>'
        + '<th>Étudiant</th><th>Date entretien</th><th>Lieu</th><th>Statut</th></tr></thead><tbody>';

    d.convocations.forEach(c => {
        const date = new Date(c.date_entretien.replace(' ', 'T'));
        const dateFr = isNaN(date) ? esc(c.date_entretien)
            : date.toLocaleString('fr-FR', { dateStyle: 'short', timeStyle: 'short' });
        html += `<tr>
            <td><a href="/pages/fiche-profil.php?id=${c.etudiant_id}">${esc(c.prenom)} ${esc(c.nom)}</a></td>
            <td>${dateFr}</td>
            <td>${esc(c.lieu) || '—'}</td>
            <td>${esc(STATUTS[c.statut] || c.statut)}</td>
        </tr>`;
    });
    html += '</tbody></table>';
    conteneur.innerHTML = html;
}

document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('liste-convocations')) charger();
});
