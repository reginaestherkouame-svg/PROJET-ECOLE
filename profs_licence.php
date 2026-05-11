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
        
        // Vérifier existence
        $check = $pdo->prepare("SELECT id FROM professeurs WHERE id = ? AND categorie = 'LICENCE'");
        $check->execute([$id]);
        
        if($check->rowCount() > 0) {
            $stmt = $pdo->prepare("DELETE FROM professeurs WHERE id = ? AND categorie = 'LICENCE'");
            if($stmt->execute([$id])) {
                $notice = '<div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle"></i> Professeur supprimé avec succès.
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

// --- 2. AJOUT PROFESSEUR LICENCE ---
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
            // Utiliser les colonnes réelles: nom_complet, contact, filiere, categorie
            $sql = "INSERT INTO professeurs (nom_complet, contact, filiere, categorie) 
                    VALUES (?, ?, ?, 'LICENCE')";
            
            $stmt = $pdo->prepare($sql);
            if($stmt->execute([$nom, $contact, $filiere])) {
                $notice = '<div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle"></i> Professeur Licence enregistré avec succès! 🎉
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

// --- 3. LISTE PROFESSEURS LICENCE ---
try {
    $sql = "SELECT * FROM professeurs WHERE categorie = 'LICENCE' ORDER BY nom_complet ASC";
    $profs = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
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
        <h2 class="fw-bold" style="color: #667eea;">
            <i class="fas fa-university"></i> Enseignants - Université (Licence)
        </h2>
        <?= $notice ?>
    </div>

    <!-- FORMULAIRE (VIOLET) -->
    <div class="col-lg-5">
        <div class="card card-custom border-0 shadow-sm h-100">
            <div class="card-header-custom" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                <i class="fas fa-user-plus"></i> Nouveau Professeur Licence
            </div>
            <div class="card-body">
                <form method="post">
                    <h6 class="text-muted fw-bold border-bottom pb-2">
                        <i class="fas fa-info-circle me-2"></i> Informations
                    </h6>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">NOM COMPLET <span class="text-danger">*</span></label>
                        <input type="text" name="nom" class="form-control form-control" placeholder="Ex: Dr. Pierre Martin" required minlength="3">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">CONTACT</label>
                        <input type="text" name="contact" class="form-control form-control" placeholder="Tél/Email">
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold small text-muted">FILIÈRE / SPÉCIALITÉ</label>
                        <input type="text" name="filiere" class="form-control form-control" placeholder="Ex: Droit Civil, Économie...">
                    </div>

                    <button type="submit" name="add" class="btn text-white w-100 fw-bold" style="background: #667eea;">
                        <i class="fas fa-save me-2"></i> Enregistrer
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- LISTE -->
    <div class="col-lg-7">
        <div class="card card-custom h-100">
            <div class="card-header bg-white py-3">
                <h5 class="m-0 fw-bold text-secondary">
                    <i class="fas fa-list me-2"></i> Annuaire Professeurs Licence (<?= count($profs) ?> profs)
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Professeur</th>
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
                                        Aucun professeur Licence enregistré.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($profs as $p): ?>
                                <tr class="table-row-hover">
                                    <td>
                                        <div class="fw-bold text-dark">
                                            <i class="fas fa-user-tie text-primary me-2"></i><?= htmlspecialchars($p['nom_complet'] ?? '') ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if(!empty($p['contact'])): ?>
                                            <small><?= htmlspecialchars($p['contact']) ?></small>
                                        <?php else: ?>
                                            <small class="text-muted">-</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if(!empty($p['filiere'])): ?>
                                            <span class="badge bg-primary">
                                                <?= htmlspecialchars($p['filiere']) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <a href="?delete=<?= (int)($p['id'] ?? 0) ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce professeur ?');">
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
        background-color: #f0f4ff;
    }
</style>

<?php include 'footer.php'; ?>
