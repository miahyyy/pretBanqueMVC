<?php
require_once __DIR__ . '/../models/EtablissementFinancier.php';

class EtablissementFinancierController {
    public static function getFonds() {
        $fonds = EtablissementFinancier::getFonds();
        Flight::json($fonds);
    }

    public static function addFonds() {
        $data = Flight::request()->data;
        $result = EtablissementFinancier::addFonds($data);
        Flight::json($result);
    }

    public static function getHistoriqueFonds(){
         $data = Flight::request()->data;
        $result = EtablissementFinancier::historiqueFond($data);
        Flight::json($result);
    }
}
