<?php
/**
 * connexion.php
 * Connexion à la base de données via PDO
 * ENSI 2025/2026 — Partie IV
 */

$host     = 'localhost';
$dbname   = 'gestion_stage';
$username = 'root';       // Changer selon votre config XAMPP
$password = '';           // Changer selon votre config XAMPP
$charset  = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    die("
    <!DOCTYPE html>
    <html lang='fr'>
    <head><meta charset='UTF-8'><title>Erreur de connexion</title>
    <style>body{font-family:Arial;padding:40px;background:#f8d7da;color:#721c24;}
    .box{background:#fff;padding:20px;border-radius:8px;border:1px solid #f5c6cb;}
    </style></head>
    <body><div class='box'>
    <h2>❌ Erreur de connexion à la base de données</h2>
    <p><strong>Message :</strong> " . htmlspecialchars($e->getMessage()) . "</p>
    <p>Vérifiez que XAMPP est démarré (Apache + MySQL) et que la base <strong>gestion_stage</strong> existe.</p>
    </div></body></html>
    ");
}
?>
