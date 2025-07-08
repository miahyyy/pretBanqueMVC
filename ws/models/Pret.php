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
        if (empty($data->id_client) || empty($data->id_type_pret) || !isset($data->montant) || !is_numeric($data->montant) || $data->montant <= 0 || empty($data->date_demande) || empty($data->duree_mois) || !is_numeric($data->duree_mois) || $data->duree_mois <= 0) {
            http_response_code(400);
            return ['error' => 'Données de prêt invalides.'];
        }

        // Default values for new fields
        $assurance = isset($data->assurance) && is_numeric($data->assurance) ? $data->assurance : 0;
        $delai_premier_remboursement = isset($data->delai_premier_remboursement) && is_numeric($data->delai_premier_remboursement) ? $data->delai_premier_remboursement : 0;
        $est_valide = 0; // Default to not validated

        $statement = $db->prepare("INSERT INTO Pret (id_client, id_type_pret, montant, date_demande, assurance, delai_premier_remboursement, est_valide, duree_mois) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $statement->execute([$data->id_client, $data->id_type_pret, $data->montant, $data->date_demande, $assurance, $delai_premier_remboursement, $est_valide, $data->duree_mois]);
        $new_id = $db->lastInsertId();

        return ['id' => $new_id, 'message' => 'Demande de prêt enregistrée.'];
    }

    // public static function simulerPret($montant_pret, $taux_annuel, $duree_mois, $assurance_pourcentage , $delai_mois) {
    //     if ($montant_pret <= 0 || $taux_annuel < 0 || $duree_mois <= 0) {
    //         return ['error' => 'Paramètres de simulation invalides.'];
    //     }

    //     $taux_mensuel = ($taux_annuel / 100) / 12;
    //     $tableau_amortissement = [];
    //     $solde_restant = $montant_pret;

    //     // Calculate effective principal including insurance
    //     // Assuming insurance is a one-time cost added to the principal
    //     $montant_avec_assurance = $montant_pret * (1 + ($assurance_pourcentage / 100));
    //     $solde_restant = $montant_avec_assurance;

    //     // Apply delay: interest accrues during the delay period
    //     for ($i = 0; $i < $delai_mois; $i++) {
    //         $interet_accru = $solde_restant * $taux_mensuel;
    //         $solde_restant += $interet_accru;
    //     }

    //     // Calculate constant annuity
    //     if ($taux_mensuel > 0) {
    //         $annuite = $montant_pret * (($taux_mensuel * pow(1 + $taux_mensuel, $duree_mois)) / (pow(1 + $taux_mensuel, $duree_mois) - 1));
    //     } else {
    //         $annuite = $montant_pret / $duree_mois; // Simple division if no interest
    //     }

    //     for ($i = 1; $i <= $duree_mois; $i++) {
    //         $interet_periode = $solde_restant * $taux_mensuel;
    //         $principal_rembourse = $annuite - $interet_periode;
    //         $solde_restant -= $principal_rembourse;

    //         // Adjust last payment to avoid floating point inaccuracies
    //         if ($i == $duree_mois) {
    //             $principal_rembourse += $solde_restant;
    //             $annuite = $interet_periode + $principal_rembourse;
    //             $solde_restant = 0;
    //         }

    //         $tableau_amortissement[] = [
    //             'periode' => $i,
    //             'solde_debut' => round($montant_pret + $principal_rembourse, 2), // Recalculer le solde de début pour la précision
    //             'annuite' => round($annuite, 2),
    //             'interet_paye' => round($interet_periode, 2),
    //             'principal_paye' => round($principal_rembourse, 2),
    //             'solde_fin' => round($solde_restant, 2)
    //         ];
    //     }

    //     return $tableau_amortissement;
    // }

    public static function simulerPret($montant_pret, $taux_annuel, $duree_mois, $assurance_pourcentage, $delai_mois) {
    if ($montant_pret <= 0 || $taux_annuel < 0 || $duree_mois <= 0) {
        return ['error' => 'Paramètres de simulation invalides.'];
    }

    $taux_mensuel = ($taux_annuel / 100) / 12;
    $tableau_amortissement = [];
    $solde_restant = $montant_pret; // Initialement, juste le montant emprunté sans assurance
    
    // Calcul de l'assurance mensuelle (ajoutée à l'annuité)
    $assurance_mensuelle = ($montant_pret * ($assurance_pourcentage / 100)) / 12;

    // Calcul des intérêts pendant le délai de différé
    $interets_delai = 0;
    for ($i = 0; $i < $delai_mois; $i++) {
        $interets_delai += $solde_restant * $taux_mensuel;
    }

    // Calcul de l'annuité constante (hors assurance)
    if ($taux_mensuel > 0) {
        $annuite = $montant_pret * (($taux_mensuel * pow(1 + $taux_mensuel, $duree_mois)) / (pow(1 + $taux_mensuel, $duree_mois) - 1));
    } else {
        $annuite = $montant_pret / $duree_mois; // Simple division si pas d'intérêt
    }

    // Annuité totale avec assurance
    $annuite_totale = $annuite + $assurance_mensuelle;

    for ($i = 1; $i <= $duree_mois; $i++) {
        $solde_debut = $solde_restant;
        $interet_periode = $solde_restant * $taux_mensuel;
        
        // Pour la première période, on ajoute les intérêts accumulés pendant le délai
        if ($i == 1 && $delai_mois > 0) {
            $interet_periode += $interets_delai;
        }

        $principal_rembourse = $annuite - $interet_periode;
        
        // Ajustement pour la dernière période
        if ($i == $duree_mois) {
            $principal_rembourse = $solde_restant;
            $annuite = $interet_periode + $principal_rembourse;
            $annuite_totale = $annuite + $assurance_mensuelle;
        }

        $solde_restant -= $principal_rembourse;

        $tableau_amortissement[] = [
            'periode' => $i,
            'solde_debut' => round($solde_debut, 2),
            'annuite' => round($annuite_totale, 2),
            'interet_paye' => round($interet_periode, 2),
            'assurance_paye' => round($assurance_mensuelle, 2),
            'principal_paye' => round($principal_rembourse, 2),
            'solde_fin' => round($solde_restant, 2)
        ];
    }

    return $tableau_amortissement;
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

    public static function valider($id) {
        $db = getDB();
        $stmt_pret = $db->prepare("SELECT est_valide FROM Pret WHERE id = ?");
        $stmt_pret->execute([$id]);
        $pret = $stmt_pret->fetch(PDO::FETCH_ASSOC);

        if (!$pret) {
            http_response_code(404);
            return ['error' => 'Prêt non trouvé.'];
        }
        if ($pret['est_valide'] == 1) {
            http_response_code(400);
            return ['error' => 'Le prêt est déjà validé.'];
        }

        $statement = $db->prepare("UPDATE Pret SET est_valide = 1 WHERE id = ?");
        $statement->execute([$id]);

        return ['message' => 'Prêt validé avec succès.'];
    }
}
