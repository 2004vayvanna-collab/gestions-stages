<?php
/**
 * insertion.php
 * Partie IV req. 4b : INSERT avec PDO (requête préparée nommée)
 * ENSI 2025/2026 — Partie IV
 */

require_once 'connexion.php';

$message = '';
$erreur  = '';
$donnees = []; // Pour re-remplir le formulaire en cas d'erreur

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Récupération des données
    $poste      = trim($_POST['poste']      ?? '');
    $entreprise = trim($_POST['entreprise'] ?? '');
    $ville      = trim($_POST['ville']      ?? '');
    $domaine    = trim($_POST['domaine']    ?? '');
    $statut     = trim($_POST['statut']     ?? 'En cours');
    $dateStr    = trim($_POST['date_candidature'] ?? '');

    $donnees = compact('poste', 'entreprise', 'ville', 'domaine', 'statut', 'dateStr');

    // ---- Revalidation PHP ----
    $erreurs = [];
    if (strlen($poste) < 3)      $erreurs[] = "Le poste doit faire au moins 3 caractères.";
    if (empty($entreprise))      $erreurs[] = "L'entreprise est obligatoire.";
    if (empty($ville))           $erreurs[] = "La ville est obligatoire.";
    if (empty($domaine))         $erreurs[] = "Le domaine est obligatoire.";
    if (!empty($dateStr) && !strtotime($dateStr)) $erreurs[] = "Date invalide.";
    if (!in_array($statut, ['En cours', 'Accepté', 'Refusé'])) $erreurs[] = "Statut invalide.";

    if (empty($erreurs)) {
        $date = !empty($dateStr) ? $dateStr : date('Y-m-d');

        // INSERT avec paramètres NOMMÉS (prepare + execute nommé)
        $sql  = "INSERT INTO candidature (poste, entreprise, date_candidature, statut, ville, domaine)
                 VALUES (:poste, :entreprise, :date_candidature, :statut, :ville, :domaine)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':poste'            => $poste,
            ':entreprise'       => $entreprise,
            ':date_candidature' => $date,
            ':statut'           => $statut,
            ':ville'            => $ville,
            ':domaine'          => $domaine,
        ]);

        $newId   = $pdo->lastInsertId();
        $message = "✅ Candidature #$newId insérée avec succès !";
        $donnees = []; // Réinitialiser le formulaire
    } else {
        $erreur = implode('<br>', $erreurs);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insertion — InternShip Manager</title>
    <link rel="stylesheet" href="../styles.css">
    <style>
        .page-wrapper { max-width:700px; margin:0 auto; padding:2rem; }
        .form-card { background:#fff; padding:28px; border-radius:10px; box-shadow:0 2px 10px rgba(0,0,0,.1); }
        .form-group { margin-bottom:16px; }
        .form-group label { display:block; font-weight:600; font-size:14px; margin-bottom:5px; color:#333; }
        .form-group input, .form-group select { width:100%; padding:9px 12px; border:1px solid #ccc; border-radius:6px; font-size:14px; }
        .form-group input:focus, .form-group select:focus { border-color:#2c3e50; outline:none; box-shadow:0 0 0 2px rgba(44,62,80,.15); }
        .err-inline { color:#dc3545; font-size:12px; margin-top:3px; display:block; }
        .btn-submit { background:#27ae60; color:#fff; border:none; padding:11px 28px; border-radius:6px; cursor:pointer; font-size:15px; width:100%; }
        .btn-submit:hover { background:#219a52; }
        .alert-success { background:#d4edda; color:#155724; padding:14px; border-radius:8px; margin-bottom:1.5rem; font-weight:500; }
        .alert-error { background:#f8d7da; color:#721c24; padding:14px; border-radius:8px; margin-bottom:1.5rem; }
        .nav-links { margin-bottom:1.5rem; }
        .nav-links a { margin-right:12px; color:#2c3e50; text-decoration:none; }
        h1 { color:#2c3e50; margin-bottom:1.5rem; }
        .code-note { font-size:11px; color:#888; font-family:monospace; margin-top:4px; }
    </style>
</head>
<body>
<div class="page-wrapper">
    <div class="nav-links">
        <a href="recherche.php">🔍 Recherche</a>
        <a href="suppression.php">🗑️ Suppression</a>
        <a href="mise-a-jour.php">✏️ Mise à jour</a>
        <a href="../index.html">🏠 Accueil</a>
    </div>

    <h1>📥 Ajouter une candidature</h1>

    <?php if ($message): ?>
        <div class="alert-success"><?= $message ?></div>
    <?php endif; ?>
    <?php if ($erreur): ?>
        <div class="alert-error">⚠️ <?= $erreur ?></div>
    <?php endif; ?>

    <div class="form-card">
        <form method="POST" action="insertion.php" onsubmit="return validerInsertion()">

            <div class="form-group">
                <label for="poste">Poste recherché *</label>
                <input type="text" id="poste" name="poste" minlength="3" maxlength="150"
                       value="<?= htmlspecialchars($donnees['poste'] ?? '') ?>" required>
                <span id="err-poste" class="err-inline"></span>
            </div>

            <div class="form-group">
                <label for="entreprise">Entreprise *</label>
                <input type="text" id="entreprise" name="entreprise" maxlength="150"
                       value="<?= htmlspecialchars($donnees['entreprise'] ?? '') ?>" required>
                <span id="err-entreprise" class="err-inline"></span>
            </div>

            <div class="form-group">
                <label for="ville">Ville *</label>
                <input type="text" id="ville" name="ville"
                       value="<?= htmlspecialchars($donnees['ville'] ?? '') ?>" required>
                <span id="err-ville" class="err-inline"></span>
            </div>

            <div class="form-group">
                <label for="domaine">Domaine *</label>
                <select id="domaine" name="domaine" required>
                    <option value="">-- Choisir --</option>
                    <?php foreach (['Informatique','Design','Marketing','Finance','Autre'] as $d): ?>
                        <option value="<?= $d ?>" <?= (($donnees['domaine'] ?? '') === $d) ? 'selected' : '' ?>><?= $d ?></option>
                    <?php endforeach; ?>
                </select>
                <span id="err-domaine" class="err-inline"></span>
            </div>

            <div class="form-group">
                <label for="statut">Statut</label>
                <select id="statut" name="statut">
                    <?php foreach (['En cours','Accepté','Refusé'] as $s): ?>
                        <option value="<?= $s ?>" <?= (($donnees['statut'] ?? 'En cours') === $s) ? 'selected' : '' ?>><?= $s ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="date_candidature">Date de candidature</label>
                <input type="date" id="date_candidature" name="date_candidature"
                       value="<?= htmlspecialchars($donnees['dateStr'] ?? date('Y-m-d')) ?>">
                <span class="code-note">Laissez vide pour utiliser la date du jour.</span>
            </div>

            <button type="submit" class="btn-submit">💾 Enregistrer la candidature</button>
        </form>
    </div>
</div>

<script>
// Validation JS (Partie IV req. 1 — 2ème formulaire)
function validerInsertion() {
    let ok = true;

    // Champ obligatoire + longueur min
    const poste = document.getElementById('poste').value.trim();
    const errPoste = document.getElementById('err-poste');
    if (poste.length < 3) {
        errPoste.innerText = '⚠️ Le poste doit faire au moins 3 caractères.';
        ok = false;
    } else { errPoste.innerText = ''; }

    // Champ obligatoire
    const ent = document.getElementById('entreprise').value.trim();
    const errEnt = document.getElementById('err-entreprise');
    if (!ent) {
        errEnt.innerText = '⚠️ L\'entreprise est obligatoire.';
        ok = false;
    } else { errEnt.innerText = ''; }

    // Champ obligatoire
    const ville = document.getElementById('ville').value.trim();
    const errVille = document.getElementById('err-ville');
    if (!ville) {
        errVille.innerText = '⚠️ La ville est obligatoire.';
        ok = false;
    } else { errVille.innerText = ''; }

    // Select obligatoire
    const domaine = document.getElementById('domaine').value;
    const errDomaine = document.getElementById('err-domaine');
    if (!domaine) {
        errDomaine.innerText = '⚠️ Veuillez choisir un domaine.';
        ok = false;
    } else { errDomaine.innerText = ''; }

    return ok;
}
</script>
</body>
</html>
