<?php
require_once __DIR__ . '/includes/auth.php';

// /dashboard.php redirige vers la page de connexion
// Si déjà connecté, va directement au profil
if (isLoggedIn()) {
    redirect('/profile.php');
} else {
    redirect('/login.php');
}

