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

// Page variables for header
$page_title = "Gestion des Colis";
$page_icon = "fas fa-boxes";
$page_heading = "Ashkili - Gestion des Colis";

// Get filter parameters
$filter_region = $_GET['region'] ?? '';
$filter_statut = $_GET['statut'] ?? '';
$filter_date_debut = $_GET['date_debut'] ?? '';
$filter_date_fin = $_GET['date_fin'] ?? '';

// Build query with filters
$query = "SELECT c.*, cl.nom as client_nom, cl.telephone as client_tel 
          FROM colis c 
          LEFT JOIN clients cl ON c.client_id = cl.client_id 
          WHERE 1=1";

if ($filter_region) {
    $query .= " AND c.location LIKE '%" . $conn->real_escape_string($filter_region) . "%'";
}
if ($filter_statut) {
    $query .= " AND c.statut_actuel = '" . $conn->real_escape_string($filter_statut) . "'";
}
if ($filter_date_debut) {
    $query .= " AND c.date_expedition >= '" . $conn->real_escape_string($filter_date_debut) . "'";
}
if ($filter_date_fin) {
    $query .= " AND c.date_expedition <= '" . $conn->real_escape_string($filter_date_fin) . "'";
}

$query .= " ORDER BY c.colis_id DESC";
$colis_result = $conn->query($query);

// Get unique locations for filter dropdown
$locations_query = "SELECT DISTINCT location FROM colis WHERE location IS NOT NULL AND location != '' ORDER BY location";
$locations_result = $conn->query($locations_query);

// Set page variables for header
$page_title = "Gestion des Colis - Admin";
$page_icon = "fas fa-boxes";
$page_heading = "Ashkili - " . t('package_management', 'Gestion des Colis');

// Include header
include 'includes/header.php';
?>

<style>

        .filters-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
        }

        .filters-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            color: #333;
        }

        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .filter-group label {
            display: block;
            margin-bottom: 8px;
            color: #666;
            font-size: 14px;
            font-weight: 600;
        }

        .filter-group input,
        .filter-group select {
            width: 100%;
            padding: 10px;
            background: var(--input-bg);
            color: var(--text-main);
            border: 2px solid var(--input-border);
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .filter-group input:focus,
        .filter-group select:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        .filter-actions {
            display: flex;
            gap: 10px;
        }

        .btn-filter {
            background: var(--primary-gradient);
            color: #333;
            padding: 10px 25px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-filter:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(var(--primary-rgb), 0.4);
        }

        .btn-reset {
            background: #f5f5f5;
            color: #666;
            padding: 10px 25px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-reset:hover {
            background: #e0e0e0;
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

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }

        .status-expedie {
            background: #E3F2FD;
            color: #1976D2;
        }

        .status-transit {
            background: #FFF3E0;
            color: #F57C00;
        }

        .status-livre {
            background: #E8F5E9;
            color: #388E3C;
        }

        .status-annule {
            background: #FFEBEE;
            color: #D32F2F;
        }

        .status-preparation {
            background: #F3E5F5;
            color: #7B1FA2;
        }

        .btn-action {
            background: var(--primary-gradient);
            color: #333;
            padding: 8px 15px;
            border: none;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 3px 10px rgba(var(--primary-rgb), 0.3);
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
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal-content {
            background: white;
            margin: 5% auto;
            padding: 30px;
            border-radius: 12px;
            width: 90%;
            max-width: 500px;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
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
            transition: all 0.3s ease;
        }

        .close-modal:hover {
            color: #333;
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

        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            background: var(--input-bg);
            color: var(--text-main);
            border: 2px solid var(--input-border);
            border-radius: 8px;
            font-size: 14px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            transition: all 0.3s ease;
        }

        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
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

        @media (max-width: 768px) {
            .container {
                padding: 0 15px;
            }

            .filters-grid {
                grid-template-columns: 1fr;
            }

            .table-card {
                overflow-x: auto;
            }

            table {
                min-width: 800px;
            }
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
    </style>

    <div class="container">
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-boxes"></i>
                <?php echo t('package_management', 'Gestion des Colis'); ?>
            </h1>
            <a href="add_colis.php" class="btn-add">
                <i class="fas fa-plus"></i> <?php echo t('add_package', 'Ajouter un Colis'); ?>
            </a>
        </div>

        <!-- Filters Card -->
        <div class="filters-card">
            <div class="filters-title"><i class="fas fa-filter"></i> <?php echo t('filter_packages', 'Filtrer les Colis'); ?></div>
            <form method="GET" action="">
                <div class="filters-grid">
                    <div class="filter-group">
                        <label for="region"><?php echo t('region', 'Région'); ?></label>
                        <select name="region" id="region">
                            <option value=""><?php echo t('all_regions', 'Toutes les régions'); ?></option>
                            <?php while ($loc = $locations_result->fetch_assoc()): ?>
                                <option value="<?php echo htmlspecialchars($loc['location']); ?>" 
                                    <?php echo ($filter_region == $loc['location']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($loc['location']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="statut"><?php echo t('status', 'Statut'); ?></label>
                        <select name="statut" id="statut">
                            <option value=""><?php echo t('all_statuses', 'Tous les statuts'); ?></option>
                            <option value="En préparation" <?php echo ($filter_statut == 'En préparation') ? 'selected' : ''; ?>><?php echo t('in_progress', 'En préparation'); ?></option>
                            <option value="Expédié" <?php echo ($filter_statut == 'Expédié') ? 'selected' : ''; ?>><?php echo t('expedited', 'Expédié'); ?></option>
                            <option value="En transit" <?php echo ($filter_statut == 'En transit') ? 'selected' : ''; ?>><?php echo t('in_transit', 'En transit'); ?></option>
                            <option value="En cours de livraison" <?php echo ($filter_statut == 'En cours de livraison') ? 'selected' : ''; ?>><?php echo t('in_progress', 'En cours de livraison'); ?></option>
                            <option value="Livré" <?php echo ($filter_statut == 'Livré') ? 'selected' : ''; ?>><?php echo t('delivered', 'Livré'); ?></option>
                            <option value="Retardé" <?php echo ($filter_statut == 'Retardé') ? 'selected' : ''; ?>><?php echo t('returned', 'Retardé'); ?></option>
                            <option value="Annulé" <?php echo ($filter_statut == 'Annulé') ? 'selected' : ''; ?>><?php echo t('returned', 'Annulé'); ?></option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="date_debut"><?php echo t('start_date', 'Date de début'); ?></label>
                        <input type="date" name="date_debut" id="date_debut" value="<?php echo htmlspecialchars($filter_date_debut); ?>">
                    </div>

                    <div class="filter-group">
                        <label for="date_fin"><?php echo t('end_date', 'Date de fin'); ?></label>
                        <input type="date" name="date_fin" id="date_fin" value="<?php echo htmlspecialchars($filter_date_fin); ?>">
                    </div>
                </div>

                <div class="filter-actions">
                    <button type="submit" class="btn-filter">
                        <i class="fas fa-search"></i> <?php echo t('filter', 'Filtrer'); ?>
                    </button>
                    <a href="manage_colis.php" class="btn-reset">
                        <i class="fas fa-times"></i> <?php echo t('reset', 'Réinitialiser'); ?>
                    </a>
                </div>
            </form>
        </div>

        <!-- Colis Table -->
        <div class="table-card">
            <?php if ($colis_result && $colis_result->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th><?php echo t('tracking_code', 'Code de Suivi'); ?></th>
                            <th><?php echo t('client_name', 'Nom Client'); ?></th>
                            <th><?php echo t('location', 'Localisation'); ?></th>
                            <th><?php echo t('status', 'Statut Actuel'); ?></th>
                            <th><?php echo t('expedition_date', 'Date d\'Expédition'); ?></th>
                            <th><?php echo t('price', 'Prix'); ?> (DA)</th>
                            <th><?php echo t('actions', 'Action'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($colis = $colis_result->fetch_assoc()): ?>
                            <tr>
                                <td><strong>NOS-<?php echo htmlspecialchars($colis['code_suivi']); ?></strong></td>
                                <td><?php echo htmlspecialchars($colis['nom_client']); ?></td>
                                <td><?php echo htmlspecialchars($colis['location']); ?></td>
                                <td>
                                    <?php
                                    $statut = $colis['statut_actuel'];
                                    $class = 'status-badge ';
                                    if (strpos($statut, 'Livré') !== false) $class .= 'status-livre';
                                    elseif (strpos($statut, 'Expédié') !== false) $class .= 'status-expedie';
                                    elseif (strpos($statut, 'transit') !== false || strpos($statut, 'livraison') !== false) $class .= 'status-transit';
                                    elseif (strpos($statut, 'Annulé') !== false || strpos($statut, 'Retourné') !== false) $class .= 'status-annule';
                                    else $class .= 'status-preparation';
                                    ?>
                                    <span class="<?php echo $class; ?>">
                                        <?php echo htmlspecialchars($statut ?: 'Non défini'); ?>
                                    </span>
                                </td>
                                <td><?php echo $colis['date_expedition'] ? date('d/m/Y', strtotime($colis['date_expedition'])) : '-'; ?></td>
                                <td><?php echo htmlspecialchars($colis['price']); ?> DA</td>
                                <td>
                                    <button class="btn-action" onclick="openUpdateModal(<?php echo $colis['colis_id']; ?>, '<?php echo htmlspecialchars($colis['code_suivi']); ?>', '<?php echo htmlspecialchars($statut); ?>')">
                                        <i class="fas fa-edit"></i> <?php echo t('update', 'Mettre à jour'); ?>
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-box-open"></i>
                    <h3><?php echo t('no_packages_found', 'Aucun colis trouvé'); ?></h3>
                    <p><?php echo t('add_package', 'Commencez par ajouter un nouveau colis'); ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Update Status Modal -->
    <div id="updateModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-sync-alt"></i> <?php echo t('update', 'Mettre à jour'); ?> <?php echo t('status', 'le Statut'); ?></h3>
                <button class="close-modal" onclick="closeModal()">&times;</button>
            </div>
            <form id="updateForm" method="POST" action="update_colis_status.php">
                <input type="hidden" name="colis_id" id="modal_colis_id">
                
                <div class="form-group">
                    <label><?php echo t('tracking_code', 'Code de Suivi'); ?></label>
                    <input type="text" id="modal_code_suivi" readonly style="background: var(--input-bg); color: var(--text-main); border: 2px solid var(--input-border); cursor: not-allowed; opacity: 0.6;">
                </div>

                <div class="form-group">
                    <label for="nouveau_statut"><?php echo t('status', 'Nouveau Statut'); ?> <span style="color: #D32F2F;">*</span></label>
                    <select name="nouveau_statut" id="nouveau_statut" required>
                        <option value=""><?php echo t('select_status', '-- Sélectionner un statut --'); ?></option>
                        <option value="En préparation"><?php echo t('in_preparation', 'En préparation'); ?></option>
                        <option value="Expédié"><?php echo t('expedited', 'Expédié'); ?></option>
                        <option value="En transit"><?php echo t('in_transit', 'En transit'); ?></option>
                        <option value="En cours de livraison"><?php echo t('in_delivery', 'En cours de livraison'); ?></option>
                        <option value="Livré"><?php echo t('delivered', 'Livré'); ?></option>
                        <option value="Retardé"><?php echo t('delayed', 'Retardé'); ?></option>
                        <option value="Retourné"><?php echo t('returned', 'Retourné'); ?></option>
                        <option value="Annulé"><?php echo t('cancelled', 'Annulé'); ?></option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="lieu"><?php echo t('location', 'Lieu'); ?></label>
                    <input type="text" name="lieu" id="lieu" placeholder="Ex: Centre de tri Casablanca" style="width: 100%; padding: 12px; background: var(--input-bg); color: var(--text-main); border: 2px solid var(--input-border); border-radius: 8px;">
                </div>

                <div class="form-group">
                    <label for="commentaire"><?php echo t('comment', 'Note / Commentaire'); ?></label>
                    <textarea name="commentaire" id="commentaire" placeholder="<?php echo t('add_comment', 'Ajouter une note sur ce changement de statut...'); ?>"></textarea>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fas fa-check"></i> <?php echo t('save', 'Enregistrer'); ?> <?php echo t('update', 'la mise à jour'); ?>
                </button>
            </form>
        </div>
    </div>

    <script>
        function openUpdateModal(colisId, codeSuivi, currentStatut) {
            document.getElementById('modal_colis_id').value = colisId;
            document.getElementById('modal_code_suivi').value = 'NOS-' + codeSuivi;
            document.getElementById('nouveau_statut').value = currentStatut;
            document.getElementById('updateModal').style.display = 'block';
        }

        function closeUpdateModal() {
            document.getElementById('updateModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('updateModal');
            if (event.target == modal) {
                closeUpdateModal();
            }
        }
    </script>

<?php 
$conn->close();
include 'includes/footer.php'; 
?>
