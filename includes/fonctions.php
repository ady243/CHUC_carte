<?php
declare(strict_types=1);

/* --- Utilitaires généraux --- */

function e(?string $valeur): string
{
    return htmlspecialchars($valeur ?? '', ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);

    return $flash;
}

function ensureUploadDirs(): void
{
    if (!is_dir(PHOTO_DIR)) {
        mkdir(PHOTO_DIR, 0775, true);
    }
}

/* --- Protection CSRF des formulaires (création, modification, suppression) --- */

function csrfToken(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }

    return $_SESSION['csrf'];
}

function csrfField(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrfToken()) . '">';
}

function checkCsrf(): void
{
    $attendu = $_SESSION['csrf'] ?? '';
    $recu    = (string)($_POST['csrf'] ?? '');

    if (!hash_equals($attendu, $recu)) {
        throw new RuntimeException('Session expirée, rechargez la page.');
    }
}

/* --- Photo de l'agent --- */

// Enregistre la photo de l'agent (et non plus une image complète de la carte)
function savePhoto(array $fichier): string
{
    if (($fichier['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Photo non reçue.');
    }
    if (($fichier['size'] ?? 0) > MAX_UPLOAD_SIZE) {
        throw new RuntimeException('Photo trop volumineuse (10 MB max).');
    }

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($fichier['tmp_name']);
    if (!isset(ALLOWED_MIME[$mime])) {
        throw new RuntimeException('Format refusé : JPG, PNG ou WEBP.');
    }

    $nom = bin2hex(random_bytes(12)) . '.' . ALLOWED_MIME[$mime];
    if (!move_uploaded_file($fichier['tmp_name'], PHOTO_DIR . $nom)) {
        throw new RuntimeException('Échec d’enregistrement de la photo.');
    }

    return 'uploads/photos/' . $nom;
}

function deletePhoto(?string $cheminRelatif): void
{
    if (!$cheminRelatif || !str_starts_with($cheminRelatif, 'uploads/photos/')) {
        return;
    }

    $chemin = __DIR__ . '/../' . $cheminRelatif;
    if (is_file($chemin)) {
        @unlink($chemin);
    }
}

/* --- Formulaire de carte --- */

// Lecture et validation des champs du formulaire
function readCardForm(): array
{
    $champsTexte  = ['matricule', 'nom', 'postnom', 'prenom', 'fonction', 'date_delivrance'];
    $obligatoires = [
        'matricule' => 'ID / Matricule',
        'nom'       => 'Nom',
        'postnom'   => 'Postnom',
        'prenom'    => 'Prénom',
        'fonction'  => 'Fonction',
    ];

    $donnees = [];
    foreach ($champsTexte as $champ) {
        $donnees[$champ] = trim((string)($_POST[$champ] ?? ''));
    }

    foreach ($obligatoires as $champ => $libelle) {
        if ($donnees[$champ] === '') {
            throw new RuntimeException('Champ obligatoire manquant : ' . $libelle);
        }
    }

    if (!preg_match('/^[A-Za-z0-9._-]{1,40}$/', $donnees['matricule'])) {
        throw new RuntimeException('Matricule invalide : lettres, chiffres, point, tiret uniquement.');
    }

    // Cadrage de la photo, borné aux plages des curseurs du formulaire
    $donnees['photo_zoom'] = max(1.0, min(3.0, (float)($_POST['photo_zoom'] ?? 1)));
    $donnees['photo_x']    = max(0, min(100, (int)($_POST['photo_x'] ?? 50)));
    $donnees['photo_y']    = max(0, min(100, (int)($_POST['photo_y'] ?? 30)));

    return $donnees;
}

/* --- Cartes --- */

function qrUrl(string $id): string
{
    return QR_BASE_URL . rawurlencode($id);
}

function fullName(array $carte): string
{
    $parties = [$carte['nom'] ?? '', $carte['postnom'] ?? '', $carte['prenom'] ?? ''];

    return trim(implode(' ', array_filter($parties)));
}

function cardById(PDO $pdo, int $id): ?array
{
    $requete = $pdo->prepare('SELECT * FROM cartes WHERE id=?');
    $requete->execute([$id]);

    return $requete->fetch() ?: null;
}
