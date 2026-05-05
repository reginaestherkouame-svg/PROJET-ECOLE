<?php
// db.php gère la connexion PDO ET démarre la session
include 'db.php';

// Si déjà connecté, rediriger vers l'accueil
if (isset($_SESSION['user'])) {
    header("Location: home.php");
    exit();
}

$error = '';

if (isset($_POST['login'])) {
    $u = trim(htmlspecialchars($_POST['username']));
    $p = $_POST['password'];

    if (empty($u) || empty($p)) {
        $error = "Veuillez remplir tous les champs.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$u]);
        $user = $stmt->fetch();

        if ($user && password_verify($p, $user['password'])) {
            $_SESSION['user'] = $user['username'];
            header("Location: home.php");
            exit();
        } else {
            $error = "Identifiant ou mot de passe incorrect.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — Univ Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="style.css" rel="stylesheet">
</head>
<body>

<div class="split-screen">
    <!-- Image de gauche -->
    <div class="left-pane" style="background-image: url('https://images.unsplash.com/photo-1541339907198-e08756dedf3f?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80');"></div>
    
    <!-- Formulaire à droite -->
    <div class="right-pane">
        <div class="auth-container">
            <div class="brand-title">
                <i class="fas fa-university"></i>
            </div>
            <h4 class="fw-bold mb-1" style="color:#333;">PLATE-FORME DE GESTION</h4>
            <p class="subtitle mb-4">Bienvenue ! Veuillez vous identifier.</p>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger d-flex align-items-center gap-2 py-2">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="post" autocomplete="off">
                <div class="form-floating mb-3">
                    <input type="text" name="username" class="form-control" id="floatingInput" placeholder="User" required>
                    <label for="floatingInput"><i class="fas fa-user me-1"></i> Identifiant</label>
                </div>
                <div class="form-floating mb-4">
                    <input type="password" name="password" class="form-control" id="floatingPassword" placeholder="Password" required>
                    <label for="floatingPassword"><i class="fas fa-key me-1"></i> Mot de passe</label>
                </div>
                
                <button type="submit" name="login" class="btn btn-grad w-100">
                    <i class="fas fa-sign-in-alt me-2"></i> Se connecter
                </button>
            </form>

            <div class="text-center mt-4">
                <p class="text-muted small">Pas encore de compte ? <a href="register.php" class="auth-link">Créer un compte</a></p>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
