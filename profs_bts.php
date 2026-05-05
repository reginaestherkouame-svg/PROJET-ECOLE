<?php
include 'db.php';
include 'header.php';

// Sécurité
if(!isset($_SESSION['user'])) header("Location: index.php");

// --- 1. SUPPRESSION ---
if(isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $pdo->prepare("DELETE FROM professeurs WHERE id = ?")->execute([$id]);
    echo "<script>window.location.href='profs_bts.php';</script>";
}

// --- 2. AJOUT ---
$notice = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $nom = trim($_POST['nom']);
    $coord = trim($_POST['coordonnees']);
    $filiere = trim($_POST['filiere']);
    $categorie = 'BTS'; // <--- C'EST ICI QUE ÇA CHANGE

    if ($nom && $filiere) {
        $stmt = $pdo->prepare('INSERT INTO professeurs (nom_complet, contact, filiere, categorie) VALUES (?,?,?,?)');
        if ($stmt->execute([$nom, $coord, $filiere, $categorie])) {
            $notice = '<div class="alert alert-success alert-dismissible fade show">Professeur BTS ajouté.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
        } else {
            $notice = '<div class="alert alert-danger">Erreur technique.</div>';
        }
    } else {
        $notice = '<div class="alert alert-warning">Champs obligatoires manquants.</div>';
    }
}

// --- 3. RECHERCHE & LISTE ---
$search = isset($_GET['q']) ? $_GET['q'] : '';
$sql = "SELECT * FROM professeurs WHERE categorie = 'BTS'"; // FILTRE SUR BTS

if($search) {
    $sql .= " AND (nom_complet LIKE :q OR filiere LIKE :q)";
    $stmt = $pdo->prepare($sql . " ORDER BY id DESC");
    $stmt->execute(['q' => "%$search%"]);
} else {
    $stmt = $pdo->query($sql . " ORDER BY id DESC");
}
$profs = $stmt->fetchAll();
?>

<div class="row g-4">
    <div class="col-12">
        <h2 class="fw-bold" style="color: #11998e;"><i class="fas fa-laptop-code"></i> Gestion Professeurs (Niveau BTS)</h2>
        <?= $notice ?>
    </div>

    <!-- FORMULAIRE AJOUT (VERT) -->
    <div class="col-md-4">
        <div class="card card-custom h-100">
            <div class="card-header-custom" style="background: linear-gradient(135deg, #11998e, #38ef7d);">
                <i class="fas fa-plus-circle"></i> Ajouter Prof (BTS)
            </div>
            <div class="card-body">
                <form method="post">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">NOM COMPLET</label>
                        <input type="text" name="nom" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">CONTACT</label>
                        <input type="text" name="coordonnees" class="form-control">
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold small text-muted">FILIÈRE</label>
                        <input type="text" name="filiere" class="form-control" required>
                    </div>
                    <button class="btn text-white w-100 fw-bold" type="submit" style="background: #11998e;">
                        Enregistrer
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- LISTE -->
    <div class="col-md-8">
        <div class="card card-custom">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <h5 class="mb-0 fw-bold text-secondary">Liste des enseignants BTS</h5>
                <form method="get" class="d-flex">
                    <input type="text" name="q" class="form-control form-control-sm me-2" placeholder="Chercher..." value="<?= htmlspecialchars($search) ?>">
                    <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-search"></i></button>
                    <?php if($search): ?><a href="profs_bts.php" class="btn btn-sm btn-light ms-1">X</a><?php endif; ?>
                </form>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr><th>Nom</th><th>Contact</th><th>Filière</th><th class="text-end">Action</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach($profs as $row): ?>
                            <tr>
                                <td class="fw-bold text-dark">
                                    <i class="fas fa-user-circle text-success me-2"></i><?= htmlspecialchars($row['nom_complet']) ?>
                                </td>
                                <td><?= htmlspecialchars($row['contact']) ?></td>
                                <td><span class="badge bg-success"><?= htmlspecialchars($row['filiere']) ?></span></td>
                                <td class="text-end">
                                    <a href="profs_bts.php?delete=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Supprimer ?');"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php if(empty($profs)) echo "<div class='p-4 text-center text-muted'>Aucun professeur BTS trouvé.</div>"; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>