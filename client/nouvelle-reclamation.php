<?php
session_start();

// Check if client is logged in
if (!isset($_SESSION['client_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

require_once '../db.php';
require_once '../includes/language_manager.php';

$clientID = $_SESSION['client_id'];
$client_nom = $_SESSION['client_nom'] ?? 'Client';

// Get client's colis for the dropdown
$colis_list = [];
$stmt = $conn->prepare("SELECT colis_id, code_suivi FROM colis WHERE client_id = ? ORDER BY date_expedition DESC");
$stmt->bind_param("i", $clientID);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $colis_list[] = $row;
}
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $colis_id = $_POST['colis_id'] ?? null;
    $type_reclamation = $_POST['type_reclamation'] ?? '';
    $description = $_POST['description'] ?? '';
    
    if ($colis_id && $type_reclamation && $description) {
        // Insert new reclamation
        $stmt = $conn->prepare("INSERT INTO reclamations (client_id, colis_id, type_reclamation, description, statut_reclamation, date_creation) VALUES (?, ?, ?, ?, 'en attente', NOW())");
        $stmt->bind_param("iiss", $clientID, $colis_id, $type_reclamation, $description);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = t('success_claim_submitted');
            header("Location: mes-reclamations.php");
            exit();
        } else {
            $error_message = t('error_claim_submission');
        }
        $stmt->close();
    } else {
        $error_message = t('error_fill_all');
    }
}

$current_lang = getCurrentLanguage();
$is_rtl = ($current_lang === 'ar');
?>

<!DOCTYPE html>
<html lang="<?php echo $current_lang; ?>" dir="<?php echo $is_rtl ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo t('new_claim_title'); ?> - Ashkili</title>
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
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
            color: #333;
        }

        /* RTL Support */
        [dir="rtl"] body {
            font-family: 'Tahoma', 'Segoe UI', sans-serif;
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
            max-width: 800px;
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
            width: 100%;
            justify-content: center;
            font-size: 1.1rem;
            padding: 1rem 2rem;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(var(--primary-rgb), 0.3);
        }

        .form-container {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .form-header {
            margin-bottom: 2rem;
            text-align: center;
        }

        .form-title {
            font-size: 1.3rem;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .form-subtitle {
            color: #666;
            font-size: 0.95rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-size: 1rem;
            font-weight: 600;
            color: #555;
            margin-bottom: 0.5rem;
        }

        .form-label .required {
            color: #dc3545;
        }

        .form-input,
        .form-select,
        .form-textarea {
            width: 100%;
            padding: 0.75rem 1rem;
            background: var(--input-bg);
            color: var(--text-main);
            border: 2px solid var(--input-border);
            border-radius: 8px;
            font-size: 1rem;
            font-family: inherit;
            transition: all 0.3s ease;
        }

        .form-input:focus,
        .form-select:focus,
        .form-textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            background: var(--input-bg);
        }

        .form-textarea {
            min-height: 120px;
            resize: vertical;
        }

        .form-help {
            font-size: 0.875rem;
            color: #666;
            margin-top: 0.25rem;
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .info-card {
            background: #e8f4fd;
            border: 1px solid #bee5eb;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .info-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #0c5460;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .info-text {
            color: #0c5460;
            font-size: 0.9rem;
        }

        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 2rem;
        }

        .empty-icon {
            font-size: 3rem;
            color: #ddd;
            margin-bottom: 1rem;
        }

        .empty-title {
            font-size: 1.3rem;
            color: #666;
            margin-bottom: 0.5rem;
        }

        .empty-desc {
            color: #999;
            margin-bottom: 1rem;
        }

        .lang-switcher {
            display: flex;
            gap: 0.5rem;
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

            .container {
                padding: 0 0.5rem;
            }

            .form-container {
                padding: 1.5rem;
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
                <i class="fas fa-plus-circle"></i>
                <?php echo t('new_claim_title'); ?>
            </h1>
            <a href="mes-reclamations.php" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i>
                <?php echo t('back_to_claims'); ?>
            </a>
        </div>

        <?php if (empty($colis_list)): ?>
            <div class="empty-state">
                <i class="fas fa-box-open empty-icon"></i>
                <h3 class="empty-title"><?php echo t('no_parcels_found'); ?></h3>
                <p class="empty-desc"><?php echo t('no_parcels_desc'); ?></p>
                <a href="dashboard.php" class="btn btn-primary">
                    <i class="fas fa-home"></i>
                    <?php echo t('back_to_dashboard'); ?>
                </a>
            </div>
        <?php else: ?>
            <div class="info-card">
                <h4 class="info-title">
                    <i class="fas fa-info-circle"></i>
                    <?php echo t('important_info'); ?>
                </h4>
                <p class="info-text">
                    <?php echo t('important_info_text'); ?>
                </p>
            </div>

            <div class="form-container">
                <div class="form-header">
                    <h2 class="form-title"><?php echo t('claim_details_title'); ?></h2>
                    <p class="form-subtitle"><?php echo t('fill_required_fields'); ?></p>
                </div>

                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-group">
                        <label for="colis_id" class="form-label">
                            <?php echo t('parcel_concerned'); ?> <span class="required">*</span>
                        </label>
                        <select name="colis_id" id="colis_id" class="form-select" required>
                            <option value=""><?php echo t('select_parcel'); ?></option>
                            <?php foreach ($colis_list as $colis): ?>
                                <option value="<?php echo $colis['colis_id']; ?>" <?php echo (isset($_POST['colis_id']) && $_POST['colis_id'] == $colis['colis_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($colis['code_suivi']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-help"><?php echo t('select_parcel_help'); ?></div>
                    </div>

                    <div class="form-group">
                        <label for="type_reclamation" class="form-label">
                            <?php echo t('claim_type'); ?> <span class="required">*</span>
                        </label>
                        <select name="type_reclamation" id="type_reclamation" class="form-select" required>
                            <option value=""><?php echo t('select_type'); ?></option>
                            <option value="Colis perdu" <?php echo (isset($_POST['type_reclamation']) && $_POST['type_reclamation'] == 'Colis perdu') ? 'selected' : ''; ?>><?php echo t('type_lost'); ?></option>
                            <option value="Colis endommagé" <?php echo (isset($_POST['type_reclamation']) && $_POST['type_reclamation'] == 'Colis endommagé') ? 'selected' : ''; ?>><?php echo t('type_damaged'); ?></option>
                            <option value="Retard de livraison" <?php echo (isset($_POST['type_reclamation']) && $_POST['type_reclamation'] == 'Retard de livraison') ? 'selected' : ''; ?>><?php echo t('type_delay'); ?></option>
                            <option value="Problème de livraison" <?php echo (isset($_POST['type_reclamation']) && $_POST['type_reclamation'] == 'Problème de livraison') ? 'selected' : ''; ?>><?php echo t('type_delivery_issue'); ?></option>
                            <option value="Contenu manquant" <?php echo (isset($_POST['type_reclamation']) && $_POST['type_reclamation'] == 'Contenu manquant') ? 'selected' : ''; ?>><?php echo t('type_missing_content'); ?></option>
                            <option value="Service client" <?php echo (isset($_POST['type_reclamation']) && $_POST['type_reclamation'] == 'Service client') ? 'selected' : ''; ?>><?php echo t('type_customer_service'); ?></option>
                            <option value="Autre" <?php echo (isset($_POST['type_reclamation']) && $_POST['type_reclamation'] == 'Autre') ? 'selected' : ''; ?>><?php echo t('type_other'); ?></option>
                        </select>
                        <div class="form-help"><?php echo t('select_type_help'); ?></div>
                    </div>

                    <div class="form-group">
                        <label for="description" class="form-label">
                            <?php echo t('detailed_description'); ?> <span class="required">*</span>
                        </label>
                        <textarea name="description" id="description" class="form-textarea" required placeholder="<?php echo t('description_placeholder'); ?>"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                        <div class="form-help">
                            <?php echo t('description_help'); ?>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i>
                        <?php echo t('submit_claim'); ?>
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <?php $conn->close(); ?>

    <script>
        // Auto-select colis if passed via URL parameter
        const urlParams = new URLSearchParams(window.location.search);
        const colisId = urlParams.get('colis_id');
        if (colisId) {
            const colisSelect = document.getElementById('colis_id');
            colisSelect.value = colisId;
        }

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