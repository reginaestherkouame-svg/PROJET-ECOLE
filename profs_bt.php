<?php
include 'db.php';
include 'header.php';

if(!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

$notice = '';

// --- 1. SUPPRESSION SÉCURISÉE ---
if(isset($_GET['delete'])) {
    try {
        $id = (int)$_GET['delete'];
        
        // Vérifier existence avec catégorie BT
        $check = $pdo->prepare("SELECT id FROM professeurs WHERE id = ? AND categorie = 'BT'");
        $check->execute([$id]);
        
        if($check->rowCount() > 0) {
            $stmt = $pdo->prepare("DELETE FROM professeurs WHERE id = ? AND categorie = 'BT'");
            if($stmt->execute([$id])) {
                $notice = '<div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle"></i> Professeur BT supprimé avec succès.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>';
            } else {
                $notice = '<div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-circle"></i> Erreur lors de la suppression.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>';
            }
        } else {
            $notice = '<div class="alert alert-warning alert-dismissible fade show">
                <i class="fas fa-info-circle"></i> Professeur non trouvé.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>';
        }
    } catch (Exception $e) {
        $notice = '<div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle"></i> Erreur: ' . htmlspecialchars($e->getMessage()) . '
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>';
    }
}

// --- 2. AJOUT PROFESSEUR BT ---
if(isset($_POST['add'])) {
    try {
        $nom = trim(htmlspecialchars($_POST['nom'] ?? ''));
        $contact = trim(htmlspecialchars($_POST['contact'] ?? ''));
        $filiere = trim(htmlspecialchars($_POST['filiere'] ?? ''));
        
        // Validation
        if (empty($nom)) {
            $notice = '<div class="alert alert-warning alert-dismissible fade show">
                <i class="fas fa-exclamation-triangle"></i> Le nom est obligatoire.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>';
        } else if (strlen($nom) < 3) {
            $notice = '<div class="alert alert-warning alert-dismissible fade show">
                <i class="fas fa-exclamation-triangle"></i> Le nom doit avoir au moins 3 caractères.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>';
        } else {
            $sql = "INSERT INTO professeurs (nom_complet, contact, filiere, categorie) 
                    VALUES (?, ?, ?, 'BT')";
            
            $stmt = $pdo->prepare($sql);
            if($stmt->execute([$nom, $contact, $filiere])) {
                $notice = '<div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle"></i> Professeur BT enregistré avec succès! 🎉
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>';
            } else {
                $notice = '<div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-circle"></i> Erreur lors de l\'enregistrement.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>';
            }
        }
    } catch (Exception $e) {
        $notice = '<div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle"></i> Erreur: ' . htmlspecialchars($e->getMessage()) . '
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>';
    }
}

// --- 3. RECHERCHE & LISTE PROFESSEURS BT ---
try {
    $search = isset($_GET['q']) ? trim(htmlspecialchars($_GET['q'])) : '';
    
    if($search) {
        $sql = "SELECT * FROM professeurs WHERE categorie = 'BT' 
                AND (nom_complet LIKE ? OR filiere LIKE ? OR contact LIKE ?) 
                ORDER BY nom_complet ASC";
        $stmt = $pdo->prepare($sql);
        $searchTerm = "%$search%";
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
    } else {
        $sql = "SELECT * FROM professeurs WHERE categorie = 'BT' ORDER BY nom_complet ASC";
        $stmt = $pdo->query($sql);
    }
    $profs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $profs = [];
    $notice .= '<div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-circle"></i> Erreur de récupération: ' . htmlspecialchars($e->getMessage()) . '
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>';
}
?>

<div class="row g-4">
    <div class="col-12">
        <h2 class="fw-bold" style="color: #e65100;">
            <i class="fas fa-tools"></i> Gestion Professeurs (Niveau BT)
        </h2>
        <?= $notice ?>
    </div>

    <!-- FORMULAIRE AJOUT (ORANGE) -->
    <div class="col-md-4">
        <div class="card card-custom h-100">
            <div class="card-header-custom" style="background: linear-gradient(135deg, #ff9966, #ff5e62);">
                <i class="fas fa-plus-circle"></i> Ajouter Prof (BT)
            </div>
            <div class="card-body">
                <form method="post">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">NOM COMPLET <span class="text-danger">*</span></label>
                        <input type="text" name="nom" class="form-control" placeholder="Ex: Jean Dupont" required minlength="3">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">CONTACT</label>
                        <input type="text" name="contact" class="form-control" placeholder="Tél/Email">
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold small text-muted">FILIÈRE</label>
                        <input type="text" name="filiere" class="form-control" placeholder="Ex: Mécanique...">
                    </div>
                    <button class="btn text-white w-100 fw-bold" type="submit" name="add" style="background: #ff5e62;">
                        <i class="fas fa-save me-2"></i> Enregistrer
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- LISTE -->
    <div class="col-md-8">
        <div class="card card-custom">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <h5 class="mb-0 fw-bold text-secondary">
                    <i class="fas fa-list me-2"></i> Liste des enseignants BT (<?= count($profs) ?> profs)
                </h5>
                <form method="get" class="d-flex gap-2">
                    <input type="text" name="q" class="form-control form-control-sm" placeholder="Chercher..." 
                           value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" style="max-width: 200px;">
                    <button type="submit" class="btn btn-sm btn-warning text-white">
                        <i class="fas fa-search"></i>
                    </button>
                    <?php if(isset($_GET['q'])): ?>
                        <a href="profs_bt.php" class="btn btn-sm btn-light">
                            <i class="fas fa-times"></i> Réinitialiser
                        </a>
                    <?php endif; ?>
                </form>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Nom</th>
                                <th>Contact</th>
                                <th>Filière</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($profs)): ?>
                                <tr>
                                    <td colspan="4" class="text-center p-4 text-muted">
                                        <i class="fas fa-inbox fs-3"></i><br>
                                        Aucun professeur BT trouvé.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($profs as $row): ?>
                                <tr class="table-row-hover">
                                    <td class="fw-bold text-dark">
                                        <i class="fas fa-user-circle text-warning me-2"></i><?= htmlspecialchars($row['nom_complet'] ?? '') ?>
                                    </td>
                                    <td>
                                        <?php if(!empty($row['contact'])): ?>
                                            <small><?= htmlspecialchars($row['contact']) ?></small>
                                        <?php else: ?>
                                            <small class="text-muted">-</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if(!empty($row['filiere'])): ?>
                                            <span class="badge bg-warning text-dark">
                                                <?= htmlspecialchars($row['filiere']) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <a href="profs_bt.php?delete=<?= (int)($row['id'] ?? 0) ?>" class="btn btn-sm btn-outline-danger" 
                                           onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce professeur ?');">
                                            <i class="fas fa-trash-alt"></i> Supprimer
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .table-row-hover:hover {
        background-color: #fff8e6;
    }
</style>

<?php include 'footer.php'; ?>
