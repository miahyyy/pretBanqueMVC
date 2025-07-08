<?php
require_once __DIR__ . '/../models/Remboursement.php';

class RemboursementController {
    public static function process() {
        $data = Flight::request()->data;
        $result = Remboursement::process($data);
        Flight::json($result);
    }
}
?>