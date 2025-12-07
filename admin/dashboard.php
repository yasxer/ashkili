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
$admin_email = $_SESSION['admin_email'] ?? '';

// Get statistics from database
$stats_reclamations_attente = 0;
$stats_clients = 0;
$stats_colis = 0;
$stats_total_reclamations = 0;

// Calculate changes (percentage from last week)
$change_reclamations = 0;
$change_clients = 0;
$change_colis = 0;
$change_total_reclamations = 0;

// Count pending complaints (current) - unread by admin
$result = $conn->query("SELECT COUNT(*) as count FROM reclamations WHERE statut_reclamation = 'en attente'");
if ($result) {
    $stats_reclamations_attente = $result->fetch_assoc()['count'];
}

// Store for notification badge
$unreadReclamationsCount = $stats_reclamations_attente;

// Count pending complaints (last week)
$result = $conn->query("SELECT COUNT(*) as count FROM reclamations WHERE statut_reclamation = 'en attente' AND date_creation <= DATE_SUB(NOW(), INTERVAL 7 DAY)");
if ($result) {
    $last_week_reclamations = $result->fetch_assoc()['count'];
    if ($last_week_reclamations > 0) {
        $change_reclamations = round((($stats_reclamations_attente - $last_week_reclamations) / $last_week_reclamations) * 100);
    } elseif ($stats_reclamations_attente > 0) {
        $change_reclamations = 100;
    }
}

// Count total clients (current)
$result = $conn->query("SELECT COUNT(*) as count FROM clients");
if ($result) {
    $stats_clients = $result->fetch_assoc()['count'];
}

// Count total clients (last week)
$result = $conn->query("SELECT COUNT(*) as count FROM clients WHERE date_inscription <= DATE_SUB(NOW(), INTERVAL 7 DAY)");
if ($result) {
    $last_week_clients = $result->fetch_assoc()['count'];
    if ($last_week_clients > 0) {
        $change_clients = round((($stats_clients - $last_week_clients) / $last_week_clients) * 100);
    } elseif ($stats_clients > 0) {
        $change_clients = 100;
    }
}

// Count total parcels (current)
$result = $conn->query("SELECT COUNT(*) as count FROM colis");
if ($result) {
    $stats_colis = $result->fetch_assoc()['count'];
}

// Count total parcels (last week)
$result = $conn->query("SELECT COUNT(*) as count FROM colis WHERE date_expedition <= DATE_SUB(NOW(), INTERVAL 7 DAY)");
if ($result) {
    $last_week_colis = $result->fetch_assoc()['count'];
    if ($last_week_colis > 0) {
        $change_colis = round((($stats_colis - $last_week_colis) / $last_week_colis) * 100);
    } elseif ($stats_colis > 0) {
        $change_colis = 100;
    }
}

// Count total complaints (current)
$result = $conn->query("SELECT COUNT(*) as count FROM reclamations");
if ($result) {
    $stats_total_reclamations = $result->fetch_assoc()['count'];
}

// Count total complaints (last week)
$result = $conn->query("SELECT COUNT(*) as count FROM reclamations WHERE date_creation <= DATE_SUB(NOW(), INTERVAL 7 DAY)");
if ($result) {
    $last_week_total_reclamations = $result->fetch_assoc()['count'];
    if ($last_week_total_reclamations > 0) {
        $change_total_reclamations = round((($stats_total_reclamations - $last_week_total_reclamations) / $last_week_total_reclamations) * 100);
    } elseif ($stats_total_reclamations > 0) {
        $change_total_reclamations = 100;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="<?php echo getCurrentLanguage(); ?>" <?php echo isRTL() ? 'dir="rtl"' : ''; ?>>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/theme.css">
    <script src="../js/theme.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f2f5;
        }

        .navbar {
            background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);
            color: #333;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.08);
            border-bottom: 1px solid #e9ecef;
        }

        [data-theme="dark"] .navbar {
            background: var(--bg-navbar);
            border-color: var(--border-color);
            color: var(--text-main);
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .navbar-brand i {
            font-size: 28px;
            color: var(--primary-color);
        }

        [data-theme="dark"] .navbar-brand i {
            color: var(--primary-color);
        }

        .navbar h1 {
            font-size: 22px;
            font-weight: 700;
            color: #2c3e50;
        }

        [data-theme="dark"] .navbar h1 {
            color: var(--text-main);
        }

        .navbar .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .navbar .user-name {
            font-size: 14px;
            color: var(--text-secondary);
            font-weight: 500;
        }

        .role-badge {
            background: var(--primary-gradient);
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            color: var(--primary-text);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .btn-logout {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 25px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
        }

        .btn-logout:hover {
            background: linear-gradient(135deg, #c82333, #a71e2a);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(220, 53, 69, 0.4);
            color: white;
        }

        .container {
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 30px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .page-header h2 {
            color: #333;
            font-size: 28px;
            font-weight: 600;
        }

        [data-theme="dark"] .page-header h2 {
            color: var(--text-main);
        }

        .filter-dropdown {
            padding: 8px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            background: white;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        [data-theme="dark"] .filter-dropdown {
            background: var(--input-bg);
            border-color: var(--input-border);
            color: var(--text-main);
        }

        .filter-dropdown:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        [data-theme="dark"] .filter-dropdown:focus {
            border-color: var(--primary-color);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
            position: relative;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        [data-theme="dark"] .stat-card {
            background: var(--bg-card);
            box-shadow: var(--shadow-sm);
            border-color: var(--border-color);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
        }

        .stat-card.green {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
        }

        .stat-card.red {
            background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);
            color: white;
        }

        .stat-card.blue {
            background: linear-gradient(135deg, #007bff 0%, #6610f2 100%);
            color: white;
        }

        .stat-card.yellow {
            background: var(--primary-gradient);
            color: var(--primary-text);
        }

        /* Dark mode colors for stat cards */
        [data-theme="dark"] .stat-card.green {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }

        [data-theme="dark"] .stat-card.red {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }

        [data-theme="dark"] .stat-card.blue {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
        }

        [data-theme="dark"] .stat-card.yellow {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .stat-icon {
            font-size: 32px;
        }

        /* Icon colors for light mode */
        .stat-card.yellow .stat-icon {
            color: #f59e0b;
        }

        .stat-card.green .stat-icon {
            color: #10b981;
        }

        .stat-card.blue .stat-icon {
            color: #3b82f6;
        }

        .stat-card.red .stat-icon {
            color: #ef4444;
        }

        /* Icon colors for dark mode */
        [data-theme="dark"] .stat-card.yellow .stat-icon {
            color: #fbbf24;
        }

        [data-theme="dark"] .stat-card.green .stat-icon {
            color: #34d399;
        }

        [data-theme="dark"] .stat-card.blue .stat-icon {
            color: #60a5fa;
        }

        [data-theme="dark"] .stat-card.red .stat-icon {
            color: #f87171;
        }

        .stat-change {
            font-size: 12px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 3px;
        }

        .stat-change.positive {
            color: rgba(255, 255, 255, 0.9);
        }

        .stat-change.negative {
            color: rgba(255, 255, 255, 0.9);
        }

        .stat-card.yellow .stat-change {
            color: var(--primary-text);
            opacity: 0.9;
        }

        .stat-number {
            font-size: 42px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 14px;
            opacity: 0.9;
            font-weight: 500;
        }

        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 25px;
            margin-bottom: 30px;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        [data-theme="dark"] .card {
            background: var(--bg-card);
            box-shadow: var(--shadow-sm);
            border-color: var(--border-color);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f8f9fa;
        }

        [data-theme="dark"] .card-header {
            border-color: var(--border-color);
        }

        .card-title {
            font-size: 18px;
            font-weight: 700;
            color: #2c3e50;
        }

        [data-theme="dark"] .card-title {
            color: var(--text-main);
        }

        .action-buttons {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .action-btn {
            padding: 18px 20px;
            background: white;
            color: #495057;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: left;
            text-decoration: none;
            display: flex;
            align-items: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        }

        [data-theme="dark"] .action-btn {
            background: var(--input-bg);
            border-color: var(--input-border);
            color: var(--text-main);
        }

        .action-btn:hover {
            background: var(--primary-color);
            color: #333;
            border-color: var(--primary-color);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(var(--primary-rgb), 0.3);
            gap: 12px;
        }

        [data-theme="dark"] .action-btn:hover {
            background: var(--primary-gradient);
            color: var(--primary-text);
            border-color: var(--primary-color);
        }
            
        

        .action-btn i {
            font-size: 20px;
            color: var(--primary-color);
        }

        .action-btn:hover {
            border-color: var(--primary-color);
            background: var(--hover-bg);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .recent-activity {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        [data-theme="dark"] .recent-activity {
            background: var(--bg-card);
            box-shadow: var(--shadow-sm);
        }

        .activity-item {
            padding: 15px 0;
            border-bottom: 1px solid #f0f2f5;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        [data-theme="dark"] .activity-item {
            border-color: var(--border-color);
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            background: rgba(var(--primary-rgb), 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-color);
            font-size: 16px;
        }

        [data-theme="dark"] .activity-icon {
            background: rgba(var(--primary-rgb), 0.1);
            color: var(--primary-color);
        }

        .activity-content {
            flex: 1;
        }

        .activity-title {
            font-size: 14px;
            font-weight: 600;
            color: #333;
            margin-bottom: 3px;
        }

        [data-theme="dark"] .activity-title {
            color: var(--text-main);
        }

        .activity-time {
            font-size: 12px;
            color: #999;
        }

        [data-theme="dark"] .activity-time {
            color: var(--text-muted);
        }

        @media (max-width: 1024px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
        }

        /* RTL Support */
        [dir="rtl"] {
            text-align: right;
        }
        
        [dir="rtl"] .navbar-brand,
        [dir="rtl"] .user-info {
            flex-direction: row-reverse;
        }
        
        [dir="rtl"] .action-btn {
            text-align: right;
        }
        
        [dir="rtl"] .stat-header {
            flex-direction: row-reverse;
        }
        
        [dir="rtl"] .activity-item {
            flex-direction: row-reverse;
            text-align: right;
        }
        
        /* Language Selector */
        .language-selector {
            position: relative;
            margin-right: 15px;
        }
        
        .language-btn {
            background: transparent;
            border: 2px solid var(--primary-color);
            color: var(--text-main);
            padding: 8px 15px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }
        
        .language-btn:hover {
            background: var(--primary-color);
            color: var(--primary-text);
            transform: translateY(-1px);
        }
        
        .language-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            min-width: 150px;
            display: none;
        }
        
        .language-dropdown.show {
            display: block;
        }
        
        .language-option {
            display: block;
            width: 100%;
            padding: 12px 15px;
            border: none;
            background: none;
            text-align: left;
            cursor: pointer;
            font-size: 14px;
            color: #333;
            transition: all 0.3s ease;
        }
        
        .language-option:hover {
            background: #f8f9fa;
        }
        
        .language-option.active {
            background: var(--primary-color);
            color: var(--primary-text);
            font-weight: 600;
        }
        
        [dir="rtl"] .language-dropdown {
            left: 0;
            right: auto;
        }
        
        [dir="rtl"] .language-option {
            text-align: right;
        }

        /* Theme Toggle Button */
        .theme-toggle {
            background: transparent;
            color: var(--text-main);
            padding: 8px 12px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .theme-toggle:hover {
            /* background: var(--primary-color); */
            color: var(--primary-color);
            transform: translateY(-1px);
        }

        @media (max-width: 768px) {
            .navbar {
                padding: 15px 20px;
            }

            .navbar-brand {
                flex-direction: column;
                gap: 10px;
            }

            .navbar .user-info {
                flex-direction: column;
                gap: 10px;
            }

            .container {
                padding: 0 15px;
            }

            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .action-buttons {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-brand">
            <i class="fas fa-box"></i>
            <h1>Ashkili - <?php echo t('admin_panel'); ?></h1>
        </div>
        <div class="user-info">
            <!-- Language Selector -->
            <div class="language-selector">
                <button type="button" class="language-btn" onclick="toggleLanguageDropdown()">
                    <i class="fas fa-language"></i>
                    <span><?php 
                        $langNames = ['fr' => 'FR', 'en' => 'EN', 'ar' => 'ع'];
                        echo $langNames[getCurrentLanguage()] ?? 'FR';
                    ?></span>
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="language-dropdown" id="languageDropdown">
                    <form method="POST" style="margin: 0;">
                        <input type="hidden" name="change_language" value="1">
                        <button type="submit" name="language" value="fr" class="language-option <?php echo getCurrentLanguage() === 'fr' ? 'active' : ''; ?>">
                            <i class="fas fa-circle" style="color: #0055A4;"></i>
                            Français
                        </button>
                        <button type="submit" name="language" value="en" class="language-option <?php echo getCurrentLanguage() === 'en' ? 'active' : ''; ?>">
                            <i class="fas fa-circle" style="color: #012169;"></i>
                            English
                        </button>
                        <button type="submit" name="language" value="ar" class="language-option <?php echo getCurrentLanguage() === 'ar' ? 'active' : ''; ?>">
                            <i class="fas fa-circle" style="color: #006233;"></i>
                            العربية
                        </button>
                    </form>
                </div>
            </div>

            <!-- Theme Toggle -->
            <button class="theme-toggle" onclick="toggleTheme()" title="Toggle Theme">
                <i class="fas fa-moon" id="theme-icon"></i>
            </button>
            
            <span class="user-name">
                <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($admin_nom); ?>
            </span>
            <a href="logout.php" class="btn-logout">
                <i class="fas fa-sign-out-alt"></i>
                <?php echo t('logout'); ?>
            </a>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h2><?php echo t('dashboard'); ?></h2>
        </div>

        <div class="stats-grid">
            <div class="stat-card yellow">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <?php if ($change_reclamations != 0): ?>
                        <div class="stat-change <?php echo $change_reclamations > 0 ? 'positive' : 'negative'; ?>">
                            <i class="fas fa-arrow-<?php echo $change_reclamations > 0 ? 'up' : 'down'; ?>"></i> 
                            <?php echo abs($change_reclamations); ?>%
                        </div>
                    <?php endif; ?>
                </div>
                <div class="stat-number"><?php echo $stats_reclamations_attente; ?></div>
                <div class="stat-label"><?php echo t('pending_complaints'); ?></div>
            </div>

            <div class="stat-card green">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <?php if ($change_clients != 0): ?>
                        <div class="stat-change <?php echo $change_clients > 0 ? 'positive' : 'negative'; ?>">
                            <i class="fas fa-arrow-<?php echo $change_clients > 0 ? 'up' : 'down'; ?>"></i> 
                            <?php echo abs($change_clients); ?>%
                        </div>
                    <?php endif; ?>
                </div>
                <div class="stat-number"><?php echo $stats_clients; ?></div>
                <div class="stat-label"><?php echo t('registered_clients'); ?></div>
            </div>

            <div class="stat-card blue">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-box"></i>
                    </div>
                    <?php if ($change_colis != 0): ?>
                        <div class="stat-change <?php echo $change_colis > 0 ? 'positive' : 'negative'; ?>">
                            <i class="fas fa-arrow-<?php echo $change_colis > 0 ? 'up' : 'down'; ?>"></i> 
                            <?php echo abs($change_colis); ?>%
                        </div>
                    <?php endif; ?>
                </div>
                <div class="stat-number"><?php echo $stats_colis; ?></div>
                <div class="stat-label"><?php echo t('tracked_packages'); ?></div>
            </div>

            <div class="stat-card red">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <?php if ($change_total_reclamations != 0): ?>
                        <div class="stat-change <?php echo $change_total_reclamations > 0 ? 'positive' : 'negative'; ?>">
                            <i class="fas fa-arrow-<?php echo $change_total_reclamations > 0 ? 'up' : 'down'; ?>"></i> 
                            <?php echo abs($change_total_reclamations); ?>%
                        </div>
                    <?php endif; ?>
                </div>
                <div class="stat-number"><?php echo $stats_total_reclamations; ?></div>
                <div class="stat-label"><?php echo t('total_complaints'); ?></div>
            </div>
        </div>

        <div class="content-grid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?php echo t('quick_actions'); ?></h3>
                </div>
                <div class="action-buttons">
                    <a href="manage_colis.php" class="action-btn">
                        <i class="fas fa-boxes"></i>
                        <span><?php echo t('manage_packages'); ?></span>
                    </a>
                    <a href="manage_clients.php" class="action-btn">
                        <i class="fas fa-users"></i>
                        <span><?php echo t('manage_clients'); ?></span>
                    </a>
                    <a href="reclamations.php" class="action-btn" style="position: relative;">
                        <i class="fas fa-clipboard-list"></i>
                        <span><?php echo t('manage_complaints'); ?></span>
                        <?php if ($unreadReclamationsCount > 0): ?>
                            <span style="position: absolute; top: -5px; right: -5px; background: linear-gradient(135deg, #f44336 0%, #e53935 100%); color: white; border-radius: 50%; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; box-shadow: 0 2px 8px rgba(244, 67, 54, 0.4);">
                                <?php echo $unreadReclamationsCount; ?>
                            </span>
                        <?php endif; ?>
                    </a>
                    <a href="add_client.php" class="action-btn">
                        <i class="fas fa-user-plus"></i>
                        <span><?php echo t('add_client'); ?></span>
                    </a>
                    <a href="add_colis.php" class="action-btn">
                        <i class="fas fa-box"></i>
                        <span><?php echo t('add_package'); ?></span>
                    </a>
                    
                </div>
            </div>

            <div class="recent-activity">
                <div class="card-header">
                    <h3 class="card-title"><?php echo t('recent_activity'); ?></h3>
                </div>
                <div class="activity-item">
                    <div class="activity-icon">
                        <i class="fas fa-plus"></i>
                    </div>
                    <div class="activity-content">
                        <div class="activity-title"><?php echo t('new_package_added'); ?></div>
                        <div class="activity-time"><?php echo t('2_hours_ago'); ?></div>
                    </div>
                </div>
                <div class="activity-item">
                    <div class="activity-icon">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="activity-content">
                        <div class="activity-title"><?php echo t('new_client_registered'); ?></div>
                        <div class="activity-time"><?php echo t('5_hours_ago'); ?></div>
                    </div>
                </div>
                <div class="activity-item">
                    <div class="activity-icon">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="activity-content">
                        <div class="activity-title"><?php echo t('complaint_resolved'); ?></div>
                        <div class="activity-time"><?php echo t('1_day_ago'); ?></div>
                    </div>
                </div>
                <div class="activity-item">
                    <div class="activity-icon">
                        <i class="fas fa-truck"></i>
                    </div>
                    <div class="activity-content">
                        <div class="activity-title"><?php echo t('package_delivered'); ?></div>
                        <div class="activity-time"><?php echo t('2_days_ago'); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleLanguageDropdown() {
            const dropdown = document.getElementById('languageDropdown');
            dropdown.classList.toggle('show');
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const selector = document.querySelector('.language-selector');
            const dropdown = document.getElementById('languageDropdown');
            
            if (!selector.contains(event.target)) {
                dropdown.classList.remove('show');
            }
        });
    </script>
</body>
</html>
