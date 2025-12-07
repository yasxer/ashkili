<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

require_once '../db.php';
require_once 'includes/language_manager.php';

$admin_nom = $_SESSION['admin_nom'] ?? 'Admin';
$admin_role = $_SESSION['admin_role'] ?? 'Support';
$success = $_SESSION['success_message'] ?? '';
$error = $_SESSION['error_message'] ?? '';
unset($_SESSION['success_message'], $_SESSION['error_message']);

// Page variables for header
$page_title = "Gestion des Clients";
$page_icon = "fas fa-users";
$page_heading = "Ashkili - " . t('client_management', 'Gestion des Clients');

// Get all clients
$clients_query = "SELECT * FROM clients ORDER BY date_inscription DESC";
$clients_result = $conn->query($clients_query);

include 'includes/header.php';
?>

<style>

        .page-header h2 {
            font-size: 28px;
            color: #333;
        }

        .header-actions {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .btn-add {
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            color: white;
            padding: 12px 25px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .btn-add:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(76, 175, 80, 0.4);
        }

        .btn-delete {
            /* background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); */
            color: white;
            padding: 12px 25px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .btn-delete:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(220, 53, 69, 0.4);
        }

        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .table-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: var(--primary-gradient);
        }

        thead th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }

        tbody tr {
            border-bottom: 1px solid #f0f0f0;
            transition: all 0.3s ease;
        }

        tbody tr:hover {
            background: #fffef5;
        }

        tbody td {
            padding: 15px;
            color: #666;
            font-size: 14px;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .btn-edit {
            background: linear-gradient(135deg, #2196F3 0%, #1976D2 100%);
            color: white;
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-edit:hover {
            transform: translateY(-2px);
            box-shadow: 0 3px 10px rgba(33, 150, 243, 0.3);
        }

        .btn-delete {
            background: linear-gradient(135deg, #f44336 0%, #e53935 100%);
            color: white;
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-delete:hover {
            transform: translateY(-2px);
            box-shadow: 0 3px 10px rgba(244, 67, 54, 0.3);
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
        }

        .modal-content {
            background: white;
            margin: 5% auto;
            padding: 30px;
            border-radius: 12px;
            width: 90%;
            max-width: 500px;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .modal-header h3 {
            font-size: 22px;
            color: #333;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 28px;
            color: #999;
            cursor: pointer;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        .btn-submit {
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: all 0.3s ease;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(76, 175, 80, 0.4);
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }

        .empty-state i {
            font-size: 64px;
            margin-bottom: 20px;
            color: #ddd;
        }
    </style>

    <div class="container">
        <div class="page-header">
            <h2><i class="fas fa-address-book"></i> <?php echo t('client_management', 'Gestion des Clients'); ?></h2>
            <div class="header-actions">
                <a href="add_client.php" class="btn-add">
                    <i class="fas fa-user-plus"></i> <?php echo t('add_new_client', 'Ajouter un Client'); ?>
                </a>
                <a href="delete_client.php" class="btn-delete">
                    <i class="fas fa-user-times"></i> <?php echo t('delete_clients', 'Supprimer un Client'); ?>
                </a>
            </div>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="table-card">
            <?php if ($clients_result && $clients_result->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th><?php echo t('id', 'ID'); ?></th>
                            <th><?php echo t('name', 'Nom'); ?></th>
                            <th><?php echo t('username', 'Nom d\'Utilisateur'); ?></th>
                            <th><?php echo t('phone', 'Téléphone'); ?></th>
                            <th><?php echo t('registration_date', 'Date d\'Inscription'); ?></th>
                            <th><?php echo t('actions', 'Actions'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($client = $clients_result->fetch_assoc()): ?>
                            <tr>
                                <td><strong>#<?php echo $client['client_id']; ?></strong></td>
                                <td><?php echo htmlspecialchars($client['nom']); ?></td>
                                <td><?php echo htmlspecialchars($client['username'] ?? $client['email'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($client['telephone'] ?: '-'); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($client['date_inscription'])); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-edit" onclick="openEditModal(<?php echo $client['client_id']; ?>, '<?php echo htmlspecialchars($client['nom']); ?>', '<?php echo htmlspecialchars($client['username'] ?? $client['email'] ?? ''); ?>', '<?php echo htmlspecialchars($client['telephone']); ?>')">
                                            <i class="fas fa-edit"></i> <?php echo t('edit', 'Modifier'); ?>
                                        </button>
                                        
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-user-slash"></i>
                    <h3><?php echo t('no_clients_found', 'Aucun client trouvé'); ?></h3>
                    <p><?php echo t('add_first_client', 'Commencez par ajouter un nouveau client'); ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function openEditModal(id, nom, username, telephone) {
            // Implement edit functionality
            alert('<?php echo t('edit_functionality_message', 'Fonctionnalité de modification à implémenter pour le client: '); ?>' + nom);
        }

        
    </script>

<?php 
$conn->close();
include 'includes/footer.php'; 
?>
