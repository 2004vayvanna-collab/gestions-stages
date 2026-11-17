/**
 * questionnaire.js
 * Validation JavaScript pour la page Questionnaire — InternShip Manager
 * ENSI 2025/2026 — Partie III + IV
 * CORRECTION : Le formulaire envoie maintenant vers PHP après validation JS
 */

/* ============================================================
   FONCTIONS DE VALIDATION
   ============================================================ */

function validerNonVide(valeur) {
  return valeur.trim().length > 0;
}

function validerEmail(email) {
  var regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return regex.test(email.trim());
}

function validerRadio(nomGroupe) {
  var radios = document.querySelectorAll('input[name="' + nomGroupe + '"]');
  for (var i = 0; i < radios.length; i++) {
    if (radios[i].checked) return true;
  }
  return false;
}

function validerCheckbox(nomGroupe) {
  var boxes = document.querySelectorAll('input[name="' + nomGroupe + '"]:checked');
  return boxes.length > 0;
}

function validerLongueur(valeur, min) {
  return valeur.trim().length >= min;
}

/* ============================================================
   AFFICHAGE DES ERREURS
   ============================================================ */

function afficherErreur(idErreur, message) {
  var el = document.getElementById(idErreur);
  if (el) {
    el.textContent = message;
    el.style.display = "block";
  }
}

function masquerErreur(idErreur) {
  var el = document.getElementById(idErreur);
  if (el) {
    el.textContent = "";
    el.style.display = "none";
  }
}

/* ============================================================
   VALIDATION PRINCIPALE
   ============================================================ */

function validerFormulaire() {
  var estValide = true;

  /* Champ 1 : Nom */
  var nom = document.getElementById("q-nom").value;
  if (!validerNonVide(nom)) {
    afficherErreur("err-nom", "⚠️ Le nom est obligatoire.");
    estValide = false;
  } else {
    masquerErreur("err-nom");
  }

  /* Champ 2 : Email + regex */
  var email = document.getElementById("q-email").value;
  if (!validerNonVide(email)) {
    afficherErreur("err-email", "⚠️ L'email est obligatoire.");
    estValide = false;
  } else if (!validerEmail(email)) {
    afficherErreur("err-email", "⚠️ Format d'email invalide (ex: nom@domaine.tn).");
    estValide = false;
  } else {
    masquerErreur("err-email");
  }

  /* Champ 3 : Satisfaction (radio) */
  if (!validerRadio("satisfaction")) {
    afficherErreur("err-satisfaction", "⚠️ Veuillez sélectionner votre niveau de satisfaction.");
    estValide = false;
  } else {
    masquerErreur("err-satisfaction");
  }

  /* Champ 4 : Fonctionnalités (checkbox) */
  if (!validerCheckbox("fonctionnalites")) {
    afficherErreur("err-fonctionnalites", "⚠️ Veuillez cocher au moins une fonctionnalité.");
    estValide = false;
  } else {
    masquerErreur("err-fonctionnalites");
  }

  /* Champ 5 : Commentaire (min 20 caractères) */
  var commentaire = document.getElementById("q-commentaire").value;
  if (!validerLongueur(commentaire, 20)) {
    afficherErreur("err-commentaire", "⚠️ Le commentaire doit contenir au moins 20 caractères.");
    estValide = false;
  } else {
    masquerErreur("err-commentaire");
  }

  // SI VALIDE → le formulaire s'envoie vers PHP (action="php/traitement-questionnaire.php")
  // SI INVALIDE → bloque l'envoi et affiche les erreurs
  return estValide;
}

/* ============================================================
   INITIALISATION
   ============================================================ */
document.addEventListener("DOMContentLoaded", function () {

  var formulaire = document.getElementById("form-questionnaire");
  if (!formulaire) return;

  /* 
   * CORRECTION IMPORTANTE :
   * On utilise onsubmit="return validerFormulaire()" dans le HTML
   * PAS e.preventDefault() ici — sinon PHP ne reçoit jamais les données !
   * Si validerFormulaire() retourne true → envoi vers PHP
   * Si validerFormulaire() retourne false → bloque l'envoi
   */
  formulaire.addEventListener("submit", function (e) {
    var valide = validerFormulaire();
    if (!valide) {
      e.preventDefault(); // Bloque seulement si invalide
    }
    // Si valide → le formulaire part vers php/traitement-questionnaire.php
  });

  /* Validation en temps réel sur l'email */
  var champEmail = document.getElementById("q-email");
  if (champEmail) {
    champEmail.addEventListener("blur", function () {
      var email = champEmail.value;
      if (validerNonVide(email) && !validerEmail(email)) {
        afficherErreur("err-email", "⚠️ Format d'email invalide.");
      } else {
        masquerErreur("err-email");
      }
    });
  }

  /* Validation en temps réel sur le commentaire */
  var champCommentaire = document.getElementById("q-commentaire");
  if (champCommentaire) {
    champCommentaire.addEventListener("input", function () {
      var restants = 20 - champCommentaire.value.trim().length;
      var compteur = document.getElementById("compteur-commentaire");
      if (compteur) {
        compteur.textContent = restants > 0
          ? "Encore " + restants + " caractères minimum"
          : "✅ Longueur suffisante";
        compteur.style.color = restants > 0 ? "var(--orange)" : "var(--green)";
      }
    });
  }
});

