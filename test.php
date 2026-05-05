<?php
// --- CONFIGURATION : NIVEAU BT ---
$niveau_actuel = "BT"; 

// 1. Connexion BDD
try {
    $bdd = new PDO("mysql:host=localhost;dbname=univ;charset=utf8", "root", "");
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) { die("Erreur : " . $e->getMessage()); }

// 2. Traitement Ajout
if(isset($_POST['btn_save'])) {
    if(!empty($_POST['nom']) && !empty($_POST['code']) && !empty($_POST['cap'])) {
        $sql = "INSERT INTO salle (nom_salle, code_salle, capacite, niveau) VALUES (?, ?, ?, ?)";
        $stmt = $bdd->prepare($sql);
        $stmt->execute([$_POST['nom'], $_POST['code'], $_POST['cap'], $niveau_actuel]);
        header("Location: test.php"); // Recharge la page
        exit();
    }
}

// 3. Récupération des données (Filtrage BT)
$req = $bdd->prepare("SELECT * FROM salle WHERE niveau = ? ORDER BY id DESC");
$req->execute([$niveau_actuel]);
$salles = $req->fetchAll();
$total = count($salles);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion Salles - <?php echo $niveau_actuel; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', sans-serif; }
        /* Style Navbar */
        .navbar { background: white; box-shadow: 0 2px 4px rgba(0,0,0,0.05); padding: 0.8rem 1rem; }
        .navbar-brand { color: #0d6efd; font-weight: bold; font-size: 1.3rem; }
        .nav-link { color: #555; font-weight: 500; margin-right: 15px; }
        .nav-link:hover { color: #0d6efd; }
        .btn-logout { background-color: #dc3545; color: white; border-radius: 20px; padding: 5px 15px; text-decoration: none; font-size: 0.9rem; }
        
        /* Cartes Statistiques */
        .card-stat { color: white; text-align: center; padding: 25px; border-radius: 6px; border:none; margin-bottom: 20px; }
        .bg-blue { background-color: #0d6efd; }
        .bg-green { background-color: #198754; } /* Vert standard */
        .bg-teal { background-color: #14a2b8; }  /* Couleur sarcelle de ton image pour le titre/bouton */
        .bg-red { background-color: #dc3545; }
        .stat-num { font-size: 2rem; font-weight: bold; display: block; }
        
        /* Titre et Formulaire */
        .page-title { color: #009688; font-weight: bold; margin: 25px 0; }
        .card-form { border: 1px solid #ddd; background: white; border-radius: 5px; overflow: hidden; }
        .form-header { background-color: #20c997; color: white; padding: 10px 15px; font-weight: bold; } /* Vert de ton image */
        .btn-custom-save { background-color: #17a2b8; color: white; width: 100%; border: none; padding: 10px; font-weight: bold; margin-top: 10px; }
        .btn-custom-save:hover { background-color: #138496; }
        .form-label-custom { font-size: 0.75rem; font-weight: bold; color: #555; text-transform: uppercase; margin-bottom: 5px; display: block; }
        
        /* Footer */
        footer { margin-top: 40px; text-align: center; color: #6c757d; font-size: 0.9rem; font-weight: bold; margin-bottom: 20px;}
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg">
    <div class="container-fluid">
        <a class="navbar-brand" href="#"><i class="fa-solid fa-building-columns"></i> UNIV MANAGER</a>
        <div class="collapse navbar-collapse justify-content-center">
            <ul class="navbar-nav">
                <li class="nav-item"><a class="nav-link" href="#"><i class="fa fa-home"></i> Accueil</a></li>
                <li class="nav-item"><a class="nav-link" href="#"><i class="fa fa-user-tie"></i> Professeurs</a></li>
                <!-- Liens vers les niveaux -->
                <li class="nav-item"><a class="nav-link" href="salle_bt.php">BT</a></li>
                <li class="nav-item"><a class="nav-link" href="salle_bts.php">BTS</a></li>
                <li class="nav-item"><a class="nav-link" href="salle_licence.php">LICENCE</a></li>
            </ul>
        </div>
        <div class="d-flex align-items-center">
            <div class="text-end me-3 lh-1">
                <span class="text-muted small">Connecté en tant que</span><br>
                <strong>admin</strong>
            </div>
            <a href="#" class="btn-logout"><i class="fa fa-sign-out-alt"></i> Déconnexion</a>
        </div>
    </div>
</nav>

<div class="container">
    <h3 class="page-title"><i class="fa-solid fa-school"></i> Gestion Salles - Niveau <?php echo $niveau_actuel; ?></h3>

    <!-- Statistiques -->
    <div class="row">
        <div class="col-md-4"><div class="card-stat bg-blue"><span class="stat-num"><?php echo $total; ?></span>Total Salles</div></div>
        <div class="col-md-4"><div class="card-stat bg-green"><span class="stat-num">0</span>Disponibles</div></div>
        <div class="col-md-4"><div class="card-stat bg-red"><span class="stat-num">0</span>Occupées</div></div>
    </div>

    <div class="row">
        <!-- Formulaire (Gauche) -->
        <div class="col-md-4">
            <div class="card-form">
                <div class="form-header"><i class="fa fa-plus-circle"></i> Ajouter Salle</div>
                <div class="p-3">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label-custom">NOM</label>
                            <input type="text" name="nom" class="form-control" placeholder="PERSEVERANCE" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label-custom">IDENTIFIANT</label>
                            <input type="text" name="code" class="form-control" placeholder="S1" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label-custom">CAPACITÉ</label>
                            <input type="number" name="cap" class="form-control" placeholder="30" required>
                        </div>
                        <button type="submit" name="btn_save" class="btn-custom-save">Enregistrer</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Tableau (Droite) -->
        <div class="col-md-8">
            <div class="card card-form p-3">
                <h5 class="text-secondary mb-3 fw-bold">État des salles</h5>
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Salle</th>
                            <th>Capacité</th>
                            <th>État Actuel</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($salles as $s): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($s['code_salle']); ?></strong> 
                                - <?php echo htmlspecialchars($s['nom_salle']); ?>
                            </td>
                            <td><?php echo htmlspecialchars($s['capacite']); ?></td>
                            <td><span class="badge bg-success">Libre</span></td> <!-- Statique pour l'instant -->
                            <td>
                                <button class="btn btn-sm btn-outline-primary"><i class="fa fa-edit"></i></button>
                                <button class="btn btn-sm btn-outline-danger"><i class="fa fa-trash"></i></button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<footer>Copyright © 2025 - By Esther</footer>

</body>
</html>