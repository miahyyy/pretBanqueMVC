<?php
require_once __DIR__ . '/../models/TypePret.php';

class TypePretController {
    public static function getAll() {
        $types = TypePret::getAll();
        Flight::json($types);
    }

    public static function add() {
        $data = Flight::request()->data;
        $result = TypePret::add($data);
        Flight::json($result, 201);
    }
}
