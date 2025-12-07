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

// Get client statistics
$stats = [
    'total_colis' => 0,
    'colis_livres' => 0,
    'reclamations_en_attente' => 0,
    'unread_responses' => 0
];

// Total colis for this client
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM colis WHERE client_id = ?");
$stmt->bind_param("i", $clientID);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $stats['total_colis'] = $row['total'];
}
$stmt->close();

// Delivered colis
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM colis WHERE client_id = ? AND statut_actuel IN ('Livré', 'Delivered', 'مسلم')");
$stmt->bind_param("i", $clientID);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $stats['colis_livres'] = $row['total'];
}
$stmt->close();

// Pending reclamations
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM reclamations WHERE client_id = ? AND statut_reclamation NOT IN ('Résolue', 'محلول', 'Annulée', 'ملغى')");
$stmt->bind_param("i", $clientID);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $stats['reclamations_en_attente'] = $row['total'];
}
$stmt->close();

// Unread responses from admin
$checkTable = $conn->query("SHOW TABLES LIKE 'reponses_reclamations'");
if ($checkTable && $checkTable->num_rows > 0) {
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM reponses_reclamations rr 
                           JOIN reclamations r ON rr.reclamation_id = r.reclamation_id 
                           WHERE r.client_id = ? AND rr.lu_par_client = 0");
    $stmt->bind_param("i", $clientID);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $stats['unread_responses'] = $row['total'];
    }
    $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="<?php echo getCurrentLanguage(); ?>" dir="<?php echo $lang_dir; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo t('dashboard_title'); ?> - Ashkili</title>
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
        
        [dir="rtl"] .stat-card.primary,
        [dir="rtl"] .stat-card.success,
        [dir="rtl"] .stat-card.warning {
            border-left: none;
            border-right: 4px solid;
        }
        
        [dir="rtl"] .notification-badge {
            right: auto;
            left: -5px;
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
            color: #333;
            min-height: 100vh;
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
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .welcome-section {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        [data-theme="dark"] .welcome-section {
            background: var(--bg-card);
            box-shadow: var(--shadow-sm);
        }

        .welcome-title {
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
            color: #333;
        }

        [data-theme="dark"] .welcome-title {
            color: var(--text-main);
        }

        .welcome-subtitle {
            color: #666;
            font-size: 1rem;
        }

        [data-theme="dark"] .welcome-subtitle {
            color: var(--text-secondary);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: transform 0.3s ease;
        }

        [data-theme="dark"] .stat-card {
            background: var(--bg-card);
            box-shadow: var(--shadow-sm);
        }

        .stat-card:hover {
            transform: translateY(-2px);
        }

        .stat-card.primary {
            border-left: 4px solid var(--primary-color);
        }

        .stat-card.success {
            border-left: 4px solid #28a745;
        }

        .stat-card.warning {
            border-left: 4px solid #dc3545;
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .stat-icon {
            font-size: 2rem;
            padding: 0.75rem;
            border-radius: 12px;
        }

        .stat-icon.primary {
            background: rgba(var(--primary-rgb), 0.1);
            color: var(--primary-color);
        }

        .stat-icon.success {
            background: rgba(40, 167, 69, 0.1);
            color: #28a745;
        }

        .stat-icon.warning {
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #333;
        }

        .stat-label {
            font-size: 0.9rem;
            color: #666;
            font-weight: 500;
        }

        .tracking-section {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 2rem;
        }

        .section-title {
            font-size: 1.3rem;
            margin-bottom: 1.5rem;
            color: #333;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .tracking-form {
            display: flex;
            gap: 1rem;
            max-width: 600px;
        }

        .form-input {
            flex: 1;
            padding: 0.75rem 1rem;
            background: var(--input-bg);
            color: var(--text-main);
            border: 2px solid var(--input-border);
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary-color);
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

        .btn-primary {
            background: var(--primary-gradient);
            color: #333;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(var(--primary-rgb), 0.3);
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

        .quick-actions {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        [data-theme="dark"] .quick-actions {
            background: var(--bg-card);
            box-shadow: var(--shadow-sm);
        }

        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .action-card {
            text-align: center;
            padding: 1.5rem;
            border-radius: 12px;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
            text-decoration: none;
            color: #333;
        }

        [data-theme="dark"] .action-card {
            border-color: var(--border-color);
            color: var(--text-main);
        }

        .action-card:hover {
            border-color: var(--primary-color);
            transform: translateY(-2px);
        }

        [data-theme="dark"] .action-card:hover {
            border-color: var(--primary-color);
        }

        .action-icon {
            font-size: 2rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .action-title {
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .action-desc {
            font-size: 0.9rem;
            color: #666;
        }

        @media (max-width: 768px) {
            .navbar {
                padding: 1rem;
                flex-direction: column;
                gap: 1rem;
            }

            .navbar-nav {
                gap: 1rem;
            }

            .tracking-form {
                flex-direction: column;
            }

            .stats-grid {
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
            <li><a href="dashboard.php" class="active"><i class="fas fa-home"></i> <?php echo t('dashboard_title'); ?></a></li>
            <li><a href="mes-colis.php"><i class="fas fa-boxes"></i> <?php echo t('my_parcels'); ?></a></li>
            <li>
                <a href="mes-reclamations.php">
                    <i class="fas fa-exclamation-circle"></i> <?php echo t('my_claims'); ?>
                    <?php if ($stats['unread_responses'] > 0): ?>
                        <span class="notification-badge"><?php echo $stats['unread_responses']; ?></span>
                    <?php endif; ?>
                </a>
            </li>
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
        <div class="welcome-section">
            <h1 class="welcome-title"><?php echo t('welcome'); ?>, <?php echo htmlspecialchars($client_nom); ?>!</h1>
            <p class="welcome-subtitle"><?php echo t('overview_activity'); ?></p>
        </div>

        <div class="stats-grid">
            <div class="stat-card primary">
                <div class="stat-header">
                    <div>
                        <div class="stat-number"><?php echo $stats['total_colis']; ?></div>
                        <div class="stat-label"><?php echo t('total_parcels'); ?></div>
                    </div>
                    <div class="stat-icon primary">
                        <i class="fas fa-boxes"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card success">
                <div class="stat-header">
                    <div>
                        <div class="stat-number"><?php echo $stats['colis_livres']; ?></div>
                        <div class="stat-label"><?php echo t('parcels_delivered'); ?></div>
                    </div>
                    <div class="stat-icon success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card warning">
                <div class="stat-header">
                    <div>
                        <div class="stat-number"><?php echo $stats['reclamations_en_attente']; ?></div>
                        <div class="stat-label"><?php echo t('claims_pending'); ?></div>
                    </div>
                    <div class="stat-icon warning">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
            </div>

            <?php if ($stats['unread_responses'] > 0): ?>
            <div class="stat-card warning">
                <div class="stat-header">
                    <div>
                        <div class="stat-number"><?php echo $stats['unread_responses']; ?></div>
                        <div class="stat-label"><?php echo t('unread_responses'); ?></div>
                    </div>
                    <div class="stat-icon warning">
                        <i class="fas fa-bell"></i>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        

        <div class="quick-actions">
            <h2 class="section-title">
                <i class="fas fa-bolt"></i>
                <?php echo t('quick_actions'); ?>
            </h2>
            <div class="actions-grid">
                <a href="mes-colis.php" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-list"></i>
                    </div>
                    <div class="action-title"><?php echo t('my_parcels'); ?></div>
                    <div class="action-desc"><?php echo t('view_parcels_desc'); ?></div>
                </a>

                <a href="mes-reclamations.php" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <div class="action-title"><?php echo t('my_claims'); ?></div>
                    <div class="action-desc"><?php echo t('manage_claims_desc'); ?></div>
                </a>

                <a href="nouvelle-reclamation.php" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-plus-circle"></i>
                    </div>
                    <div class="action-title"><?php echo t('new_claim'); ?></div>
                    <div class="action-desc"><?php echo t('new_claim_desc'); ?></div>
                </a>

                <a href="profil.php" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-user-cog"></i>
                    </div>
                    <div class="action-title"><?php echo t('my_profile'); ?></div>
                    <div class="action-desc"><?php echo t('profile_desc'); ?></div>
                </a>
            </div>
        </div>
    </div>

    <?php $conn->close(); ?>
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
    </script>
</body>
</html>