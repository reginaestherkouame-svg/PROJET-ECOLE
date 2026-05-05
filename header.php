<?php
// Démarrage de la session si elle n'est pas déjà active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// SÉCURITÉ : Vérification de la connexion
// On liste les pages accessibles SANS être connecté
$pages_publiques = ['index.php', 'register.php'];
$page_actuelle = basename($_SERVER['PHP_SELF']);

// Si l'utilisateur n'est PAS connecté ET qu'il essaie d'accéder à une page privée
if (!isset($_SESSION['user']) && !in_array($page_actuelle, $pages_publiques)) {
    header("Location: index.php"); // Retour à la case départ
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Univ Manager - Gestion</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome (Icônes) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts (Poppins) -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <!-- Ton fichier CSS personnalisé -->
    <link href="style.css" rel="stylesheet">
</head>
<body>

<!-- BARRE DE NAVIGATION (Visible seulement si connecté) -->
<?php if(isset($_SESSION['user'])): ?>
<nav class="navbar navbar-expand-lg navbar-custom mb-4 sticky-top">
  <div class="container">
    
    <!-- Logo qui redirige vers l'accueil -->
    <a class="navbar-brand fw-bold text-primary d-flex align-items-center" href="home.php">
        <i class="fas fa-university fa-lg me-2"></i> UNIV MANAGER
    </a>

    <!-- Bouton Mobile -->
    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
      <span class="navbar-toggler-icon"></span>
    </button>
    
    <!-- Contenu du Menu -->
    <div class="collapse navbar-collapse" id="navbarContent">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        
        <!-- Lien Accueil -->
        <li class="nav-item">
            <a class="nav-link fw-bold <?= ($page_actuelle == 'home.php') ? 'active text-primary' : '' ?>" href="home.php">
                <i class="fas fa-home me-1"></i> Accueil
            </a>
        </li>

        <!-- Lien Professeurs -->
        <li class="nav-item">
            <a class="nav-link fw-bold <?= ($page_actuelle == 'profs.php') ? 'active text-primary' : '' ?>" href="profs.php">
                <i class="fas fa-chalkboard-teacher me-1"></i> Professeurs
            </a>
        </li>

        <!-- Lien Salles -->
        <li class="nav-item">
            <a class="nav-link fw-bold <?= ($page_actuelle == 'salles.php') ? 'active text-primary' : '' ?>" href="salles.php">
                <i class="fas fa-door-open me-1"></i> Salles
            </a>
        </li>

        <!-- Lien Réservations -->
        <li class="nav-item">
            <a class="nav-link fw-bold <?= ($page_actuelle == 'reservations.php') ? 'active text-primary' : '' ?>" href="reservations.php">
                <i class="far fa-calendar-alt me-1"></i> Réservations
            </a>
        </li>
      </ul>

      <!-- Zone Utilisateur à droite -->
      <div class="d-flex align-items-center">
          <div class="me-3 d-none d-lg-block text-end lh-sm">
              <span class="d-block text-muted small" style="font-size: 0.8rem;">Connecté en tant que</span>
              <span class="fw-bold text-dark"><?= htmlspecialchars($_SESSION['user']) ?></span>
          </div>
          
          <a href="logout.php" class="btn btn-danger btn-sm rounded-pill px-3 shadow-sm transition-hover">
              <i class="fas fa-sign-out-alt me-1"></i> Déconnexion
          </a>
      </div>

    </div>
  </div>
</nav>
<?php endif; ?>

<!-- Début du Container Principal (Fermé dans footer.php) -->
<div class="container py-4 fade-in">