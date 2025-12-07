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

// Get client details
$client_info = null;
$stmt = $conn->prepare("SELECT * FROM clients WHERE client_id = ?");
$stmt->bind_param("i", $clientID);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $client_info = $row;
}
$stmt->close();

// Handle profile update
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if ($nom && $username) {
        // Check if username is already taken by another user
        $stmt = $conn->prepare("SELECT client_id FROM clients WHERE username = ? AND client_id != ?");
        $stmt->bind_param("si", $username, $clientID);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error_message = t('username_taken');
        } else {
            // Update profile
            if (!empty($mot_de_passe)) {
                // Password change requested
                if ($mot_de_passe !== $confirm_password) {
                    $error_message = t('passwords_do_not_match');
                } elseif (strlen($mot_de_passe) < 6) {
                    $error_message = t('password_too_short');
                } else {
                    // Update with new password
                    $hashed_password = password_hash($mot_de_passe, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("UPDATE clients SET nom = ?, username = ?, telephone = ?, mot_de_passe = ? WHERE client_id = ?");
                    $stmt->bind_param("ssssi", $nom, $username, $telephone, $hashed_password, $clientID);
                }
            } else {
                // Update without changing password
                $stmt = $conn->prepare("UPDATE clients SET nom = ?, username = ?, telephone = ? WHERE client_id = ?");
                $stmt->bind_param("sssi", $nom, $username, $telephone, $clientID);
            }
            
            if (!$error_message && $stmt->execute()) {
                $success_message = t('profile_updated_success');
                $_SESSION['client_nom'] = $nom; // Update session
                
                // Refresh client info
                $stmt2 = $conn->prepare("SELECT * FROM clients WHERE client_id = ?");
                $stmt2->bind_param("i", $clientID);
                $stmt2->execute();
                $result2 = $stmt2->get_result();
                if ($row2 = $result2->fetch_assoc()) {
                    $client_info = $row2;
                }
                $stmt2->close();
            } elseif (!$error_message) {
                $error_message = t('profile_update_error');
            }
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
    <title><?php echo t('my_profile'); ?> - Ashkili</title>
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

        .profile-container {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .profile-header {
            text-align: center;
            margin-bottom: 2rem;
            padding-bottom: 2rem;
            border-bottom: 2px solid #f0f2f5;
        }

        .profile-avatar {
            width: 100px;
            height: 100px;
            background: var(--primary-gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 3rem;
            color: #333;
        }

        .profile-name {
            font-size: 1.5rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .profile-role {
            color: #666;
            font-size: 1rem;
        }

        .form-section {
            margin-bottom: 2rem;
        }

        .form-section-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #f0f2f5;
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

        .form-input {
            width: 100%;
            padding: 0.75rem 1rem;
            background: var(--input-bg);
            color: var(--text-main);
            border: 2px solid var(--input-border);
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary-color);
            background: var(--input-bg);
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

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
            text-align: center;
            border-left: 4px solid var(--primary-color);
        }

        .stat-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 0.25rem;
        }

        .stat-label {
            font-size: 0.875rem;
            color: #666;
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

            .profile-container {
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
                <i class="fas fa-user-cog"></i>
                <?php echo t('my_profile'); ?>
            </h1>
            <a href="dashboard.php" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i>
                <?php echo t('back_to_dashboard'); ?>
            </a>
        </div>

        <div class="profile-container">
            <div class="profile-header">
                <div class="profile-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <h2 class="profile-name"><?php echo htmlspecialchars($client_info['nom'] ?? 'Client'); ?></h2>
                <p class="profile-role"><?php echo t('client_role'); ?></p>
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

            <form method="POST" action="">
                <div class="form-section">
                    <h3 class="form-section-title"><?php echo t('personal_info'); ?></h3>
                    
                    <div class="form-group">
                        <label for="nom" class="form-label">
                            <?php echo t('full_name'); ?> <span class="required">*</span>
                        </label>
                        <input type="text" name="nom" id="nom" class="form-input" 
                               value="<?php echo htmlspecialchars($client_info['nom'] ?? ''); ?>" required>
                        <div class="form-help"><?php echo t('full_name_help'); ?></div>
                    </div>

                    <div class="form-group">
                        <label for="username" class="form-label">
                            <?php echo t('username'); ?> <span class="required">*</span>
                        </label>
                        <input type="text" name="username" id="username" class="form-input" 
                               value="<?php echo htmlspecialchars($client_info['username'] ?? ''); ?>" required>
                        <div class="form-help"><?php echo t('username_help'); ?></div>
                    </div>

                    <div class="form-group">
                        <label for="telephone" class="form-label"><?php echo t('phone'); ?></label>
                        <input type="tel" name="telephone" id="telephone" class="form-input" 
                               value="<?php echo htmlspecialchars($client_info['telephone'] ?? ''); ?>">
                        <div class="form-help"><?php echo t('phone_help'); ?></div>
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="form-section-title"><?php echo t('security'); ?></h3>
                    
                    <div class="form-group">
                        <label for="mot_de_passe" class="form-label"><?php echo t('new_password'); ?></label>
                        <input type="password" name="mot_de_passe" id="mot_de_passe" class="form-input">
                        <div class="form-help"><?php echo t('new_password_help'); ?></div>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password" class="form-label"><?php echo t('confirm_password'); ?></label>
                        <input type="password" name="confirm_password" id="confirm_password" class="form-input">
                        <div class="form-help"><?php echo t('confirm_password_help'); ?></div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    <?php echo t('update_profile'); ?>
                </button>
            </form>
        </div>
    </div>

    <?php $conn->close(); ?>

    <script>
        // Password confirmation validation
        const password = document.getElementById('mot_de_passe');
        const confirmPassword = document.getElementById('confirm_password');

        function validatePasswords() {
            if (password.value && confirmPassword.value) {
                if (password.value !== confirmPassword.value) {
                    confirmPassword.setCustomValidity('Les mots de passe ne correspondent pas');
                } else {
                    confirmPassword.setCustomValidity('');
                }
            }
        }

        password.addEventListener('input', validatePasswords);
        confirmPassword.addEventListener('input', validatePasswords);

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