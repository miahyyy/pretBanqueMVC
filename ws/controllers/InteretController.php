<?php
require_once __DIR__ . '/../models/Interet.php';

class InteretController {
    public static function getInterets() {
        $date_debut = Flight::request()->query['date_debut'];
        $date_fin = Flight::request()->query['date_fin'];

        $interets = Interet::getInteretsGagnes($date_debut, $date_fin);
        Flight::json($interets);
    }
}
