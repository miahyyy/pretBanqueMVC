<?php
require_once __DIR__ . '/../db.php';

class Admin {
    public static function loginAdmin($data){
        $db = getDB();

    if (empty($data->nom) || empty($data->mdp)) {
        send_json(['error' => 'Nom ou mot de passe manquant.'], 400);
        return;
    }

    $stmt = $db->prepare("SELECT id FROM admin WHERE nom = ? AND mdp = ?");
    $stmt->execute([$data->nom, $data->mdp]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin) {
        send_json(['success' => true]);
    } else {
        send_json(['error' => 'Identifiants invalides.'], 401);
    }
    }
}