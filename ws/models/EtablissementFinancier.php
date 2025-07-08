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

    public static function getFondsClient($id_client) {
        $db = getDB();
        $statement = $db->prepare("SELECT compte FROM Fond_Client WHERE id_client = ?");
        $statement->execute([$id_client]);
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            return $result;
        } else {
            return ['compte' => 0];
        }
    }

    public static function addFondsClient($data) {
        $db = getDB();
        if (!isset($data->compte) || !is_numeric($data->compte) || $data->compte <= 0 ) {
            http_response_code(400);
            return ['error' => 'Le montant doit être un nombre positif.'];
        }
        if (!isset($data->id_client) || !is_numeric($data->id_client) || $data->id_client <= 0) {
            http_response_code(400);
            return ['error' => 'ID client invalide.'];
        }

        // Check if client exists in Fond_Client
        $checkStmt = $db->prepare("SELECT compte FROM Fond_Client WHERE id_client = ?");
        $checkStmt->execute([$data->id_client]);
        $exists = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if ($exists) {
            // Update existing record
            $statement = $db->prepare("UPDATE Fond_Client SET compte = compte + ? WHERE id_client = ?");
            $statement->execute([$data->compte, $data->id_client]);
        } else {
            // Insert new record
            $statement = $db->prepare("INSERT INTO Fond_Client (compte, id_client) VALUES (?, ?)");
            $statement->execute([$data->compte, $data->id_client]);
        }

        return ['message' => 'Fonds ajoutés avec succès.'];
    }
}
