Système d'authentification PHP
Application web d'authentification développée en PHP avec une base de données MySQL, conteneurisée via Docker.
Stack technique

PHP 8.2 avec Apache
MySQL 8
phpMyAdmin pour la gestion de la base de données
Docker & Docker Compose pour la conteneurisation
PDO pour les interactions avec la base de données

Fonctionnalités

Inscription avec nom, prénom, date de naissance et pays de résidence
Connexion par email ou nom d'utilisateur
Page de profil modifiable (informations personnelles, mot de passe)
Mots de passe hachés en bcrypt
Protection contre les injections SQL via les requêtes préparées
Système de rôles admin / user
Interface d'administration pour gérer les comptes (activation/désactivation avec horodatage)
Protection des pages contre le cache navigateur après déconnexion
