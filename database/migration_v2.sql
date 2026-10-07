-- À exécuter UNIQUEMENT si la base de l'ancienne version existe déjà.
-- Nouvelle installation : importer chu50_cartes.sql à la place.
-- Attention : dans l'ancienne version, photo_recto contenait l'image complète du recto,
-- pas la photo de l'agent. Les cartes existantes devront recevoir une vraie photo
-- (bouton « Modifier »).
USE chu50_cartes;
ALTER TABLE cartes
 CHANGE photo_recto photo VARCHAR(255) NOT NULL,
 DROP COLUMN image_verso,
 ADD COLUMN photo_zoom DECIMAL(4,2) NOT NULL DEFAULT 1.00 AFTER photo,
 ADD COLUMN photo_x TINYINT UNSIGNED NOT NULL DEFAULT 50 AFTER photo_zoom,
 ADD COLUMN photo_y TINYINT UNSIGNED NOT NULL DEFAULT 30 AFTER photo_x;
