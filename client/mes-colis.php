<?php
session_start();
require_once '../includes/language_manager.php';

$lang_dir = isRTL() ? 'rtl' : 'ltr';

// Check if client is logged in
if (!isset($_SESSION['client_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

require_once '../db.php';

$clientID = $_SESSION['client_id'];
$client_nom = $_SESSION['client_nom'] ?? 'Client';

// Get all colis for this client with latest status from historique
$query = "SELECT c.*, 
          COALESCE((SELECT statut FROM historique_statuts_colis h 
           WHERE h.colis_id = c.colis_id 
           ORDER BY h.date_heure DESC LIMIT 1), c.statut_actuel) as latest_statut,
          COALESCE((SELECT lieu FROM historique_statuts_colis h 
           WHERE h.colis_id = c.colis_id 
           ORDER BY h.date_heure DESC LIMIT 1), 'Non spécifiée') as latest_localisation
          FROM colis c 
          WHERE c.client_id = ? 
          ORDER BY c.date_expedition DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $clientID);
$stmt->execute();
$result = $stmt->get_result();
$colis_list = [];
while ($row = $result->fetch_assoc()) {
    $colis_list[] = $row;
}
$stmt->close();

// Handle detail modal data
$selected_colis = null;
$historique = [];
if (isset($_GET['colis_id'])) {
    $colis_id = (int) $_GET['colis_id'];
    
    // Get colis details
    $stmt = $conn->prepare("SELECT * FROM colis WHERE colis_id = ? AND client_id = ?");
    $stmt->bind_param("ii", $colis_id, $clientID);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $selected_colis = $row;
        
        // Get status history
        $checkHistTable = $conn->query("SHOW TABLES LIKE 'historique_statuts_colis'");
        if ($checkHistTable && $checkHistTable->num_rows > 0) {
            $stmt2 = $conn->prepare("SELECT * FROM historique_statuts_colis WHERE colis_id = ? ORDER BY date_heure ASC");
            $stmt2->bind_param("i", $colis_id);
            $stmt2->execute();
            $result2 = $stmt2->get_result();
            while ($hist = $result2->fetch_assoc()) {
                $historique[] = $hist;
            }
            $stmt2->close();
        }
    }
    $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="<?php echo getCurrentLanguage(); ?>" dir="<?php echo $lang_dir; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo t('mes_colis_title'); ?> - Ashkili</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/theme.css">
    <script src="../js/theme.js"></script>
    <style>
        :root {
            /* --primary-color is defined in theme.css */
            --secondary-color: #2c3e50;
        }
        
        /* RTL Support */
        [dir="rtl"] {
            text-align: right;
        }
        
        [dir="rtl"] .navbar-nav {
            padding-right: 0;
        }
        
        [dir="rtl"] .colis-card {
            border-left: none;
            border-right: 4px solid var(--primary-color);
        }
        
        [dir="rtl"] .timeline-item:not(:last-child)::after {
            left: auto;
            right: 0.75rem;
        }
        
        [dir="rtl"] .timeline-icon {
            margin-left: 1rem;
            margin-right: 0;
        }

        /* Language Switcher Styles */
        .language-selector {
            position: relative;
            margin-left: 15px;
        }
        
        [dir="rtl"] .language-selector {
            margin-left: 0;
            margin-right: 15px;
        }

        .lang-btn {
            background: none;
            border: 1px solid #ddd;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 14px;
            color: #333;
        }

        .lang-dropdown {
            display: none;
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            min-width: 120px;
        }
        
        [dir="rtl"] .lang-dropdown {
            right: auto;
            left: 0;
        }

        .lang-dropdown.show {
            display: block;
        }

        .lang-option {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            cursor: pointer;
            transition: background 0.2s;
        }

        .lang-option:hover {
            background: #f5f5f5;
        }

        .lang-flag {
            width: 20px;
            height: 15px;
            object-fit: cover;
            border-radius: 2px;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
            color: #333;
        }

        .navbar {
            background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);
            padding: 15px 25px;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.08);
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #2c3e50;
            font-size: 20px;
            font-weight: 700;
        }

        .navbar-brand i {
            color: var(--primary-color);
            font-size: 24px;
        }

        .navbar-nav {
            display: flex;
            gap: 20px;
            list-style: none;
            align-items: center;
        }

        .navbar-nav a {
            color: #495057;
            text-decoration: none;
            font-weight: 600;
            padding: 8px 16px;
            border-radius: 10px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            position: relative;
            font-size: 14px;
        }

        .navbar-nav a:hover {
            background: var(--primary-gradient);
            color: #333;
            transform: translateY(-1px);
        }

        .notification-badge {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
            border-radius: 50%;
            padding: 3px 7px;
            font-size: 11px;
            position: absolute;
            top: -5px;
            right: -5px;
            min-width: 18px;
            text-align: center;
            font-weight: 700;
            box-shadow: 0 2px 8px rgba(220, 53, 69, 0.4);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
            color: #495057;
            font-weight: 500;
        }

        .container {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .page-header {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-title {
            font-size: 1.8rem;
            color: #333;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-outline {
            background: linear-gradient(135deg, #dc3545, #c82333);
            border: none;
            color: white;
            padding: 10px 16px;
            border-radius: 25px;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
            transition: all 0.3s ease;
        }

        .btn-outline:hover {
            background: linear-gradient(135deg, #c82333, #a71e2a);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(220, 53, 69, 0.4);
        }

        .colis-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 1.5rem;
        }

        .colis-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: transform 0.3s ease;
            border-left: 4px solid var(--primary-color);
        }

        .colis-card:hover {
            transform: translateY(-2px);
        }

        .colis-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .colis-code {
            font-size: 1.1rem;
            font-weight: 700;
            color: #333;
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-en-transit {
            background: #e3f2fd;
            color: #1976d2;
        }

        .status-livre {
            background: #e8f5e8;
            color: #388e3c;
        }

        .status-expedie {
            background: #fff3e0;
            color: #f57c00;
        }

        .status-default {
            background: #f5f5f5;
            color: #757575;
        }

        .colis-info {
            margin-bottom: 1rem;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        .info-label {
            color: #666;
        }

        .info-value {
            font-weight: 500;
            color: #333;
        }

        .btn-primary {
            background: var(--primary-gradient);
            color: #333;
            width: 100%;
            justify-content: center;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(var(--primary-rgb), 0.3);
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
            max-width: 800px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            padding: 2rem 2rem 1rem;
            border-bottom: 2px solid #f0f2f5;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            font-size: 1.5rem;
            color: #333;
        }

        .btn-close {
            background: transparent;
            border: none;
            font-size: 1.5rem;
            color: #999;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .btn-close:hover {
            color: #333;
        }

        .modal-body {
            padding: 2rem;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .detail-item {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .detail-label {
            font-size: 0.875rem;
            font-weight: 600;
            color: #666;
            text-transform: uppercase;
        }

        .detail-value {
            font-size: 1rem;
            color: #333;
            font-weight: 500;
        }

        .timeline {
            margin-top: 2rem;
        }

        .timeline-title {
            font-size: 1.25rem;
            color: #333;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .timeline-item {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            position: relative;
        }

        .timeline-item:not(:last-child)::after {
            content: '';
            position: absolute;
            left: 0.75rem;
            top: 2.5rem;
            width: 2px;
            height: calc(100% - 1rem);
            background: #e9ecef;
        }

        .timeline-icon {
            width: 1.5rem;
            height: 1.5rem;
            background: var(--primary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #333;
            font-size: 0.75rem;
            flex-shrink: 0;
            margin-top: 0.25rem;
        }

        .timeline-content {
            flex: 1;
        }

        .timeline-status {
            font-weight: 600;
            color: #333;
            margin-bottom: 0.25rem;
        }

        .timeline-location {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 0.25rem;
        }

        .timeline-date {
            color: #999;
            font-size: 0.8rem;
        }

        .reclamation-section {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 12px;
            margin-top: 2rem;
            text-align: center;
        }

        .btn-reclamation {
            background: var(--primary-gradient);
            color: #333;
            padding: 1rem 2rem;
            font-size: 1.1rem;
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .empty-icon {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 1rem;
        }

        .empty-title {
            font-size: 1.5rem;
            color: #666;
            margin-bottom: 0.5rem;
        }

        .empty-desc {
            color: #999;
        }

        @media (max-width: 768px) {
            .navbar {
                padding: 1rem;
                flex-direction: column;
                gap: 1rem;
            }

            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .colis-grid {
                grid-template-columns: 1fr;
            }

            .detail-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-brand">
            <i class="fas fa-box"></i>
            Ashkili
        </div>
        <ul class="navbar-nav">
            <li><a href="dashboard.php"><i class="fas fa-home"></i> <?php echo t('dashboard_title'); ?></a></li>
            <li><a href="mes-colis.php" class="active"><i class="fas fa-boxes"></i> <?php echo t('my_parcels'); ?></a></li>
            <li><a href="mes-reclamations.php"><i class="fas fa-exclamation-circle"></i> <?php echo t('my_claims'); ?></a></li>
        </ul>
        <div class="user-info">
            <!-- Theme Toggle -->
            <button class="theme-toggle" onclick="toggleTheme()" title="Toggle Theme">
                <i class="fas fa-moon" id="theme-icon"></i>
            </button>

            <!-- Language Switcher -->
            <div class="language-selector">
                <button type="button" class="lang-btn" id="langBtn">
                    <i class="fas fa-language"></i>
                    <span><?php 
                        $langNames = ['fr' => 'FR', 'en' => 'EN', 'ar' => 'ع'];
                        echo $langNames[getCurrentLanguage()] ?? 'FR';
                    ?></span>
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="lang-dropdown" id="langDropdown">
                    <div class="lang-option" data-lang="fr">
                        <i class="fas fa-circle" style="color: #0055A4;"></i>
                        <span>Français</span>
                    </div>
                    <div class="lang-option" data-lang="en">
                        <i class="fas fa-circle" style="color: #012169;"></i>
                        <span>English</span>
                    </div>
                    <div class="lang-option" data-lang="ar">
                        <i class="fas fa-circle" style="color: #006233;"></i>
                        <span>العربية</span>
                    </div>
                </div>
                <form id="langForm" method="POST" style="display: none;">
                    <input type="hidden" name="lang" id="langInput">
                </form>
            </div>

            <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($client_nom); ?></span>
            <a href="../auth/logout.php" class="btn btn-outline">
                <i class="fas fa-sign-out-alt"></i> <?php echo t('logout'); ?>
            </a>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-boxes"></i>
                <?php echo t('mes_colis_title'); ?>
            </h1>
            <a href="dashboard.php" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i>
                <?php echo t('back_to_dashboard'); ?>
            </a>
        </div>

        <?php if (empty($colis_list)): ?>
            <div class="empty-state">
                <i class="fas fa-box-open empty-icon"></i>
                <h3 class="empty-title"><?php echo t('no_parcels_found'); ?></h3>
                <p class="empty-desc"><?php echo t('no_parcels_desc'); ?></p>
            </div>
        <?php else: ?>
            <div class="colis-grid">
                <?php foreach ($colis_list as $colis): ?>
                    <div class="colis-card">
                        <div class="colis-header">
                            <div class="colis-code"><?php echo htmlspecialchars($colis['code_suivi']); ?></div>
                            <span class="status-badge <?php 
                                $status = strtolower($colis['statut_actuel']);
                                if (strpos($status, 'livr') !== false || strpos($status, 'مسلم') !== false) {
                                    echo 'status-livre';
                                } elseif (strpos($status, 'transit') !== false || strpos($status, 'expédié') !== false) {
                                    echo 'status-en-transit';
                                } elseif (strpos($status, 'expedi') !== false) {
                                    echo 'status-expedie';
                                } else {
                                    echo 'status-default';
                                }
                            ?>">
                                <?php echo htmlspecialchars($colis['statut_actuel']); ?>
                            </span>
                        </div>
                        
                        <div class="colis-info">
                            <div class="info-row">
                                <span class="info-label"><?php echo t('destination'); ?>:</span>
                                <span class="info-value"><?php echo htmlspecialchars($colis['adresse_livraison'] ?? 'Non spécifiée'); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label"><?php echo t('last_location'); ?>:</span>
                                <span class="info-value"><?php echo htmlspecialchars($colis['latest_localisation'] ?? 'Non spécifiée'); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label"><?php echo t('shipping_date'); ?>:</span>
                                <span class="info-value"><?php echo date('d/m/Y', strtotime($colis['date_expedition'])); ?></span>
                            </div>
                            <?php if (!empty($colis['poids'])): ?>
                            <div class="info-row">
                                <span class="info-label"><?php echo t('weight'); ?>:</span>
                                <span class="info-value"><?php echo htmlspecialchars($colis['poids']); ?> kg</span>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <button class="btn btn-primary" onclick="openModal(<?php echo $colis['colis_id']; ?>)">
                            <i class="fas fa-eye"></i>
                            <?php echo t('view_details'); ?>
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal for colis details -->
    <div id="colisModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><?php echo t('parcel_details'); ?></h3>
                <button class="btn-close" onclick="closeModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>

    <script>
        // Language Switcher
        const langBtn = document.getElementById('langBtn');
        const langDropdown = document.getElementById('langDropdown');
        const langOptions = document.querySelectorAll('.lang-option');
        const langForm = document.getElementById('langForm');
        const langInput = document.getElementById('langInput');

        if (langBtn) {
            langBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                langDropdown.classList.toggle('show');
            });

            document.addEventListener('click', (e) => {
                if (!langDropdown.contains(e.target) && !langBtn.contains(e.target)) {
                    langDropdown.classList.remove('show');
                }
            });

            langOptions.forEach(option => {
                option.addEventListener('click', () => {
                    const lang = option.getAttribute('data-lang');
                    langInput.value = lang;
                    langForm.submit();
                });
            });
        }

        function openModal(colisId) {
            window.location.href = 'mes-colis.php?colis_id=' + colisId + '#modal';
        }

        function closeModal() {
            window.location.href = 'mes-colis.php';
        }

        // Check if modal should be opened
        <?php if ($selected_colis): ?>
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('colisModal');
            const modalBody = document.getElementById('modalBody');
            
            modalBody.innerHTML = `
                <div class="detail-grid">
                    <div class="detail-item">
                        <span class="detail-label"><?php echo t('tracking_number'); ?></span>
                        <span class="detail-value"><?php echo htmlspecialchars($selected_colis['code_suivi']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label"><?php echo t('current_status'); ?></span>
                        <span class="detail-value"><?php echo htmlspecialchars($selected_colis['statut_actuel']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label"><?php echo t('shipping_date'); ?></span>
                        <span class="detail-value"><?php echo date('d/m/Y H:i', strtotime($selected_colis['date_expedition'])); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label"><?php echo t('destination'); ?></span>
                        <span class="detail-value"><?php echo htmlspecialchars($selected_colis['adresse_livraison'] ?? 'Non spécifiée'); ?></span>
                    </div>
                    <?php if (!empty($selected_colis['poids'])): ?>
                    <div class="detail-item">
                        <span class="detail-label"><?php echo t('weight'); ?></span>
                        <span class="detail-value"><?php echo htmlspecialchars($selected_colis['poids']); ?> kg</span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($selected_colis['prix'])): ?>
                    <div class="detail-item">
                        <span class="detail-label"><?php echo t('price'); ?></span>
                        <span class="detail-value"><?php echo htmlspecialchars($selected_colis['prix']); ?> DH</span>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="timeline">
                    <h4 class="timeline-title">
                        <i class="fas fa-history"></i>
                        <?php echo t('tracking_history'); ?>
                    </h4>
                    <?php if (empty($historique)): ?>
                        <p style="color: #666; text-align: center; padding: 2rem;"><?php echo t('no_history'); ?></p>
                    <?php else: ?>
                        <?php foreach ($historique as $index => $hist): ?>
                        <div class="timeline-item">
                            <div class="timeline-icon">
                                <i class="fas fa-circle"></i>
                            </div>
                            <div class="timeline-content">
                                <div class="timeline-status"><?php echo htmlspecialchars($hist['statut']); ?></div>
                                <?php if (!empty($hist['lieu'])): ?>
                                <div class="timeline-location">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <?php echo htmlspecialchars($hist['lieu']); ?>
                                </div>
                                <?php endif; ?>
                                <div class="timeline-date">
                                    <?php echo date('d/m/Y à H:i', strtotime($hist['date_heure'])); ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="reclamation-section">
                    <h4 style="margin-bottom: 1rem;"><?php echo t('problem_with_parcel'); ?></h4>
                    <a href="nouvelle-reclamation.php?colis_id=<?php echo $selected_colis['colis_id']; ?>" class="btn btn-reclamation">
                        <i class="fas fa-exclamation-triangle"></i>
                        <?php echo t('claim_for_parcel'); ?>
                    </a>
                </div>
            `;
            
            modal.classList.add('active');
        });
        <?php endif; ?>

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('colisModal');
            if (event.target === modal) {
                closeModal();
            }
        }
    </script>

    <?php $conn->close(); ?>
</body>
</html>