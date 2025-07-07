<?php
require_once __DIR__ . '/../../fpdf186/fpdf.php'; // Assurez-vous que le chemin est correct
require_once __DIR__ . '/../db.php';


class PdfController {
    public static function generate() {
        $client_id = intval(Flight::request()->data->client_id);
        $dest_dir = Flight::request()->data->dest_dir ?? 'public/temp'; // default directory relative to ws/

        if ($client_id <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'ID client invalide.']);
            exit;
        }

        // Validate dest_dir to prevent directory traversal
        if (preg_match('/\.\./', $dest_dir)) {
            http_response_code(400);
            echo json_encode(['error' => 'Chemin de destination invalide.']);
            exit;
        }

        $pdo = getDB();

        // Récupération des informations du client
        $sqlClient = "
            SELECT c.nom AS client_nom, a.nom AS assurance_nom, c.compte
            FROM Client c
            LEFT JOIN Assurance a ON c.id_assurance = a.id
            WHERE c.id = :id
        ";
        $stmtClient = $pdo->prepare($sqlClient);
        $stmtClient->execute(['id' => $client_id]);
        $client = $stmtClient->fetch();
        if (!$client) {
            http_response_code(404);
            echo json_encode(['error' => 'Client non trouvé.']);
            exit;
        }

        // Récupération des prêts du client
        $sqlLoans = "
            SELECT p.montant, r.montant_par_moi, p.date_demande, r.date_debut, r.nombre_mois
            FROM Pret p
            JOIN Remboursement r ON r.id_Pret = p.id
            WHERE p.id_client = :id
        ";
        $stmtLoans = $pdo->prepare($sqlLoans);
        $stmtLoans->execute(['id' => $client_id]);
        $loans = $stmtLoans->fetchAll();

        // Génération du PDF
        $fileName = 'rapport_pret_client_' . $client_id . '.pdf';
        $filePath = __DIR__ . '../public/temp' . $dest_dir . '/' . $fileName;

        // Remove directory creation since PDF is output directly to browser
        // $dir = dirname($filePath);
        // if (!is_dir($dir)) {
        //     // Use recursive mkdir and check for errors
        //     if (!mkdir($dir, 0777, true) && !is_dir($dir)) {
        //         http_response_code(500);
        //         echo json_encode(['error' => 'Impossible de créer le répertoire de destination.']);
        //         exit;
        //     }
        // }

        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, 'banque be leaza', 0, 1, 'C');
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 8, 'Nom du client : ' . $client['client_nom'], 0, 1);
        $pdf->Cell(0, 8, 'Assurance : ' . ($client['assurance_nom'] ?? 'N/A'), 0, 1);
        $pdf->Cell(0, 8, 'Compte : ' . number_format($client['compte'], 2, ',', ' '), 0, 1);
        $pdf->Ln(5);
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 8, 'Liste des prêts', 0, 1);

        $pdf->SetFont('Arial', 'B', 12);
        $headers = ['Montant', 'Remb. / mois', 'Date demande', 'Début remb.', 'Nb mois'];
        $widths  = [30, 40, 40, 40, 20];
        foreach ($headers as $i => $h) {
            $pdf->Cell($widths[$i], 8, $h, 1);
        }
        $pdf->Ln();

        $pdf->SetFont('Arial', '', 12);
        foreach ($loans as $loan) {
            $pdf->Cell($widths[0], 8, number_format($loan['montant'], 2, ',', ' '), 1);
            $pdf->Cell($widths[1], 8, number_format($loan['montant_par_moi'], 2, ',', ' '), 1);
            $pdf->Cell($widths[2], 8, $loan['date_demande'], 1);
            $pdf->Cell($widths[3], 8, $loan['date_debut'], 1);
            $pdf->Cell($widths[4], 8, $loan['nombre_mois'], 1);
            $pdf->Ln();
        }

        // Output PDF directly to browser for download, allowing user to choose destination directory
        $pdf->Output('D', $fileName);
        exit;

http_response_code(405);
echo json_encode(['error' => 'Méthode non autorisée.']);
    

    }
}