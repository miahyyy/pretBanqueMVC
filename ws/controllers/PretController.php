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
}
