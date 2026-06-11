/**
 * js/admin.js — Tableau de bord admin : stats, comptes, création entreprise
 */

/* ─── Utilitaires graphiques ────────────────────────────────────── */
const DOMAINE_LABELS = {
    stage_1a:                      'Stage 1re an.',
    stage_2a:                      'Stage 2e an.',
    alternance_apprentissage:      'Apprentissage',
    alternance_professionnalisation:'Professionnalisation',
    mobilite_internationale:       'Mobilité int.',
    cdi:                           'CDI',
};

const PALETTE = ['#6B2C91','#F39200','#9b59b6','#d97f00','#4f1f6e','#e67e22'];

let chartComptes  = null;
let chartDomaines = null;

function couleurTexte() {
    return getComputedStyle(document.documentElement).getPropertyValue('--texte').trim() || '#2b2533';
}
function couleurBordure() {
    return getComputedStyle(document.documentElement).getPropertyValue('--bordure').trim() || '#e4e0ea';
}

function creerGraphiques(stats) {
    const txtColor  = couleurTexte();
    const gridColor = couleurBordure();
    const blanc     = getComputedStyle(document.documentElement).getPropertyValue('--blanc').trim() || '#fff';

    Chart.defaults.color       = txtColor;
    Chart.defaults.borderColor = gridColor;

    /* Graphique 1 — Donut : répartition des comptes (étudiants vs entreprises) */
    const ctx1 = document.getElementById('graphique-comptes');
    if (ctx1) {
        if (chartComptes) chartComptes.destroy();
        chartComptes = new Chart(ctx1, {
            type: 'doughnut',
            data: {
                labels: ['Étudiants', 'Entreprises'],
                datasets: [{
                    data: [stats.etudiants, stats.entreprises],
                    backgroundColor: ['#6B2C91', '#F39200'],
                    borderWidth: 3,
                    borderColor: blanc,
                    hoverOffset: 6,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { color: txtColor, padding: 18, font: { size: 12 } },
                    },
                    tooltip: {
                        callbacks: {
                            label: ctx => ` ${ctx.label} : ${ctx.parsed}`,
                        },
                    },
                },
            },
        });
    }

    /* Graphique 2 — Barres horizontales : domaines les plus recherchés */
    const ctx2 = document.getElementById('graphique-domaines');
    if (ctx2) {
        if (chartDomaines) chartDomaines.destroy();
        const domaines = (stats.domaines || []).sort((a, b) => b.total - a.total);
        if (domaines.length === 0) {
            ctx2.parentElement.innerHTML = '<p class="vide-message">Aucune donnée disponible.</p>';
        } else {
            chartDomaines = new Chart(ctx2, {
                type: 'bar',
                data: {
                    labels: domaines.map(d => DOMAINE_LABELS[d.type] || d.type),
                    datasets: [{
                        data: domaines.map(d => parseInt(d.total)),
                        backgroundColor: PALETTE,
                        borderRadius: 6,
                        borderSkipped: false,
                    }],
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: { stepSize: 1, color: txtColor },
                            grid: { color: gridColor },
                        },
                        y: {
                            ticks: { color: txtColor, font: { size: 11 } },
                            grid: { display: false },
                        },
                    },
                },
            });
        }
    }
}

function esc(v) {
    if (v === null || v === undefined) return '';
    return String(v).replace(/&/g, '&amp;').replace(/</g, '&lt;')
        .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

// ─── Statistiques ────────────────────────────────────────────────
async function chargerStats() {
    const res = await fetch('/api/admin/stats.php');
    const d = await res.json();
    if (!d.success) return;
    document.getElementById('stat-etudiants').textContent    = d.stats.etudiants;
    document.getElementById('stat-entreprises').textContent  = d.stats.entreprises;
    document.getElementById('stat-convocations').textContent = d.stats.convocations;
    document.getElementById('stat-demandes').textContent     = d.stats.demandes;
    creerGraphiques(d.stats);
}

// ─── Liste des comptes ───────────────────────────────────────────
let roleCourant = 'etudiant';

async function chargerComptes() {
    const res = await fetch('/api/admin/comptes.php?role=' + roleCourant);
    const d = await res.json();
    const conteneur = document.getElementById('liste-comptes');

    if (!d.success || d.comptes.length === 0) {
        conteneur.innerHTML = '<p class="vide-message">Aucun compte.</p>';
        return;
    }

    let html = '<table class="tableau"><thead><tr>'
        + '<th>Nom</th><th>Email</th><th>Inscrit le</th><th>Statut</th><th>Actions</th></tr></thead><tbody>';

    d.comptes.forEach(c => {
        const actif = Number(c.is_active) === 1;
        const date = new Date(c.created_at.replace(' ', 'T'));
        const dateFr = isNaN(date) ? esc(c.created_at) : date.toLocaleDateString('fr-FR');
        html += `<tr>
            <td>${esc(c.nom_affiche)}</td>
            <td>${esc(c.email)}</td>
            <td>${dateFr}</td>
            <td class="${actif ? 'etat-actif' : 'etat-suspendu'}">${actif ? 'Actif' : 'Suspendu'}</td>
            <td class="actions-cellule">
                <button class="btn-secondaire" onclick="actionCompte(${c.id}, 'suspend')">
                    ${actif ? 'Suspendre' : 'Réactiver'}
                </button>
                <button class="btn-danger" onclick="actionCompte(${c.id}, 'delete')">Supprimer</button>
            </td>
        </tr>`;
    });
    html += '</tbody></table>';
    conteneur.innerHTML = html;
}

async function actionCompte(id, action) {
    if (action === 'delete' && !confirm('Supprimer définitivement ce compte ?')) return;

    const res = await fetch('/api/admin/comptes.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action, id }),
    });
    const d = await res.json();
    if (d.success) {
        chargerComptes();
        chargerStats();
    } else {
        alert(d.error || 'Erreur.');
    }
}

// ─── Création entreprise ─────────────────────────────────────────
async function creerEntreprise(e) {
    e.preventDefault();
    const data = {
        nom:         document.getElementById('e-nom').value.trim(),
        email:       document.getElementById('e-email').value.trim(),
        secteur:     document.getElementById('e-secteur').value.trim(),
        contact_nom: document.getElementById('e-contact').value.trim(),
    };
    const zone = document.getElementById('msg-entreprise');

    const res = await fetch('/api/admin/creer-entreprise.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data),
    });
    const d = await res.json();

    if (d.success) {
        zone.className = 'message message-succes';
        zone.innerHTML = `Compte créé. Identifiants à transmettre :<br>`
            + `<strong>Email :</strong> ${esc(d.identifiants.email)}<br>`
            + `<strong>Mot de passe :</strong> ${esc(d.identifiants.mot_de_passe)}`;
        document.getElementById('form-entreprise').reset();
        chargerStats();
        if (roleCourant === 'entreprise') chargerComptes();
    } else {
        zone.className = 'message message-erreur';
        zone.textContent = d.error || 'Erreur.';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    if (!document.getElementById('liste-comptes')) return;

    chargerStats();
    chargerComptes();

    document.querySelectorAll('.onglet').forEach(o => {
        o.addEventListener('click', () => {
            document.querySelectorAll('.onglet').forEach(x => x.classList.remove('actif'));
            o.classList.add('actif');
            roleCourant = o.dataset.role;
            chargerComptes();
        });
    });

    document.getElementById('form-entreprise').addEventListener('submit', creerEntreprise);

    /* Recrée les graphiques quand le thème change (couleurs adaptées) */
    window.addEventListener('themechange', chargerStats);
});
