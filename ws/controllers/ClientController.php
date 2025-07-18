<?php
require_once __DIR__ . '/../models/Client.php';

class ClientController {
    public static function getAll() {
        $clients = Client::getAll();
        Flight::json($clients);
    }

    public static function add() {
        $data = Flight::request()->data;
        $result = Client::add($data);
        Flight::json($result, 201);
    }

    public static function login() {
        $data = Flight::request()->data;
        $result = Client::login($data);
        Flight::json($result);
    }

}
