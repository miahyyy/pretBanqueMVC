<?php
require 'vendor/autoload.php';
require 'db.php';
require 'routes/admin_routes.php';
require 'routes/api_routes.php';

// --- Configuration CORS ---
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Gérer les requêtes pre-flight OPTIONS
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// --- Fonctions utilitaires ---
function send_json($data, $status_code = 200) {
    http_response_code($status_code);
    Flight::json($data);
}

Flight::start();
?>