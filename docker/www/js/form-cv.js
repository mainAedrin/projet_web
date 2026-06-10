/**
 * js/form-cv.js — Formulaire CV dynamique (étudiant)
 * Gère : ajout/suppression d'entrées, pré-remplissage, envoi multipart.
 */

function msg(texte, type = 'erreur') {
    const zone = document.getElementById('msg');
    if (!zone) return;
    zone.textContent = texte;
    zone.className = 'message message-' + type;
    zone.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

// ─── Générateurs de blocs dynamiques ─────────────────────────────
function blocFormation(f = {}) {
    const div = document.createElement('div');
    div.className = 'entree-dynamique formation';
    div.innerHTML = `
        <button type="button" class="supprimer" title="Supprimer">×</button>
        <div class="grille-2">
            <div><label>Établissement</label>
                <input type="text" class="f-etablissement" value="${esc(f.etablissement)}"></div>
            <div><label>Diplôme</label>
                <input type="text" class="f-diplome" value="${esc(f.diplome)}"></div>
            <div><label>Domaine</label>
                <input type="text" class="f-domaine" value="${esc(f.domaine)}"></div>
            <div></div>
            <div><label>Année début</label>
                <input type="number" class="f-debut" min="1990" max="2099" value="${esc(f.date_debut)}"></div>
            <div><label>Année fin (vide si en cours)</label>
                <input type="number" class="f-fin" min="1990" max="2099" value="${esc(f.date_fin)}"></div>
        </div>
        <label>Description</label>
        <textarea class="f-description">${esc(f.description)}</textarea>
    `;
    div.querySelector('.supprimer').onclick = () => div.remove();
    return div;
}

function blocExperience(x = {}) {
    const div = document.createElement('div');
    div.className = 'entree-dynamique experience';
    div.innerHTML = `
        <button type="button" class="supprimer" title="Supprimer">×</button>
        <div class="grille-2">
            <div><label>Poste</label>
                <input type="text" class="x-poste" value="${esc(x.poste)}"></div>
            <div><label>Entreprise</label>
                <input type="text" class="x-entreprise" value="${esc(x.entreprise)}"></div>
            <div><label>Lieu</label>
                <input type="text" class="x-lieu" value="${esc(x.lieu)}"></div>
            <div></div>
            <div><label>Date début</label>
                <input type="date" class="x-debut" value="${esc(x.date_debut)}"></div>
            <div><label>Date fin (vide si en cours)</label>
                <input type="date" class="x-fin" value="${esc(x.date_fin)}"></div>
        </div>
        <label>Description</label>
        <textarea class="x-description">${esc(x.description)}</textarea>
    `;
    div.querySelector('.supprimer').onclick = () => div.remove();
    return div;
}

function blocTechnique(c = {}) {
    const div = document.createElement('div');
    div.className = 'entree-dynamique technique';
    div.innerHTML = `
        <button type="button" class="supprimer" title="Supprimer">×</button>
        <div class="grille-2">
            <div><label>Compétence</label>
                <input type="text" class="t-libelle" value="${esc(c.libelle)}"></div>
            <div><label>Niveau</label>
                <select class="t-niveau">
                    ${optionsNiveau(['debutant','intermediaire','avance','expert'], c.niveau)}
                </select></div>
        </div>
    `;
    div.querySelector('.supprimer').onclick = () => div.remove();
    return div;
}

function blocLangue(l = {}) {
    const div = document.createElement('div');
    div.className = 'entree-dynamique langue';
    div.innerHTML = `
        <button type="button" class="supprimer" title="Supprimer">×</button>
        <div class="grille-2">
            <div><label>Langue</label>
                <input type="text" class="l-langue" value="${esc(l.langue)}"></div>
            <div><label>Niveau</label>
                <select class="l-niveau">
                    ${optionsNiveau(['A1','A2','B1','B2','C1','C2','natif'], l.niveau)}
                </select></div>
        </div>
    `;
    div.querySelector('.supprimer').onclick = () => div.remove();
    return div;
}

// ─── Utilitaires ─────────────────────────────────────────────────
function esc(v) {
    if (v === null || v === undefined) return '';
    return String(v).replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}
function optionsNiveau(liste, choisi) {
    return liste.map(n => `<option value="${n}" ${n === choisi ? 'selected' : ''}>${n}</option>`).join('');
}

// ─── Pré-remplissage depuis l'API ────────────────────────────────
async function chargerCV() {
    try {
        const res = await fetch('/api/recuperer-cv.php');
        const d = await res.json();
        if (!d.success) return;

        const e = d.etudiant;
        document.getElementById('nom').value            = e.nom || '';
        document.getElementById('prenom').value         = e.prenom || '';
        document.getElementById('date_naissance').value = e.date_naissance || '';
        document.getElementById('telephone').value      = e.telephone || '';
        document.getElementById('adresse').value        = e.adresse || '';
        document.getElementById('ville').value          = e.ville || '';
        document.getElementById('code_postal').value    = e.code_postal || '';
        document.getElementById('promotion').value      = e.promotion || '';
        document.getElementById('biographie').value     = e.biographie || '';

        if (e.photo) {
            const ap = document.getElementById('apercu-photo');
            ap.src = '/uploads/' + e.photo;
            ap.style.display = 'block';
        }

        d.domaines.forEach(dom => {
            const cb = document.querySelector(`input[name="domaines[]"][value="${dom}"]`);
            if (cb) cb.checked = true;
        });

        d.formations.forEach(f  => document.getElementById('liste-formations').appendChild(blocFormation(f)));
        d.experiences.forEach(x => document.getElementById('liste-experiences').appendChild(blocExperience(x)));
        d.techniques.forEach(c  => document.getElementById('liste-techniques').appendChild(blocTechnique(c)));
        d.langues.forEach(l     => document.getElementById('liste-langues').appendChild(blocLangue(l)));
    } catch (err) {
        // Pas de CV existant → formulaire vierge, rien à faire
    }
}

// ─── Collecte + envoi ────────────────────────────────────────────
function collecterListe(selector, mapper) {
    return [...document.querySelectorAll(selector)].map(mapper);
}

async function envoyerCV(e) {
    e.preventDefault();

    if (!document.getElementById('nom').value.trim() || !document.getElementById('prenom').value.trim()) {
        return msg('Le nom et le prénom sont obligatoires.');
    }

    const fd = new FormData();
    ['nom','prenom','date_naissance','telephone','adresse','ville','code_postal','promotion','biographie']
        .forEach(id => fd.append(id, document.getElementById(id).value.trim()));

    // Photo
    const photo = document.getElementById('photo').files[0];
    if (photo) fd.append('photo', photo);

    // Domaines cochés
    document.querySelectorAll('input[name="domaines[]"]:checked')
        .forEach(cb => fd.append('domaines[]', cb.value));

    // Listes dynamiques (JSON)
    fd.append('formations', JSON.stringify(collecterListe('.formation', d => ({
        etablissement: d.querySelector('.f-etablissement').value.trim(),
        diplome:       d.querySelector('.f-diplome').value.trim(),
        domaine:       d.querySelector('.f-domaine').value.trim(),
        date_debut:    d.querySelector('.f-debut').value,
        date_fin:      d.querySelector('.f-fin').value,
        description:   d.querySelector('.f-description').value.trim(),
    }))));

    fd.append('experiences', JSON.stringify(collecterListe('.experience', d => ({
        poste:       d.querySelector('.x-poste').value.trim(),
        entreprise:  d.querySelector('.x-entreprise').value.trim(),
        lieu:        d.querySelector('.x-lieu').value.trim(),
        date_debut:  d.querySelector('.x-debut').value,
        date_fin:    d.querySelector('.x-fin').value,
        description: d.querySelector('.x-description').value.trim(),
    }))));

    fd.append('competences_tech', JSON.stringify(collecterListe('.technique', d => ({
        libelle: d.querySelector('.t-libelle').value.trim(),
        niveau:  d.querySelector('.t-niveau').value,
    }))));

    fd.append('competences_lang', JSON.stringify(collecterListe('.langue', d => ({
        langue: d.querySelector('.l-langue').value.trim(),
        niveau: d.querySelector('.l-niveau').value,
    }))));

    const res = await fetch('/api/enregistrer-cv.php', { method: 'POST', body: fd });
    const d = await res.json();
    if (d.success) {
        msg('CV enregistré ! Redirection vers votre profil…', 'succes');
        setTimeout(() => (window.location.href = '/pages/profil.php'), 1500);
    } else {
        msg(d.error || 'Erreur lors de l\'enregistrement.');
    }
}

// ─── Initialisation ──────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    if (!document.getElementById('form-cv')) return;

    document.getElementById('ajouter-formation').onclick  = () => document.getElementById('liste-formations').appendChild(blocFormation());
    document.getElementById('ajouter-experience').onclick = () => document.getElementById('liste-experiences').appendChild(blocExperience());
    document.getElementById('ajouter-technique').onclick  = () => document.getElementById('liste-techniques').appendChild(blocTechnique());
    document.getElementById('ajouter-langue').onclick     = () => document.getElementById('liste-langues').appendChild(blocLangue());

    // Aperçu photo
    document.getElementById('photo').onchange = (ev) => {
        const f = ev.target.files[0];
        if (!f) return;
        const ap = document.getElementById('apercu-photo');
        ap.src = URL.createObjectURL(f);
        ap.style.display = 'block';
    };

    document.getElementById('form-cv').addEventListener('submit', envoyerCV);
    chargerCV();
});
