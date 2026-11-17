<?php
/**
 * mise-a-jour.php
 * Partie IV req. 4b : UPDATE avec PDO (prepare + execute positionnel)
 * ENSI 2025/2026 — Partie IV
 */

require_once 'connexion.php';

$message    = '';
$erreur     = '';
$candidature = null;

/* ============================================================
   ÉTAPE 1 : Chercher la candidature à modifier (GET)
   ============================================================ */
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    // Requête préparée avec paramètre POSITIONNEL (?)
    $stmt = $pdo->prepare("SELECT * FROM candidature WHERE id = ?");
    $stmt->execute([$id]);
    $candidature = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$candidature) {
        $erreur = "Aucune candidature trouvée avec l'ID #$id.";
    }
}

/* ============================================================
   ÉTAPE 2 : Traiter la mise à jour (POST)
   ============================================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {

    $id         = intval($_POST['id']);
    $poste      = trim($_POST['poste']      ?? '');
    $entreprise = trim($_POST['entreprise'] ?? '');
    $ville      = trim($_POST['ville']      ?? '');
    $domaine    = trim($_POST['domaine']    ?? '');
    $statut     = trim($_POST['statut']     ?? '');

    // Revalidation PHP
    $erreurs = [];
    if (strlen($poste) < 3)  $erreurs[] = "Le poste doit faire au moins 3 caractères.";
    if (empty($entreprise))  $erreurs[] = "L'entreprise est obligatoire.";
    if (empty($ville))       $erreurs[] = "La ville est obligatoire.";
    if (empty($domaine))     $erreurs[] = "Le domaine est obligatoire.";
    if (!in_array($statut, ['En cours', 'Accepté', 'Refusé'])) $erreurs[] = "Statut invalide.";

    if (empty($erreurs)) {
        // UPDATE avec paramètres POSITIONNELS (?) — Partie IV req. utiliser les deux types
        $sql  = "UPDATE candidature SET poste=?, entreprise=?, ville=?, domaine=?, statut=? WHERE id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$poste, $entreprise, $ville, $domaine, $statut, $id]);

        $message = "✅ Candidature #$id mise à jour avec succès !";

        // Recharger les données mises à jour
        $stmt2 = $pdo->prepare("SELECT * FROM candidature WHERE id = ?");
        $stmt2->execute([$id]);
        $candidature = $stmt2->fetch(PDO::FETCH_ASSOC);

    } else {
        $erreur = implode('<br>', $erreurs);
        // Conserver les valeurs saisies
        $candidature = compact('id', 'poste', 'entreprise', 'ville', 'domaine', 'statut');
    }
}

// Liste pour sélection rapide
$liste = $pdo->query("SELECT id, poste, entreprise FROM candidature ORDER BY id")
             ->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mise à jour — InternShip Manager</title>
    <link rel="stylesheet" href="../styles.css">
    <style>
        .page-wrapper { max-width:800px; margin:0 auto; padding:2rem; }
        .form-card { background:#fff; padding:24px; border-radius:10px; box-shadow:0 2px 10px rgba(0,0,0,.1); margin-bottom:1.5rem; }
        .form-group { margin-bottom:14px; }
        .form-group label { display:block; font-weight:600; font-size:14px; margin-bottom:4px; }
        .form-group input, .form-group select { width:100%; padding:9px 12px; border:1px solid #ccc; border-radius:6px; font-size:14px; }
        .err-inline { color:#dc3545; font-size:12px; margin-top:3px; display:block; }
        .btn-update { background:#e67e22; color:#fff; border:none; padding:11px 24px; border-radius:6px; cursor:pointer; font-size:14px; }
        .btn-update:hover { background:#d35400; }
        .btn-search { background:#2c3e50; color:#fff; border:none; padding:9px 18px; border-radius:6px; cursor:pointer; font-size:14px; }
        .alert-success { background:#d4edda; color:#155724; padding:14px; border-radius:8px; margin-bottom:1.5rem; }
        .alert-error   { background:#f8d7da; color:#721c24; padding:14px; border-radius:8px; margin-bottom:1.5rem; }
        .select-list { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:1rem; }
        .select-list a { padding:6px 12px; border:1px solid #ccc; border-radius:6px; font-size:13px; color:#2c3e50; text-decoration:none; }
        .select-list a:hover { background:#f0f0f0; }
        .nav-links { margin-bottom:1.5rem; }
        .nav-links a { margin-right:12px; color:#2c3e50; text-decoration:none; }
        h1 { color:#e67e22; margin-bottom:1.5rem; }
        h2 { font-size:16px; margin-bottom:12px; color:#333; }
    </style>
</head>
<body>
<div class="page-wrapper">
    <div class="nav-links">
        <a href="recherche.php">🔍 Recherche</a>
        <a href="insertion.php">📥 Insertion</a>
        <a href="suppression.php">🗑️ Suppression</a>
        <a href="../index.html">🏠 Accueil</a>
    </div>

    <h1>✏️ Modifier une candidature</h1>

    <?php if ($message): ?>
        <div class="alert-success"><?= $message ?></div>
    <?php endif; ?>
    <?php if ($erreur): ?>
        <div class="alert-error">⚠️ <?= $erreur ?></div>
    <?php endif; ?>

    <!-- Sélection de la candidature à modifier -->
    <div class="form-card">
        <h2>Choisir la candidature à modifier</h2>
        <div class="select-list">
            <?php foreach ($liste as $item): ?>
                <a href="mise-a-jour.php?id=<?= $item['id'] ?>">
                    #<?= $item['id'] ?> — <?= htmlspecialchars($item['poste']) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <form method="GET" action="mise-a-jour.php" style="display:flex;gap:10px;align-items:flex-end;">
            <div class="form-group" style="flex:1;margin:0;">
                <label>Ou entrez l'ID directement :</label>
                <input type="number" name="id" min="1" placeholder="ID de la candidature"
                       value="<?= htmlspecialchars($_GET['id'] ?? '') ?>">
            </div>
            <button type="submit" class="btn-search">Charger</button>
        </form>
    </div>

    <!-- Formulaire de modification -->
    <?php if ($candidature): ?>
    <div class="form-card">
        <h2>Modifier la candidature #<?= htmlspecialchars($candidature['id']) ?></h2>

        <form method="POST" action="mise-a-jour.php" onsubmit="return validerMAJ()">
            <input type="hidden" name="id" value="<?= htmlspecialchars($candidature['id']) ?>">

            <div class="form-group">
                <label for="poste">Poste *</label>
                <input type="text" id="poste" name="poste" minlength="3"
                       value="<?= htmlspecialchars($candidature['poste']) ?>" required>
                <span id="err-poste" class="err-inline"></span>
            </div>

            <div class="form-group">
                <label for="entreprise">Entreprise *</label>
                <input type="text" id="entreprise" name="entreprise"
                       value="<?= htmlspecialchars($candidature['entreprise']) ?>" required>
                <span id="err-entreprise" class="err-inline"></span>
            </div>

            <div class="form-group">
                <label for="ville">Ville *</label>
                <input type="text" id="ville" name="ville"
                       value="<?= htmlspecialchars($candidature['ville']) ?>" required>
            </div>

            <div class="form-group">
                <label for="domaine">Domaine *</label>
                <select id="domaine" name="domaine" required>
                    <?php foreach (['Informatique','Design','Marketing','Finance','Autre'] as $d): ?>
                        <option value="<?= $d ?>" <?= ($candidature['domaine'] === $d) ? 'selected' : '' ?>><?= $d ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="statut">Statut</label>
                <select id="statut" name="statut">
                    <?php foreach (['En cours','Accepté','Refusé'] as $s): ?>
                        <option value="<?= $s ?>" <?= ($candidature['statut'] === $s) ? 'selected' : '' ?>><?= $s ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn-update">💾 Enregistrer les modifications</button>
        </form>
    </div>
    <?php endif; ?>
</div>

<script>
function validerMAJ() {
    let ok = true;
    const poste = document.getElementById('poste').value.trim();
    const errPoste = document.getElementById('err-poste');
    if (poste.length < 3) {
        errPoste.innerText = '⚠️ Le poste doit faire au moins 3 caractères.';
        ok = false;
    } else { errPoste.innerText = ''; }

    const ent = document.getElementById('entreprise').value.trim();
    const errEnt = document.getElementById('err-entreprise');
    if (!ent) {
        errEnt.innerText = '⚠️ L\'entreprise est obligatoire.';
        ok = false;
    } else { errEnt.innerText = ''; }

    return ok;
}
</script>
</body>
</html>
