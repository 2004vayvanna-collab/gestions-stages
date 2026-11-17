<?php
/**
 * suppression.php
 * Partie IV req. 4b : DELETE avec PDO (exec + prepare/execute positionnel)
 * ENSI 2025/2026 — Partie IV
 */

require_once 'connexion.php';

$message = '';
$erreur  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id = intval($_POST['id'] ?? 0);

    // Revalidation PHP
    if ($id <= 0) {
        $erreur = "ID invalide. Veuillez entrer un identifiant positif.";
    } else {
        // Vérifier que l'enregistrement existe (fetch avec paramètre positionnel ?)
        $stmtCheck = $pdo->prepare("SELECT id, poste, entreprise FROM candidature WHERE id = ?");
        $stmtCheck->execute([$id]);
        $existe = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if (!$existe) {
            $erreur = "Aucune candidature trouvée avec l'ID #$id.";
        } else {
            // DELETE avec exec() (Partie IV — utiliser exec())
            $nbLignes = $pdo->exec("DELETE FROM candidature WHERE id = $id");
            if ($nbLignes > 0) {
                $message = "✅ Candidature #$id (" . htmlspecialchars($existe['poste']) . " chez " . htmlspecialchars($existe['entreprise']) . ") supprimée avec succès.";
            } else {
                $erreur = "La suppression a échoué.";
            }
        }
    }
}

// Charger toutes les candidatures pour le tableau de sélection
$candidatures = $pdo->query("SELECT id, poste, entreprise, statut FROM candidature ORDER BY id")
                    ->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suppression — InternShip Manager</title>
    <link rel="stylesheet" href="../styles.css">
    <style>
        .page-wrapper { max-width:800px; margin:0 auto; padding:2rem; }
        .form-card { background:#fff; padding:24px; border-radius:10px; box-shadow:0 2px 10px rgba(0,0,0,.1); margin-bottom:1.5rem; }
        .form-group { margin-bottom:16px; }
        .form-group label { display:block; font-weight:600; font-size:14px; margin-bottom:5px; }
        .form-group input, .form-group select { width:100%; padding:9px 12px; border:1px solid #ccc; border-radius:6px; font-size:14px; }
        .btn-delete { background:#dc3545; color:#fff; border:none; padding:11px 24px; border-radius:6px; cursor:pointer; font-size:14px; }
        .btn-delete:hover { background:#c82333; }
        .alert-success { background:#d4edda; color:#155724; padding:14px; border-radius:8px; margin-bottom:1.5rem; }
        .alert-error   { background:#f8d7da; color:#721c24; padding:14px; border-radius:8px; margin-bottom:1.5rem; }
        .data-table { width:100%; border-collapse:collapse; font-size:13px; }
        .data-table th, .data-table td { padding:8px 12px; border:1px solid #dee2e6; }
        .data-table th { background:#c0392b; color:#fff; }
        .data-table tr:nth-child(even) { background:#f8f9fa; }
        .btn-del-row { background:#dc3545; color:#fff; border:none; padding:4px 10px; border-radius:4px; cursor:pointer; font-size:12px; }
        .warning-box { background:#fff3cd; color:#856404; border:1px solid #ffc107; padding:12px; border-radius:6px; margin-bottom:1rem; font-size:13px; }
        .nav-links { margin-bottom:1.5rem; }
        .nav-links a { margin-right:12px; color:#2c3e50; text-decoration:none; }
        h1 { color:#c0392b; margin-bottom:1.5rem; }
        h2 { font-size:16px; margin-bottom:12px; color:#333; }
    </style>
</head>
<body>
<div class="page-wrapper">
    <div class="nav-links">
        <a href="recherche.php">🔍 Recherche</a>
        <a href="insertion.php">📥 Insertion</a>
        <a href="mise-a-jour.php">✏️ Mise à jour</a>
        <a href="../index.html">🏠 Accueil</a>
    </div>

    <h1>🗑️ Supprimer une candidature</h1>

    <?php if ($message): ?>
        <div class="alert-success"><?= $message ?></div>
    <?php endif; ?>
    <?php if ($erreur): ?>
        <div class="alert-error">⚠️ <?= htmlspecialchars($erreur) ?></div>
    <?php endif; ?>

    <!-- Formulaire de suppression par ID -->
    <div class="form-card">
        <h2>Supprimer par ID</h2>
        <div class="warning-box">⚠️ Cette action est irréversible. Vérifiez bien l'ID avant de supprimer.</div>

        <form method="POST" action="suppression.php"
              onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer la candidature #' + document.getElementById(\'id\').value + ' ?');">
            <div class="form-group">
                <label for="id">ID de la candidature à supprimer *</label>
                <input type="number" id="id" name="id" min="1" placeholder="Ex: 3" required>
            </div>
            <button type="submit" class="btn-delete">🗑️ Supprimer</button>
        </form>
    </div>

    <!-- Tableau des candidatures avec bouton de suppression rapide -->
    <div class="form-card">
        <h2>Supprimer depuis la liste</h2>
        <?php if (empty($candidatures)): ?>
            <p style="color:#888;font-style:italic;">Aucune candidature dans la base.</p>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr><th>ID</th><th>Poste</th><th>Entreprise</th><th>Statut</th><th>Action</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($candidatures as $c): ?>
                    <tr>
                        <td><?= $c['id'] ?></td>
                        <td><?= htmlspecialchars($c['poste']) ?></td>
                        <td><?= htmlspecialchars($c['entreprise']) ?></td>
                        <td><?= htmlspecialchars($c['statut']) ?></td>
                        <td>
                            <form method="POST" action="suppression.php" style="display:inline;"
                                  onsubmit="return confirm('Supprimer la candidature #<?= $c['id'] ?> ?');">
                                <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                <button type="submit" class="btn-del-row">🗑️ Supprimer</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
