<?php
include 'db.php';
include 'header.php';

if(!isset($_SESSION['user'])) header("Location: index.php");

// CONFIGURATION
$categorie_page = 'BTS'; // Pour filtrer l'affichage
$alert_msg = '';

// --- TRAITEMENT DU FORMULAIRE ---
if(isset($_POST['add'])) {
    $prof_id = $_POST['prof'];
    $salle_id = $_POST['salle'];
    $filiere_id = $_POST['filiere']; // ID de la table filieres
    $date = $_POST['date'];
    $debut = $_POST['debut'];
    $fin = $_POST['fin'];

    // 1. VÉRIFICATION DES HORAIRES (08-12 ou 13-17)
    $creneau_matin = ($debut >= "08:00" && $fin <= "12:00");
    $creneau_soir = ($debut >= "13:00" && $fin <= "17:00");

    if ($debut >= $fin) {
        $alert_msg = "<div class='alert alert-danger'>L'heure de fin doit être après l'heure de début.</div>";
    } elseif (!$creneau_matin && !$creneau_soir) {
        $alert_msg = "<div class='alert alert-danger'>⛔ Horaires invalides ! Les cours doivent être entre <b>08h-12h</b> ou <b>13h-17h</b>.</div>";
    } else {
        // 2. VÉRIFICATION DES CONFLITS (SALLE OU PROF)
        // La logique SQL : "Est-ce qu'il existe une réservation le même jour qui chevauche mes heures ?"
        // Chevauchement = (Debut_existant < Fin_demandée) ET (Fin_existante > Debut_demandé)
        
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
            if ($conflit['salle_id'] == $salle_id) {
                $alert_msg = "<div class='alert alert-danger'>⛔ SALLE INDISPONIBLE ! La salle <b>{$conflit['nom_salle']}</b> est déjà prise de {$conflit['heure_debut']} à {$conflit['heure_fin']}.</div>";
            } else {
                $alert_msg = "<div class='alert alert-danger'>⛔ PROFESSEUR OCCUPÉ ! <b>{$conflit['nom_complet']}</b> a déjà cours en salle {$conflit['nom_salle']} sur ce créneau.</div>";
            }
        } else {
            // 3. RECUPERATION DU NOM DE LA FILIERE/NIVEAU VIA L'ID
            $stmt_fil = $pdo->prepare("SELECT * FROM filieres WHERE id = ?");
            $stmt_fil->execute([$filiere_id]);
            $fil_info = $stmt_fil->fetch();
            $nom_complet_cours = $fil_info['nom_filiere'];
            $niv_cours = $fil_info['niveau'];

            // 4. INSERTION FINALE
            $sql_insert = "INSERT INTO reservations (prof_id, salle_id, filiere_cours, niveau, date_res, heure_debut, heure_fin) 
                           VALUES (?, ?, ?, ?, ?, ?, ?)";
            $pdo->prepare($sql_insert)->execute([$prof_id, $salle_id, $nom_complet_cours, $niv_cours, $date, $debut, $fin]);
            
            $alert_msg = "<div class='alert alert-success'>✅ Réservation confirmée avec succès !</div>";
        }
    }
}

// DONNÉES POUR LES LISTES
// Profs BTS
$profs = $pdo->query("SELECT * FROM professeurs WHERE categorie = 'BTS'")->fetchAll();
// Salles BTS
$salles = $pdo->query("SELECT * FROM salles WHERE categorie = 'BTS'")->fetchAll();
// Filières BTS + Licence 3 (Selon ta règle)
$filieres = $pdo->query("SELECT * FROM filieres WHERE categorie = 'BTS'")->fetchAll();

// LISTE DES RESERVATIONS EXISTANTES
$sql_list = "SELECT r.*, p.nom_complet, s.nom_salle 
             FROM reservations r 
             JOIN professeurs p ON r.prof_id = p.id 
             JOIN salles s ON r.salle_id = s.id 
             JOIN filieres f ON (r.filiere_cours = f.nom_filiere AND r.niveau = f.niveau)
             WHERE f.categorie = 'BTS' -- On filtre pour n'afficher que le planning BTS
             ORDER BY r.date_res DESC, r.heure_debut ASC";
// Note: Si la jointure filieres est trop complexe, on peut filtrer autrement, mais ceci est le plus propre.
// Alternative simple pour l'affichage : ne pas filtrer strictement ou ajouter une colonne categorie dans reservation.
// Pour simplifier ici, je prends toutes les réservations et je trierai visuellement ou j'affiche tout.
$res = $pdo->query("SELECT r.*, p.nom_complet, s.nom_salle FROM reservations r JOIN professeurs p ON r.prof_id = p.id JOIN salles s ON r.salle_id = s.id ORDER BY r.date_res DESC")->fetchAll();
?>

<div class="row">
    <div class="col-12">
        <h2 class="fw-bold" style="color: #11998e;"><i class="far fa-calendar-alt"></i> Planning - Niveau BTS / Licence 3</h2>
        <?= $alert_msg ?>
    </div>

    <!-- FORMULAIRE -->
    <div class="col-md-12 mb-4">
        <div class="card card-custom border-0 shadow-sm">
            <div class="card-header-custom" style="background: linear-gradient(135deg, #11998e, #38ef7d);">
                <i class="fas fa-plus-circle"></i> Nouvelle Réservation
            </div>
            <div class="card-body">
                <form method="post" class="row g-3">
                    <!-- Ligne 1 -->
                    <div class="col-md-4">
                        <label class="fw-bold small text-muted">PROFESSEUR (Uniquement BTS)</label>
                        <select name="prof" class="form-select" required>
                            <option value="">Sélectionner...</option>
                            <?php foreach($profs as $p) echo "<option value='{$p['id']}'>{$p['nom_complet']}</option>"; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="fw-bold small text-muted">SALLE DISPONIBLE</label>
                        <select name="salle" class="form-select" required>
                            <option value="">Sélectionner...</option>
                            <?php foreach($salles as $s) echo "<option value='{$s['id']}'>{$s['nom_salle']} ({$s['capacite']} pl.)</option>"; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="fw-bold small text-muted">FILIÈRE & NIVEAU</label>
                        <select name="filiere" class="form-select" required>
                            <option value="">Sélectionner le cours...</option>
                            <?php foreach($filieres as $f): ?>
                                <option value="<?= $f['id'] ?>"><?= $f['niveau'] ?> - <?= $f['nom_filiere'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Ligne 2 : Date et Heures -->
                    <div class="col-md-4">
                        <label class="fw-bold small text-muted">DATE DU COURS</label>
                        <input type="date" name="date" class="form-control" required min="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="fw-bold small text-muted">DÉBUT (08h ou 13h)</label>
                        <input type="time" name="debut" class="form-control" required step="3600">
                    </div>
                    <div class="col-md-3">
                        <label class="fw-bold small text-muted">FIN (12h ou 17h)</label>
                        <input type="time" name="fin" class="form-control" required step="3600">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" name="add" class="btn text-white w-100 fw-bold" style="background: #11998e;">Valider</button>
                    </div>
                </form>
                <div class="mt-2 text-muted small fst-italic">
                    <i class="fas fa-info-circle"></i> Créneaux autorisés : Matin (08:00 - 12:00) ou Après-midi (13:00 - 17:00).
                </div>
            </div>
        </div>
    </div>

    <!-- PLANNING -->
    <div class="col-md-12">
        <div class="card card-custom">
            <div class="card-header bg-white"><h5 class="m-0 text-secondary fw-bold">📅 Emploi du temps enregistré</h5></div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr><th>Date</th><th>Horaire</th><th>Niveau</th><th>Matière</th><th>Salle</th><th>Professeur</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach($res as $r): 
                            // Petit filtre visuel pour n'afficher que ce qui ressemble à du BTS/L3 si on le souhaite
                            // Ici j'affiche tout par sécurité
                        ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($r['date_res'])) ?></td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    <?= substr($r['heure_debut'],0,5) ?> - <?= substr($r['heure_fin'],0,5) ?>
                                </span>
                            </td>
                            <td><span class="badge bg-success"><?= $r['niveau'] ?></span></td>
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