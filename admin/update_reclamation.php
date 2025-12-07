<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

require_once '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reclamation_id = $_POST['reclamation_id'] ?? null;
    $statut_reclamation = $_POST['statut_reclamation'] ?? null;
    $reponse_admin = $_POST['reponse_admin'] ?? null;
    $admin_id = $_SESSION['admin_id'];

    if ($reclamation_id && $statut_reclamation !== null) {
        // Insert the admin response into `reponses_reclamations` and update the main reclamation status.
        // We set `lu_par_client` = 0 on the new response so the client will be notified.
        $conn->begin_transaction();
        try {
            // Check if reponses_reclamations table exists
            $checkTable = $conn->query("SHOW TABLES LIKE 'reponses_reclamations'");
            $responseTableExists = $checkTable && $checkTable->num_rows > 0;
            
            if ($responseTableExists && !empty($reponse_admin)) {
                // Insert response
                $stmtIns = $conn->prepare("INSERT INTO reponses_reclamations (reclamation_id, admin_id, contenu_reponse, lu_par_client, date_reponse) VALUES (?, ?, ?, 0, NOW())");
                if (!$stmtIns) throw new Exception('Prepare failed: ' . $conn->error);
                $stmtIns->bind_param('iis', $reclamation_id, $admin_id, $reponse_admin);
                if (!$stmtIns->execute()) throw new Exception('Insert failed: ' . $stmtIns->error);
                $stmtIns->close();
            }

            // Check if lu_par_admin column exists
            $checkCol = $conn->query("SHOW COLUMNS FROM reclamations LIKE 'lu_par_admin'");
            $luParAdminExists = $checkCol && $checkCol->num_rows > 0;
            
            // Update main reclamation status
            if ($luParAdminExists) {
                $stmtUpd = $conn->prepare("UPDATE reclamations SET statut_reclamation = ?, date_mise_a_jour = NOW(), lu_par_admin = 1 WHERE reclamation_id = ?");
            } else {
                $stmtUpd = $conn->prepare("UPDATE reclamations SET statut_reclamation = ?, date_mise_a_jour = NOW() WHERE reclamation_id = ?");
            }
            if (!$stmtUpd) throw new Exception('Prepare failed: ' . $conn->error);
            $stmtUpd->bind_param('si', $statut_reclamation, $reclamation_id);
            if (!$stmtUpd->execute()) throw new Exception('Update failed: ' . $stmtUpd->error);
            $stmtUpd->close();

            $conn->commit();
            $_SESSION['success_message'] = "Réponse enregistrée et statut mis à jour.";
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['error_message'] = "Erreur lors de l'enregistrement: " . $e->getMessage();
        }
    } else {
        $_SESSION['error_message'] = "Données invalides.";
    }
}

$conn->close();
header("Location: reclamations.php");
exit();
?>
