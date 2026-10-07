/* Rendu des cartes CHU50 : QR code local (sans Internet) + ajustement automatique
   de la taille du texte pour que les noms longs ne débordent pas. */
(() => {
  // Tailles de police en cqw : valeur de départ et plancher de chaque bloc
  const TAILLES = {
    nom: { depart: 4.4, min: 2.2 },
    fonction: { depart: 3.9, min: 2 },
    id: { depart: 4.2, min: 2 },
  };
  const PAS = 0.1;
  const INTERLIGNE = 1.05;
  const LIGNES_FONCTION_MAX = 2;
  // Part de la hauteur de la carte réservée au bloc nom + fonction (sous le bandeau bleu)
  const HAUTEUR_TEXTE_MAX = 0.36;
  const MARGE_FONCTION = 0.55;
  const LARGEUR_ID_MAX = 0.9;

  /* --- QR code --- */

  function qrSvg(texte) {
    const qr = qrcode(0, 'M');
    qr.addData(texte);
    qr.make();

    const n = qr.getModuleCount();
    let trace = '';
    for (let ligne = 0; ligne < n; ligne++) {
      for (let colonne = 0; colonne < n; colonne++) {
        if (qr.isDark(ligne, colonne)) trace += `M${colonne} ${ligne}h1v1h-1z`;
      }
    }

    return `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${n} ${n}" shape-rendering="crispEdges">`
      + `<rect width="${n}" height="${n}" fill="#fff"/>`
      + `<path d="${trace}" fill="#000"/>`
      + '</svg>';
  }

  /* --- Mesures --- */

  const taillePx = (el) => parseFloat(getComputedStyle(el).fontSize);
  const nombreLignes = (el) => Math.round(el.offsetHeight / (taillePx(el) * INTERLIGNE));
  const debordeEnLargeur = (el, conteneur = el) => el.scrollWidth > conteneur.clientWidth + 1;
  const unPasDeMoins = (taille) => Math.round((taille - PAS) * 10) / 10;

  function fixerTaille(el, taille) {
    el.style.fontSize = `${taille}cqw`;
  }

  // Réduit la taille (en cqw) tant que l'élément déborde
  function reduire(el, { depart, min }, deborde) {
    let taille = depart;
    fixerTaille(el, taille);
    while (taille > min && deborde()) {
      taille = unPasDeMoins(taille);
      fixerTaille(el, taille);
    }
  }

  /* --- Ajustement du texte --- */

  function ajusterNomEtFonction(carte, nom, fonction) {
    // 1. Chaque ligne du nom (nom / postnom / prénom) doit tenir dans la largeur
    reduire(nom, TAILLES.nom, () => Array.from(nom.children).some((ligne) => debordeEnLargeur(ligne, nom)));

    // 2. La fonction tient sur 2 lignes maximum
    reduire(fonction, TAILLES.fonction, () => nombreLignes(fonction) > LIGNES_FONCTION_MAX || debordeEnLargeur(fonction));

    // 3. L'ensemble ne doit pas remonter jusqu'au bandeau bleu
    const hauteurMax = carte.clientHeight * HAUTEUR_TEXTE_MAX;
    const hauteurBloc = () => nom.offsetHeight + fonction.offsetHeight + taillePx(fonction) * MARGE_FONCTION;
    let tailleNom = parseFloat(nom.style.fontSize);
    let tailleFonction = parseFloat(fonction.style.fontSize);
    while (hauteurBloc() > hauteurMax && tailleNom > TAILLES.nom.min) {
      tailleNom = unPasDeMoins(tailleNom);
      tailleFonction = Math.max(TAILLES.fonction.min, unPasDeMoins(tailleFonction));
      fixerTaille(nom, tailleNom);
      fixerTaille(fonction, tailleFonction);
    }
  }

  function ajuster(carte) {
    const nom = carte.querySelector('.carte-nom');
    const fonction = carte.querySelector('.carte-fonction');
    const id = carte.querySelector('.carte-id');

    if (nom) ajusterNomEtFonction(carte, nom, fonction);

    if (id) {
      const contenu = id.firstElementChild;
      reduire(id, TAILLES.id, () => contenu.offsetWidth > id.clientWidth * LARGEUR_ID_MAX);
    }
  }

  /* --- Rendu --- */

  function rendre(carte) {
    const qr = carte.querySelector('.carte-qr');
    if (qr) {
      const url = carte.dataset.qr;
      qr.innerHTML = url ? qrSvg(url) : '';
    }
    ajuster(carte);
  }

  const cartes = () => document.querySelectorAll('.carte-recto');

  function tout() {
    cartes().forEach((carte) => rendre(carte));
  }

  window.CarteCHU = { rendre, ajuster, tout, qrSvg };

  /* --- Démarrage --- */

  // Attendre les polices pour mesurer correctement
  function demarrer() {
    const policesPretes = document.fonts && document.fonts.ready ? document.fonts.ready : Promise.resolve();
    policesPretes.then(() => {
      tout();
      document.dispatchEvent(new Event('cartes-pretes'));
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', demarrer);
  } else {
    demarrer();
  }

  window.addEventListener('resize', () => cartes().forEach((carte) => ajuster(carte)));
})();
