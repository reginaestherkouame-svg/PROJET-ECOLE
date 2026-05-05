<?php
include 'db.php';
include 'header.php';
if(!isset($_SESSION['user'])) header("Location: index.php");

// Ajout Prof
if(isset($_POST['add'])) {
    $sql = "INSERT INTO professeurs (nom_complet, contact, filiere) VALUES (?, ?, ?)";
    $pdo->prepare($sql)->execute([$_POST['nom'], $_POST['contact'], $_POST['filiere']]);
}

$profs = $pdo->query("SELECT * FROM professeurs ORDER BY id DESC")->fetchAll();
?>

<div class="row">
    <!-- Formulaire -->
    <div class="col-md-4">
        <div class="card card-custom mb-4">
            <div class="card-header-custom"><i class="fas fa-plus-circle"></i> Ajouter un Professeur</div>
            <div class="card-body">
                <form method="post">
                    <div class="mb-3">
                        <label>Nom Complet</label>
                        <input type="text" name="nom" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Coordonnées (Tél/Email)</label>
                        <input type="text" name="contact" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Filière Enseignée</label>
                        <input type="text" name="filiere" class="form-control" required>
                    </div>
                    <button type="submit" name="add" class="btn btn-grad w-100">Enregistrer</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Tableau -->
    <div class="col-md-8">
        <div class="card card-custom">
            <div class="card-header-custom bg-secondary"><i class="fas fa-list"></i> Liste des Professeurs</div>
            <div class="card-body p-0">
                <table class="table table-custom table-hover mb-0">
                    <thead>
                        <tr><th>Nom</th><th>Contact</th><th>Filière</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach($profs as $p): ?>
                        <tr class="table-row-custom">
                            <td class="fw-bold"><?= $p['nom_complet'] ?></td>
                            <td><?= $p['contact'] ?></td>
                            <td><span class="badge bg-info"><?= $p['filiere'] ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>