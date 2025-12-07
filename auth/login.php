<?php
session_start();

// Include language manager
require_once '../includes/language_manager.php';

// Redirect if already logged in
if (isset($_SESSION['client_id'])) {
    header("Location: ../client/dashboard.php");
    exit();
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle language change
    if (isset($_POST['change_language'])) {
        // Language change is handled in language_manager.php
        // Just need to prevent login logic from running
    } else {
        require_once '../db.php';
        
        $username = trim($_POST['username'] ?? '');
        $mot_de_passe = $_POST['mot_de_passe'] ?? '';
        
        if (empty($username) || empty($mot_de_passe)) {
            $error = t('fill_all_fields');
        } else {
            // Query database for user by username
            $stmt = $conn->prepare("SELECT client_id, nom, username, mot_de_passe FROM clients WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                
                // Verify password
                if (password_verify($mot_de_passe, $user['mot_de_passe'])) {
                    // Set session variables
                    $_SESSION['client_id'] = $user['client_id'];
                    $_SESSION['client_nom'] = $user['nom'];
                    $_SESSION['client_username'] = $user['username'];
                    
                    // Redirect to dashboard
                    header("Location: ../client/dashboard.php");
                    exit();
                } else {
                    $error = t('login_error');
                }
            } else {
                $error = t('login_error');
            }
            
            $stmt->close();
        }
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="<?php echo getCurrentLanguage(); ?>" dir="<?php echo isRTL() ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo t('login_title'); ?></title>
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
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        /* RTL Support */
        [dir="rtl"] {
            text-align: right;
        }

        [dir="rtl"] .forgot-password {
            text-align: left;
        }

        [dir="rtl"] input[type="password"] {
            padding-right: 15px;
            padding-left: 45px;
        }

        [dir="rtl"] .toggle-password {
            right: auto;
            left: 12px;
        }

        /* Language Selector */
        .language-selector {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            overflow: visible;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        [dir="rtl"] .language-selector {
            right: auto;
            left: 20px;
        }

        .lang-btn {
            background: white;
            border: none;
            padding: 8px 15px;
            border-radius: 25px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            color: #333;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            position: relative;
            z-index: 10001;
        }

        .lang-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.15);
        }

        .lang-dropdown {
            display: none;
            position: absolute;
            top: 110%;
            right: 0;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            z-index: 10000;
            min-width: 140px;
            border: 1px solid #f0f0f0;
        }
        
        [dir="rtl"] .lang-dropdown {
            right: auto;
            left: 0;
        }

        .lang-dropdown.show {
            display: block !important;
        }

        .lang-option {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 20px;
            cursor: pointer;
            transition: background 0.2s;
            color: #333;
            font-size: 14px;
        }

        .lang-option:hover {
            background: #f8f9fa;
        }

        .lang-flag {
            width: 20px;
            height: 15px;
            object-fit: cover;
            border-radius: 2px;
        }

        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
            padding: 40px;
            width: 100%;
            max-width: 450px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo-btn {
            display: inline-block;
            padding: 10px 30px;
            border: 2px solid var(--text-main);
            border-radius: 25px;
            background: transparent;
            margin-bottom: 20px;
        }

        .logo h1 {
            color: var(--text-main);
            font-size: 18px;
            font-weight: 500;
            margin: 0;
        }

        .logo p {
            color: var(--text-secondary);
            font-size: 14px;
        }

        h2 {
            text-align: center;
            color: var(--text-main);
            margin-bottom: 10px;
            font-size: 24px;
        }

        .subtitle {
            text-align: center;
            color: var(--text-secondary);
            margin-bottom: 30px;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-main);
            font-weight: 500;
            font-size: 14px;
        }

        .input-wrapper {
            position: relative;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        input[type="password"] {
            padding-right: 45px;
        }

        input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.1);
        }

        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #666;
            font-size: 18px;
            padding: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .toggle-password:hover {
            color: var(--primary-color);
        }

        .toggle-password:focus {
            outline: none;
        }

        .toggle-password:active {
            transform: translateY(0);
        }

        .toggle-password svg {
            width: 20px;
            height: 20px;
        }

        .alert {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .forgot-password {
            text-align: right;
            margin-top: 8px;
        }

        .forgot-password a {
            color: #333;
            text-decoration: none;
            font-size: 13px;
        }

        .forgot-password a:hover {
            text-decoration: underline;
        }

        .btn-submit {
            width: 100%;
            padding: 14px;
            background: var(--primary-gradient);
            color: #333;
            border: none;
            border-radius: 25px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(var(--primary-rgb), 0.3);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .register-link {
            text-align: center;
            margin-top: 30px;
            color: #666;
            font-size: 14px;
        }

        .register-link a {
            color: #333;
            text-decoration: none;
            font-weight: 600;
        }

        .register-link a:hover {
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 30px 20px;
            }

            .logo h1 {
                font-size: 24px;
            }

            h2 {
                font-size: 20px;
            }
        }
    </style>
</head>
    

    <div class="login-container">
        <div class="logo">
            <div class="logo-btn">
                <a href="../index.php" style="text-decoration: none; color: inherit;"> <h1><?php echo t('site_name'); ?></h1></a>
            </div>
        </div>

        <h2><?php echo t('login_header'); ?></h2>
        <p class="subtitle"><?php echo t('login_subtitle'); ?></p>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="" id="loginForm">
            <div class="form-group">
                <label for="username"><?php echo t('username_label'); ?></label>
                <input type="text" id="username" name="username" required value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" placeholder="<?php echo t('username_placeholder'); ?>">
            </div>

            <div class="form-group">
                <label for="mot_de_passe"><?php echo t('password_label'); ?></label>
                <div class="input-wrapper">
                    <input type="password" id="mot_de_passe" name="mot_de_passe" required placeholder="<?php echo t('password_placeholder'); ?>">
                    <button type="button" class="toggle-password" onclick="togglePassword('mot_de_passe', this)">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </button>
                </div>
                <div class="forgot-password">
                    <a href="forgot-password.php"><?php echo t('forgot_password'); ?></a>
                </div>
            </div>

            <button type="submit" class="btn-submit"><?php echo t('login_btn'); ?></button>
        </form>

        <div class="register-link">
            <?php echo t('register_text'); ?>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Language Switcher
            const langBtn = document.getElementById('langBtn');
            const langDropdown = document.getElementById('langDropdown');
            const langOptions = document.querySelectorAll('.lang-option');
            const langForm = document.getElementById('langForm');
            const langInput = document.getElementById('langInput');

            if (langBtn && langDropdown) {
                langBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    langDropdown.classList.toggle('show');
                });

                document.addEventListener('click', (e) => {
                    if (!langDropdown.contains(e.target) && !langBtn.contains(e.target)) {
                        langDropdown.classList.remove('show');
                    }
                });

                langOptions.forEach(option => {
                    option.addEventListener('click', (e) => {
                        e.preventDefault();
                        const lang = option.getAttribute('data-lang');
                        if (langInput && langForm) {
                            langInput.value = lang;
                            langForm.submit();
                        }
                    });
                });
            }
        });

        function togglePassword(inputId, button) {
            const input = document.getElementById(inputId);
            if (input.type === 'password') {
                input.type = 'text';
                button.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" /></svg>';
            } else {
                input.type = 'password';
                button.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>';
            }
        }

        // Form validation
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('mot_de_passe').value;
            
            if (!username || !password) {
                e.preventDefault();
                alert('<?php echo t('fill_all_fields'); ?>');
                return false;
            }
            
            if (username.length < 3) {
                e.preventDefault();
                alert('<?php echo t('username_min_length'); ?>');
                return false;
            }
        });
    </script>
</body>
</html>
