<?php
require_once __DIR__ . '/../models/Admin.php';
require_once __DIR__ . '/../helpers/Utils.php';



class AdminController {
    public static function login($data){
        $log = Admin::loginAdmin($data);
        Flight::json($log);
    }
}