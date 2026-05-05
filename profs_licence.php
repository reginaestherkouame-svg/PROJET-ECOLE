<?php
include 'db.php';
include 'header.php';

if(!isset($_SESSION['user'])) header("Location: index.php");

// --- 1. SUPPRESSION ---
if(isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM professeurs WHERE id = ?")->execute([$_GET['delete']]);
    echo "<script>window.location.href='profs_licence.php';</script>";
}

// --- 2. AJOUT PROFESSEUR ---
$notice = '';
if(isset($_POST['add'])) {
    $nom = strtoupper($_POST['nom']);
    $prenoms = ucwords($_POST['prenoms']);
    $email = $_POST['email'];
    $c1 = $_POST['contact1'];
    $c2 = $_POST['contact2'];
    $modules = $_POST['modules'];
    
    // Classes Université (L1, L2, L3, M1, M2)
    $classes = isset($_POST['classes']) ? implode(', ', $_POST['classes']) : '';

    $sql = "INSERT INTO professeurs (nom, prenoms, email, contact1, contact2, modules_enseignes, classes_autorisees, categorie) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 'LICENCE')";
    
    if($pdo->prepare($sql)->execute([$nom, $prenoms, $email, $c1, $c2, $modules, $classes])) {
        $notice = '<div class="alert alert-success">✅ Professeur Université enregistré.</div>';
    } else {
        $notice = '<div class="alert alert-danger">Erreur technique.</div>';
    }
}

// --- 3. LISTE ---
$profs = $pdo->query("SELECT * FROM professeurs WHERE categorie = 'LICENCE' ORDER BY id DESC")->fetchAll();
?>

<div class="row g-4">
    <div class="col-12">
        <h2 class="fw-bold" style="color: #667eea;"><i class="fas fa-university"></i> Enseignants - Université</h2>
        <?= $notice ?>
    </div>

    <!-- FORMULAIRE (VIOLET) -->
    <div class="col-lg-5">
        <div class="card card-custom border-0 shadow-sm">
            <div class="card-header-custom" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                <i class="fas fa-user-plus"></i> Nouveau Professeur Univ
            </div>
            <div class="card-body">
                <form method="post">
                    <h6 class="text-muted fw-bold border-bottom pb-2">1. Identité</h6>
                    <div class="row mb-2">
                        <div class="col-6"><label class="small text-muted">NOM</label><input type="text" name="nom" class="form-control form-control-sm" required></div>
                        <div class="col-6"><label class="small text-muted">PRÉNOMS</label><input type="text" name="prenoms" class="form-control form-control-sm" required></div>
                    </div>

                    <h6 class="text-muted fw-bold border-bottom pb-2 mt-3">2. Coordonnées</h6>
                    <div class="mb-2"><label class="small text-muted">EMAIL</label><input type="email" name="email" class="form-control form-control-sm"></div>
                    <div class="row mb-2">
                        <div class="col-6"><label class="small text-muted">CONTACT 1</label><input type="text" name="contact1" class="form-control form-control-sm" required></div>
                        <div class="col-6"><label class="small text-muted">CONTACT 2</label><input type="text" name="contact2" class="form-control form-control-sm"></div>
                    </div>

                    <h6 class="text-muted fw-bold border-bottom pb-2 mt-3">3. Pédagogie</h6>
                    <div class="mb-2">
                        <label class="small text-muted fw-bold">MODULES ENSEIGNÉS</label>
                        <textarea name="modules" class="form-control form-control-sm" placeholder="Ex: Droit Civil, Marketing..." required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="small text-muted fw-bold d-block">CLASSES ATTRIBUÉES</label>
                        <div class="btn-group flex-wrap" role="group">
                            <input type="checkbox" class="btn-check" name="classes[]" value="L1" id="l1" autocomplete="off"><label class="btn btn-outline-primary btn-sm m-1" for="l1">L1</label>
                            <input type="checkbox" class="btn-check" name="classes[]" value="L2" id="l2" autocomplete="off"><label class="btn btn-outline-primary btn-sm m-1" for="l2">L2</label>
                            <input type="checkbox" class="btn-check" name="classes[]" value="L3" id="l3" autocomplete="off"><label class="btn btn-outline-primary btn-sm m-1" for="l3">L3</label>
                            <input type="checkbox" class="btn-check" name="classes[]" value="M1" id="m1" autocomplete="off"><label class="btn btn-outline-primary btn-sm m-1" for="m1">M1</label>
                            <input type="checkbox" class="btn-check" name="classes[]" value="M2" id="m2" autocomplete="off"><label class="btn btn-outline-primary btn-sm m-1" for="m2">M2</label>
                        </div>
                    </div>

                    <button type="submit" name="add" class="btn text-white w-100 fw-bold mt-2" style="background: #667eea;">Enregistrer</button>
                </form>
            </div>
        </div>
    </div>

    <!-- LISTE -->
    <div class="col-lg-7">
        <div class="card card-custom">
            <div class="card-header bg-white py-3"><h5 class="m-0 fw-bold text-secondary">Annuaire Professeurs Univ</h5></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light"><tr><th>Professeur</th><th>Contacts</th><th>Modules & Classes</th><th>Action</th></tr></thead>
                        <tbody>
                            <?php foreach($profs as $p): ?>
                            <tr>
                                <td>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($p['nom']) ?></div>
                                    <div class="small text-muted"><?= htmlspecialchars($p['prenoms']) ?></div>
                                </td>
                                <td>
                                    <div class="small"><i class="fas fa-phone-alt text-primary"></i> <?= htmlspecialchars($p['contact1']) ?></div>
                                    <?php if($p['contact2']): ?><div class="small"><i class="fas fa-phone text-muted"></i> <?= htmlspecialchars($p['contact2']) ?></div><?php endif; ?>
                                    <?php if($p['email']): ?><div class="small text-info"><i class="fas fa-envelope"></i> <?= htmlspecialchars($p['email']) ?></div><?php endif; ?>
                                </td>
                                <td>
                                    <div class="text-dark fw-bold small"><?= htmlspecialchars($p['modules_enseignes']) ?></div>
                                    <div class="mt-1">
                                        <?php 
                                            $cls = explode(',', $p['classes_autorisees']);
                                            foreach($cls as $c) echo "<span class='badge bg-primary text-white me-1'>".trim($c)."</span>";
                                        ?>
                                    </div>
                                </td>
                                <td class="text-end">
                                    <a href="?delete=<?= $p['id'] ?>" class="btn btn-sm btn-light text-danger" onclick="return confirm('Supprimer ?');"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>