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

// PLACEHOLDER: Set lu_par_client = 1 for all unread responses for this reclamation
// UPDATE reponses_reclamations SET lu_par_client = 1 WHERE reclamation_id = $current_reclamation_id AND lu_par_client = 0;
if (isset($_GET['reclamation_id'])) {
    $current_reclamation_id = (int) $_GET['reclamation_id'];
    if ($current_reclamation_id > 0) {
        // Check if table exists and mark responses as read
        $checkTable = $conn->query("SHOW TABLES LIKE 'reponses_reclamations'");
        if ($checkTable && $checkTable->num_rows > 0) {
            $stmtMark = $conn->prepare("UPDATE reponses_reclamations SET lu_par_client = 1 
                                      WHERE reclamation_id IN (SELECT reclamation_id FROM reclamations WHERE reclamation_id = ? AND client_id = ?) 
                                      AND lu_par_client = 0");
            if ($stmtMark) {
                $stmtMark->bind_param('ii', $current_reclamation_id, $clientID);
                $stmtMark->execute();
                $stmtMark->close();
            }
        }
    }
}

// Get all reclamations for this client with colis info
$query = "SELECT r.*, 
          c.code_suivi,
          (SELECT COUNT(*) FROM reponses_reclamations rr 
           WHERE rr.reclamation_id = r.reclamation_id AND rr.lu_par_client = 0) as unread_responses
          FROM reclamations r 
          LEFT JOIN colis c ON r.colis_id = c.colis_id 
          WHERE r.client_id = ? 
          ORDER BY r.date_creation DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $clientID);
$stmt->execute();
$result = $stmt->get_result();
$reclamations = [];
while ($row = $result->fetch_assoc()) {
    $reclamations[] = $row;
}
$stmt->close();

// Handle detail modal data
$selected_reclamation = null;
$responses = [];
if (isset($_GET['reclamation_id'])) {
    $reclamation_id = (int) $_GET['reclamation_id'];
    
    // Get reclamation details
    $stmt = $conn->prepare("SELECT r.*, c.code_suivi 
                           FROM reclamations r 
                           LEFT JOIN colis c ON r.colis_id = c.colis_id 
                           WHERE r.reclamation_id = ? AND r.client_id = ?");
    $stmt->bind_param("ii", $reclamation_id, $clientID);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $selected_reclamation = $row;
        
        // Get admin responses if table exists
        $checkTable = $conn->query("SHOW TABLES LIKE 'reponses_reclamations'");
        if ($checkTable && $checkTable->num_rows > 0) {
            $stmt2 = $conn->prepare("SELECT rr.*, a.nom as admin_nom 
                                    FROM reponses_reclamations rr 
                                    LEFT JOIN admins a ON rr.admin_id = a.admin_id 
                                    WHERE rr.reclamation_id = ? 
                                    ORDER BY rr.date_reponse ASC");
            $stmt2->bind_param("i", $reclamation_id);
            $stmt2->execute();
            $result2 = $stmt2->get_result();
            while ($resp = $result2->fetch_assoc()) {
                $responses[] = $resp;
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
    <title><?php echo t('my_claims_title'); ?> - Ashkili</title>
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
        
        [dir="rtl"] .reclamation-card {
            border-left: none;
            border-right: 4px solid var(--primary-color);
        }
        
        [dir="rtl"] .message-admin {
            margin-right: auto;
            margin-left: 2rem;
        }
        
        [dir="rtl"] .message-client {
            margin-left: auto;
            margin-right: 2rem;
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

        .btn-primary {
            background: var(--primary-gradient);
            color: #333;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(var(--primary-rgb), 0.3);
        }

        .table-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            overflow: hidden;
        }

        .table-header {
            padding: 1.5rem 2rem;
            border-bottom: 2px solid #f0f2f5;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: #f8f9fa;
        }

        th {
            padding: 1rem 1.5rem;
            text-align: left;
            font-size: 0.875rem;
            font-weight: 600;
            color: #555;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        tbody tr {
            border-bottom: 1px solid #f0f2f5;
            transition: background-color 0.3s ease;
        }

        tbody tr:hover {
            background: #f8f9fa;
        }

        tbody tr.unread {
            background: #fff9e6;
            font-weight: 600;
        }

        tbody tr.unread:hover {
            background: #fff4cc;
        }

        td {
            padding: 1.25rem 1.5rem;
            font-size: 0.9rem;
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-en-attente {
            background: #fff3cd;
            color: #856404;
        }

        .status-en-cours {
            background: #cce5ff;
            color: #0056b3;
        }

        .status-resolue {
            background: #d4edda;
            color: #155724;
        }

        .status-annulee {
            background: #f8d7da;
            color: #721c24;
        }

        .unread-indicator {
            color: #dc3545;
            font-weight: 700;
            margin-left: 0.5rem;
        }

        .btn-view {
            background: var(--primary-color);
            color: #333;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-size: 0.8rem;
        }

        .btn-view:hover {
            background: var(--primary-color);
            opacity: 0.9;
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

        .reclamation-details {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-bottom: 1rem;
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

        .description-section {
            margin-top: 1rem;
        }

        .description-text {
            background: white;
            padding: 1rem;
            border-radius: 8px;
            border-left: 4px solid var(--primary-color);
            font-style: italic;
            color: #555;
        }

        .responses-section {
            margin-top: 2rem;
        }

        .section-title {
            font-size: 1.25rem;
            color: #333;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .response-item {
            background: #e8f4fd;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border-left: 4px solid #2196F3;
        }

        .response-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .response-author {
            font-weight: 600;
            color: #333;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .response-date {
            color: #666;
            font-size: 0.875rem;
        }

        .response-message {
            color: #555;
            line-height: 1.6;
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
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
            margin-bottom: 2rem;
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

            .table-container {
                overflow-x: auto;
            }

            .detail-grid {
                grid-template-columns: 1fr;
            }

            th, td {
                padding: 0.75rem 1rem;
                font-size: 0.8rem;
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
            <li><a href="mes-colis.php"><i class="fas fa-boxes"></i> <?php echo t('my_parcels'); ?></a></li>
            <li><a href="mes-reclamations.php" class="active"><i class="fas fa-exclamation-circle"></i> <?php echo t('my_claims'); ?></a></li>
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
                <i class="fas fa-exclamation-circle"></i>
                <?php echo t('my_claims_title'); ?>
            </h1>
            <div style="display: flex; gap: 1rem;">
                <a href="nouvelle-reclamation.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    <?php echo t('new_claim'); ?>
                </a>
                <a href="dashboard.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i>
                    <?php echo t('return_dashboard'); ?>
                </a>
            </div>
        </div>

        <div class="table-container">
            <div class="table-header">
                <h3 class="table-title"><?php echo t('my_claims_title'); ?></h3>
                <span style="color: #666; font-size: 0.9rem;">
                    <?php echo count($reclamations); ?> <?php echo t('claims_found'); ?>
                </span>
            </div>

            <?php if (empty($reclamations)): ?>
                <div class="empty-state">
                    <i class="fas fa-clipboard-check empty-icon"></i>
                    <h3 class="empty-title"><?php echo t('no_claims_found'); ?></h3>
                    <p class="empty-desc"><?php echo t('no_claims_desc'); ?></p>
                    <a href="nouvelle-reclamation.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        <?php echo t('new_claim'); ?>
                    </a>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th><?php echo t('tracking_number'); ?></th>
                            <th><?php echo t('type'); ?></th>
                            <th><?php echo t('status'); ?></th>
                            <th><?php echo t('date'); ?></th>
                            <th><?php echo t('actions'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reclamations as $reclamation): ?>
                            <tr class="<?php echo $reclamation['unread_responses'] > 0 ? 'unread' : ''; ?>">
                                <td>#<?php echo $reclamation['reclamation_id']; ?></td>
                                <td>
                                    <?php if ($reclamation['code_suivi']): ?>
                                        <strong><?php echo htmlspecialchars($reclamation['code_suivi']); ?></strong>
                                    <?php else: ?>
                                        <em><?php echo t('not_specified'); ?></em>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($reclamation['type_reclamation']); ?></td>
                                <td>
                                    <span class="status-badge <?php 
                                        $status = strtolower($reclamation['statut_reclamation']);
                                        if (in_array($status, ['جديد', 'en attente', 'nouveau'])) {
                                            echo 'status-en-attente';
                                        } elseif (in_array($status, ['قيد المعالجة', 'en cours', 'traitement'])) {
                                            echo 'status-en-cours';
                                        } elseif (in_array($status, ['محلول', 'résolue', 'terminé'])) {
                                            echo 'status-resolue';
                                        } elseif (in_array($status, ['ملغى', 'annulée', 'annulé'])) {
                                            echo 'status-annulee';
                                        } else {
                                            echo 'status-en-attente';
                                        }
                                    ?>">
                                        <?php 
                                        $status_display = $reclamation['statut_reclamation'];
                                        if ($status_display === 'جديد' || $status_display === 'en attente') {
                                            echo t('status_pending');
                                        } elseif ($status_display === 'قيد المعالجة' || $status_display === 'en cours') {
                                            echo t('status_in_progress');
                                        } elseif ($status_display === 'محلول' || $status_display === 'résolue') {
                                            echo t('status_resolved');
                                        } elseif ($status_display === 'ملغى' || $status_display === 'annulée') {
                                            echo t('status_closed');
                                        } else {
                                            echo htmlspecialchars($status_display);
                                        }
                                        ?>
                                    </span>
                                    <?php if ($reclamation['unread_responses'] > 0): ?>
                                        <i class="fas fa-bell unread-indicator" title="<?php echo $reclamation['unread_responses']; ?> <?php echo t('unread_responses'); ?>"></i>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($reclamation['date_creation'])); ?></td>
                                <td>
                                    <button class="btn btn-view" onclick="openModal(<?php echo $reclamation['reclamation_id']; ?>)">
                                        <i class="fas fa-eye"></i>
                                        <?php echo t('view'); ?>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal for reclamation details -->
    <div id="reclamationModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><?php echo t('claim_details'); ?></h3>
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

        function openModal(reclamationId) {
            window.location.href = 'mes-reclamations.php?reclamation_id=' + reclamationId + '#modal';
        }

        function closeModal() {
            window.location.href = 'mes-reclamations.php';
        }

        // Check if modal should be opened
        <?php if ($selected_reclamation): ?>
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('reclamationModal');
            const modalBody = document.getElementById('modalBody');
            
            modalBody.innerHTML = `
                <div class="reclamation-details">
                    <div class="detail-grid">
                        <div class="detail-item">
                            <span class="detail-label"><?php echo t('claim_id'); ?></span>
                            <span class="detail-value">#<?php echo $selected_reclamation['reclamation_id']; ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label"><?php echo t('tracking_number'); ?></span>
                            <span class="detail-value"><?php echo $selected_reclamation['code_suivi'] ? htmlspecialchars($selected_reclamation['code_suivi']) : t('not_specified'); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label"><?php echo t('type'); ?></span>
                            <span class="detail-value"><?php echo htmlspecialchars($selected_reclamation['type_reclamation']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label"><?php echo t('current_status'); ?></span>
                            <span class="detail-value">
                                <?php 
                                $status_display = $selected_reclamation['statut_reclamation'];
                                if ($status_display === 'جديد' || $status_display === 'en attente') {
                                    echo t('status_pending');
                                } elseif ($status_display === 'قيد المعالجة' || $status_display === 'en cours') {
                                    echo t('status_in_progress');
                                } elseif ($status_display === 'محلول' || $status_display === 'résolue') {
                                    echo t('status_resolved');
                                } elseif ($status_display === 'ملغى' || $status_display === 'annulée') {
                                    echo t('status_closed');
                                } else {
                                    echo htmlspecialchars($status_display);
                                }
                                ?>
                            </span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label"><?php echo t('date'); ?></span>
                            <span class="detail-value"><?php echo date('d/m/Y H:i', strtotime($selected_reclamation['date_creation'])); ?></span>
                        </div>
                        <?php if ($selected_reclamation['date_mise_a_jour']): ?>
                        <div class="detail-item">
                            <span class="detail-label"><?php echo t('last_update'); ?></span>
                            <span class="detail-value"><?php echo date('d/m/Y H:i', strtotime($selected_reclamation['date_mise_a_jour'])); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (!empty($selected_reclamation['description'])): ?>
                    <div class="description-section">
                        <div class="detail-label"><?php echo t('description_label'); ?></div>
                        <div class="description-text">
                            "<?php echo nl2br(htmlspecialchars($selected_reclamation['description'])); ?>"
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="responses-section">
                    <h4 class="section-title">
                        <i class="fas fa-comments"></i>
                        <?php echo t('admin_responses'); ?>
                    </h4>
                    
                    <?php if (empty($responses)): ?>
                        <div style="text-align: center; padding: 2rem; color: #666;">
                            <i class="fas fa-inbox" style="font-size: 2rem; margin-bottom: 1rem; display: block;"></i>
                            <?php echo t('no_responses_message'); ?>
                        </div>
                    <?php else: ?>
                        <?php foreach ($responses as $response): ?>
                        <div class="response-item">
                            <div class="response-header">
                                <div class="response-author">
                                    <i class="fas fa-user-tie"></i>
                                    <?php echo htmlspecialchars($response['admin_nom'] ?? t('admin_default_name')); ?>
                                </div>
                                <div class="response-date">
                                    <?php echo date('d/m/Y H:i', strtotime($response['date_reponse'])); ?>
                                </div>
                            </div>
                            <div class="response-message">
                                <?php echo nl2br(htmlspecialchars($response['contenu_reponse'])); ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            `;
            
            modal.classList.add('active');
        });
        <?php endif; ?>

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('reclamationModal');
            if (event.target === modal) {
                closeModal();
            }
        }
    </script>

    <?php $conn->close(); ?>
</body>
</html>