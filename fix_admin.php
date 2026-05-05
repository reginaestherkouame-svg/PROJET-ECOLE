<?php
// fix_admin.php
require 'db.php';

// 1. On crypte le mot de passe "admin"
$nouveau_mdp = password_hash("admin", PASSWORD_DEFAULT);

try {
    // 2. On vide la table users pour éviter les doublons
    $pdo->query("TRUNCATE TABLE users");

    // 3. On insère le bon utilisateur
    $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (:u, :p)");
    $stmt->execute(['u' => 'admin', 'p' => $nouveau_mdp]);
<?php
// On inclut db.php qui contient session_start()
require 'db.php';

// Si déjà connecté, on redirige
if (isset($_SESSION['user'])) {
    header("Location: home.php");
    exit();
}

$error = null;

if (isset($_POST['login'])) {
    $u = $_POST['username'];
    $p = $_POST['password'];

    // Recherche de l'utilisateur
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$u]);
    $user = $stmt->fetch();

    if ($user) {
        // L'utilisateur existe, on vérifie le mot de passe
        if (password_verify($p, $user['password'])) {
            // SUCCÈS !
            $_SESSION['user'] = $user['username'];
            header("Location: home.php");
            exit();
        } else {
            $error = "Mot de passe incorrect.";
        }
    } else {
        $error = "Cet utilisateur n'existe pas.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion - Univ Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="style.css" rel="stylesheet">
</head>
<body>

<div class="split-screen">
    <div class="left-pane" style="background-image: url('https://images.unsplash.com/photo-1541339907198-e08756dedf3f?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80');"></div>
    
    <div class="right-pane">
        <div class="auth-container">
            <div class="brand-title">
                <i class="fas fa-university"></i> UNIV MANAGER
            </div>
            <p class="subtitle">Bienvenue ! Veuillez vous identifier.</p>

            <!-- Affichage de l'erreur -->
            <?php if($error): ?>
                <div class="alert alert-danger fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i> <?= $error ?>
                </div>
            <?php endif; ?>

            <form method="post">
                <div class="form-floating mb-3">
                    <input type="text" name="username" class="form-control" id="floatingInput" placeholder="User" required>
                    <label for="floatingInput"><i class="fas fa-user"></i> Identifiant</label>
                </div>
                <div class="form-floating mb-4">
                    <input type="password" name="password" class="form-control" id="floatingPassword" placeholder="Password" required>
                    <label for="floatingPassword"><i class="fas fa-key"></i> Mot de passe</label>
                </div>
                
                <button type="submit" name="login" class="btn btn-grad w-100 py-3">Connexion</button>
            </form>

            <div class="text-center mt-4">
                <p class="text-muted">Pas encore de compte ? <a href="register.php" class="auth-link">Créer un compte</a></p>
            </div>
        </div>
    </div>
</div>

</body>
</html>
    echo "<h1 style='color:green; text-align:center;'>SUCCÈS ! ✅</h1>";
    echo "<p style='text-align:center;'>L'utilisateur <b>admin</b> a été recréé avec le mot de passe <b>admin</b> (crypté).</p>";
    echo "<p style='text-align:center;'><a href='index.php'>Retourner à la connexion</a></p>";

} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage();
}
?>