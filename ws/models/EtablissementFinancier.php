<?php
require_once __DIR__ . '/../db.php';

class EtablissementFinancier {
    public static function getFonds() {
        $db = getDB();
        $statement = $db->query("SELECT fond FROM EtablissementFinancier ORDER BY id DESC LIMIT 1");
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            return $result;
        } else {
            return ['fond' => 0];
        }
    }

    public static function addFonds($data) {
        $db = getDB();
        if (!isset($data->montant) || !is_numeric($data->montant) || $data->montant <= 0) {
            http_response_code(400);
            return ['error' => 'Le montant doit être un nombre positif.'];
        }

        $statement = $db->prepare("UPDATE EtablissementFinancier SET fond = fond + ?");
        $statement_historique = $db->prepare("INSERT INTO historique_EtablissementFinancier (fond, type) VALUES (?, 'ENTREE')");
        $statement_historique->execute([$data->montant]);
        $statement->execute([$data->montant]);

        return ['message' => 'Fonds ajoutés avec succès.'];
    }
}
