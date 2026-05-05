<?php
include 'db.php';
include 'header.php';

if(!isset($_SESSION['user'])) header("Location: index.php");

// AJOUT
if(isset($_POST['add'])) {
    $sql = "INSERT INTO filieres (nom_filiere, niveau, categorie) VALUES (?, ?, ?)";
    $pdo->prepare($sql)->execute([$_POST['nom'], $_POST['niveau'], $_POST['categorie']]);
    echo "<div class='alert alert-success'>Filière ajoutée !</div>";
}

// SUPPRESSION
if(isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM filieres WHERE id=?")->execute([$_GET['delete']]);
    echo "<script>window.location.href='gestion_filieres.php';</script>";
}

$filieres = $pdo->query("SELECT * FROM filieres ORDER BY categorie, niveau, nom_filiere")->fetchAll();
?>

<div class="row g-4">
    <!-- Formulaire d'ajout -->
    <div class="col-md-4">
        <div class="card card-custom">
            <div class="card-header-custom bg-dark">
                <i class="fas fa-layer-group"></i> Configurer les Filières
            </div>
            <div class="card-body">
                <form method="post">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nom Filière</label>
                        <input type="text" name="nom" class="form-control" placeholder="Ex: Réseau Informatique" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Catégorie (Groupe)</label>
                        <select name="categorie" class="form-select" id="catSelect" onchange="updateNiveaux()" required>
                            <option value="BT">Niveau BT</option>
                            <option value="BTS">Niveau BTS</option>
                            <option value="LICENCE">Niveau Université</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Niveau d'étude</label>
                        <select name="niveau" class="form-select" id="niveauSelect" required>
                            <!-- Rempli par Javascript -->
                        </select>
                    </div>
                    <button type="submit" name="add" class="btn btn-grad w-100">Ajouter</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Liste -->
    <div class="col-md-8">
        <div class="card card-custom">
            <div class="card-header bg-white"><h5 class="m-0 text-primary fw-bold">Liste des Filières actives</h5></div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light"><tr><th>Catégorie</th><th>Niveau</th><th>Filière</th><th>Action</th></tr></thead>
                    <tbody>
                        <?php foreach($filieres as $f): ?>
                        <tr>
                            <td><span class="badge bg-secondary"><?= $f['categorie'] ?></span></td>
                            <td class="fw-bold"><?= $f['niveau'] ?></td>
                            <td><?= $f['nom_filiere'] ?></td>
                            <td><a href="?delete=<?= $f['id'] ?>" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></a></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function updateNiveaux() {
    const cat = document.getElementById('catSelect').value;
    const niv = document.getElementById('niveauSelect');
    niv.innerHTML = "";
    
    let options = [];
    if(cat === 'BT') {
        options = ['BT 1', 'BT 2', 'BT 3'];
    } else if(cat === 'BTS') {
        options = ['BTS 1', 'BTS 2', 'LICENCE 3 (Pro)']; // La règle BTS + L3
    } else {
        options = ['Licence 1', 'Licence 2', 'Licence 3', 'Master 1', 'Master 2'];
    }
    
    options.forEach(opt => {
        let el = document.createElement("option");
        el.value = opt;
        el.textContent = opt;
        niv.appendChild(el);
    });
}
// Init au chargement
window.onload = updateNiveaux;
</script>

<?php include 'footer.php'; ?>