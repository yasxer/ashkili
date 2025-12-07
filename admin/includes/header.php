<?php
// Make sure session is started and user is authenticated
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Include language manager
require_once __DIR__ . '/language_manager.php';

$admin_id = $_SESSION['admin_id'];
$admin_nom = $_SESSION['admin_nom'] ?? 'Admin';
$admin_role = $_SESSION['admin_role'] ?? 'Support';
?>

<!DOCTYPE html>
<html lang="<?php echo getCurrentLanguage(); ?>" <?php echo isRTL() ? 'dir="rtl"' : ''; ?>>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Admin - Ashkili'; ?></title>
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
        }

        /* RTL Support */
        [dir="rtl"] {
            text-align: right;
        }
        
        [dir="rtl"] .navbar-brand,
        [dir="rtl"] .user-info {
            flex-direction: row-reverse;
        }
        
        [dir="rtl"] .navbar-nav {
            flex-direction: row-reverse;
        }
        
        /* Language Selector */
        .language-selector {
            position: relative;
            margin-right: 15px;
        }
        
        [dir="rtl"] .language-selector {
            margin-right: 0;
            margin-left: 15px;
        }
        
        .language-btn {
            background: rgba(var(--primary-rgb), 0.1);
            border: 2px solid var(--primary-color);
            color: #333;
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
            font-weight: 600;
        }
        
        [dir="rtl"] .language-dropdown {
            left: 0;
            right: auto;
        }
        
        [dir="rtl"] .language-option {
            text-align: right;
        }

        .navbar {
            background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);
            color: #333;
            padding: 15px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.08);
            border-bottom: 1px solid #e9ecef;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .navbar-brand i {
            font-size: 24px;
            color: #333;
        }

        .navbar h1 {
            font-size: 24px;
            font-weight: 600;
            margin: 0;
        }

        .navbar-nav {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .nav-link {
            color: #333;
            text-decoration: none;
            font-weight: 500;
            padding: 8px 16px;
            border-radius: 12px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(0, 0, 0, 0.05);
        }

        .nav-link:hover {
            background: rgba(0, 0, 0, 0.1);
            transform: translateY(-1px);
        }

        .navbar .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
            font-weight: 500;
        }

        .navbar .user-name {
            font-size: 14px;
            font-weight: 500;
        }

        .role-badge {
            background: rgba(0, 0, 0, 0.1);
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }

        .btn-logout {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 20px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 600;
        }

        .btn-logout:hover {
            background: linear-gradient(135deg, #c82333, #bd2130);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px;
        }

        .page-header {
            background: white;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-title {
            font-size: 28px;
            font-weight: 600;
            color: #333;
            display: flex;
            align-items: center;
            gap: 15px;
            margin: 0;
        }

        .page-title i {
            color: var(--primary-color);
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                gap: 15px;
                padding: 15px;
            }

            .navbar-nav {
                gap: 10px;
            }

            .navbar .user-info {
                gap: 10px;
            }

            .page-header {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }

            .container {
                padding: 20px 15px;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-brand">
            <i class="<?php echo isset($page_icon) ? $page_icon : 'fas fa-cog'; ?>"></i>
            <h1><?php echo isset($page_heading) ? $page_heading : 'Ashkili - Administration'; ?></h1>
        </div>
        <div class="navbar-nav">
            <a href="dashboard.php" class="nav-link">
                <i class="fas fa-tachometer-alt"></i> <?php echo t('dashboard'); ?>
            </a>
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
                            <i class="fas fa-circle" style="color: #0055A4; margin-right: 8px;"></i>
                            Français
                        </button>
                        <button type="submit" name="language" value="en" class="language-option <?php echo getCurrentLanguage() === 'en' ? 'active' : ''; ?>">
                            <i class="fas fa-circle" style="color: #012169; margin-right: 8px;"></i>
                            English
                        </button>
                        <button type="submit" name="language" value="ar" class="language-option <?php echo getCurrentLanguage() === 'ar' ? 'active' : ''; ?>">
                            <i class="fas fa-circle" style="color: #006233; margin-right: 8px;"></i>
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
                <i class="fas fa-sign-out-alt"></i> <?php echo t('logout'); ?>
            </a>
        </div>
    </nav>