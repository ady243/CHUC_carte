/* Export d'une carte en image PNG (téléchargement ou copie dans le presse-papiers).
   La carte affichée est redessinée sur un canvas à la résolution du modèle :
   positions et tailles sont relevées sur la page, puis mises à l'échelle. */
(() => {
  const FOND_PHOTO = '#e8edf4';

  /* --- Géométrie --- */

  // Rectangle d'un élément (ou d'un Range) en pixels du canvas, relatif à la carte
  function rectangle(cible, origine, echelle) {
    const r = cible.getBoundingClientRect();
    return {
      x: (r.left - origine.left) * echelle,
      y: (r.top - origine.top) * echelle,
      l: r.width * echelle,
      h: r.height * echelle,
    };
  }

  function tracerRectangleArrondi(ctx, { x, y, l, h }, rayon) {
    const r = Math.min(rayon, l / 2, h / 2);
    ctx.beginPath();
    ctx.moveTo(x + r, y);
    ctx.arcTo(x + l, y, x + l, y + h, r);
    ctx.arcTo(x + l, y + h, x, y + h, r);
    ctx.arcTo(x, y + h, x, y, r);
    ctx.arcTo(x, y, x + l, y, r);
    ctx.closePath();
  }

  /* --- Texte --- */

  // Découpe un nœud texte en lignes telles qu'elles sont affichées (retours à la ligne compris)
  function lignesAffichees(noeud, origine, echelle) {
    const lignes = [];
    const plage = document.createRange();
    const mots = /\S+/g;
    let mot;
    while ((mot = mots.exec(noeud.data))) {
      plage.setStart(noeud, mot.index);
      plage.setEnd(noeud, mot.index + mot[0].length);
      const rect = rectangle(plage, origine, echelle);
      const ligne = lignes[lignes.length - 1];
      if (ligne && Math.abs(rect.y - ligne.y) < rect.h / 2) {
        ligne.texte += ` ${mot[0]}`;
      } else {
        lignes.push({ texte: mot[0], x: rect.x, y: rect.y, h: rect.h });
      }
    }
    return lignes;
  }

  function dessinerTexte(ctx, racine, origine, echelle) {
    const parcours = document.createTreeWalker(racine, NodeFilter.SHOW_TEXT);
    let noeud;
    while ((noeud = parcours.nextNode())) {
      const parent = noeud.parentElement;
      const style = getComputedStyle(parent);
      const majuscules = style.textTransform === 'uppercase';

      ctx.font = `${style.fontWeight} ${parseFloat(style.fontSize) * echelle}px ${style.fontFamily}`;
      ctx.fillStyle = style.color;
      ctx.globalAlpha = parent.closest('.vide') ? 0.3 : 1;
      if ('letterSpacing' in ctx) {
        ctx.letterSpacing = `${(parseFloat(style.letterSpacing) || 0) * echelle}px`;
      }

      // Ligne de base : le texte est centré verticalement dans sa boîte, comme en CSS
      const mesures = ctx.measureText('x');
      const montee = mesures.fontBoundingBoxAscent;
      const hauteurPolice = montee + mesures.fontBoundingBoxDescent;

      lignesAffichees(noeud, origine, echelle).forEach((ligne) => {
        const texte = majuscules ? ligne.texte.toLocaleUpperCase('fr') : ligne.texte;
        ctx.fillText(texte, ligne.x, ligne.y + (ligne.h - hauteurPolice) / 2 + montee);
      });
    }
    ctx.globalAlpha = 1;
  }

  /* --- Éléments de la carte --- */

  function dessinerBadgeId(ctx, id, origine, echelle) {
    const zone = rectangle(id, origine, echelle);
    tracerRectangleArrondi(ctx, zone, zone.h / 2);
    ctx.fillStyle = getComputedStyle(id).backgroundColor;
    ctx.fill();
    dessinerTexte(ctx, id, origine, echelle);
  }

  function dessinerQr(ctx, conteneur, url, origine, echelle) {
    if (!url) return;
    const zone = rectangle(conteneur, origine, echelle);
    const qr = qrcode(0, 'M');
    qr.addData(url);
    qr.make();

    const n = qr.getModuleCount();
    const module = zone.l / n;
    ctx.fillStyle = '#fff';
    ctx.fillRect(zone.x, zone.y, zone.l, zone.h);
    ctx.fillStyle = '#000';
    for (let ligne = 0; ligne < n; ligne++) {
      for (let colonne = 0; colonne < n; colonne++) {
        if (!qr.isDark(ligne, colonne)) continue;
        // Bords arrondis au pixel pour éviter les filets blancs entre modules
        const x = Math.round(zone.x + colonne * module);
        const y = Math.round(zone.y + ligne * module);
        ctx.fillRect(x, y, Math.round(zone.x + (colonne + 1) * module) - x, Math.round(zone.y + (ligne + 1) * module) - y);
      }
    }
  }

  // Reproduit object-fit: cover + object-position + transform: scale() de la photo
  function dessinerPhoto(ctx, cadre, origine, echelle) {
    const zone = rectangle(cadre, origine, echelle);
    const img = cadre.querySelector('img');
    const rayon = parseFloat(getComputedStyle(cadre).borderTopLeftRadius) * echelle;

    ctx.save();
    tracerRectangleArrondi(ctx, zone, rayon);
    ctx.clip();
    ctx.fillStyle = FOND_PHOTO;
    ctx.fillRect(zone.x, zone.y, zone.l, zone.h);

    const chargee = img.complete && img.naturalWidth > 0;
    if (chargee && !cadre.classList.contains('sans-photo')) {
      const [posX, posY] = (img.style.objectPosition || '50% 50%').split(' ').map((v) => parseFloat(v) / 100);
      const zoom = parseFloat((/scale\(([^)]+)\)/.exec(img.style.transform) || [])[1]) || 1;
      const couverture = Math.max(zone.l / img.naturalWidth, zone.h / img.naturalHeight);
      const largeur = img.naturalWidth * couverture;
      const hauteur = img.naturalHeight * couverture;
      const pivotX = zone.x + zone.l * posX;
      const pivotY = zone.y + zone.h * posY;

      ctx.translate(pivotX, pivotY);
      ctx.scale(zoom, zoom);
      ctx.translate(-pivotX, -pivotY);
      ctx.drawImage(img, zone.x + (zone.l - largeur) * posX, zone.y + (zone.h - hauteur) * posY, largeur, hauteur);
    }
    ctx.restore();
  }

  /* --- Carte complète --- */

  // Dessine une face (.carte-recto ou .carte-verso) à la résolution du modèle
  function versCanvas(carte) {
    const fond = carte.querySelector('.carte-fond');
    const canvas = document.createElement('canvas');
    canvas.width = fond.naturalWidth;
    canvas.height = fond.naturalHeight;

    const ctx = canvas.getContext('2d');
    const origine = carte.getBoundingClientRect();
    const echelle = canvas.width / origine.width;

    ctx.drawImage(fond, 0, 0, canvas.width, canvas.height);

    const texte = carte.querySelector('.carte-texte');
    const id = carte.querySelector('.carte-id');
    const qr = carte.querySelector('.carte-qr');
    const photo = carte.querySelector('.carte-photo');
    if (texte) dessinerTexte(ctx, texte, origine, echelle);
    if (id) dessinerBadgeId(ctx, id, origine, echelle);
    if (qr) dessinerQr(ctx, qr, carte.dataset.qr, origine, echelle);
    if (photo) dessinerPhoto(ctx, photo, origine, echelle);

    return canvas;
  }

  function versPng(carte) {
    return new Promise((resolve, reject) => {
      versCanvas(carte).toBlob((blob) => (blob ? resolve(blob) : reject(new Error('Image non générée'))), 'image/png');
    });
  }

  /* --- Actions --- */

  async function telecharger(carte, nomFichier) {
    const url = URL.createObjectURL(await versPng(carte));
    const lien = document.createElement('a');
    lien.href = url;
    lien.download = nomFichier;
    lien.click();
    setTimeout(() => URL.revokeObjectURL(url), 1000);
  }

  // Le presse-papiers n'accepte les images que sur https ou localhost
  const copiePossible = () => Boolean(navigator.clipboard && navigator.clipboard.write && window.ClipboardItem);

  async function copier(carte) {
    const image = await versPng(carte);
    await navigator.clipboard.write([new ClipboardItem({ 'image/png': image })]);
  }

  window.CarteImage = { versCanvas, versPng, telecharger, copier, copiePossible };
})();
