<?php
require_once __DIR__ . '/../db.php';

class EtablissementFinancier {
    public static function getFonds() {
        $db = getDB();
        $statement = $db->query("SELECT * FROM EtablissementFinancier ORDER BY id DESC LIMIT 1");
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            return $result;
        } else {
            return ['fond' => 0];
        }
    }

    public static function addFonds($data) {
        $db = getDB();
        if (!isset($data->montant) || !is_numeric($data->montant) || $data->montant <= 0 ) {
            http_response_code(400);
            return ['error' => 'Le montant doit être un nombre positif.'];
        }
        if(empty($data->description)){
             return ['error' => 'Remplissez la description'];
        }

        $statement = $db->prepare("UPDATE EtablissementFinancier SET fond = fond + ?");
        $statement_historique = $db->prepare("INSERT INTO historique_EtablissementFinancier (fond, type, description) VALUES (?, 'ENTREE', ?)");
        $statement_historique->execute([$data->montant, $data->description]);
        $statement->execute([$data->montant]);

        return ['message' => 'Fonds ajoutés avec succès.'];
    }

    public static function historiqueFond($data){
        $db = getDB();
        $statement = $db->query("SELECT * FROM historique_EtablissementFinancier ORDER BY id DESC");
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
}
