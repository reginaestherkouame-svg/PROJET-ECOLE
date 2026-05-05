<?php
// Inclure db.php pour s'assurer que la session est déjà démarrée proprement
include 'db.php';

// Vider toutes les variables de session
$_SESSION = array();

// Détruire le cookie de session si présent
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Détruire la session
session_destroy();

// Rediriger vers la page de connexion
header("Location: index.php");
exit();
?>
