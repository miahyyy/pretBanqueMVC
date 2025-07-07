<?php
require_once __DIR__ . '/../db.php';

class Admin {
    public static function loginAdmin($nom, $mdp){
        $db = getDB();

        if (empty($nom) || empty($mdp)) {
            return false;
        }

        $stmt = $db->prepare("SELECT id FROM Admin WHERE nom = ? AND mdp = ?");
        $stmt->execute([$nom, $mdp]);
        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }
}