<?php
require_once __DIR__ . '/../../fpdf186/fpdf.php'; // Assurez-vous que le chemin est correct
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../models/Pret.php';
require_once __DIR__ . '/../models/TypePret.php';

class PdfController {
    public static function generate() {
        $pret_id = intval(Flight::request()->data->pret_id);

        if ($pret_id <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'ID de prêt invalide.']);
            exit;
        }

        $pdo = getDB();

        // Récupération des informations du prêt
        $sqlPret = "
            SELECT p.montant, p.date_demande, p.assurance, p.delai_premier_remboursement, p.duree_mois,
                   c.nom AS client_nom, tp.nom AS type_pret_nom, tp.taux AS type_pret_taux
            FROM Pret p
            JOIN Client c ON p.id_client = c.id
            JOIN TypePret tp ON p.id_type_pret = tp.id
            WHERE p.id = :id
        ";
        $stmtPret = $pdo->prepare($sqlPret);
        $stmtPret->execute(['id' => $pret_id]);
        $pret = $stmtPret->fetch(PDO::FETCH_ASSOC);

        if (!$pret) {
            http_response_code(404);
            echo json_encode(['error' => 'Prêt non trouvé.']);
            exit;
        }

        // Simulation du tableau d'amortissement
        $tableau_amortissement = Pret::simulerPret(
            $pret['montant'],
            $pret['type_pret_taux'],
            $pret['duree_mois'],
            $pret['assurance'],
            $pret['delai_premier_remboursement']
        );

        if (isset($tableau_amortissement['error'])) {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur lors de la simulation du prêt: ' . $tableau_amortissement['error']]);
            exit;
        }

        // Génération du PDF
        $fileName = 'tableau_amortissement_pret_' . $pret_id . '.pdf';

        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, 'Tableau d\'Amortissement', 0, 1, 'C');
        $pdf->Ln(10);

        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 8, 'Client: ' . $pret['client_nom'], 0, 1);
        $pdf->Cell(0, 8, 'Type de Prêt: ' . $pret['type_pret_nom'], 0, 1);
        $pdf->Cell(0, 8, 'Montant du Prêt: ' . number_format($pret['montant'], 2, ',', ' ') . ' €', 0, 1);
        $pdf->Cell(0, 8, 'Taux Annuel: ' . $pret['type_pret_taux'] . '%', 0, 1);
        $pdf->Cell(0, 8, 'Durée: ' . $pret['duree_mois'] . ' mois', 0, 1);
        $pdf->Cell(0, 8, 'Assurance: ' . $pret['assurance'] . '%', 0, 1);
        $pdf->Cell(0, 8, 'Délai avant 1er Remboursement: ' . $pret['delai_premier_remboursement'] . ' mois', 0, 1);
        $pdf->Ln(10);

        $pdf->SetFont('Arial', 'B', 10);
        $headers = ['Période', 'Solde Début', 'Annuité', 'Intérêt Payé', 'Principal Payé', 'Solde Fin'];
        $widths  = [20, 30, 30, 30, 30, 30];
        foreach ($headers as $i => $h) {
            $pdf->Cell($widths[$i], 7, $h, 1, 0, 'C');
        }
        $pdf->Ln();

        $pdf->SetFont('Arial', '', 9);
        foreach ($tableau_amortissement as $ligne) {
            $pdf->Cell($widths[0], 6, $ligne['periode'], 1, 0, 'C');
            $pdf->Cell($widths[1], 6, number_format($ligne['solde_debut'], 2, ',', ' '), 1, 0, 'R');
            $pdf->Cell($widths[2], 6, number_format($ligne['annuite'], 2, ',', ' '), 1, 0, 'R');
            $pdf->Cell($widths[3], 6, number_format($ligne['interet_paye'], 2, ',', ' '), 1, 0, 'R');
            $pdf->Cell($widths[4], 6, number_format($ligne['principal_paye'], 2, ',', ' '), 1, 0, 'R');
            $pdf->Cell($widths[5], 6, number_format($ligne['solde_fin'], 2, ',', ' '), 1, 0, 'R');
            $pdf->Ln();
        }

        $pdf->Output('D', $fileName);
        exit;
    }
}