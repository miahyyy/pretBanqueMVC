<?php
require_once __DIR__ . '/../db.php';

class Interet {
    public static function getInteretsGagnes($date_debut, $date_fin) {
        $db = getDB();
        
        $query = "
            SELECT p.montant, p.date_demande, t.taux
            FROM Pret p
            JOIN TypePret t ON p.id_type_pret = t.id
            WHERE p.statut = 'ACCORDE'
        ";
        
        $stmt = $db->query($query);
        $prets = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $interetsParMois = [];
        $start = new DateTime($date_debut);
        $end = new DateTime($date_fin);
        $end->modify('first day of next month');

        $interval = new DateInterval('P1M');
        $period = new DatePeriod($start, $interval, $end);

        foreach ($period as $dt) {
            $mois = $dt->format('Y-m');
            $interetsParMois[$mois] = 0;
        }

        foreach ($prets as $pret) {
            $date_pret = new DateTime($pret['date_demande']);
            $taux_mensuel = $pret['taux'] / 100 / 12;
            $interet_mensuel = $pret['montant'] * $taux_mensuel;

            foreach ($period as $dt) {
                if ($date_pret <= $dt) {
                    $mois = $dt->format('Y-m');
                    if (isset($interetsParMois[$mois])) {
                        $interetsParMois[$mois] += $interet_mensuel;
                    }
                }
            }
        }

        $result = [];
        foreach ($interetsParMois as $mois => $total) {
            $result[] = ['mois' => $mois, 'interet' => round($total, 2)];
        }

        return $result;
    }
}
