<?php
require_once __DIR__ . '/../models/Pret.php';

class PretController {
    public static function getAll() {
        $prets = Pret::getAll();
        Flight::json($prets);
    }

    public static function getByClientId($id_client) {
        $prets = Pret::getByClientId($id_client);
        Flight::json($prets);
    }

    public static function add() {
        $data = Flight::request()->data;
        $result = Pret::add($data);
        Flight::json($result, 201);
    }

    public static function approuver($id) {
        $result = Pret::approuver($id);
        Flight::json($result);
    }

    public static function rejeter($id) {
        $result = Pret::rejeter($id);
        Flight::json($result);
    }

    public static function annuler($id) {
        $result = Pret::annuler($id);
        Flight::json($result);
    }

    public static function valider($id) {
        $result = Pret::valider($id);
        Flight::json($result);
    }

    public static function simuler() {
        $data = Flight::request()->data;
        $montant_pret = $data->montant_pret;
        $taux_annuel = $data->taux_annuel;
        $duree_mois = $data->duree_mois;
        $assurance_pourcentage = isset($data->assurance_pourcentage) ? $data->assurance_pourcentage : 0;
        $delai_mois = isset($data->delai_mois) ? $data->delai_mois : 0;

        $simulation = Pret::simulerPret($montant_pret, $taux_annuel, $duree_mois, $assurance_pourcentage, $delai_mois);

        if (isset($simulation['error'])) {
            Flight::json($simulation, 400);
        } else {
            Flight::json($simulation);
        }
    }
}
