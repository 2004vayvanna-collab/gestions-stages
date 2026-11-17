<?php
/**
 * recherche.php
 * Partie IV req. 4a : Recherche avec SELECT, fetch(), fetchAll(), fetchObject()
 * ENSI 2025/2026 — Partie IV
 */

require_once 'connexion.php';

$resultats    = [];
$recherche    = '';
$filtreDomaine = '';
$filtreStatut = '';
$methode      = 'fetchAll'; // méthode utilisée (pour affichage pédagogique)

/* ============================================================
   TRAITEMENT DE LA RECHERCHE
   ============================================================ */
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['recherche'])) {

    $recherche     = trim($_GET['recherche']    ?? '');
    $filtreDomaine = trim($_GET['domaine']      ?? '');
    $filtreStatut  = trim($_GET['statut']       ?? '');
    $methode       = $_GET['methode']           ?? 'fetchAll';

    // Construction de la requête SQL dynamique avec critères
    $sql    = "SELECT * FROM candidature WHERE 1=1";
    $params = [];

    // Critère : mot-clé dans poste, entreprise ou ville
    if (!empty($recherche)) {
        $sql .= " AND (poste LIKE :kw OR entreprise LIKE :kw OR ville LIKE :kw)";
        $params[':kw'] = '%' . $recherche . '%';
    }

    // Critère : domaine
    if (!empty($filtreDomaine)) {
        $sql .= " AND domaine = :domaine";
        $params[':domaine'] = $filtreDomaine;
    }

    // Critère : statut
    if (!empty($filtreStatut)) {
        $sql .= " AND statut = :statut";
        $params[':statut'] = $filtreStatut;
    }

    $sql .= " ORDER BY date_candidature DESC";

    // Utilisation de prepare() + execute() — paramètres nommés
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    // ---- Démonstration des 3 méthodes de fetch ----
    if ($methode === 'fetch') {
        // fetch() : récupère ligne par ligne
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $resultats[] = $row;
        }
    } elseif ($methode === 'fetchObject') {
        // fetchObject() : retourne un objet stdClass
        while ($obj = $stmt->fetchObject()) {
            $resultats[] = (array) $obj;
        }
    } else {
        // fetchAll() : récupère toutes les lignes d'un coup (par défaut)
        $resultats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Récupérer les domaines distincts pour le filtre (query simple)
$domaines = $pdo->query("SELECT DISTINCT domaine FROM candidature ORDER BY domaine")
                ->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recherche Candidatures — InternShip Manager</title>
    <link rel="stylesheet" href="../styles.css">
    <style>
        .page-wrapper { max-width:1100px; margin:0 auto; padding:2rem; }
        .search-card { background:#fff; padding:24px; border-radius:10px; box-shadow:0 2px 8px rgba(0,0,0,.08); margin-bottom:2rem; }
        .form-row { display:flex; gap:12px; flex-wrap:wrap; align-items:flex-end; }
        .form-group { flex:1; min-width:160px; }
        .form-group label { display:block; font-weight:600; font-size:13px; margin-bottom:4px; }
        .form-group input, .form-group select { width:100%; padding:8px 12px; border:1px solid #ccc; border-radius:6px; font-size:14px; }
        .btn-search { background:#2c3e50; color:#fff; border:none; padding:9px 20px; border-radius:6px; cursor:pointer; font-size:14px; }
        .btn-reset { background:#6c757d; color:#fff; border:none; padding:9px 16px; border-radius:6px; cursor:pointer; font-size:14px; text-decoration:none; display:inline-block; }
        .data-table { width:100%; border-collapse:collapse; }
        .data-table th, .data-table td { padding:10px 14px; border:1px solid #dee2e6; font-size:14px; }
        .data-table th { background:#2c3e50; color:#fff; }
        .data-table tr:nth-child(even) { background:#f8f9fa; }
        .data-table tr:hover { background:#e8f4f8; }
        .status-badge { padding:3px 10px; border-radius:12px; font-size:12px; font-weight:600; }
        .status-accepte  { background:#d4edda; color:#155724; }
        .status-refuse   { background:#f8d7da; color:#721c24; }
        .status-en-cours { background:#fff3cd; color:#856404; }
        .result-info { color:#555; font-size:13px; margin-bottom:1rem; }
        .methode-badge { display:inline-block; background:#e8f0fe; color:#1a73e8; padding:3px 10px; border-radius:12px; font-size:12px; font-family:monospace; }
        .no-result { color:#888; font-style:italic; padding:20px; text-align:center; }
        .nav-links { margin-bottom:1.5rem; }
        .nav-links a { margin-right:12px; color:#2c3e50; text-decoration:none; font-weight:500; }
        .nav-links a:hover { text-decoration:underline; }
        h1 { color:#2c3e50; margin-bottom:0.5rem; }
    </style>
</head>
<body>
<div class="page-wrapper">
    <div class="nav-links">
        <a href="../index.html">🏠 Accueil</a>
        <a href="traitement-candidature.php">➕ Ajouter</a>
        <a href="insertion.php">📥 Insertion</a>
        <a href="suppression.php">🗑️ Suppression</a>
        <a href="mise-a-jour.php">✏️ Mise à jour</a>
    </div>

    <h1>🔍 Recherche de Candidatures</h1>

    <!-- Formulaire de recherche -->
    <div class="search-card">
        <form method="GET" action="recherche.php">
            <div class="form-row">
                <div class="form-group">
                    <label for="recherche">Mot-clé (poste, entreprise, ville)</label>
                    <input type="text" id="recherche" name="recherche"
                           value="<?= htmlspecialchars($recherche) ?>"
                           placeholder="Ex: Développeur, Tunis...">
                </div>
                <div class="form-group">
                    <label for="domaine">Domaine</label>
                    <select id="domaine" name="domaine">
                        <option value="">Tous les domaines</option>
                        <?php foreach ($domaines as $d): ?>
                            <option value="<?= htmlspecialchars($d) ?>"
                                <?= ($filtreDomaine === $d) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($d) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="statut">Statut</label>
                    <select id="statut" name="statut">
                        <option value="">Tous les statuts</option>
                        <option value="En cours" <?= ($filtreStatut === 'En cours') ? 'selected' : '' ?>>En cours</option>
                        <option value="Accepté"  <?= ($filtreStatut === 'Accepté')  ? 'selected' : '' ?>>Accepté</option>
                        <option value="Refusé"   <?= ($filtreStatut === 'Refusé')   ? 'selected' : '' ?>>Refusé</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="methode">Méthode fetch</label>
                    <select id="methode" name="methode">
                        <option value="fetchAll"    <?= ($methode === 'fetchAll')    ? 'selected' : '' ?>>fetchAll()</option>
                        <option value="fetch"       <?= ($methode === 'fetch')       ? 'selected' : '' ?>>fetch()</option>
                        <option value="fetchObject" <?= ($methode === 'fetchObject') ? 'selected' : '' ?>>fetchObject()</option>
                    </select>
                </div>
                <div>
                    <button type="submit" class="btn-search">🔍 Rechercher</button>
                    <a href="recherche.php" class="btn-reset" style="margin-left:8px;">↺ Reset</a>
                </div>
            </div>
        </form>
    </div>

    <!-- Résultats -->
    <?php if (isset($_GET['recherche'])): ?>
        <div class="search-card">
            <div class="result-info">
                <strong><?= count($resultats) ?></strong> résultat(s) trouvé(s)
                <?php if (!empty($recherche)): ?>
                    pour "<em><?= htmlspecialchars($recherche) ?></em>"
                <?php endif; ?>
                — Méthode utilisée : <span class="methode-badge"><?= htmlspecialchars($methode) ?>()</span>
            </div>

            <?php if (empty($resultats)): ?>
                <p class="no-result">😕 Aucune candidature ne correspond à votre recherche.</p>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th><th>Poste</th><th>Entreprise</th>
                            <th>Ville</th><th>Domaine</th><th>Date</th><th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($resultats as $row):
                            $classeStatut = match($row['statut']) {
                                'Accepté' => 'status-accepte',
                                'Refusé'  => 'status-refuse',
                                default   => 'status-en-cours',
                            };
                        ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['poste']) ?></td>
                            <td><?= htmlspecialchars($row['entreprise']) ?></td>
                            <td><?= htmlspecialchars($row['ville']) ?></td>
                            <td><?= htmlspecialchars($row['domaine']) ?></td>
                            <td><?= htmlspecialchars($row['date_candidature']) ?></td>
                            <td><span class="status-badge <?= $classeStatut ?>"><?= htmlspecialchars($row['statut']) ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
