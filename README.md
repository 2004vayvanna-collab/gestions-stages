# InternShip Manager — Gestion des stages

A web application for exploring internship opportunities and managing internship applications, with student and company pages and PHP/MySQL database operations.

## Tech stack

- HTML5, CSS3, JavaScript
- PHP with PDO
- MySQL (local development with XAMPP)

## Main pages

- `index.html` — Home
- `offres.html` — Internship offers
- `espace-etudiant.html` — Student area
- `espace-entreprise.html` — Company area
- `questionnaire.html` — Forms/questionnaire
- `contact.html` — Contact
- `about.html` — About
- `funpage.html` — Interactive page

PHP scripts in `php/` demonstrate application processing and database CRUD (create, read, update, delete).

## Run locally

1. Install [XAMPP](https://www.apachefriends.org/) with Apache, PHP and MySQL.
2. Copy the `gestion-stages` folder into XAMPP's `htdocs` directory.
3. Start **Apache** and **MySQL** in the XAMPP Control Panel.
4. Open phpMyAdmin at `http://localhost/phpmyadmin` and import `sql/database.sql` to create the `gestion_stage` database and sample data (or run the script using MySQL).
5. Check the local connection settings in `php/connexion.php`. **Never commit real database passwords or production credentials.**
6. Open `http://localhost/gestion-stages/`.

> PHP features require a PHP-enabled server and a configured MySQL database. Opening HTML files directly from disk is not sufficient for backend features. GitHub Pages cannot execute PHP or host MySQL.

## Project structure

```text
gestion-stages/
├── index.html
├── about.html
├── contact.html
├── espace-entreprise.html
├── espace-etudiant.html
├── offres.html
├── questionnaire.html
├── funpage.html
├── styles.css
├── js/
├── php/
├── sql/
└── README.md
```

## Security note

This is an educational project. Review the backend's authentication, input validation, error handling, and database privileges before using it in production or accepting real personal data.
