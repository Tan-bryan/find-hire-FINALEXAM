<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
function isLoggedIn() {
    return isset($_SESSION['user']) && !empty($_SESSION['user']);
}

function isHR() {
    return isset($_SESSION['user']) && $_SESSION['user']['role'] === 'HR';
}

function redirect($url) {
    header("Location: $url");
    exit;
}
