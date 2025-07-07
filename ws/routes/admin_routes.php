<?php
require_once __DIR__ . '/../controllers/AdminController.php';

Flight::route('POST /login', ['AdminController', 'login']);