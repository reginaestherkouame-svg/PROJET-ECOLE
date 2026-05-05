<?php
include 'db.php';
include 'header.php';
if(!isset($_SESSION['user'])) header("Location: index.php");

$id = $_GET['id'];

if(isset($_POST['update'])) {
    $sql = "UPDATE reservations SET filiere_cours=?, niveau=?, date_res=?, heure_debut=?, heure_fin=? WHERE id=?";
    $pdo->prepare($sql)->execute([$_POST['filiere'], $_POST['niveau'], $_POST['date'], $_POST['debut'], $_POST['fin'], $id]);
    echo "<script>window.location='reservations.php';</script>";
}

$r = $pdo->prepare("SELECT * FROM reservations WHERE id=?");
$r->execute([$id]);
$data = $r->fetch();
?>

<div class="d-flex justify-content-center">
    <div class="card card-custom col-md-6 p-4">
        <h4 class="text-primary mb-3">Modifier la réservation</h4>
        <form method="post">
            <div class="mb-3">
                <label>Filière</label>
                <input type="text" name="filiere" class="form-control" value="<?= $data['filiere_cours'] ?>">
            </div>
            <div class="mb-3">
                <label>Niveau</label>
                <select name="niveau" class="form-select">
                    <option <?= $data['niveau']=='Licence 1'?'selected':'' ?>>Licence 1</option>
                    <option <?= $data['niveau']=='Licence 2'?'selected':'' ?>>Licence 2</option>
                    <option <?= $data['niveau']=='Licence 3'?'selected':'' ?>>Licence 3</option>
                    <option <?= $data['niveau']=='Master'?'selected':'' ?>>Master</option>
                </select>
            </div>
            <div class="mb-3">
                <label>Date</label>
                <input type="date" name="date" class="form-control" value="<?= $data['date_res'] ?>">
            </div>
            <div class="row">
                <div class="col mb-3">
                    <label>Début</label>
                    <input type="time" name="debut" class="form-control" value="<?= $data['heure_debut'] ?>">
                </div>
                <div class="col mb-3">
                    <label>Fin</label>
                    <input type="time" name="fin" class="form-control" value="<?= $data['heure_fin'] ?>">
                </div>
            </div>
            <button type="submit" name="update" class="btn btn-grad w-100">Enregistrer</button>
            <a href="reservations.php" class="btn btn-light w-100 mt-2">Annuler</a>
        </form>
    </div>
</div>
<?php include 'footer.php'; ?>