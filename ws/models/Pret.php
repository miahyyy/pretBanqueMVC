<?php
require_once __DIR__ . '/../db.php';

class Pret {
    public static function getAll() {
        $db = getDB();
        $query = "
            SELECT p.id, c.nom AS client, tp.nom AS type_pret, p.montant, p.date_demande, p.statut
            FROM Pret p
            JOIN Client c ON p.id_client = c.id
            JOIN TypePret tp ON p.id_type_pret = tp.id
            ORDER BY p.date_demande DESC, p.id DESC
        ";
        $statement = $db->query($query);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getByClientId($id_client) {
        $db = getDB();
        $query = "
            SELECT p.id, tp.nom AS type_pret, p.montant, p.date_demande, p.statut
            FROM Pret p
            JOIN TypePret tp ON p.id_type_pret = tp.id
            WHERE p.id_client = ?
            ORDER BY p.date_demande DESC, p.id DESC
        ";
        $statement = $db->prepare($query);
        $statement->execute([$id_client]);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function add($data) {
        $db = getDB();
        if (empty($data->id_client) || empty($data->id_type_pret) || !isset($data->montant) || !is_numeric($data->montant) || $data->montant <= 0) {
            http_response_code(400);
            return ['error' => 'Données de prêt invalides.'];
        }

        $statement = $db->prepare("INSERT INTO Pret (id_client, id_type_pret, montant, date_demande) VALUES (?, ?, ?, CURDATE())");
        $statement->execute([$data->id_client, $data->id_type_pret, $data->montant]);
        $new_id = $db->lastInsertId();

        return ['id' => $new_id, 'message' => 'Demande de prêt enregistrée.'];
    }

    public static function approuver($id) {
        $db = getDB();
        $db->beginTransaction();

        try {
            $stmt_pret = $db->prepare("SELECT id_client, montant, statut FROM Pret WHERE id = ?");
            $stmt_pret->execute([$id]);
            $pret = $stmt_pret->fetch(PDO::FETCH_ASSOC);

            if (!$pret) {
                throw new Exception("Prêt non trouvé.");
            }
            if ($pret['statut'] !== 'EN ATTENTE') {
                throw new Exception("Le prêt n'est pas en attente de validation.");
            }

            $stmt_fonds = $db->query("SELECT fond FROM EtablissementFinancier LIMIT 1");
            $fonds_actuels = $stmt_fonds->fetchColumn();

            if ($fonds_actuels < $pret['montant']) {
                throw new Exception("Fonds insuffisants pour approuver ce prêt.");
            }

            $stmt_update_pret = $db->prepare("UPDATE Pret SET statut = 'ACCORDE' WHERE id = ?");
            $stmt_update_pret->execute([$id]);

            $stmt_update_fonds = $db->prepare("UPDATE EtablissementFinancier SET fond = fond - ?");
            $stmt_update_fonds->execute([$pret['montant']]);
            
            $stmt_update_historique_fonds = $db->prepare("INSERT INTO historique_EtablissementFinancier (fond, type) VALUES (?, 'SORTIE')");
            $stmt_update_historique_fonds->execute([$pret['montant']]);

            $stmt_update_client = $db->prepare("UPDATE Client SET compte = compte + ? WHERE id = ?");
            $stmt_update_client->execute([$pret['montant'], $pret['id_client']]);

            $db->commit();
            return ['message' => 'Prêt approuvé avec succès.'];

        } catch (Exception $e) {
            $db->rollBack();
            http_response_code(400);
            return ['error' => $e->getMessage()];
        }
    }

    public static function rejeter($id) {
        $db = getDB();
        $stmt_pret = $db->prepare("SELECT statut FROM Pret WHERE id = ?");
        $stmt_pret->execute([$id]);
        $pret = $stmt_pret->fetch(PDO::FETCH_ASSOC);

        if (!$pret) {
            http_response_code(404);
            return ['error' => 'Prêt non trouvé.'];
        }
        if ($pret['statut'] !== 'EN ATTENTE') {
            http_response_code(400);
            return ['error' => "Le prêt n'est pas en attente de validation."];
        }

        $statement = $db->prepare("UPDATE Pret SET statut = 'REFUSE' WHERE id = ?");
        $statement->execute([$id]);

        return ['message' => 'Prêt rejeté.'];
    }

    public static function annuler($id) {
        $db = getDB();
        $stmt_pret = $db->prepare("SELECT statut FROM Pret WHERE id = ?");
        $stmt_pret->execute([$id]);
        $pret = $stmt_pret->fetch(PDO::FETCH_ASSOC);

        if (!$pret) {
            http_response_code(404);
            return ['error' => 'Prêt non trouvé.'];
        }
        if ($pret['statut'] !== 'EN ATTENTE') {
            http_response_code(400);
            return ['error' => "Le prêt n'est pas en attente de validation."];
        }

        $statement = $db->prepare("UPDATE Pret SET statut = 'ANNULE' WHERE id = ?");
        $statement->execute([$id]);

        return ['message' => 'Prêt annulé.'];
    }
}
