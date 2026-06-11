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
    initAutocomplete('f-competence', 'ac-competence');
});

// ─── Autocomplétion compétences ──────────────────────────────────
function initAutocomplete(inputId, listeId) {
    const input  = document.getElementById(inputId);
    const liste  = document.getElementById(listeId);
    if (!input || !liste) return;

    let indexActif = -1;
    let dernierQ   = '';

    const fermer = () => {
        liste.hidden = true;
        liste.innerHTML = '';
        indexActif = -1;
    };

    const surbrillance = (idx) => {
        const items = liste.querySelectorAll('.autocomplete-item');
        items.forEach((el, i) => el.classList.toggle('actif', i === idx));
        indexActif = idx;
    };

    const choisir = (valeur) => {
        input.value = valeur;
        fermer();
        chargerProfils();
    };

    const afficher = (suggestions) => {
        liste.innerHTML = '';
        indexActif = -1;

        if (!suggestions.length) { fermer(); return; }

        const q = input.value.trim().toLowerCase();
        suggestions.forEach(s => {
            const li = document.createElement('li');
            li.className = 'autocomplete-item';
            li.setAttribute('role', 'option');

            // Met en gras la partie qui correspond à la saisie
            const idx = s.toLowerCase().indexOf(q);
            if (idx >= 0) {
                li.innerHTML = escHtml(s.slice(0, idx))
                    + '<strong>' + escHtml(s.slice(idx, idx + q.length)) + '</strong>'
                    + escHtml(s.slice(idx + q.length));
            } else {
                li.textContent = s;
            }

            li.addEventListener('mousedown', (e) => { e.preventDefault(); choisir(s); });
            liste.appendChild(li);
        });

        liste.hidden = false;
    };

    const fetchSuggestions = debounce(async () => {
        const q = input.value.trim();
        if (q === dernierQ) return;
        dernierQ = q;

        if (q.length < 1) { fermer(); return; }

        const res = await fetch('/api/suggestions.php?q=' + encodeURIComponent(q));
        const data = await res.json();
        // Vérifier que la saisie n'a pas changé entre temps
        if (input.value.trim() === q) afficher(data);
    }, 200);

    input.addEventListener('input', () => { fetchSuggestions(); });

    input.addEventListener('keydown', (e) => {
        const items = liste.querySelectorAll('.autocomplete-item');
        if (!items.length) return;

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            surbrillance(Math.min(indexActif + 1, items.length - 1));
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            surbrillance(Math.max(indexActif - 1, -1));
            if (indexActif === -1) input.value = dernierQ;
        } else if (e.key === 'Enter' && indexActif >= 0) {
            e.preventDefault();
            choisir(items[indexActif].textContent);
        } else if (e.key === 'Escape') {
            fermer();
        }
    });

    document.addEventListener('click', (e) => {
        if (!input.contains(e.target) && !liste.contains(e.target)) fermer();
    });
}

// Petit debounce pour ne pas spammer l'API à chaque frappe
function debounce(fn, delai) {
    let t;
    return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), delai); };
}
