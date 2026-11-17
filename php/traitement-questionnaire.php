<?php
/**
 * traitement-questionnaire.php — InternShip Manager
 * Partie IV req. 3a/3b : reçoit POST de questionnaire.html + affiche en HTML
 * CORRECTION : noms des champs corrigés + satisfaction numérique
 * ENSI 2025/2026
 */

require_once 'connexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../questionnaire.html');
    exit;
}

$erreurs = [];
$donnees = [];

/* ============================================================
   REVALIDATION PHP — noms correspondent exactement au HTML
   ============================================================ */

// Champ 1 : Nom → name="q-nom" dans questionnaire.html
$nom = trim($_POST['q-nom'] ?? '');
if (empty($nom)) {
    $erreurs[] = "Le nom est obligatoire.";
} else {
    $donnees['nom'] = htmlspecialchars($nom);
}

// Champ 2 : Email → name="q-email" dans questionnaire.html
$email = trim($_POST['q-email'] ?? '');
if (empty($email)) {
    $erreurs[] = "L'email est obligatoire.";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $erreurs[] = "Format d'email invalide (ex: nom@domaine.tn).";
} else {
    $donnees['email'] = htmlspecialchars($email);
}

// Champ 3 : Profil → name="profil"
$profil = htmlspecialchars(trim($_POST['profil'] ?? ''));
$donnees['profil'] = $profil;

// Champ 4 : Satisfaction → name="satisfaction" value="1" à "5"
$satisfaction = intval($_POST['satisfaction'] ?? 0);
if ($satisfaction < 1 || $satisfaction > 5) {
    $erreurs[] = "Veuillez sélectionner votre niveau de satisfaction.";
} else {
    $donnees['satisfaction'] = $satisfaction;
}

// Champ 5 : Fonctionnalités → name="fonctionnalites" (tableau checkbox)
$fonctionnalites = $_POST['fonctionnalites'] ?? [];
if (empty($fonctionnalites)) {
    $erreurs[] = "Veuillez cocher au moins une fonctionnalité.";
} else {
    $fonctionnalites = array_map('htmlspecialchars', $fonctionnalites);
    $donnees['fonctionnalites'] = $fonctionnalites;
}

// Champ 6 : Recommandation → name="recommandation"
$recommandation = htmlspecialchars(trim($_POST['recommandation'] ?? ''));
$donnees['recommandation'] = $recommandation;

// Champ 7 : Commentaire → name="q-commentaire" dans questionnaire.html
$commentaire = trim($_POST['q-commentaire'] ?? '');
if (strlen($commentaire) < 20) {
    $erreurs[] = "Le commentaire doit contenir au moins 20 caractères.";
} else {
    $donnees['commentaire'] = htmlspecialchars($commentaire);
}

// ---- Sauvegarde en BD si pas d'erreurs ----
if (empty($erreurs)) {
    $sql  = "INSERT INTO questionnaire_reponse
             (nom, email, profil, satisfaction, fonctionnalites, recommandation, commentaire)
             VALUES (:nom, :email, :profil, :satisfaction, :fonctionnalites, :recommandation, :commentaire)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':nom'             => $nom,
        ':email'           => $email,
        ':profil'          => $profil,
        ':satisfaction'    => $satisfaction,
        ':fonctionnalites' => implode(',', $fonctionnalites),
        ':recommandation'  => $recommandation,
        ':commentaire'     => $commentaire,
    ]);
}

// Labels satisfaction
$labelsSatisfaction = [
    1 => '⭐ Mauvais',
    2 => '⭐⭐ Insuffisant',
    3 => '⭐⭐⭐ Moyen',
    4 => '⭐⭐⭐⭐ Bien',
    5 => '⭐⭐⭐⭐⭐ Excellent',
];
$labelsRecommandation = [
    'oui'      => '✅ Oui, certainement',
    'peutetre' => '🤔 Peut-être',
    'non'      => '❌ Non',
];
$labelsProfil = [
    'etudiant'   => '🎓 Étudiant',
    'entreprise' => '🏢 Représentant d\'entreprise',
    'enseignant' => '📚 Enseignant / Encadrant',
    'autre'      => 'Autre',
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Résultat Questionnaire — InternShip Manager</title>
  <link rel="stylesheet" href="../styles.css">
  <style>
    .page-wrapper { max-width:800px; margin:0 auto; padding:2rem; }
    .result-card  { background:#fff; border-radius:10px; box-shadow:0 2px 12px rgba(0,0,0,.1); padding:2rem; }
    .result-table { width:100%; border-collapse:collapse; margin:1rem 0; }
    .result-table th, .result-table td { padding:12px 16px; border:1px solid #e0e0e0; text-align:left; }
    .result-table th { background:#2c3e50; color:#fff; width:35%; }
    .result-table tr:nth-child(even) { background:#f5f5f5; }
    .badge-fonc { display:inline-block; background:var(--blue-pale); color:var(--blue-dark);
                  padding:3px 10px; border-radius:12px; font-size:13px; margin:2px; }
    .alert-error   { background:#f8d7da; color:#721c24; padding:16px; border-radius:8px; margin:1rem 0; }
    .alert-success { background:#d4edda; color:#155724; padding:16px; border-radius:8px; margin:1rem 0; }
    .btn-back { display:inline-block; margin-top:1.5rem; background:#2c3e50; color:#fff;
                padding:10px 20px; border-radius:6px; text-decoration:none; margin-right:10px; }
    h1 { color:#2c3e50; margin-bottom:1.5rem; }
    .icon-ok { text-align:center; font-size:3rem; display:block; margin-bottom:1rem; }
  </style>
</head>
<body>
  <header>
    <div class="header-inner">
      <a href="../index.html" class="logo-wrap">
        <span style="font-size:2rem;">🎓</span>
        <span class="logo-text">InternShip Manager</span>
      </a>
      <nav><ul>
        <li><a href="../index.html">Accueil</a></li>
        <li><a href="../questionnaire.html" class="active">Questionnaire</a></li>
        <li><a href="../contact.html">Contact</a></li>
      </ul></nav>
    </div>
  </header>

  <main>
    <div class="page-wrapper">
      <h1>📝 Résultat du Questionnaire</h1>

      <?php if (!empty($erreurs)): ?>
        <div class="alert-error">
          <strong>⚠️ Erreurs de validation :</strong>
          <ul style="margin-top:0.5rem;padding-left:1.2rem;">
            <?php foreach ($erreurs as $err): ?>
              <li><?= htmlspecialchars($err) ?></li>
            <?php endforeach; ?>
          </ul>
          <a href="../questionnaire.html" class="btn-back" style="margin-top:1rem;display:inline-block;">← Retour au formulaire</a>
        </div>

      <?php else: ?>
        <div class="alert-success">✅ Votre réponse a été enregistrée avec succès !</div>

        <div class="result-card">
          <span class="icon-ok">✔️</span>
          <table class="result-table">
            <tr>
              <th>Nom complet</th>
              <td><?= $donnees['nom'] ?></td>
            </tr>
            <tr>
              <th>Email</th>
              <td><?= $donnees['email'] ?></td>
            </tr>
            <tr>
              <th>Profil</th>
              <td><?= $labelsProfil[$donnees['profil']] ?? ($donnees['profil'] ?: '—') ?></td>
            </tr>
            <tr>
              <th>Satisfaction</th>
              <td><?= $labelsSatisfaction[$donnees['satisfaction']] ?? '—' ?></td>
            </tr>
            <tr>
              <th>Fonctionnalités utilisées</th>
              <td>
                <?php foreach ($donnees['fonctionnalites'] as $f): ?>
                  <span class="badge-fonc"><?= $f ?></span>
                <?php endforeach; ?>
              </td>
            </tr>
            <tr>
              <th>Recommandation</th>
              <td><?= $labelsRecommandation[$donnees['recommandation']] ?? '—' ?></td>
            </tr>
            <tr>
              <th>Commentaire</th>
              <td><em>"<?= $donnees['commentaire'] ?>"</em></td>
            </tr>
            <tr>
              <th>Date de soumission</th>
              <td><?= date('d/m/Y à H:i:s') ?></td>
            </tr>
          </table>

          <a href="../questionnaire.html" class="btn-back">← Nouveau questionnaire</a>
          <a href="../index.html" class="btn-back">🏠 Accueil</a>
        </div>
      <?php endif; ?>
    </div>
  </main>

  <footer>
    <div class="footer-inner">
      <div class="footer-col"><h4>InternShip Manager</h4><p>© 2026</p></div>
    </div>
    <div class="footer-bottom">InternShip Manager — Partie IV PHP & MySQL</div>
  </footer>
</body>
</html>
