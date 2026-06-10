<?php
/**
 * pages/contact.php — Demande de partenariat (entreprises non-partenaires)
 */
$titrePage = "Contact";
require_once __DIR__ . '/../inc/header.php';
?>

<section class="form-auth" style="max-width:560px">
    <h1>Devenir entreprise partenaire</h1>
    <p style="text-align:center;color:var(--texte-doux);margin-bottom:1rem">
        Vous souhaitez accéder aux profils étudiants JUNIA ? Envoyez-nous votre demande.
    </p>
    <div id="msg" class="message"></div>

    <form id="form-contact" novalidate>
        <label for="nom_entreprise">Nom de l'entreprise</label>
        <input type="text" id="nom_entreprise" required>

        <label for="contact_nom">Votre nom</label>
        <input type="text" id="contact_nom" required>

        <label for="email">Email professionnel</label>
        <input type="email" id="email" required>

        <label for="message">Message</label>
        <textarea id="message" rows="5" placeholder="Présentez votre besoin de recrutement…" required></textarea>

        <button type="submit" class="btn-principal">Envoyer ma demande</button>
    </form>
</section>

<script>
document.getElementById('form-contact').addEventListener('submit', async (e) => {
    e.preventDefault();
    const zone = document.getElementById('msg');
    const data = {
        nom_entreprise: document.getElementById('nom_entreprise').value.trim(),
        contact_nom:    document.getElementById('contact_nom').value.trim(),
        email:          document.getElementById('email').value.trim(),
        message:        document.getElementById('message').value.trim(),
    };
    const res = await fetch('/api/contact.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data),
    });
    const d = await res.json();
    if (d.success) {
        zone.className = 'message message-succes';
        zone.textContent = d.message;
        document.getElementById('form-contact').reset();
    } else {
        zone.className = 'message message-erreur';
        zone.textContent = d.error || 'Erreur lors de l\'envoi.';
    }
});
</script>
<?php require_once __DIR__ . '/../inc/footer.php'; ?>
