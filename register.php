<?php
include 'db.php';

if (isset($_POST['register'])) {
    $u = htmlspecialchars($_POST['username']);
    $p = $_POST['password'];
    $p_confirm = $_POST['password_confirm'];

    // Vérification si l'utilisateur existe déjà
    $check = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $check->execute([$u]);

    if ($check->rowCount() > 0) {
        $error = "Cet identifiant est déjà utilisé.";
    } elseif ($p != $p_confirm) {
        $error = "Les mots de passe ne correspondent pas.";
    } else {
        // Hachage du mot de passe (Sécurité)
        $p_hash = password_hash($p, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO users (username, password) VALUES (?, ?)";
        if ($pdo->prepare($sql)->execute([$u, $p_hash])) {
            $success = "Compte créé avec succès ! Vous pouvez vous connecter.";
        } else {
            $error = "Erreur lors de l'inscription.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription - Univ Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="style.css" rel="stylesheet">
</head>
<body>

<div class="split-screen">
    <!-- Image de gauche (Change l'URL si tu veux une autre image) -->
    <div class="left-pane" style="background-image: url('https://images.unsplash.com/photo-1523050854058-8df90110c9f1?ixlib=rb-4.0.3&auto=format&fit=crop&w=1350&q=80');"></div>
    
    <!-- Formulaire à droite -->
    <div class="right-pane">
        <div class="auth-container">
            <div class="brand-title">
                <i class="fas fa-graduation-cap"></i> UNIV MANAGER
            </div>
            <p class="subtitle">Créez votre compte administrateur</p>

            <?php if(isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
            <?php if(isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>

            <form method="post">
                <div class="form-floating mb-3">
                    <input type="text" name="username" class="form-control" id="user" placeholder="Identifiant" required>
                    <label for="user"><i class="fas fa-user"></i> Identifiant</label>
                </div>
                <div class="form-floating mb-3">
                    <input type="password" name="password" class="form-control" id="pass" placeholder="Mot de passe" required>
                    <label for="pass"><i class="fas fa-lock"></i> Mot de passe</label>
                </div>
                <div class="form-floating mb-4">
                    <input type="password" name="password_confirm" class="form-control" id="passConf" placeholder="Confirmer" required>
                    <label for="passConf"><i class="fas fa-lock"></i> Confirmer mot de passe</label>
                </div>

                <button type="submit" name="register" class="btn btn-grad">S'inscrire</button>
            </form>

            <div class="text-center mt-4">
                <p class="text-muted">Déjà un compte ? <a href="index.php" class="auth-link">Se connecter</a></p>
            </div>
        </div>
    </div>
</div>

</body>
</html>