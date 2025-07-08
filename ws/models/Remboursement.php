<?php
require_once __DIR__ . '/../db.php';

class Remboursement {
    public static function process($data) {
        $db = getDB();
        $db->beginTransaction();

        try {
            // 1. Récupérer les informations du prêt
            $stmt_pret = $db->prepare("SELECT * FROM Pret WHERE id = ? FOR UPDATE");
            $stmt_pret->execute([$data->id_pret]);
            $pret = $stmt_pret->fetch(PDO::FETCH_ASSOC);

            if (!$pret || $pret['statut'] !== 'ACCORDE') {
                throw new Exception("Le prêt n'est pas valide pour un remboursement.");
            }

            // 2. Vérifier le solde du client
            $stmt_client_fond = $db->prepare("SELECT compte FROM Fond_Client WHERE id_client = ? FOR UPDATE");
            $stmt_client_fond->execute([$pret['id_client']]);
            $compte_client = $stmt_client_fond->fetchColumn();

            if ($compte_client < $pret['mensualite']) {
                throw new Exception("Fonds insuffisants pour effectuer le remboursement.");
            }

            // 3. Effectuer les transactions
            $nouveau_montant_restant = $pret['montant_restant'] - $pret['mensualite'];
            if ($nouveau_montant_restant < 0) {
                $nouveau_montant_restant = 0;
            }

            // Décrémenter le compte du client
            $stmt_update_client = $db->prepare("UPDATE Fond_Client SET compte = compte - ? WHERE id_client = ?");
            $stmt_update_client->execute([$pret['mensualite'], $pret['id_client']]);

            // Incrémenter les fonds de l'établissement
            $stmt_update_ef = $db->prepare("UPDATE EtablissementFinancier SET fond = fond + ?");
            $stmt_update_ef->execute([$pret['mensualite']]);

            // Mettre à jour le prêt
            $stmt_update_pret = $db->prepare("UPDATE Pret SET montant_restant = ? WHERE id = ?");
            $stmt_update_pret->execute([$nouveau_montant_restant, $data->id_pret]);

            // Historiser le remboursement
            $stmt_insert_remboursement = $db->prepare("INSERT INTO Remboursement (id_pret, date_remboursement, montant_rembourse) VALUES (?, NOW(), ?)");
            $stmt_insert_remboursement->execute([$data->id_pret, $pret['mensualite']]);

            // 4. Mettre à jour le statut du prêt si entièrement remboursé
            if ($nouveau_montant_restant <= 0) {
                $stmt_statut_pret = $db->prepare("UPDATE Pret SET statut = 'REMBOURSE' WHERE id = ?");
                $stmt_statut_pret->execute([$data->id_pret]);
            }

            $db->commit();
            return ['success' => true, 'message' => 'Remboursement effectué avec succès.'];
        } catch (Exception $e) {
            $db->rollBack();
            http_response_code(400);
            return ['error' => $e->getMessage()];
        }
    }
}
?>