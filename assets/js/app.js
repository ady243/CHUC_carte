/* Formulaire carte : aperçu en direct (texte, QR, photo, cadrage) et bascule RECTO / VERSO */
document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('editeur');
  if (!form) return;

  const carte = form.querySelector('.carte-recto');
  const apercu = form.querySelector('.apercu');
  const cadre = carte.querySelector('.carte-photo');
  const img = cadre.querySelector('img');
  const boutonsFace = form.querySelectorAll('[data-face]');
  const qrBase = carte.dataset.qrBase || '';

  // Texte affiché sur la carte tant que le champ correspondant est vide
  const DEFAUTS = {
    nom: 'NOM',
    postnom: 'POSTNOM',
    prenom: 'PRÉNOM',
    fonction: 'Fonction',
    matricule: '00000000',
  };

  const champ = (id) => document.getElementById(id);
  const valeur = (id) => (champ(id).value || '').trim();

  /* --- Texte et QR code --- */

  function majTexte() {
    Object.entries(DEFAUTS).forEach(([nom, defaut]) => {
      const texte = valeur(nom);
      const cible = carte.querySelector(`[data-champ="${nom}"]`);
      cible.textContent = texte || defaut;
      cible.classList.toggle('vide', !texte);
    });

    const matricule = valeur('matricule');
    carte.dataset.qr = matricule ? qrBase + encodeURIComponent(matricule) : '';
    CarteCHU.rendre(carte);
  }

  /* --- Photo : chargement et cadrage --- */

  function majPhoto() {
    const fichier = champ('photo').files && champ('photo').files[0];
    if (!fichier) return;
    img.src = URL.createObjectURL(fichier);
    cadre.classList.remove('sans-photo');
  }

  function majCadrage() {
    const position = `${champ('photo_x').value}% ${champ('photo_y').value}%`;
    img.style.objectPosition = position;
    img.style.transformOrigin = position;
    img.style.transform = `scale(${champ('photo_zoom').value})`;
  }

  /* --- Bascule RECTO / VERSO --- */

  function afficherFace(bouton) {
    const face = bouton.dataset.face;
    apercu.dataset.faceCourante = face;
    boutonsFace.forEach((b) => b.classList.toggle('active', b === bouton));
    if (face === 'recto') CarteCHU.ajuster(carte);
  }

  /* --- Écouteurs --- */

  Object.keys(DEFAUTS).forEach((id) => champ(id).addEventListener('input', majTexte));
  ['photo_zoom', 'photo_x', 'photo_y'].forEach((id) => champ(id).addEventListener('input', majCadrage));
  champ('photo').addEventListener('change', majPhoto);
  boutonsFace.forEach((bouton) => bouton.addEventListener('click', () => afficherFace(bouton)));
  document.addEventListener('cartes-pretes', majTexte);
});
