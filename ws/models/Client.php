<?php
require_once __DIR__ . '/../db.php';

class Client {
    public static function getAll() {
        $db = getDB();
        $statement = $db->query("SELECT * FROM Client");
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function add($data) {
        $db = getDB();
        if (empty($data->nom)) {
            http_response_code(400);
            return ['error' => 'Le nom du client est requis.'];
        }

        if (empty($data->mdp)) {
            http_response_code(400);
            return ['error' => 'Le mot de passe est requis.'];
        }

        $statement = $db->prepare("INSERT INTO Client (nom, mdp) VALUES (?, ?)");
        $statement->execute([$data->nom, $data->mdp]);
        $statement->fetchAll(PDO::FETCH_ASSOC);
        $new_id = $db->lastInsertId();

        return ['id' => $new_id, 'nom' => $data->nom, 'mdp' => $data->mdp];
    }

    public static function login($data) {
        $db = getDB();
        if (empty($data->nom) || empty($data->mdp)) {
            http_response_code(400);
            return ['error' => 'Nom ou mot de passe manquant.'];
        }

        $stmt = $db->prepare("SELECT id, nom FROM Client WHERE nom = ? AND mdp = ?");
        $stmt->execute([$data->nom, $data->mdp]);
        $client = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($client) {
            return $client;
        } else {
            http_response_code(401);
            return ['error' => 'Identifiants invalides.'];
        }
    }
}
