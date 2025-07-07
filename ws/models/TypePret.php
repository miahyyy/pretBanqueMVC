<?php
require_once __DIR__ . '/../db.php';

class TypePret {
    public static function getAll() {
        $db = getDB();
        $statement = $db->query("SELECT * FROM TypePret");
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function add($data) {
        $db = getDB();
        if (empty($data->nom) || !isset($data->taux) || !is_numeric($data->taux) || $data->taux < 0) {
            http_response_code(400);
            return ['error' => 'Données invalides pour le type de prêt.'];
        }

        $statement = $db->prepare("INSERT INTO TypePret (nom, taux) VALUES (?, ?)");
        $statement->execute([$data->nom, $data->taux]);
        $new_id = $db->lastInsertId();

        return ['id' => $new_id, 'nom' => $data->nom, 'taux' => $data->taux];
    }
}
