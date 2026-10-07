<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

/* Connexion MySQL : par socket Unix si DB_SOCKET est défini, sinon par hôte */
$cible = DB_SOCKET !== ''
    ? 'unix_socket=' . DB_SOCKET
    : 'host=' . DB_HOST;

$dsn = 'mysql:' . $cible . ';dbname=' . DB_NAME . ';charset=utf8mb4';

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    die('<h2>Connexion MySQL impossible</h2><p>' . htmlspecialchars($e->getMessage()) . '</p>');
}
    