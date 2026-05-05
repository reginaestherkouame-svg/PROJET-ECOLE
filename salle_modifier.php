<?php
// --- PAGE : MODIFIER UNE SALLE ---

// 1. Connexion BDD
try {
    $bdd = new PDO("mysql:host=localhost;dbname=univ;charset=utf8", "root", "");
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) { die("Erreur : " . $e->getMessage()); }

// 2. Vérifier si on a un ID dans l'URL
if(!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: liste_salles.php"); // Si pas d'ID, on retourne à la liste
    exit();
}

$id = intval($_GET['id']);

// 3. Traitement de la modification (Si on clique sur "Mettre à jour")
if(isset($_POST['btn_update'])) {
    if(!empty($_POST['nom']) && !empty($_POST['code']) && !empty($_POST['cap']) && !empty($_POST['niv'])) {
        
        $sql = "UPDATE salle SET nom_salle = ?, code_salle = ?, capacite = ?, niveau = ? WHERE id = ?";
        $stmt = $bdd->prepare($sql);
        $stmt->execute([
            $_POST['nom'], 
            $_POST['code'], 
            $_POST['cap'], 
            $_POST['niv'], 
            $id
        ]);

        // Redirection vers l'inventaire après succès
        header("Location: liste_salles.php");
        exit();
    }
}

// 4. Récupérer les infos actuelles de la salle pour pré-remplir le formulaire
$req = $bdd->prepare("SELECT * FROM salle WHERE id = ?");
$req->execute([$id]);
$salle = $req->fetch();

// Si l'ID n'existe pas dans la base
if(!$salle) {
    die("Salle introuvable.");
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier Salle</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', sans-serif; }
        .card-form { 
            background: white; 
            border: 1px solid #dee2e6; 
            border-radius: 8px; 
            max-width: 500px; 
            margin: 50px auto;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        .form-header { background-color: #ffc107; color: #333; padding: 15px; font-weight: bold; text-align: center; }
        .label-custom { font-size: 0.75rem; font-weight: 700; color: #6c757d; text-transform: uppercase; margin-bottom: 6px; margin-top: 15px; display: block; }
        .btn-warning-custom { background-color: #ffc107; color: #333; width: 100%; border: none; padding: 12px; font-weight: bold; margin-top: 20px; border-radius: 4px; }
        .btn-warning-custom:hover { background-color: #e0a800; }
        .btn-back { display: block; text-align: center; margin-top: 15px; text-decoration: none; color: #6c757d; font-size: 0.9rem; }
    </style>
</head>
<body>

<div class="container">
    <div class="card-form">
        <div class="form-header"><i class="fa-solid fa-pen-to-square"></i> Modifier la salle</div>
        <div class="p-4">
            <form method="POST">
                
                <label class="label-custom">NIVEAU</label>
                <select name="niv" class="form-select">
                    <option value="BT" <?php if($salle['niveau'] == 'BT') echo 'selected'; ?>>BT</option>
                    <option value="BTS" <?php if($salle['niveau'] == 'BTS') echo 'selected'; ?>>BTS</option>
                    <option value="LICENCE" <?php if($salle['niveau'] == 'LICENCE') echo 'selected'; ?>>LICENCE</option>
                </select>

                <label class="label-custom">NOM DE LA SALLE</label>
                <!-- On met value="..." pour afficher l'ancienne valeur -->
                <input type="text" name="nom" class="form-control" value="<?php echo htmlspecialchars($salle['nom_salle']); ?>" required>

                <label class="label-custom">CODE / IDENTIFIANT</label>
                <input type="text" name="code" class="form-control" value="<?php echo htmlspecialchars($salle['code_salle']); ?>" required>

                <label class="label-custom">CAPACITÉ</label>
                <input type="number" name="cap" class="form-control" value="<?php echo htmlspecialchars($salle['capacite']); ?>" required>

                <button type="submit" name="btn_update" class="btn-warning-custom">Mettre à jour</button>
                
                <a href="liste_salles.php" class="btn-back"><i class="fa-solid fa-arrow-left"></i> Annuler et retour</a>
            </form>
        </div>
    </div>
</div>

</body>
</html>