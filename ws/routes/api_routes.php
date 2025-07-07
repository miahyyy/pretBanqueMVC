<?php
require_once __DIR__ . '/../controllers/EtablissementFinancierController.php';
require_once __DIR__ . '/../controllers/TypePretController.php';
require_once __DIR__ . '/../controllers/ClientController.php';
require_once __DIR__ . '/../controllers/PretController.php';
require_once __DIR__ . '/../controllers/InteretController.php';

// GESTION DE L'ÉTABLISSEMENT FINANCIER
Flight::route('GET /ef/fonds', ['EtablissementFinancierController', 'getFonds']);
Flight::route('POST /ef/fonds', ['EtablissementFinancierController', 'addFonds']);

// GESTION DES TYPES DE PRÊT
Flight::route('GET /types-pret', ['TypePretController', 'getAll']);
Flight::route('POST /types-pret', ['TypePretController', 'add']);

// GESTION DES CLIENTS
Flight::route('GET /clients', ['ClientController', 'getAll']);
Flight::route('POST /clients', ['ClientController', 'add']);
Flight::route('POST /client/login', ['ClientController', 'login']);

// GESTION DES PRÊTS
Flight::route('GET /prets', ['PretController', 'getAll']);
Flight::route('GET /prets/client/@id_client', ['PretController', 'getByClientId']);
Flight::route('POST /prets', ['PretController', 'add']);
Flight::route('POST /prets/@id/approuver', ['PretController', 'approuver']);
Flight::route('POST /prets/@id/rejeter', ['PretController', 'rejeter']);
Flight::route('POST /prets/@id/annuler', ['PretController', 'annuler']);

// GESTION DES INTÉRÊTS
Flight::route('GET /interets-gagnes', ['InteretController', 'getInterets']);
