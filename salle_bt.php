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
        header("Location: salle_bt.php");
        exit();
    }
}

// 3. Récupération
$req = $bdd->prepare("SELECT * FROM salle WHERE niveau = ? ORDER BY id DESC");
$req->execute([$niveau_actuel]);
$salles = $req->fetchAll();
$total = count($salles);
?>
<!-- COPIE EXACTE DU HTML DU FICHIER PRECEDENT (salle_bts.php) -->
<!-- JE LE REMETS ICI POUR EVITER TOUTE ERREUR -->
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Univ Manager - BT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', sans-serif; }
        .navbar { background-color: #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.05); padding: 0.8rem 1rem; }
        .navbar-brand { color: #0d6efd; font-weight: 700; font-size: 1.2rem; display: flex; align-items: center; gap: 8px; }
        .nav-link { color: #555; font-weight: 600; font-size: 0.95rem; margin-right: 15px; display: flex; align-items: center; gap: 6px; }
        .nav-link:hover, .nav-link.active { color: #333; }
        .btn-logout { background-color: #dc3545; color: white; border-radius: 20px; padding: 6px 20px; font-size: 0.85rem; text-decoration: none; font-weight: 600; display: flex; align-items: center; gap: 5px; }
        .user-info { text-align: right; line-height: 1.1; margin-right: 15px; font-size: 0.85rem; }
        .page-title { color: #009688; font-weight: bold; margin: 30px 0 20px 0; font-size: 1.6rem; display: flex; align-items: center; gap: 10px; }
        .card-stat { color: white; text-align: center; padding: 25px 15px; border-radius: 6px; border:none; margin-bottom: 20px; }
        .bg-blue { background-color: #0d6efd; }
        .bg-green { background-color: #198754; }
        .bg-red { background-color: #dc3545; }
        .stat-num { font-size: 1.8rem; font-weight: bold; display: block; margin-bottom: 5px; }
        .card-form { background: white; border: 1px solid #dee2e6; border-radius: 5px; overflow: hidden; }
        .form-header { background-color: #20c997; color: white; padding: 10px 15px; font-weight: bold; font-size: 0.95rem; display: flex; align-items: center; gap: 8px; }
        .form-control { border-radius: 4px; border: 1px solid #ced4da; padding: 10px; font-size: 0.9rem; }
        .label-custom { font-size: 0.75rem; font-weight: 700; color: #6c757d; text-transform: uppercase; margin-bottom: 6px; margin-top: 15px; display: block; }
        .label-custom:first-child { margin-top: 0; }
        .btn-teal { background-color: #1aa18e; color: white; width: 100%; border: none; padding: 12px; font-weight: bold; margin-top: 20px; border-radius: 4px; }
        .btn-teal:hover { background-color: #138a79; color: white; }
        .table-container { background: white; border: 1px solid #dee2e6; border-radius: 5px; padding: 20px; min-height: 420px; }
        .table thead th { border-bottom: 2px solid #dee2e6; color: #212529; font-weight: 700; font-size: 0.9rem; }
        .table tbody td { vertical-align: middle; font-size: 0.9rem; font-weight: 600; }
        footer { text-align: center; margin-top: 50px; color: #6c757d; font-weight: 700; font-size: 0.9rem; padding-bottom: 20px; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg sticky-top">
    <div class="container-fluid px-4">
        <a class="navbar-brand" href="#"><i class="fa-solid fa-building-columns"></i> UNIV MANAGER</a>
        <div class="collapse navbar-collapse justify-content-center">
            <ul class="navbar-nav">
                <li class="nav-item"><a class="nav-link" href="#"><i class="fa-solid fa-house"></i> Accueil</a></li>
                <li class="nav-item"><a class="nav-link" href="#"><i class="fa-solid fa-user-tie"></i> Professeurs</a></li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle active" href="#" data-bs-toggle="dropdown"><i class="fa-solid fa-door-open"></i> Salles</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="salle_bt.php">Niveau BT</a></li>
                        <li><a class="dropdown-item" href="salle_bts.php">Niveau BTS</a></li>
                        <li><a class="dropdown-item" href="salle_licence.php">Niveau LICENCE</a></li>
                    </ul>
                </li>
                <li class="nav-item"><a class="nav-link" href="#"><i class="fa-solid fa-table-cells"></i> Réservations</a></li>
            </ul>
        </div>
        <div class="d-flex align-items-center">
            <div class="user-info"><span class="text-muted small">Connecté en tant que</span><br><strong>admin</strong></div>
            <a href="#" class="btn-logout"><i class="fa-solid fa-arrow-right-from-bracket"></i> Déconnexion</a>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <h2 class="page-title"><i class="fa-solid fa-building"></i> Gestion Salles - Niveau <?php echo $niveau_actuel; ?></h2>
    <div class="row g-4 mb-4">
        <div class="col-md-4"><div class="card-stat bg-blue"><span class="stat-num"><?php echo $total; ?></span>Total Salles</div></div>
        <div class="col-md-4"><div class="card-stat bg-green"><span class="stat-num">0</span>Disponibles</div></div>
        <div class="col-md-4"><div class="card-stat bg-red"><span class="stat-num">0</span>Occupées</div></div>
    </div>
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card-form">
                <div class="form-header"><i class="fa-solid fa-circle-plus"></i> Ajouter Salle</div>
                <div class="p-3">
                    <form method="POST">
                        <label class="label-custom">NOM</label>
                        <input type="text" name="nom" class="form-control" placeholder="LABO 1" required>
                        <label class="label-custom">IDENTIFIANT</label>
                        <input type="text" name="code" class="form-control" placeholder="BT-01" required>
                        <label class="label-custom">CAPACITÉ</label>
                        <input type="number" name="cap" class="form-control" placeholder="30" required>
                        <button type="submit" name="btn_save" class="btn-teal">Enregistrer</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="table-container">
                <h5 class="fw-bold text-secondary mb-3">État des salles</h5>
                <table class="table table-hover">
                    <thead><tr><th>Salle</th><th>Capacité</th><th>État Actuel</th><th>Action</th></tr></thead>
                    <tbody>
                        <?php if($total > 0): ?>
                            <?php foreach($salles as $s): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($s['code_salle']); ?></strong> - <?php echo htmlspecialchars($s['nom_salle']); ?></td>
                                <td><?php echo htmlspecialchars($s['capacite']); ?></td>
                                <td><span class="badge bg-success">Libre</span></td>
                                <td><button class="btn btn-sm btn-outline-primary border-0"><i class="fa-solid fa-pen"></i></button> <button class="btn btn-sm btn-outline-danger border-0"><i class="fa-solid fa-trash"></i></button></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="text-center text-muted py-5">Aucune salle BT.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<footer>Copyright © 2025 - By Esther</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>