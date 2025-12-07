<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

require_once '../db.php';
require_once 'includes/language_manager.php';

$admin_id = $_SESSION['admin_id'];
$admin_nom = $_SESSION['admin_nom'] ?? 'Admin';
$admin_role = $_SESSION['admin_role'] ?? 'Support';

// Automatically update all "en attente" reclamations to "en cours" when admin accesses the page
$updateQuery = "UPDATE reclamations SET 
                statut_reclamation = 'en cours', 
                date_mise_a_jour = NOW()
                WHERE statut_reclamation IN ('en attente', 'جديد', 'nouveau')";
$conn->query($updateQuery);

// Get all reclamations with client and colis info
$query = "SELECT r.*, 
          c.nom as client_nom, 
          COALESCE(c.username, '') as client_email,
          col.code_suivi,
          col.location
          FROM reclamations r 
          LEFT JOIN clients c ON r.client_id = c.client_id 
          LEFT JOIN colis col ON r.colis_id = col.colis_id 
          ORDER BY r.date_creation DESC";

$result = $conn->query($query);
$reclamations = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $reclamations[] = $row;
    }
}

// Get unique types and locations for filter options
$types = array_unique(array_filter(array_column($reclamations, 'type_reclamation')));
$locations = array_unique(array_filter(array_column($reclamations, 'location')));

// Set page variables for header
$page_title = t('complaint_management', 'Gestion des Réclamations') . " - Admin";
$page_icon = "fas fa-exclamation-triangle";
$page_heading = "Ashkili - " . t('complaint_management', 'Gestion des Réclamations');

// Include header
include 'includes/header.php';
?>

<style>
        .table-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .table-header {
            padding: 20px 25px;
            border-bottom: 2px solid #f0f2f5;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table-title {
            font-size: 18px;
            font-weight: 600;
            color: #333;
        }

        .filter-tabs {
            display: flex;
            gap: 10px;
        }

        .btn-toggle-filters {
            background: var(--primary-gradient);
            color: #333;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-toggle-filters:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(244, 196, 48, 0.3);
        }

        .advanced-filters {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
        }

        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .filter-group label {
            font-size: 12px;
            font-weight: 600;
            color: #555;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .filter-group select,
        .filter-group input {
            padding: 10px 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            transition: all 0.3s ease;
            background: white;
        }

        .filter-group select:focus,
        .filter-group input:focus {
            outline: none;
            border-color: var(--primary-color);
            background: #fffef5;
        }

        .btn-clear-filters {
            background: #dc3545;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: auto;
        }

        .btn-clear-filters:hover {
            background: #c82333;
            transform: translateY(-1px);
        }

        .filter-results {
            background: white;
            padding: 12px 15px;
            border-radius: 8px;
            border: 1px solid #e9ecef;
            font-size: 14px;
            color: #666;
            text-align: center;
        }

        .filter-tab {
            padding: 8px 16px;
            border: none;
            background: transparent;
            color: #666;
            font-size: 14px;
            cursor: pointer;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .filter-tab.active {
            background: #FFF9E6;
            color: var(--primary-color);
            font-weight: 600;
        }

        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: #f8f9fa;
        }

        th {
            padding: 15px 20px;
            text-align: left;
            font-size: 13px;
            font-weight: 600;
            color: #555;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        tbody tr {
            border-bottom: 1px solid #f0f2f5;
            transition: all 0.3s ease;
        }

        tbody tr:hover {
            background: #f8f9fa;
        }

        tbody tr.highlight-new {
            background: #FFF9E6;
        }

        tbody tr.highlight-new:hover {
            background: #FFF4CC;
        }

        td {
            padding: 18px 20px;
            font-size: 14px;
            color: #333;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }

        .status-en-attente {
            background: #FFF9E6;
            color: var(--primary-color);
        }

        .status-en-cours {
            background: #E3F2FD;
            color: #2196F3;
        }

        .status-resolue {
            background: #E8F5E9;
            color: #4CAF50;
        }

        .status-annulee {
            background: #FFEBEE;
            color: #F44336;
        }

        .btn-traiter {
            background: var(--primary-gradient);
            color: #333;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-traiter:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(var(--primary-rgb), 0.3);
        }

        .client-info {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .client-name {
            font-weight: 600;
            color: #333;
        }

        .client-email {
            font-size: 12px;
            color: #999;
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
            background: white;
            border-radius: 15px;
            max-width: 700px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            padding: 25px 30px;
            border-bottom: 2px solid #f0f2f5;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            font-size: 22px;
            color: #333;
            font-weight: 600;
        }

        .btn-close {
            background: transparent;
            border: none;
            font-size: 24px;
            color: #999;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-close:hover {
            color: #333;
        }

        .modal-body {
            padding: 30px;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .detail-item {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .detail-item.full-width {
            grid-column: span 2;
        }

        .detail-label {
            font-size: 12px;
            font-weight: 600;
            color: #999;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .detail-value {
            font-size: 14px;
            color: var(--text-main);
            font-weight: 500;
        }

        .form-section {
            background: var(--bg-body);
            padding: 25px;
            border-radius: 12px;
            margin-top: 20px;
        }

        .form-section h4 {
            font-size: 16px;
            color: var(--text-main);
            margin-bottom: 20px;
            font-weight: 600;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #555;
            margin-bottom: 8px;
        }

        .form-select, .form-textarea {
            width: 100%;
            padding: 12px 15px;
            background: var(--input-bg);
            color: var(--text-main);
            border: 2px solid var(--input-border);
            border-radius: 10px;
            font-size: 14px;
            font-family: inherit;
            transition: all 0.3s ease;
        }

        .form-select:focus, .form-textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            background: var(--input-bg);
        }

        .form-textarea {
            min-height: 120px;
            resize: vertical;
        }

        .btn-submit {
            background: var(--primary-gradient);
            color: #333;
            border: none;
            padding: 14px 30px;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(244, 196, 48, 0.4);
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

            .filter-tabs {
                flex-wrap: wrap;
            }

            .detail-grid {
                grid-template-columns: 1fr;
            }

            .detail-item.full-width {
                grid-column: span 1;
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
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-clipboard-list"></i>
                <?php echo t('complaint_management', 'Gestion des Réclamations'); ?>
            </h1>
        </div>

        <div class="table-card">
            <div class="table-header">
                <h3 class="table-title"><?php echo t('all_complaints', 'Toutes les réclamations'); ?></h3>
                <button class="btn-toggle-filters" onclick="toggleAdvancedFilters()">
                    <i class="fas fa-filter"></i>
                    <?php echo t('advanced_filters', 'Filtres avancés'); ?>
                </button>
            </div>

            <div class="advanced-filters" id="advancedFilters" style="display: none;">
                <div class="filters-grid">
                    <div class="filter-group">
                        <label for="dateFilter">Date</label>
                        <input type="date" id="dateFilter" onchange="applyFilters()" placeholder="Sélectionner une date">
                    </div>
                    
                    <div class="filter-group">
                        <label for="locationFilter">Localisation</label>
                        <select id="locationFilter" onchange="applyFilters()">
                            <option value="">Toutes les wilayas</option>
                            <?php foreach ($locations as $location): ?>
                                <option value="<?php echo htmlspecialchars($location); ?>"><?php echo htmlspecialchars($location); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="typeFilter">Type de réclamation</label>
                        <select id="typeFilter" onchange="applyFilters()">
                            <option value="">Tous les types</option>
                            <?php foreach ($types as $type): ?>
                                <option value="<?php echo htmlspecialchars($type); ?>"><?php echo htmlspecialchars($type); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="statusFilter">Statut</label>
                        <select id="statusFilter" onchange="applyFilters()">
                            <option value="">Tous les statuts</option>
                            <option value="en attente">En attente</option>
                            <option value="en cours">En cours</option>
                            <option value="résolue">Résolue</option>
                            <option value="annulée">Annulée</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <button class="btn-clear-filters" onclick="clearAllFilters()">
                            <i class="fas fa-times"></i>
                            Effacer les filtres
                        </button>
                    </div>
                </div>
                
                <div class="filter-results">
                    <span id="filterResults">Affichage de toutes les réclamations</span>
                </div>
            </div>

            <div class="table-container">
                <?php if (empty($reclamations)): ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <h3>Aucune réclamation</h3>
                        <p>Il n'y a aucune réclamation pour le moment</p>
                    </div>
                <?php else: ?>
                    <table id="reclamationsTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Code Colis</th>
                                <th>Client</th>
                                <th>Localisation</th>
                                <th>Type</th>
                                <th>Statut</th>
                                <th>Date de création</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reclamations as $reclamation): ?>
                                        <?php
                                            // Determine highlight: either unread by admin or status 'en attente' (DB may store Arabic or French values)
                                            $highlight = '';
                                            $stat = $reclamation['statut_reclamation'];
                                            $isEnAttente = in_array($stat, ['جديد', 'En attente', 'en attente', 'en-attente', 'EN ATTENTE'], true);
                                            $luParAdmin = isset($reclamation['lu_par_admin']) ? $reclamation['lu_par_admin'] : 1; // Default to read if column doesn't exist
                                            if ($luParAdmin == 0 || $isEnAttente) {
                                                $highlight = 'highlight-new';
                                            }
                                        ?>
                                        <tr class="<?php echo $highlight; ?>" 
                                           data-status="<?php echo htmlspecialchars($reclamation['statut_reclamation']); ?>"
                                           data-location="<?php echo htmlspecialchars($reclamation['location'] ?? ''); ?>"
                                           data-type="<?php echo htmlspecialchars($reclamation['type_reclamation']); ?>"
                                           data-date="<?php echo date('Y-m-d', strtotime($reclamation['date_creation'])); ?>">
                                            <td>#<?php echo htmlspecialchars($reclamation['reclamation_id']); ?></td>
                                            <td><strong><a href="manage_colis.php?colis_id=<?php echo (int)$reclamation['colis_id']; ?>"><?php echo htmlspecialchars($reclamation['code_suivi']); ?></a></strong></td>
                                            <td>
                                                <div class="client-info">
                                                    <span class="client-name"><a href="manage_clients.php?client_id=<?php echo (int)$reclamation['client_id']; ?>"><?php echo htmlspecialchars($reclamation['client_nom']); ?></a></span>
                                                    <span class="client-email"><?php echo htmlspecialchars($reclamation['client_email']); ?></span>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($reclamation['location'] ?? 'Non spécifié'); ?></td>
                                    <td><?php echo htmlspecialchars($reclamation['type_reclamation']); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php 
                                            $status = strtolower(str_replace(' ', '-', $reclamation['statut_reclamation']));
                                            if ($status === 'جديد') echo 'en-attente';
                                            elseif ($status === 'قيد-المعالجة') echo 'en-cours';
                                            elseif ($status === 'محلول') echo 'resolue';
                                            elseif ($status === 'ملغى') echo 'annulee';
                                            else echo $status;
                                        ?>">
                                            <?php 
                                            $status_display = $reclamation['statut_reclamation'];
                                            if ($status_display === 'جديد') echo 'En attente';
                                            elseif ($status_display === 'قيد المعالجة') echo 'En cours';
                                            elseif ($status_display === 'محلول') echo 'Résolue';
                                            elseif ($status_display === 'ملغى') echo 'Annulée';
                                            else echo htmlspecialchars($status_display);
                                            ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($reclamation['date_creation'])); ?></td>
                                    <td>
                                        <button class="btn-traiter" onclick='openModal(<?php echo json_encode($reclamation); ?>)'>
                                            <i class="fas fa-edit"></i>
                                            Traiter
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

    <!-- Modal for handling reclamation -->
    <?php
    // PLACEHOLDER: Set lu_par_admin = 1 for the current reclamation ID
    // UPDATE reclamations SET lu_par_admin = 1 WHERE reclamation_id = $current_reclamation_id AND lu_par_admin = 0;
    // If the admin navigates to this page with a ?reclamation_id=... parameter we mark it as read server-side.
    if (isset($_GET['reclamation_id'])) {
        $current_reclamation_id = (int) $_GET['reclamation_id'];
        if ($current_reclamation_id > 0) {
            // Check if lu_par_admin column exists first
            $checkCol = $conn->query("SHOW COLUMNS FROM reclamations LIKE 'lu_par_admin'");
            if ($checkCol && $checkCol->num_rows > 0) {
                $stmtMark = $conn->prepare("UPDATE reclamations SET lu_par_admin = 1 WHERE reclamation_id = ? AND lu_par_admin = 0");
                if ($stmtMark) {
                    $stmtMark->bind_param('i', $current_reclamation_id);
                    $stmtMark->execute();
                    $stmtMark->close();
                }
            }
        }
    }

    ?>

    <div id="reclamationModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><?php echo t('process_complaint', 'Traiter la réclamation'); ?></h3>
                <button class="btn-close" onclick="closeModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="reclamationForm" method="POST" action="update_reclamation.php">
                    <input type="hidden" name="reclamation_id" id="modal_reclamation_id">
                    
                    <div class="detail-grid">
                        <div class="detail-item">
                            <span class="detail-label">ID Réclamation</span>
                            <span class="detail-value" id="modal_display_id">-</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Code Colis</span>
                            <span class="detail-value" id="modal_code_suivi">-</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Client</span>
                            <span class="detail-value" id="modal_client_nom">-</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Type</span>
                            <span class="detail-value" id="modal_type">-</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Date de création</span>
                            <span class="detail-value" id="modal_date_creation">-</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Statut actuel</span>
                            <span class="detail-value" id="modal_statut_actuel">-</span>
                        </div>
                        <div class="detail-item full-width">
                            <span class="detail-label">Description du client</span>
                            <span class="detail-value" id="modal_description">-</span>
                        </div>
                    </div>

                    <div class="form-section">
                        <h4><i class="fas fa-tools"></i> Traitement de la réclamation</h4>
                        
                        <div class="form-group">
                            <label class="form-label" for="statut_reclamation">
                                <i class="fas fa-flag"></i> Nouveau statut
                            </label>
                            <select name="statut_reclamation" id="statut_reclamation" class="form-select" required>
                                <option value="جديد">En attente</option>
                                <option value="قيد المعالجة">En cours</option>
                                <option value="محلول">Résolue</option>
                                <option value="ملغى">Annulée</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="reponse_admin">
                                <i class="fas fa-comment-dots"></i> Commentaire et Réponse de l'Admin
                            </label>
                            <textarea name="reponse_admin" id="reponse_admin" class="form-textarea" 
                                      placeholder="Entrez votre commentaire et la réponse pour le client..."></textarea>
                        </div>

                        <button type="submit" class="btn-submit">
                            <i class="fas fa-save"></i>
                            Enregistrer la Réponse
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openModal(reclamation) {
            // Mark as read by admin
            markAsRead(reclamation.reclamation_id);
            
            // Populate modal with data
            document.getElementById('modal_reclamation_id').value = reclamation.reclamation_id;
            document.getElementById('modal_display_id').textContent = '#' + reclamation.reclamation_id;
            document.getElementById('modal_code_suivi').textContent = reclamation.code_suivi;
            document.getElementById('modal_client_nom').textContent = reclamation.client_nom;
            document.getElementById('modal_type').textContent = reclamation.type_reclamation;
            document.getElementById('modal_date_creation').textContent = new Date(reclamation.date_creation).toLocaleString('fr-FR');
            
            // Set status display
            let statusDisplay = reclamation.statut_reclamation;
            if (statusDisplay === 'جديد') statusDisplay = 'En attente';
            else if (statusDisplay === 'قيد المعالجة') statusDisplay = 'En cours';
            else if (statusDisplay === 'محلول') statusDisplay = 'Résolue';
            else if (statusDisplay === 'ملغى') statusDisplay = 'Annulée';
            document.getElementById('modal_statut_actuel').textContent = statusDisplay;
            
            document.getElementById('modal_description').textContent = reclamation.description || 'Aucune description fournie';
            
            // Set current values in form
            document.getElementById('statut_reclamation').value = reclamation.statut_reclamation;
            document.getElementById('reponse_admin').value = reclamation.response_admin || '';
            
            // Show modal
            document.getElementById('reclamationModal').classList.add('active');
        }

        function closeModal() {
            document.getElementById('reclamationModal').classList.remove('active');
        }

        function markAsRead(reclamationId) {
            // AJAX call to mark as read
            fetch('mark_reclamation_read.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'reclamation_id=' + reclamationId
            });
        }

        function toggleAdvancedFilters() {
            const filtersDiv = document.getElementById('advancedFilters');
            if (filtersDiv.style.display === 'none' || filtersDiv.style.display === '') {
                filtersDiv.style.display = 'block';
            } else {
                filtersDiv.style.display = 'none';
            }
        }

        function applyFilters() {
            const rows = document.querySelectorAll('#reclamationsTable tbody tr');
            const dateFilter = document.getElementById('dateFilter').value;
            const locationFilter = document.getElementById('locationFilter').value;
            const typeFilter = document.getElementById('typeFilter').value;
            const statusFilter = document.getElementById('statusFilter').value;
            
            let visibleCount = 0;
            
            rows.forEach(row => {
                let showRow = true;
                
                // Date filter - exact date match
                if (dateFilter) {
                    const rowDate = row.dataset.date;
                    showRow = showRow && (rowDate === dateFilter);
                }
                
                // Location filter
                if (locationFilter && row.dataset.location) {
                    showRow = showRow && (row.dataset.location === locationFilter);
                }
                
                // Type filter
                if (typeFilter) {
                    showRow = showRow && (row.dataset.type === typeFilter);
                }
                
                // Status filter
                if (statusFilter) {
                    const rowStatus = row.dataset.status;
                    let matchStatus = false;
                    
                    switch(statusFilter) {
                        case 'en attente':
                            matchStatus = (rowStatus === 'جديد' || rowStatus === 'en attente');
                            break;
                        case 'en cours':
                            matchStatus = (rowStatus === 'قيد المعالجة' || rowStatus === 'en cours');
                            break;
                        case 'résolue':
                            matchStatus = (rowStatus === 'محلول' || rowStatus === 'résolue');
                            break;
                        case 'annulée':
                            matchStatus = (rowStatus === 'ملغى' || rowStatus === 'annulée');
                            break;
                    }
                    showRow = showRow && matchStatus;
                }
                
                // Show/hide row
                if (showRow) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });
            
            // Update filter results
            updateFilterResults(visibleCount, rows.length);
        }

        function updateFilterResults(visible, total) {
            const resultsSpan = document.getElementById('filterResults');
            if (visible === total) {
                resultsSpan.textContent = `Affichage de toutes les réclamations (${total})`;
            } else {
                resultsSpan.textContent = `Affichage de ${visible} réclamation(s) sur ${total}`;
            }
        }

        function clearAllFilters() {
            // Reset all filter inputs
            document.getElementById('dateFilter').value = '';
            document.getElementById('locationFilter').value = '';
            document.getElementById('typeFilter').value = '';
            document.getElementById('statusFilter').value = '';
            
            // Show all rows
            const rows = document.querySelectorAll('#reclamationsTable tbody tr');
            rows.forEach(row => {
                row.style.display = '';
            });
            
            // Update results
            updateFilterResults(rows.length, rows.length);
        }

        // Initialize filter results on page load
        document.addEventListener('DOMContentLoaded', function() {
            const rows = document.querySelectorAll('#reclamationsTable tbody tr');
            updateFilterResults(rows.length, rows.length);
        });

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('reclamationModal');
            if (event.target === modal) {
                closeModal();
            }
        }
    </script>

<?php include 'includes/footer.php'; ?>
