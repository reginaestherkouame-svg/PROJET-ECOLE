<?php
// 1. Inclure db.php (connexion BDD + démarrage session)
include 'db.php';

// 2. Vérification de la connexion obligatoire
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

$username = htmlspecialchars($_SESSION['user']);

// 3. Compteurs pour le tableau de bord - REQUÊTES CORRIGÉES
try {
    // Correction: Les noms de tables doivent être cohérents (salle vs salles, professeurs vs profs)
    // Utiliser un try-catch pour éviter les erreurs si les tables n'existent pas
    
    $nbSalles = 0;
    $nbProfs = 0;
    $nbReservations = 0;
    $nbP1 = 0;
    $nbP2 = 0;
    $nbP3 = 0;
    
    // Essayer plusieurs noms de table possibles pour les salles
    try {
        $nbSalles = $pdo->query("SELECT COUNT(*) FROM salles")->fetchColumn();
        $nbP1 = $pdo->query("SELECT COUNT(*) FROM salles WHERE niveau='LICENCE'")->fetchColumn();
        $nbP2 = $pdo->query("SELECT COUNT(*) FROM salles WHERE niveau='BTS'")->fetchColumn();
        $nbP3 = $pdo->query("SELECT COUNT(*) FROM salles WHERE niveau='BT'")->fetchColumn();
    } catch (Exception $e1) {
        try {
            $nbSalles = $pdo->query("SELECT COUNT(*) FROM salle")->fetchColumn();
            $nbP1 = $pdo->query("SELECT COUNT(*) FROM salle WHERE niveau='LICENCE'")->fetchColumn();
            $nbP2 = $pdo->query("SELECT COUNT(*) FROM salle WHERE niveau='BTS'")->fetchColumn();
            $nbP3 = $pdo->query("SELECT COUNT(*) FROM salle WHERE niveau='BT'")->fetchColumn();
        } catch (Exception $e2) {
            // Table n'existe pas
        }
    }
    
    // Essayer pour les professeurs
    try {
        $nbProfs = $pdo->query("SELECT COUNT(*) FROM professeurs")->fetchColumn();
    } catch (Exception $e) {
        try {
            $nbProfs = $pdo->query("SELECT COUNT(*) FROM profs")->fetchColumn();
        } catch (Exception $e2) {
            // Table n'existe pas
        }
    }
    
    // Essayer pour les réservations
    try {
        $nbReservations = $pdo->query("SELECT COUNT(*) FROM reservations")->fetchColumn();
    } catch (Exception $e) {
        // Table n'existe pas
    }
    
    // Récupérer aussi les dernières réservations pour améliorer le tableau de bord
    $recentReservations = [];
    try {
        $recentReservations = $pdo->query(
            "SELECT * FROM reservations ORDER BY date_res DESC LIMIT 5"
        )->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        // Table n'existe pas
    }
    
} catch (Exception $e) {
    error_log("Erreur dashboard: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord — Univ Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --p1: #4e73df;
            --p2: #1cc88a;
            --p3: #f6c23e;
            --danger: #e74a3b;
            --sidebar-w: 240px;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Poppins', sans-serif; background: #f0f2f8; color: #333; display: flex; min-height: 100vh; }

        /* ===== SIDEBAR AMÉLIORÉ ===== */
        .sidebar {
            width: var(--sidebar-w);
            background: linear-gradient(180deg, #1a237e 0%, #283593 100%);
            color: white;
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0; left: 0; bottom: 0;
            z-index: 100;
            padding-bottom: 1rem;
            overflow-y: auto;
        }
        
        .sidebar-brand {
            padding: 1.5rem 1.2rem 1rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-brand .logo-icon {
            width: 42px; height: 42px;
            background: rgba(255,255,255,0.15);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem; margin-bottom: 0.6rem;
            animation: fadeIn 0.3s ease-in;
        }
        
        .sidebar-brand h6 { font-size: 0.78rem; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; color: rgba(255,255,255,0.9); }
        .sidebar-brand small { font-size: 0.68rem; color: rgba(255,255,255,0.5); }

        .sidebar-nav { flex: 1; padding: 1rem 0; overflow-y: auto; }
        .nav-section { padding: 0.8rem 1.2rem 0.3rem; font-size: 0.65rem; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; color: rgba(255,255,255,0.4); }
        
        .sidebar-link {
            display: flex; align-items: center; gap: 10px;
            padding: 0.6rem 1.2rem;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            font-size: 0.85rem; font-weight: 500;
            border-left: 3px solid transparent;
            transition: all 0.2s;
        }
        
        .sidebar-link:hover, .sidebar-link.active {
            color: white; 
            background: rgba(255,255,255,0.08);
            border-left-color: #90caf9;
        }
        
        .sidebar-link i { width: 18px; text-align: center; font-size: 0.9rem; }

        .sidebar-submenu { margin-left: 1.8rem; }
        .sidebar-submenu .sidebar-link { font-size: 0.8rem; padding: 0.4rem 0.8rem; }

        .sidebar-footer {
            padding: 1rem 1.2rem;
            border-top: 1px solid rgba(255,255,255,0.1);
            margin-top: auto;
        }
        
        .user-info { display: flex; align-items: center; gap: 10px; margin-bottom: 0.8rem; }
        .user-avatar {
            width: 34px; height: 34px; border-radius: 50%;
            background: linear-gradient(135deg, #90caf9, #42a5f5);
            display: flex; align-items: center; justify-content: center;
            font-size: 0.85rem; font-weight: 700; color: white;
        }
        
        .user-name { font-size: 0.82rem; font-weight: 600; color: white; }
        .user-role { font-size: 0.7rem; color: rgba(255,255,255,0.5); }
        
        .btn-logout-side {
            display: flex; align-items: center; justify-content: center; gap: 8px;
            width: 100%; padding: 0.5rem;
            background: rgba(231, 74, 59, 0.2);
            color: #ef9a9a; border: 1px solid rgba(231,74,59,0.3);
            border-radius: 8px; font-size: 0.82rem; font-weight: 600;
            text-decoration: none; transition: all 0.2s;
        }
        
        .btn-logout-side:hover { 
            background: rgba(231,74,59,0.4); 
            color: white;
        }

        /* ===== MAIN CONTENT ===== */
        .main {
            margin-left: var(--sidebar-w);
            flex: 1; 
            padding: 2rem;
            min-height: 100vh;
            overflow-y: auto;
        }

        /* Top bar */
        .topbar {
            display: flex; 
            align-items: center; 
            justify-content: space-between;
            background: white; 
            border-radius: 14px;
            padding: 1rem 1.5rem; 
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .topbar h4 { font-size: 1.1rem; font-weight: 700; color: #1a237e; margin: 0; }
        .topbar small { color: #888; font-size: 0.78rem; }
        .topbar-right { display: flex; align-items: center; gap: 10px; }
        .badge-date { background: #f0f2f8; color: #555; padding: 0.4rem 0.9rem; border-radius: 20px; font-size: 0.78rem; font-weight: 600; }

        /* Stat Cards - AMÉLIORÉ */
        .stat-card {
            background: white; 
            border-radius: 16px; 
            padding: 1.5rem;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            display: flex; 
            align-items: center; 
            gap: 1rem;
            text-decoration: none; 
            color: inherit;
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
            position: relative; 
            overflow: hidden;
        }
        
        .stat-card:hover { 
            transform: translateY(-6px); 
            box-shadow: 0 12px 28px rgba(0,0,0,0.15);
        }
        
        .stat-card.blue { border-left-color: var(--p1); }
        .stat-card.green { border-left-color: var(--p2); }
        .stat-card.orange { border-left-color: var(--p3); }

        .stat-icon {
            width: 52px; 
            height: 52px; 
            border-radius: 12px;
            display: flex; 
            align-items: center; 
            justify-content: center;
            font-size: 1.3rem; 
            flex-shrink: 0;
        }
        
        .stat-icon.blue { background: #e8edff; color: var(--p1); }
        .stat-icon.green { background: #e6f9f2; color: var(--p2); }
        .stat-icon.orange { background: #fff8e6; color: #f0a500; }

        .stat-num { font-size: 2rem; font-weight: 800; line-height: 1; }
        .stat-label { font-size: 0.82rem; color: #888; margin-top: 2px; }
        .stat-action { font-size: 0.72rem; color: #aaa; margin-top: 4px; }

        /* Palier Cards */
        .palier-card {
            background: white; 
            border-radius: 14px; 
            padding: 1.3rem 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
            display: flex; 
            align-items: center; 
            justify-content: space-between;
            text-decoration: none; 
            color: inherit;
            transition: all 0.3s ease;
            border-top: 4px solid transparent;
        }
        
        .palier-card:hover { 
            transform: translateY(-4px); 
            box-shadow: 0 8px 24px rgba(0,0,0,0.1);
        }
        
        .palier-card.p1 { border-top-color: var(--p1); }
        .palier-card.p2 { border-top-color: var(--p2); }
        .palier-card.p3 { border-top-color: var(--p3); }

        .palier-badge {
            font-size: 0.7rem; 
            font-weight: 700; 
            letter-spacing: 1px;
            text-transform: uppercase; 
            padding: 0.25rem 0.7rem;
            border-radius: 20px; 
            margin-bottom: 0.4rem; 
            display: inline-block;
        }
        
        .palier-badge.p1 { background: #e8edff; color: var(--p1); }
        .palier-badge.p2 { background: #e6f9f2; color: var(--p2); }
        .palier-badge.p3 { background: #fff8e6; color: #f0a500; }

        .palier-name { font-size: 0.95rem; font-weight: 700; color: #333; }
        .palier-sub { font-size: 0.75rem; color: #999; }
        .palier-count { font-size: 2rem; font-weight: 800; }
        .palier-count.p1 { color: var(--p1); }
        .palier-count.p2 { color: var(--p2); }
        .palier-count.p3 { color: #f0a500; }

        /* Section titles */
        .section-title { 
            font-size: 0.8rem; 
            font-weight: 700; 
            text-transform: uppercase; 
            letter-spacing: 1.5px; 
            color: #999; 
            margin-bottom: 1rem;
            margin-top: 1.5rem;
        }

        /* Recent Activity */
        .recent-activity {
            background: white;
            border-radius: 14px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
            margin-top: 2rem;
        }

        .activity-item {
            padding: 0.8rem 0;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-text {
            font-size: 0.85rem;
            color: #555;
        }

        .activity-date {
            font-size: 0.75rem;
            color: #aaa;
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .stat-card, .palier-card {
            animation: fadeIn 0.4s ease-out;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
                --sidebar-w: 200px;
            }

            .main {
                padding: 1rem;
            }

            .palier-card {
                flex-direction: column;
                text-align: center;
            }

            .topbar {
                flex-direction: column;
                align-items: flex-start;
            }
        }

        footer { 
            text-align: center; 
            margin-top: 3rem; 
            color: #bbb; 
            font-size: 0.8rem;
            padding-bottom: 1rem;
        }
    </style>
</head>
<body>

<!-- ===== SIDEBAR ===== -->
<aside class="sidebar">
    <div class="sidebar-brand">
        <div class="logo-icon"><i class="fas fa-university"></i></div>
        <h6>Univ Manager</h6>
        <small>Gestion des salles</small>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section">Navigation</div>
        <a href="home.php" class="sidebar-link active">
            <i class="fas fa-gauge-high"></i> Tableau de bord
        </a>
        <a href="profs.php" class="sidebar-link">
            <i class="fas fa-chalkboard-teacher"></i> Professeurs
        </a>
        <a href="reservations.php" class="sidebar-link">
            <i class="far fa-calendar-alt"></i> Réservations
        </a>

        <div class="nav-section" style="margin-top:0.5rem;">Salles par Palier</div>
        <div class="sidebar-submenu">
            <a href="salle_licence.php" class="sidebar-link">
                <i class="fas fa-landmark"></i> Palier 1 · Licence
            </a>
            <a href="salle_bts.php" class="sidebar-link">
                <i class="fas fa-graduation-cap"></i> Palier 2 · BTS
            </a>
            <a href="salle_bt.php" class="sidebar-link">
                <i class="fas fa-school"></i> Palier 3 · BT
            </a>
        </div>

        <div class="nav-section" style="margin-top:0.5rem;">Administration</div>
        <a href="salles.php" class="sidebar-link">
            <i class="fas fa-door-open"></i> Liste des salles
        </a>
        <a href="register.php" class="sidebar-link">
            <i class="fas fa-user-plus"></i> Nouvel utilisateur
        </a>
    </nav>

    <div class="sidebar-footer">
        <div class="user-info">
            <div class="user-avatar"><?= strtoupper(substr($username, 0, 1)) ?></div>
            <div>
                <div class="user-name"><?= $username ?></div>
                <div class="user-role">Administrateur</div>
            </div>
        </div>
        <a href="logout.php" class="btn-logout-side">
            <i class="fas fa-sign-out-alt"></i> Déconnexion
        </a>
    </div>
</aside>

<!-- ===== MAIN CONTENT ===== -->
<main class="main">

    <!-- Top Bar -->
    <div class="topbar">
        <div>
            <h4><i class="fas fa-gauge-high me-2"></i>Tableau de bord</h4>
            <small>Bienvenue, <strong><?= $username ?></strong> — voici un résumé de la plateforme</small>
        </div>
        <div class="topbar-right">
            <span class="badge-date"><i class="far fa-calendar me-1"></i><?= date('d/m/Y H:i') ?></span>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="section-title">📊 Vue d'ensemble</div>
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <a href="salles.php" class="stat-card blue d-flex">
                <div class="stat-icon blue"><i class="fas fa-building"></i></div>
                <div>
                    <div class="stat-num"><?= $nbSalles ?></div>
                    <div class="stat-label">Salles au total</div>
                    <div class="stat-action"><i class="fas fa-arrow-right me-1"></i>Voir la liste</div>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="profs.php" class="stat-card green d-flex">
                <div class="stat-icon green"><i class="fas fa-chalkboard-teacher"></i></div>
                <div>
                    <div class="stat-num"><?= $nbProfs ?></div>
                    <div class="stat-label">Professeurs enregistrés</div>
                    <div class="stat-action"><i class="fas fa-arrow-right me-1"></i>Voir l'annuaire</div>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="reservations.php" class="stat-card orange d-flex">
                <div class="stat-icon orange"><i class="fas fa-calendar-check"></i></div>
                <div>
                    <div class="stat-num"><?= $nbReservations ?></div>
                    <div class="stat-label">Réservations actives</div>
                    <div class="stat-action"><i class="fas fa-arrow-right me-1"></i>Gérer</div>
                </div>
            </a>
        </div>
    </div>

    <!-- Palier Cards -->
    <div class="section-title">🏢 Salles par palier</div>
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <a href="salle_licence.php" class="palier-card p1 d-block">
                <div>
                    <span class="palier-badge p1">Palier 1</span>
                    <div class="palier-name">Licence</div>
                    <div class="palier-sub">Université</div>
                </div>
                <div class="palier-count p1"><?= $nbP1 ?></div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="salle_bts.php" class="palier-card p2 d-block">
                <div>
                    <span class="palier-badge p2">Palier 2</span>
                    <div class="palier-name">BTS</div>
                    <div class="palier-sub">Études supérieures</div>
                </div>
                <div class="palier-count p2"><?= $nbP2 ?></div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="salle_bt.php" class="palier-card p3 d-block">
                <div>
                    <span class="palier-badge p3">Palier 3</span>
                    <div class="palier-name">BT</div>
                    <div class="palier-sub">Technicien</div>
                </div>
                <div class="palier-count p3"><?= $nbP3 ?></div>
            </a>
        </div>
    </div>

    <!-- Recent Activity (Nouveau) -->
    <?php if (!empty($recentReservations)): ?>
    <div class="section-title">📅 Dernières réservations</div>
    <div class="recent-activity">
        <h6 class="mb-3" style="color: #333; font-weight: 700;">Activité récente</h6>
        <?php foreach ($recentReservations as $res): ?>
        <div class="activity-item">
            <div>
                <div class="activity-text">
                    <strong><?= htmlspecialchars($res['filiere_cours'] ?? 'N/A') ?></strong>
                    <span class="text-muted ms-2">·</span>
                    <span class="text-muted ms-2"><?= htmlspecialchars($res['niveau'] ?? 'N/A') ?></span>
                </div>
                <div class="activity-date">
                    <?= date('d/m/Y H:i', strtotime($res['date_res'] ?? 'now')) ?>
                </div>
            </div>
            <div>
                <span class="badge bg-info"><?= isset($res['heure_debut']) ? substr($res['heure_debut'], 0, 5) : 'N/A' ?></span>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <footer>
        <i class="fas fa-copyright me-1"></i>2025 — Univ Manager · Développé par Esther
    </footer>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
