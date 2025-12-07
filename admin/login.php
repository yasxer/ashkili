<?php
session_start();

require_once 'includes/language_manager.php';

// Redirect if already logged in
if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once '../db.php';
    
    $email = trim($_POST['email'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';
    
    if (empty($email) || empty($mot_de_passe)) {
        $error = t('fill_all_fields', 'Veuillez remplir tous les champs.');
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = t('invalid_email', 'Adresse e-mail invalide.');
    } else {
        // Query database for admin
        $stmt = $conn->prepare("SELECT admin_id, nom, mot_de_passe, actif FROM admins WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $admin = $result->fetch_assoc();
            
            // Check if account is active
            if ($admin['actif'] != 1) {
                $error = t('account_inactive', 'Ce compte est désactivé. Contactez l\'administrateur.');
            } elseif (password_verify($mot_de_passe, $admin['mot_de_passe'])) {
                // Set session variables
                $_SESSION['admin_id'] = $admin['admin_id'];
                $_SESSION['admin_nom'] = $admin['nom'];
                $_SESSION['admin_email'] = $email;
                $_SESSION['admin_role'] = $admin['role'];
                
                // Redirect to admin dashboard
                header("Location: dashboard.php");
                exit();
            } else {
                $error = t('invalid_credentials', 'E-mail ou mot de passe incorrect.');
            }
        } else {
            $error = t('invalid_credentials', 'E-mail ou mot de passe incorrect.');
        }
        
        $stmt->close();
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="<?php echo getCurrentLanguage(); ?>" <?php echo isRTL() ? 'dir="rtl"' : ''; ?>>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo t('admin_login', 'Connexion Admin'); ?> - Ashkili</title>
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
            border: 2px solid #333;
            border-radius: 25px;
            background: transparent;
            margin-bottom: 20px;
        }

        .logo h1 {
            color: #333;
            font-size: 18px;
            font-weight: 500;
            margin: 0;
        }

        .admin-badge {
            text-align: center;
            margin-bottom: 20px;
        }

        .admin-badge span {
            display: inline-block;
            padding: 8px 20px;
            background: var(--primary-gradient);
            color: #333;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 10px;
            font-size: 24px;
        }

        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
            font-size: 14px;
        }

        .input-wrapper {
            position: relative;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            background: var(--input-bg);
            color: var(--text-main);
            border: 2px solid var(--input-border);
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

        .client-link {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
        }

        .client-link a {
            color: #666;
            text-decoration: none;
            font-size: 13px;
        }

        .client-link a:hover {
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 30px 20px;
            }

            h2 {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <div class="logo-btn">
                <a href="../index.php">Ashkili</a>
            </div>
        </div>

        <div class="admin-badge">
            <span>👤 <?php echo t('admin_space', 'ESPACE ADMINISTRATEUR'); ?></span>
        </div>

        <h2><?php echo t('admin_login', 'Connexion Admin'); ?></h2>
        <p class="subtitle"><?php echo t('access_admin_panel', 'Accédez au panneau d\'administration'); ?></p>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="" id="loginForm">
            <div class="form-group">
                <label for="email"><?php echo t('email', 'E-mail'); ?></label>
                <input type="email" id="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" placeholder="admin@ashkili.com">
            </div>

            <div class="form-group">
                <label for="mot_de_passe"><?php echo t('password', 'Mot de passe'); ?></label>
                <div class="input-wrapper">
                    <input type="password" id="mot_de_passe" name="mot_de_passe" required placeholder="••••••••">
                    <button type="button" class="toggle-password" onclick="togglePassword('mot_de_passe', this)">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-submit"><?php echo t('login', 'Se connecter'); ?></button>
        </form>

        <div class="register-link">
            <?php echo t('create_admin_account', 'Créer un compte admin ?'); ?> <a href="register.php"><?php echo t('register', 'S\'inscrire'); ?></a>
        </div>

        <div class="client-link">
            <a href="../auth/login.php">← <?php echo t('back_to_client_space', 'Retour à l\'espace client'); ?></a>
        </div>
    </div>

    <script>
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

        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('mot_de_passe').value;
            
            if (!email || !password) {
                e.preventDefault();
                alert('Veuillez remplir tous les champs.');
                return false;
            }
            
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailPattern.test(email)) {
                e.preventDefault();
                alert('Veuillez entrer une adresse e-mail valide.');
                return false;
            }
        });
    </script>
</body>
</html>
