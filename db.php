<?php
$host = "localhost";
$dbname = "school";
$user = "root";
$pass = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// IMPORTANT : Démarrer la session ici pour tout le site
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>