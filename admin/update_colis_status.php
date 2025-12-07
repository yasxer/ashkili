<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

require_once '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $colis_id = intval($_POST['colis_id'] ?? 0);
    $nouveau_statut = trim($_POST['nouveau_statut'] ?? '');
    $lieu = trim($_POST['lieu'] ?? '');
    $commentaire = trim($_POST['commentaire'] ?? '');

    if ($colis_id && $nouveau_statut) {
        // Update colis status
        $stmt = $conn->prepare("UPDATE colis SET statut_actuel = ? WHERE colis_id = ?");
        $stmt->bind_param("si", $nouveau_statut, $colis_id);
        
        if ($stmt->execute()) {
            // Add to history
            $stmt = $conn->prepare("INSERT INTO historique_statuts_colis (colis_id, statut, lieu, date_heure) VALUES (?, ?, ?, NOW())");
            $description = $nouveau_statut . ($commentaire ? ' - ' . $commentaire : '');
            $stmt->bind_param("iss", $colis_id, $description, $lieu);
            $stmt->execute();
            
            $_SESSION['success_message'] = "Statut mis à jour avec succès !";
        } else {
            $_SESSION['error_message'] = "Erreur lors de la mise à jour du statut.";
        }
        $stmt->close();
    }
}

$conn->close();
header("Location: manage_colis.php");
exit();
?>
