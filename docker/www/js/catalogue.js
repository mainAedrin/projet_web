/**
 * js/catalogue.js — Catalogue entreprise : filtres + convocation
 */

const LIBELLES_DOMAINE = {
    stage_1a: 'Stage 1re année',
    stage_2a: 'Stage 2e année',
    alternance_apprentissage: 'Apprentissage',
    alternance_professionnalisation: 'Professionnalisation',
    mobilite_internationale: 'Mobilité internationale',
    cdi: 'CDI',
};

function escHtml(v) {
    if (v === null || v === undefined) return '';
    return String(v)
        .replace(/&/g, '&amp;').replace(/</g, '&lt;')
        .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

// ─── Chargement + rendu des profils ──────────────────────────────
async function chargerProfils() {
    const params = new URLSearchParams({
        domaine:    document.getElementById('f-domaine').value,
        competence: document.getElementById('f-competence').value.trim(),
        promotion:  document.getElementById('f-promotion').value.trim(),
        q:          document.getElementById('f-recherche').value.trim(),
    });

    const grille = document.getElementById('grille-profils');
    grille.innerHTML = '<p class="vide-message">Chargement…</p>';

    const res = await fetch('/api/profils.php?' + params.toString());
    const d = await res.json();

    if (!d.success || d.profils.length === 0) {
        grille.innerHTML = '<p class="vide-message">Aucun profil ne correspond à votre recherche.</p>';
        return;
    }

    grille.innerHTML = '';
    d.profils.forEach(p => {
        const avatar = p.photo
            ? `<img class="avatar" src="/uploads/${escHtml(p.photo)}" alt="">`
            : `<div class="avatar avatar-vide">${escHtml((p.prenom || '?')[0])}</div>`;

        const badges = (p.domaines || [])
            .map(dom => `<span class="badge">${escHtml(LIBELLES_DOMAINE[dom] || dom)}</span>`)
            .join('');

        const carte = document.createElement('div');
        carte.className = 'carte-profil';
        carte.innerHTML = `
            <div class="bandeau"></div>
            <div class="corps">
                ${avatar}
                <h3>${escHtml(p.prenom)} ${escHtml(p.nom)}</h3>
                <p class="promo">${escHtml(p.promotion) || '—'}</p>
                <div class="badges">${badges}</div>
                <p class="bio">${escHtml((p.biographie || '').slice(0, 110))}${(p.biographie || '').length > 110 ? '…' : ''}</p>
                <div class="actions">
                    <a class="btn-secondaire" href="/pages/fiche-profil.php?id=${p.id}">Voir le CV</a>
                </div>
            </div>
        `;
        grille.appendChild(carte);
    });
}

document.addEventListener('DOMContentLoaded', () => {
    if (!document.getElementById('grille-profils')) return;

    ['f-domaine', 'f-competence', 'f-promotion', 'f-recherche'].forEach(id => {
        const el = document.getElementById(id);
        el.addEventListener('input', debounce(chargerProfils, 300));
        el.addEventListener('change', chargerProfils);
    });

    chargerProfils();
});

// Petit debounce pour ne pas spammer l'API à chaque frappe
function debounce(fn, delai) {
    let t;
    return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), delai); };
}
