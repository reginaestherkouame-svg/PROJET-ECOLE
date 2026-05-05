<?php
include 'db.php';
include 'header.php';

if(!isset($_SESSION['user'])) header("Location: index.php");

$alert_msg = '';

// --- TRAITEMENT ---
if(isset($_POST['add'])) {
    $prof_id = $_POST['prof'];
    $salle_id = $_POST['salle'];
    $filiere_id = $_POST['filiere'];
    $date = $_POST['date'];
    $debut = $_POST['debut'];
    $fin = $_POST['fin'];

    // Horaires (Même logique)
    $creneau_matin = ($debut >= "08:00" && $fin <= "12:00");
    $creneau_soir = ($debut >= "13:00" && $fin <= "17:00");

    if ($debut >= $fin) {
        $alert_msg = "<div class='alert alert-danger'>Heures incohérentes.</div>";
    } elseif (!$creneau_matin && !$creneau_soir) {
        $alert_msg = "<div class='alert alert-danger'>⛔ Horaires invalides (08-12 ou 13-17 uniquement).</div>";
    } else {
        // Conflits
        $sql_check = "SELECT r.*, p.nom_complet, s.nom_salle 
                      FROM reservations r
                      JOIN professeurs p ON r.prof_id = p.id
                      JOIN salles s ON r.salle_id = s.id
                      WHERE r.date_res = ? 
                      AND (r.salle_id = ? OR r.prof_id = ?) 
                      AND (r.heure_debut < ? AND r.heure_fin > ?)";
        
        $stmt = $pdo->prepare($sql_check);
        $stmt->execute([$date, $salle_id, $prof_id, $fin, $debut]);
        $conflit = $stmt->fetch();

        if ($conflit) {
            $alert_msg = "<div class='alert alert-danger'>⛔ CONFLIT DÉTECTÉ : Salle ou Professeur déjà pris.</div>";
        } else {
            // Infos Filière
            $stmt_fil = $pdo->prepare("SELECT * FROM filieres WHERE id = ?");
            $stmt_fil->execute([$filiere_id]);
            $fil_info = $stmt_fil->fetch();

            $sql_insert = "INSERT INTO reservations (prof_id, salle_id, filiere_cours, niveau, date_res, heure_debut, heure_fin) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $pdo->prepare($sql_insert)->execute([$prof_id, $salle_id, $fil_info['nom_filiere'], $fil_info['niveau'], $date, $debut, $fin]);
            $alert_msg = "<div class='alert alert-success'>✅ Cours Universitaire planifié !</div>";
        }
    }
}

// Données (Filtre sur 'LICENCE')
$profs = $pdo->query("SELECT * FROM professeurs WHERE categorie = 'LICENCE'")->fetchAll();
$salles = $pdo->query("SELECT * FROM salles WHERE categorie = 'LICENCE'")->fetchAll();
$filieres = $pdo->query("SELECT * FROM filieres WHERE categorie = 'LICENCE'")->fetchAll();

// Liste (Filtre large pour afficher tout ce qui n'est pas BT/BTS ou juste LICENCE)
$res = $pdo->query("SELECT r.*, p.nom_complet, s.nom_salle FROM reservations r JOIN professeurs p ON r.prof_id = p.id JOIN salles s ON r.salle_id = s.id WHERE r.niveau LIKE 'Licence%' OR r.niveau LIKE 'Master%' ORDER BY r.date_res DESC")->fetchAll();
?>

<div class="row">
    <div class="col-12">
        <h2 class="fw-bold" style="color: #667eea;"><i class="fas fa-university"></i> Planning - Université</h2>
        <?= $alert_msg ?>
    </div>

    <div class="col-md-12 mb-4">
        <div class="card card-custom border-0 shadow-sm">
            <div class="card-header-custom" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                <i class="fas fa-calendar-plus"></i> Nouveau Cours Magistral
            </div>
            <div class="card-body">
                <form method="post" class="row g-3">
                    <div class="col-md-4">
                        <label class="fw-bold small text-muted">ENSEIGNANT</label>
                        <select name="prof" class="form-select" required>
                            <option value="">-- Choisir --</option>
                            <?php foreach($profs as $p) echo "<option value='{$p['id']}'>{$p['nom_complet']}</option>"; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="fw-bold small text-muted">AMPHITHÉÂTRE / SALLE</label>
                        <select name="salle" class="form-select" required>
                            <option value="">-- Choisir --</option>
                            <?php foreach($salles as $s) echo "<option value='{$s['id']}'>{$s['nom_salle']} ({$s['capacite']} pl.)</option>"; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="fw-bold small text-muted">UE / MATIÈRE</label>
                        <select name="filiere" class="form-select" required>
                            <option value="">-- Choisir --</option>
                            <?php foreach($filieres as $f) echo "<option value='{$f['id']}'>{$f['niveau']} - {$f['nom_filiere']}</option>"; ?>
                        </select>
                    </div>
                    <div class="col-md-4"><label class="fw-bold small text-muted">DATE</label><input type="date" name="date" class="form-control" required></div>
                    <div class="col-md-3"><label class="fw-bold small text-muted">DEBUT</label><input type="time" name="debut" class="form-control" required></div>
                    <div class="col-md-3"><label class="fw-bold small text-muted">FIN</label><input type="time" name="fin" class="form-control" required></div>
                    <div class="col-md-2 d-flex align-items-end"><button type="submit" name="add" class="btn text-white w-100 fw-bold" style="background: #667eea;">Valider</button></div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-12">
        <div class="card card-custom">
            <div class="card-header bg-white"><h5 class="m-0 text-secondary fw-bold">📅 Emploi du temps Université</h5></div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light"><tr><th>Date</th><th>Horaire</th><th>Niveau</th><th>Matière</th><th>Salle</th><th>Professeur</th></tr></thead>
                    <tbody>
                        <?php foreach($res as $r): ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($r['date_res'])) ?></td>
                            <td><span class="badge bg-light text-dark border"><?= substr($r['heure_debut'],0,5) ?> - <?= substr($r['heure_fin'],0,5) ?></span></td>
                            <td><span class="badge bg-primary"><?= $r['niveau'] ?></span></td>
                            <td class="fw-bold"><?= $r['filiere_cours'] ?></td>
                            <td><?= $r['nom_salle'] ?></td>
                            <td><?= $r['nom_complet'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>