/**
 * js/mes-invitations.js — Invitations reçues (étudiant)
 */
function esc(v) {
    if (v === null || v === undefined) return '';
    return String(v).replace(/&/g, '&amp;').replace(/</g, '&lt;')
        .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

const STATUTS = {
    en_attente: { label: 'En attente',  cls: 'statut-attente' },
    acceptee:   { label: 'Acceptée',    cls: 'statut-acceptee' },
    refusee:    { label: 'Refusée',     cls: 'statut-refusee' },
};

async function repondre(id, statut, carte) {
    const btns = carte.querySelectorAll('button');
    btns.forEach(b => { b.disabled = true; });

    const res = await fetch('/api/repondre-invitation.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id, statut }),
    });
    const d = await res.json();

    if (d.success) {
        const info = STATUTS[statut];
        carte.querySelector('.badge-statut').className = 'badge-statut ' + info.cls;
        carte.querySelector('.badge-statut').textContent = info.label;
        carte.querySelector('.invitation-actions').remove();
    } else {
        btns.forEach(b => { b.disabled = false; });
        alert(d.error || 'Une erreur est survenue.');
    }
}

async function charger() {
    const conteneur = document.getElementById('liste-invitations');
    if (!conteneur) return;

    const res = await fetch('/api/mes-invitations.php');
    const d   = await res.json();

    if (!d.success || d.invitations.length === 0) {
        conteneur.innerHTML = '<p class="vide-message">Aucune invitation reçue pour le moment.</p>';
        return;
    }

    let html = '';
    d.invitations.forEach(inv => {
        const date = new Date(inv.date_entretien.replace(' ', 'T'));
        const dateFr = isNaN(date) ? esc(inv.date_entretien)
            : date.toLocaleString('fr-FR', { dateStyle: 'long', timeStyle: 'short' });

        const statut = STATUTS[inv.statut] || { label: esc(inv.statut), cls: '' };

        const actions = inv.statut === 'en_attente'
            ? `<div class="invitation-actions">
                <button class="btn-principal" data-id="${inv.id}" data-statut="acceptee">Accepter</button>
                <button class="btn-danger"    data-id="${inv.id}" data-statut="refusee">Refuser</button>
               </div>`
            : '';

        html += `
        <div class="carte-invitation" data-id="${inv.id}">
            <div class="carte-invitation-entete">
                <div>
                    <h3>${esc(inv.entreprise_nom)}</h3>
                    ${inv.secteur ? `<span class="secteur">${esc(inv.secteur)}</span>` : ''}
                </div>
                <span class="badge-statut ${statut.cls}">${statut.label}</span>
            </div>
            <dl class="invitation-details">
                <dt>Date de l'entretien</dt>
                <dd>${dateFr}</dd>
                ${inv.lieu    ? `<dt>Lieu</dt><dd>${esc(inv.lieu)}</dd>` : ''}
                ${inv.message ? `<dt>Message</dt><dd>${esc(inv.message)}</dd>` : ''}
            </dl>
            ${actions}
        </div>`;
    });

    conteneur.innerHTML = html;

    conteneur.addEventListener('click', e => {
        const btn = e.target.closest('button[data-statut]');
        if (!btn) return;
        const carte = btn.closest('.carte-invitation');
        repondre(parseInt(btn.dataset.id), btn.dataset.statut, carte);
    });
}

document.addEventListener('DOMContentLoaded', charger);
