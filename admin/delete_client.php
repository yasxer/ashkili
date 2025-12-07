<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

require_once '../db.php';

$admin_id = $_SESSION['admin_id'];
$admin_nom = $_SESSION['admin_nom'] ?? 'Admin';
$admin_role = $_SESSION['admin_role'] ?? 'Support';

$success_message = '';
$error_message = '';

// Set page variables for header
$page_title = 'Supprimer Client - Ashkili Admin';
$page_icon = 'fas fa-user-times';
$page_heading = 'Ashkili - Supprimer Client';

// Handle client deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_client'])) {
    $client_id = (int) $_POST['client_id'];
    
    if ($client_id > 0) {
        $conn->begin_transaction();
        try {
            // Check if client has any colis
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM colis WHERE client_id = ?");
            $stmt->bind_param("i", $client_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $colis_count = $result->fetch_assoc()['count'];
            $stmt->close();
            
            // Check if client has any reclamations
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM reclamations WHERE client_id = ?");
            $stmt->bind_param("i", $client_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $reclamations_count = $result->fetch_assoc()['count'];
            $stmt->close();
            
            if ($colis_count > 0 || $reclamations_count > 0) {
                // Client has data, ask for confirmation or force delete
                if (isset($_POST['force_delete'])) {
                    // Delete related data first
                    
                    // Delete responses related to client's reclamations
                    $checkTable = $conn->query("SHOW TABLES LIKE 'reponses_reclamations'");
                    if ($checkTable && $checkTable->num_rows > 0) {
                        $stmt = $conn->prepare("DELETE rr FROM reponses_reclamations rr 
                                               INNER JOIN reclamations r ON rr.reclamation_id = r.reclamation_id 
                                               WHERE r.client_id = ?");
                        $stmt->bind_param("i", $client_id);
                        $stmt->execute();
                        $stmt->close();
                    }
                    
                    // Delete reclamations
                    $stmt = $conn->prepare("DELETE FROM reclamations WHERE client_id = ?");
                    $stmt->bind_param("i", $client_id);
                    $stmt->execute();
                    $stmt->close();
                    
                    // Delete historique for client's colis
                    $checkHist = $conn->query("SHOW TABLES LIKE 'historique_statuts_colis'");
                    if ($checkHist && $checkHist->num_rows > 0) {
                        $stmt = $conn->prepare("DELETE h FROM historique_statuts_colis h 
                                               INNER JOIN colis c ON h.colis_id = c.colis_id 
                                               WHERE c.client_id = ?");
                        $stmt->bind_param("i", $client_id);
                        $stmt->execute();
                        $stmt->close();
                    }
                    
                    // Delete colis
                    $stmt = $conn->prepare("DELETE FROM colis WHERE client_id = ?");
                    $stmt->bind_param("i", $client_id);
                    $stmt->execute();
                    $stmt->close();
                    
                    // Finally delete the client
                    $stmt = $conn->prepare("DELETE FROM clients WHERE client_id = ?");
                    $stmt->bind_param("i", $client_id);
                    $stmt->execute();
                    $stmt->close();
                    
                    $conn->commit();
                    $success_message = "Client et toutes ses données associées supprimés avec succès.";
                } else {
                    $error_message = "Ce client a {$colis_count} colis et {$reclamations_count} réclamations. Confirmez la suppression pour supprimer toutes les données associées.";
                    $_POST['confirm_needed'] = true;
                }
            } else {
                // No related data, safe to delete
                $stmt = $conn->prepare("DELETE FROM clients WHERE client_id = ?");
                $stmt->bind_param("i", $client_id);
                if ($stmt->execute()) {
                    $success_message = "Client supprimé avec succès.";
                } else {
                    $error_message = "Erreur lors de la suppression du client.";
                }
                $stmt->close();
                $conn->commit();
            }
        } catch (Exception $e) {
            $conn->rollback();
            $error_message = "Erreur lors de la suppression: " . $e->getMessage();
        }
    }
}

// Get all clients with their statistics
$query = "SELECT c.*, 
          (SELECT COUNT(*) FROM colis WHERE client_id = c.client_id) as total_colis,
          (SELECT COUNT(*) FROM reclamations WHERE client_id = c.client_id) as total_reclamations
          FROM clients c 
          ORDER BY c.nom ASC";

$result = $conn->query($query);
$clients = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $clients[] = $row;
    }
}

// Include header
include 'includes/header.php';
?>

<style>
    .btn-back {
            background: var(--card-bg);
            color: var(--text-main);
            border: 2px solid var(--border-color);
            padding: 10px 20px;
            border-radius: 10px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-back:hover {
            border-color: var(--primary-color);
            background: var(--hover-bg);
        }

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border-left: 4px solid var(--primary-color);
        }

        .table-card {
            background: var(--card-bg);
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .table-header {
            padding: 20px 25px;
            border-bottom: 2px solid var(--border-color);
            display: flex;
            justify-content: between;
            align-items: center;
        }

        .table-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--text-main);
        }

        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: var(--table-header-bg);
        }

        th {
            padding: 15px 20px;
            text-align: left;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        tbody tr {
            border-bottom: 1px solid var(--border-color);
            transition: all 0.3s ease;
        }

        tbody tr:hover {
            background: var(--hover-bg);
        }

        td {
            padding: 18px 20px;
            font-size: 14px;
            color: var(--text-main);
        }

        .client-info {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .client-name {
            font-weight: 600;
            color: var(--text-main);
        }

        .client-username {
            font-size: 12px;
            color: var(--text-muted);
        }

        .stats-info {
            display: flex;
            gap: 15px;
            font-size: 12px;
            color: #666;
        }

        .stat-item {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .btn-delete {
            background: #dc3545;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .btn-delete:hover {
            background: #c82333;
            transform: translateY(-1px);
        }

        .btn-delete.force {
            background: #6c757d;
        }

        .btn-delete.force:hover {
            background: #5a6268;
        }

        .danger-zone {
            background: #fff5f5;
            border: 2px solid #fed7d7;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .danger-title {
            color: #c53030;
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .danger-text {
            color: #742a2a;
            font-size: 14px;
            line-height: 1.5;
        }

        .empty-state {
            padding: 60px 20px;
            text-align: center;
        }

        .empty-state i {
            font-size: 64px;
            color: #ddd;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            color: #666;
            font-size: 18px;
            margin-bottom: 10px;
        }

        .empty-state p {
            color: #999;
            font-size: 14px;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
        }

        .modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: var(--card-bg);
            border-radius: 15px;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            padding: 25px 30px 20px;
            border-bottom: 2px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            font-size: 18px;
            color: var(--text-main);
            font-weight: 600;
        }

        .btn-close {
            background: transparent;
            border: none;
            font-size: 20px;
            color: var(--text-muted);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-close:hover {
            color: var(--text-main);
        }

        .modal-body {
            padding: 20px 30px 30px;
        }

        .confirm-text {
            color: var(--text-secondary);
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .modal-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        .btn-cancel {
            background: #6c757d;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-cancel:hover {
            background: #5a6268;
        }

        .btn-confirm {
            background: #dc3545;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-confirm:hover {
            background: #c82333;
        }

        @media (max-width: 768px) {
            .navbar {
                padding: 15px 20px;
            }

            .container {
                padding: 0 15px;
            }

            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            table {
                font-size: 12px;
            }

            th, td {
                padding: 12px 10px;
            }
        }
</style>

<div class="container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h2 style="color: var(--text-main); font-size: 28px; font-weight: 600; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-user-times"></i>
            Supprimer Client
        </h2>
        <a href="manage_clients.php" class="btn-back">
            <i class="fas fa-arrow-left"></i>
            Retour à la gestion des clients
        </a>
    </div>

        <div class="danger-zone">
            <div class="danger-title">
                <i class="fas fa-exclamation-triangle"></i>
                Zone Dangereuse
            </div>
            <div class="danger-text">
                La suppression d'un client est une action <strong>irréversible</strong>. 
                Tous les colis, réclamations et données associées seront définitivement supprimés.
                Procédez avec précaution.
            </div>
        </div>

        <?php if ($success_message): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i>
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <div class="table-card">
            <div class="table-header">
                <h3 class="table-title">Liste des clients à supprimer</h3>
            </div>

            <div class="table-container">
                <?php if (empty($clients)): ?>
                    <div class="empty-state">
                        <i class="fas fa-users"></i>
                        <h3>Aucun client</h3>
                        <p>Il n'y a aucun client enregistré dans le système</p>
                    </div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Client</th>
                                <th>Contact</th>
                                <th>Statistiques</th>
                                <th>Date d'inscription</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($clients as $client): ?>
                                <tr>
                                    <td>
                                        <div class="client-info">
                                            <span class="client-name"><?php echo htmlspecialchars($client['nom']); ?></span>
                                            <span class="client-username">@<?php echo htmlspecialchars($client['username']); ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if (!empty($client['telephone'])): ?>
                                            <i class="fas fa-phone"></i> <?php echo htmlspecialchars($client['telephone']); ?>
                                        <?php else: ?>
                                            <em style="color: #999;">Non renseigné</em>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="stats-info">
                                            <span class="stat-item">
                                                <i class="fas fa-boxes"></i>
                                                <?php echo $client['total_colis']; ?> colis
                                            </span>
                                            <span class="stat-item">
                                                <i class="fas fa-exclamation-circle"></i>
                                                <?php echo $client['total_reclamations']; ?> réclamations
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <?php echo date('d/m/Y', strtotime($client['date_inscription'])); ?>
                                    </td>
                                    <td>
                                        <button class="btn-delete" onclick="confirmDelete(<?php echo $client['client_id']; ?>, '<?php echo htmlspecialchars($client['nom']); ?>', <?php echo $client['total_colis']; ?>, <?php echo $client['total_reclamations']; ?>)">
                                            <i class="fas fa-trash"></i>
                                            Supprimer
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Confirmer la suppression</h3>
                <button class="btn-close" onclick="closeModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="confirm-text" id="confirmText">
                    <!-- Dynamic content will be inserted here -->
                </div>
                
                <form method="POST" action="">
                    <input type="hidden" name="client_id" id="deleteClientId">
                    <input type="hidden" name="delete_client" value="1">
                    <input type="hidden" name="force_delete" id="forceDelete" value="0">
                    
                    <div class="modal-actions">
                        <button type="button" class="btn-cancel" onclick="closeModal()">
                            <i class="fas fa-times"></i> Annuler
                        </button>
                        <button type="submit" class="btn-confirm" id="confirmBtn">
                            <i class="fas fa-trash"></i> Confirmer la suppression
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../js/theme.js"></script>
    <script>
        function confirmDelete(clientId, clientName, colisCount, reclamationsCount) {
            const modal = document.getElementById('deleteModal');
            const confirmText = document.getElementById('confirmText');
            const deleteClientId = document.getElementById('deleteClientId');
            const forceDelete = document.getElementById('forceDelete');
            const confirmBtn = document.getElementById('confirmBtn');
            
            deleteClientId.value = clientId;
            
            if (colisCount > 0 || reclamationsCount > 0) {
                confirmText.innerHTML = `
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Attention!</strong> Ce client possède des données associées.
                    </div>
                    <p><strong>Client:</strong> ${clientName}</p>
                    <p><strong>Données à supprimer:</strong></p>
                    <ul style="margin: 10px 0; padding-left: 20px;">
                        <li>${colisCount} colis (avec leur historique)</li>
                        <li>${reclamationsCount} réclamations (avec leurs réponses)</li>
                    </ul>
                    <p style="color: #dc3545; font-weight: 600;">
                        Cette action supprimera définitivement le client et toutes ses données associées.
                    </p>
                `;
                forceDelete.value = '1';
                confirmBtn.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Supprimer tout';
                confirmBtn.style.background = '#dc3545';
            } else {
                confirmText.innerHTML = `
                    <p>Êtes-vous sûr de vouloir supprimer le client <strong>${clientName}</strong>?</p>
                    <p style="color: #666;">Ce client n'a pas de données associées.</p>
                `;
                forceDelete.value = '0';
                confirmBtn.innerHTML = '<i class="fas fa-trash"></i> Confirmer la suppression';
                confirmBtn.style.background = '#dc3545';
            }
            
            modal.classList.add('active');
        }

        function closeModal() {
            document.getElementById('deleteModal').classList.remove('active');
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('deleteModal');
            if (event.target === modal) {
                closeModal();
            }
        }
    </script>

<?php 
$conn->close();
include 'includes/footer.php'; 
?>

