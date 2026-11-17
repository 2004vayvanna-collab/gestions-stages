<?php
/**
 * traitement-candidature.php
 * Partie III req. 3c : Classe PHP + tableau d'objets + fonction d'affichage
 * Reçoit aussi les données du formulaire espace-etudiant (Partie III req. 3a/3b)
 * ENSI 2025/2026 — Partie IV
 */

require_once 'connexion.php';

/* ============================================================
   CLASSE : Candidature
   Représente un enregistrement de la table `candidature`
   ============================================================ */
class Candidature {
    // Attributs privés
    private int    $id;
    private string $poste;
    private string $entreprise;
    private string $dateCandidature;
    private string $statut;
    private string $ville;
    private string $domaine;

    // Constructeur
    public function __construct(int $id, string $poste, string $entreprise,
                                string $dateCandidature, string $statut,
                                string $ville, string $domaine) {
        $this->id               = $id;
        $this->poste            = $poste;
        $this->entreprise       = $entreprise;
        $this->dateCandidature  = $dateCandidature;
        $this->statut           = $statut;
        $this->ville            = $ville;
        $this->domaine          = $domaine;
    }

    // Getters
    public function getId()              : int    { return $this->id; }
    public function getPoste()           : string { return $this->poste; }
    public function getEntreprise()      : string { return $this->entreprise; }
    public function getDateCandidature() : string { return $this->dateCandidature; }
    public function getStatut()          : string { return $this->statut; }
    public function getVille()           : string { return $this->ville; }
    public function getDomaine()         : string { return $this->domaine; }

    // Setters
    public function setPoste(string $poste)           : void { $this->poste = $poste; }
    public function setEntreprise(string $entreprise) : void { $this->entreprise = $entreprise; }
    public function setStatut(string $statut)         : void { $this->statut = $statut; }
    public function setVille(string $ville)           : void { $this->ville = $ville; }
    public function setDomaine(string $domaine)       : void { $this->domaine = $domaine; }

    // Méthode : retourne la classe CSS selon le statut
    public function getStatutClass() : string {
        return match($this->statut) {
            'Accepté' => 'status-accepte',
            'Refusé'  => 'status-refuse',
            default   => 'status-en-cours',
        };
    }

    // Méthode : retourne une représentation HTML de la ligne
    public function toHTMLRow() : string {
        $classe = htmlspecialchars($this->getStatutClass());
        $statut = htmlspecialchars($this->statut);
        return "
        <tr>
            <td>" . $this->id . "</td>
            <td>" . htmlspecialchars($this->poste) . "</td>
            <td>" . htmlspecialchars($this->entreprise) . "</td>
            <td>" . htmlspecialchars($this->ville) . "</td>
            <td>" . htmlspecialchars($this->domaine) . "</td>
            <td>" . htmlspecialchars($this->dateCandidature) . "</td>
            <td><span class='status-badge $classe'>$statut</span></td>
        </tr>";
    }
}

/* ============================================================
   TABLEAU D'OBJETS : charger toutes les candidatures depuis la BD
   ============================================================ */
/**
 * Charge toutes les candidatures depuis la BD et retourne un tableau d'objets Candidature
 * Utilise fetchAll() — Partie IV req. 4a
 */
function chargerToutesCandidatures(PDO $pdo) : array {
    $stmt = $pdo->query("SELECT * FROM candidature ORDER BY date_candidature DESC");
    $rows = $stmt->fetchAll();

    $tableau = [];
    foreach ($rows as $row) {
        $tableau[] = new Candidature(
            $row['id'],
            $row['poste'],
            $row['entreprise'],
            $row['date_candidature'],
            $row['statut'],
            $row['ville'],
            $row['domaine']
        );
    }
    return $tableau;
}

/* ============================================================
   FONCTION D'AFFICHAGE : parcourt le tableau et génère un tableau HTML
   ============================================================ */
/**
 * Affiche un tableau HTML complet à partir d'un tableau d'objets Candidature
 * Utilise une boucle + condition (structure d'itération et de sélection)
 * @param Candidature[] $candidatures
 */
function afficherTableauCandidatures(array $candidatures) : void {
    if (empty($candidatures)) {
        echo "<p class='no-result'>Aucune candidature trouvée.</p>";
        return;
    }
    echo "
    <table class='data-table'>
        <thead>
            <tr>
                <th>#</th>
                <th>Poste</th>
                <th>Entreprise</th>
                <th>Ville</th>
                <th>Domaine</th>
                <th>Date</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>";

    // Structure d'itération + sélection
    foreach ($candidatures as $c) {
        // Sélection : mettre en évidence les candidatures acceptées
        if ($c->getStatut() === 'Accepté') {
            echo "<tr class='row-accepte'>";
        } else {
            echo "<tr>";
        }
        echo "
                <td>" . $c->getId() . "</td>
                <td>" . htmlspecialchars($c->getPoste()) . "</td>
                <td>" . htmlspecialchars($c->getEntreprise()) . "</td>
                <td>" . htmlspecialchars($c->getVille()) . "</td>
                <td>" . htmlspecialchars($c->getDomaine()) . "</td>
                <td>" . htmlspecialchars($c->getDateCandidature()) . "</td>
                <td><span class='status-badge " . $c->getStatutClass() . "'>" . htmlspecialchars($c->getStatut()) . "</span></td>
            </tr>";
    }

    echo "
        </tbody>
    </table>
    <p class='count-info'>Total : <strong>" . count($candidatures) . "</strong> candidature(s)</p>";
}

/* ============================================================
   TRAITEMENT DU FORMULAIRE D'AJOUT (POST depuis espace-etudiant)
   ============================================================ */
$message = '';
$erreur  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'ajouter') {

    // Récupération + nettoyage des données
    $poste      = trim($_POST['poste']      ?? '');
    $entreprise = trim($_POST['entreprise'] ?? '');
    $ville      = trim($_POST['ville']      ?? '');
    $domaine    = trim($_POST['domaine']    ?? '');
    $statut     = trim($_POST['statut']     ?? 'En cours');

    // ---- REVALIDATION PHP (obligatoire même si JS valide côté client) ----
    $erreurs = [];
    if (empty($poste))      $erreurs[] = "Le poste est obligatoire.";
    if (empty($entreprise)) $erreurs[] = "L'entreprise est obligatoire.";
    if (empty($ville))      $erreurs[] = "La ville est obligatoire.";
    if (empty($domaine))    $erreurs[] = "Le domaine est obligatoire.";
    if (!in_array($statut, ['En cours', 'Accepté', 'Refusé'])) $erreurs[] = "Statut invalide.";

    if (empty($erreurs)) {
        // Insertion via requête préparée nommée (Partie IV req. 4b)
        $sql  = "INSERT INTO candidature (poste, entreprise, date_candidature, statut, ville, domaine)
                 VALUES (:poste, :entreprise, :date_candidature, :statut, :ville, :domaine)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':poste'            => $poste,
            ':entreprise'       => $entreprise,
            ':date_candidature' => date('Y-m-d'),
            ':statut'           => $statut,
            ':ville'            => $ville,
            ':domaine'          => $domaine,
        ]);
        $message = "✅ Candidature ajoutée avec succès !";
        header('Refresh: 2; URL=../espace-etudiant.html');
    } else {
        $erreur = implode('<br>', $erreurs);
    }
}

// Charger le tableau d'objets
$candidatures = chargerToutesCandidatures($pdo);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Étudiant — InternShip Manager</title>
    <link rel="stylesheet" href="../styles.css">
    <style>
        .data-table { width:100%; border-collapse:collapse; margin:1rem 0; }
        .data-table th, .data-table td { padding:10px 14px; border:1px solid #ddd; text-align:left; font-size:14px; }
        .data-table th { background:#2c3e50; color:#fff; }
        .data-table tr:nth-child(even) { background:#f9f9f9; }
        .row-accepte { background:#d4edda !important; }
        .status-badge { padding:4px 10px; border-radius:12px; font-size:12px; font-weight:600; }
        .status-accepte  { background:#d4edda; color:#155724; }
        .status-refuse   { background:#f8d7da; color:#721c24; }
        .status-en-cours { background:#fff3cd; color:#856404; }
        .alert-success { background:#d4edda; color:#155724; padding:12px; border-radius:6px; margin:1rem 0; }
        .alert-error   { background:#f8d7da; color:#721c24; padding:12px; border-radius:6px; margin:1rem 0; }
        .count-info { color:#666; font-size:13px; margin-top:8px; }
        .no-result { color:#888; font-style:italic; }
        .form-card { background:#fff; padding:24px; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,.08); margin-bottom:2rem; }
        .form-row { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
        .form-group { margin-bottom:14px; }
        .form-group label { display:block; font-weight:600; margin-bottom:4px; font-size:14px; }
        .form-group input, .form-group select { width:100%; padding:8px 12px; border:1px solid #ccc; border-radius:6px; font-size:14px; }
        .btn-submit { background:#2c3e50; color:#fff; border:none; padding:10px 24px; border-radius:6px; cursor:pointer; font-size:14px; }
        .btn-submit:hover { background:#34495e; }
        h2 { color:#2c3e50; margin-bottom:1rem; }
        .page-wrapper { max-width:1100px; margin:0 auto; padding:2rem; }
    </style>
</head>
<body>
<div class="page-wrapper">
    <h1>🎓 Espace Étudiant — Mes Candidatures</h1>

    <!-- Formulaire d'ajout d'une candidature -->
    <div class="form-card">
        <h2>➕ Ajouter une candidature</h2>

        <?php if ($message): ?>
            <div class="alert-success"><?= $message ?></div>
        <?php endif; ?>
        <?php if ($erreur): ?>
            <div class="alert-error">⚠️ <?= $erreur ?></div>
        <?php endif; ?>

        <form method="POST" action="traitement-candidature.php" onsubmit="return validerFormAjout()">
            <input type="hidden" name="action" value="ajouter">
            <div class="form-row">
                <div class="form-group">
                    <label for="poste">Poste *</label>
                    <input type="text" id="poste" name="poste" minlength="3" maxlength="150" required>
                    <span id="err-poste" style="color:red;font-size:12px;"></span>
                </div>
                <div class="form-group">
                    <label for="entreprise">Entreprise *</label>
                    <input type="text" id="entreprise" name="entreprise" required>
                    <span id="err-entreprise" style="color:red;font-size:12px;"></span>
                </div>
                <div class="form-group">
                    <label for="ville">Ville *</label>
                    <input type="text" id="ville" name="ville" required>
                    <span id="err-ville" style="color:red;font-size:12px;"></span>
                </div>
                <div class="form-group">
                    <label for="domaine">Domaine</label>
                    <select id="domaine" name="domaine">
                        <option value="Informatique">Informatique</option>
                        <option value="Design">Design</option>
                        <option value="Marketing">Marketing</option>
                        <option value="Finance">Finance</option>
                        <option value="Autre">Autre</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="statut">Statut</label>
                    <select id="statut" name="statut">
                        <option value="En cours">En cours</option>
                        <option value="Accepté">Accepté</option>
                        <option value="Refusé">Refusé</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn-submit">Enregistrer la candidature</button>
        </form>
    </div>

    <!-- Tableau des candidatures (classe + tableau d'objets + fonction) -->
    <div class="form-card">
        <h2>📋 Liste des candidatures</h2>
        <?php afficherTableauCandidatures($candidatures); ?>
    </div>
</div>

<script>
// Validation JS côté client (Partie IV req. 1)
function validerFormAjout() {
    let ok = true;
    const poste = document.getElementById('poste').value.trim();
    const errPoste = document.getElementById('err-poste');
    if (poste.length < 3) {
        errPoste.innerText = '⚠️ Le poste doit faire au moins 3 caractères.';
        ok = false;
    } else { errPoste.innerText = ''; }

    const entreprise = document.getElementById('entreprise').value.trim();
    const errEnt = document.getElementById('err-entreprise');
    if (entreprise === '') {
        errEnt.innerText = '⚠️ L\'entreprise est obligatoire.';
        ok = false;
    } else { errEnt.innerText = ''; }

    const ville = document.getElementById('ville').value.trim();
    const errVille = document.getElementById('err-ville');
    if (ville === '') {
        errVille.innerText = '⚠️ La ville est obligatoire.';
        ok = false;
    } else { errVille.innerText = ''; }

    return ok;
}
</script>
</body>
</html>
