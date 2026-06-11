/**
 * js/fiche-profil.js — Affiche le CV complet d'un étudiant + convocation
 */

const LIB_DOMAINE = {
    stage_1a: 'Stage 1re année',
    stage_2a: 'Stage 2e année',
    alternance_apprentissage: 'Apprentissage',
    alternance_professionnalisation: 'Professionnalisation',
    mobilite_internationale: 'Mobilité internationale',
    cdi: 'CDI',
};

function esc(v) {
    if (v === null || v === undefined) return '';
    return String(v).replace(/&/g, '&amp;').replace(/</g, '&lt;')
        .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function paramId() {
    return new URLSearchParams(window.location.search).get('id');
}

function notif(texte, type = 'erreur') {
    const z = document.getElementById('msg-convoc');
    if (!z) return;
    z.textContent = texte;
    z.className = 'message message-' + type;
}

async function chargerFiche() {
    const id = paramId();
    const conteneur = document.getElementById('fiche');
    if (!id) { conteneur.innerHTML = '<p class="vide-message">Profil introuvable.</p>'; return; }

    const res = await fetch('/api/profil-detail.php?id=' + encodeURIComponent(id));
    const d = await res.json();
    if (!d.success) { conteneur.innerHTML = '<p class="vide-message">Profil introuvable.</p>'; return; }

    const e = d.etudiant;
    const avatar = e.photo
        ? `<img class="avatar" src="/uploads/${esc(e.photo)}" alt="">`
        : `<div class="avatar avatar-vide">${esc((e.prenom || '?')[0])}</div>`;

    const badges = (d.domaines || [])
        .map(x => `<span class="tag">${esc(LIB_DOMAINE[x] || x)}</span>`).join('');

    const formations = d.formations.map(f => `
        <div class="cv-item">
            <h3>${esc(f.diplome)} — ${esc(f.etablissement)}</h3>
            <div class="meta">${esc(f.date_debut)}${f.date_fin ? ' – ' + esc(f.date_fin) : ' – en cours'}${f.domaine ? ' · ' + esc(f.domaine) : ''}</div>
            ${f.description ? `<p>${esc(f.description)}</p>` : ''}
        </div>`).join('') || '<p class="meta">Non renseigné.</p>';

    const experiences = d.experiences.map(x => `
        <div class="cv-item">
            <h3>${esc(x.poste)} — ${esc(x.entreprise)}</h3>
            <div class="meta">${esc(x.date_debut)}${x.date_fin ? ' – ' + esc(x.date_fin) : ' – en cours'}${x.lieu ? ' · ' + esc(x.lieu) : ''}</div>
            ${x.description ? `<p>${esc(x.description)}</p>` : ''}
        </div>`).join('') || '<p class="meta">Non renseigné.</p>';

    const techniques = d.techniques.map(c =>
        `<span class="tag">${esc(c.libelle)} <span class="tag-niveau">(${esc(c.niveau)})</span></span>`
    ).join('') || '<p class="meta">Non renseigné.</p>';

    const langues = d.langues.map(l =>
        `<span class="tag">${esc(l.langue)} <span class="tag-niveau">(${esc(l.niveau)})</span></span>`
    ).join('') || '<p class="meta">Non renseigné.</p>';

    conteneur.innerHTML = `
        <div class="cv">
            <div class="cv-entete">
                ${avatar}
                <div>
                    <h1>${esc(e.prenom)} ${esc(e.nom)}</h1>
                    <p class="promo">${esc(e.promotion) || ''}</p>
                    <p class="coords">${esc(e.ville) || ''}</p>
                    <div class="tags" style="margin-top:.6rem">${badges}</div>
                </div>
            </div>
            <div class="cv-corps">
                ${e.biographie ? `<div class="cv-section"><h2>À propos</h2><p>${esc(e.biographie)}</p></div>` : ''}
                <div class="cv-section"><h2>Formation</h2>${formations}</div>
                <div class="cv-section"><h2>Expériences</h2>${experiences}</div>
                <div class="cv-section"><h2>Compétences techniques</h2><div class="tags">${techniques}</div></div>
                <div class="cv-section"><h2>Langues</h2><div class="tags">${langues}</div></div>
            </div>
            <div class="cv-actions">
                <button class="btn-principal" id="btn-convoquer">Convoquer à un entretien</button>
                <a class="btn-secondaire" href="/api/cv-pdf.php?id=${id}" target="_blank">Télécharger le CV (PDF)</a>
                <a class="btn-secondaire" href="/pages/catalogue.php">Retour au catalogue</a>
            </div>
        </div>
    `;

    document.getElementById('btn-convoquer').onclick = () => ouvrirModale(id);
}

// ─── Modale de convocation ───────────────────────────────────────
function ouvrirModale(etudiantId) {
    document.getElementById('modale-convoc').classList.add('ouverte');
    document.getElementById('convoc-etudiant-id').value = etudiantId;
}
function fermerModale() {
    document.getElementById('modale-convoc').classList.remove('ouverte');
    notif('');
}

async function envoyerConvocation() {
    const data = {
        etudiant_id:    document.getElementById('convoc-etudiant-id').value,
        date_entretien: document.getElementById('convoc-date').value,
        lieu:           document.getElementById('convoc-lieu').value.trim(),
        message:        document.getElementById('convoc-message').value.trim(),
    };
    if (!data.date_entretien) return notif('Veuillez choisir une date et une heure.');

    const res = await fetch('/api/convoquer.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data),
    });
    const d = await res.json();
    if (d.success) {
        notif(d.message, 'succes');
        setTimeout(fermerModale, 1800);
    } else {
        notif(d.error || 'Erreur lors de l\'envoi.');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    if (!document.getElementById('fiche')) return;
    chargerFiche();
    document.getElementById('convoc-envoyer').onclick = envoyerConvocation;
    document.getElementById('convoc-annuler').onclick = fermerModale;
});
