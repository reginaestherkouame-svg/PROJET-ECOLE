<?php
include("db.php");
include("header.php");

// Requêtes pour compter les données
$nb_salles = $pdo->query("SELECT COUNT(*) FROM salles")->fetchColumn();
$nb_profs = $pdo->query("SELECT COUNT(*) FROM professeurs")->fetchColumn();
$nb_res = $pdo->query("SELECT COUNT(*) FROM reservations")->fetchColumn();
?>

<div class="container">
    <h2 class="mb-4">Bienvenue sur le gestionnaire de salles</h2>

    <div class="row g-4">
        <!-- Carte Salles -->
        <div class="col-md-4">
            <div class="card bg-primary text-white h-100 p-3">
                <div class="card-body">
                    <h5 class="card-title">Salles de classe</h5>
                    <p class="display-4 fw-bold"><?= $nb_salles ?></p>
                    <a href="salles.php" class="text-white text-decoration-none">Gérer les salles &rarr;</a>
                </div>
            </div>
        </div>

        <!-- Carte Profs -->
        <div class="col-md-4">
            <div class="card bg-success text-white h-100 p-3">
                <div class="card-body">
                    <h5 class="card-title">Professeurs</h5>
                    <p class="display-4 fw-bold"><?= $nb_profs ?></p>
                    <a href="profs.php" class="text-white text-decoration-none">Gérer les profs &rarr;</a>
                </div>
            </div>
        </div>

        <!-- Carte Réservations -->
        <div class="col-md-4">
            <div class="card bg-warning text-dark h-100 p-3">
                <div class="card-body">
                    <h5 class="card-title">Réservations</h5>
                    <p class="display-4 fw-bold"><?= $nb_res ?></p>
                    <a href="reservations.php" class="text-dark text-decoration-none">Voir le planning &rarr;</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include("footer.php"); ?>