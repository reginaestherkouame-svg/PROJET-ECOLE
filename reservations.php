<?php
include 'db.php';
include 'header.php';
if(!isset($_SESSION['user'])) header("Location: index.php");

// Ajout Réservation
if(isset($_POST['add'])) {
    $sql = "INSERT INTO reservations (prof_id, salle_id, filiere_cours, niveau, date_res, heure_debut, heure_fin) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $pdo->prepare($sql)->execute([
        $_POST['prof'], $_POST['salle'], $_POST['filiere'], $_POST['niveau'], 
        $_POST['date'], $_POST['debut'], $_POST['fin']
    ]);
}

// Données pour listes déroulantes
$profs = $pdo->query("SELECT * FROM professeurs")->fetchAll();
$salles = $pdo->query("SELECT * FROM salles")->fetchAll();

// Liste des réservations
$sql_list = "SELECT r.*, p.nom_complet, s.nom_salle 
             FROM reservations r 
             JOIN professeurs p ON r.prof_id = p.id 
             JOIN salles s ON r.salle_id = s.id 
             ORDER BY r.date_res, r.heure_debut";
$res = $pdo->query($sql_list)->fetchAll();
?>

<!-- Formulaire Enregistrement -->
<div class="card card-custom mb-4">
    <div class="card-header-custom"><i class="fas fa-calendar-plus"></i> Nouvelle Réservation</div>
    <div class="card-body">
        <form method="post" class="row g-3">
            <div class="col-md-3">
                <label class="form-label fw-bold">Professeur</label>
                <select name="prof" class="form-select" required>
                    <?php foreach($profs as $p) echo "<option value='{$p['id']}'>{$p['nom_complet']}</option>"; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold">Salle</label>
                <select name="salle" class="form-select" required>
                    <?php foreach($salles as $s) echo "<option value='{$s['id']}'>{$s['nom_salle']} ({$s['capacite']} pl.)</option>"; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold">Filière</label>
                <input type="text" name="filiere" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold">Niveau</label>
                <select name="niveau" class="form-select">
                    <option>Licence 1</option><option>Licence 2</option><option>Licence 3</option><option>Master</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold">Date</label>
                <input type="date" name="date" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold">Heure Début</label>
                <input type="time" name="debut" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold">Heure Fin</label>
                <input type="time" name="fin" class="form-control" required>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" name="add" class="btn btn-grad w-100">Valider</button>
            </div>
        </form>
    </div>
</div>

<!-- Tableau Consultation -->
<div class="card card-custom">
    <div class="card-header-custom bg-warning"><i class="fas fa-history"></i> Planning des Cours</div>
    <div class="card-body p-0">
        <table class="table table-custom table-hover mb-0">
            <thead>
                <tr>
                    <th>Date</th><th>Horaire</th><th>Professeur</th><th>Salle</th><th>Cours</th><th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($res as $r): ?>
                <tr class="table-row-custom">
                    <td><?= date('d/m/Y', strtotime($r['date_res'])) ?></td>
                    <td><span class="badge bg-primary"><?= substr($r['heure_debut'],0,5) ?> - <?= substr($r['heure_fin'],0,5) ?></span></td>
                    <td class="fw-bold"><?= $r['nom_complet'] ?></td>
                    <td><?= $r['nom_salle'] ?></td>
                    <td><?= $r['filiere_cours'] ?> <small class="text-muted">(<?= $r['niveau'] ?>)</small></td>
                    <td>
                        <a href="edit_res.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-outline-warning"><i class="fas fa-edit"></i> Modifier</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include 'footer.php'; ?>