<?php
require_once __DIR__ . '/../models/Admin.php';
require_once __DIR__ . '/../helpers/Utils.php';



class AdminController {
    public static function login() {
        $data = Flight::request()->data->getData();
        $nom = $data['nom'] ?? '';
        $mdp = $data['mdp'] ?? '';

        if (empty($nom) || empty($mdp)) {
            Flight::json(['success' => false, 'message' => 'Nom ou mot de passe manquant'], 400);
            return;
        }

        if (Admin::loginAdmin($nom, $mdp)) {
            Flight::json(['success' => true, 'message' => 'Connexion réussie']);
        } else {
            Flight::json(['success' => false, 'message' => 'Identifiants incorrects'], 401);
        }
    }
}