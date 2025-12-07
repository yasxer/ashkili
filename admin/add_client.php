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
$page_title = t('add_new_client', 'Ajouter un Client');
$page_icon = "fas fa-user-plus";
$page_heading = "Ashkili - " . t('add_new_client', 'Ajouter un Client');

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';
    $telephone = trim($_POST['telephone'] ?? '');

    // Validation
    if (empty($nom) || empty($username) || empty($mot_de_passe) || empty($telephone)) {
        $error = t('all_fields_required', 'Tous les champs sont obligatoires.');
    } elseif (strlen($mot_de_passe) < 8) {
        $error = t('password_min_length', 'Le mot de passe doit contenir au moins 8 caractères.');
    } else {
        // Check if username already exists
        $stmt = $conn->prepare("SELECT client_id FROM clients WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = t('username_exists', 'Ce nom d\'utilisateur existe déjà. Veuillez en choisir un autre.');
            $stmt->close();
        } else {
            $stmt->close();
            
            // Hash password
            $hashed_password = password_hash($mot_de_passe, PASSWORD_BCRYPT);
            
            // Insert new client
            $stmt = $conn->prepare("INSERT INTO clients (nom, username, mot_de_passe, telephone, date_inscription) VALUES (?, ?, ?, ?, NOW())");
            $stmt->bind_param("ssss", $nom, $username, $hashed_password, $telephone);
            
            if ($stmt->execute()) {
                $success = t('client_created_success', 'Le compte client a été créé avec succès! Nom d\'utilisateur: ') . htmlspecialchars($username);
                // Clear form
                $nom = $username = $telephone = '';
            } else {
                $error = t('client_creation_error', 'Erreur lors de la création du compte: ') . $conn->error;
            }
            
            $stmt->close();
        }
    }
}

$conn->close();

include 'includes/header.php';
?>

<style>
            

        .navbar h1 {
            font-size: 24px;
            font-weight: 600;
        }

        .navbar .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .navbar .user-name {
            font-size: 14px;
        }

        .role-badge {
            background: rgba(0, 0, 0, 0.1);
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }

        .btn-logout {
            background: transparent;
            color: #333;
            border: 2px solid #333;
            padding: 8px 20px;
            border-radius: 20px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-logout:hover {
            background: #333;
            color: var(--primary-color);
        }

        .container {
            max-width: 800px;
            margin: 40px auto;
            padding: 0 20px;
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
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-back {
            background: white;
            color: #333;
            border: 2px solid #e0e0e0;
            padding: 10px 20px;
            border-radius: 10px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-back:hover {
            border-color: var(--primary-color);
            background: #fffef5;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 14px;
            animation: slideIn 0.3s ease;
        }

        .alert-info {
            background: #E3F2FD;
            color: #1976D2;
            border-left: 4px solid #2196F3;
        }

        .alert-success {
            background: #E8F5E9;
            color: #2E7D32;
            border-left: 4px solid #4CAF50;
        }

        .alert-error {
            background: #FFEBEE;
            color: #C62828;
            border-left: 4px solid #F44336;
        }

        .alert i {
            font-size: 20px;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-card {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        .form-card-header {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f2f5;
        }

        .form-card-header h3 {
            font-size: 22px;
            color: #333;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .form-card-header p {
            font-size: 14px;
            color: #666;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #555;
            margin-bottom: 8px;
        }

        .form-label i {
            color: var(--primary-color);
            margin-right: 6px;
        }

        .form-label .required {
            color: #F44336;
            margin-left: 3px;
        }

        .form-input {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 15px;
            font-family: inherit;
            transition: all 0.3s ease;
            background: #f9f9f9;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary-color);
            background: white;
            box-shadow: 0 0 0 4px rgba(var(--primary-rgb), 0.1);
        }

        .form-input::placeholder {
            color: #aaa;
        }

        .password-wrapper {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: transparent;
            border: none;
            color: #999;
            cursor: pointer;
            font-size: 18px;
            transition: all 0.3s ease;
        }

        .toggle-password:hover {
            color: var(--primary-color);
        }

        .toggle-password:focus {
            outline: none;
        }

        .password-strength {
            margin-top: 8px;
            font-size: 12px;
            color: #666;
        }

        .password-strength ul {
            list-style: none;
            padding-left: 0;
            margin-top: 5px;
        }

        .password-strength li {
            padding: 3px 0;
            color: #999;
        }

        .password-strength li i {
            margin-right: 6px;
            font-size: 10px;
        }

        .password-strength li.valid {
            color: #4CAF50;
        }

        .btn-submit {
            background: var(--primary-gradient);
            color: #333;
            border: none;
            padding: 16px 40px;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 30px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(244, 196, 48, 0.4);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .form-help {
            font-size: 13px;
            color: #666;
            margin-top: 6px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .form-help i {
            color: #2196F3;
            font-size: 14px;
        }

        @media (max-width: 768px) {
            .navbar {
                padding: 15px 20px;
            }

            .navbar-brand {
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

            .form-card {
                padding: 25px 20px;
            }
        }
    </style>

    <div class="container">

        <!-- Important Notice -->
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            <strong><?php echo t('attention', 'Attention'); ?>:</strong> <?php echo t('public_registration_disabled', 'La page d\'enregistrement publique pour les clients a été désactivée. Tous les comptes doivent être créés ici.'); ?>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="form-card">
            <div class="form-card-header">
                <h3><?php echo t('client_information', 'Informations du Client'); ?></h3>
                <p><?php echo t('fill_all_fields_create_account', 'Remplissez tous les champs pour créer un nouveau compte client'); ?></p>
            </div>

            <form method="POST" action="" id="clientForm">
                <div class="form-group">
                    <label class="form-label" for="nom">
                        <i class="fas fa-user"></i>
                        <?php echo t('full_name_client', 'Nom Complet du Client'); ?>
                        <span class="required">*</span>
                    </label>
                    <input 
                        type="text" 
                        class="form-input" 
                        id="nom" 
                        name="nom" 
                        placeholder="<?php echo t('example', 'Ex'); ?>: Ahmed Ben Ali"
                        value="<?php echo htmlspecialchars($nom ?? ''); ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label class="form-label" for="username">
                        <i class="fas fa-id-badge"></i>
                        <?php echo t('username_label', 'Nom d\'Utilisateur'); ?>
                        <span class="required">*</span>
                    </label>
                    <input 
                        type="text" 
                        class="form-input" 
                        id="username" 
                        name="username" 
                        placeholder="<?php echo t('example', 'Ex'); ?>: ahmed.benali"
                        value="<?php echo htmlspecialchars($username ?? ''); ?>"
                        required
                        autocomplete="off"
                    >
                    <div class="form-help">
                        <i class="fas fa-info-circle"></i>
                        <?php echo t('username_help', 'Le nom d\'utilisateur doit être unique et sera utilisé pour la connexion'); ?>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="mot_de_passe">
                        <i class="fas fa-lock"></i>
                        <?php echo t('initial_password', 'Mot de Passe Initial'); ?>
                        <span class="required">*</span>
                    </label>
                    <div class="password-wrapper">
                        <input 
                            type="password" 
                            class="form-input" 
                            id="mot_de_passe" 
                            name="mot_de_passe" 
                            placeholder="<?php echo t('minimum_8_chars', 'Minimum 8 caractères'); ?>"
                            required
                            minlength="8"
                        >
                        <button type="button" class="toggle-password" onclick="togglePassword('mot_de_passe', this)">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="password-strength">
                        <ul id="password-requirements">
                            <li id="req-length"><i class="fas fa-circle"></i> <?php echo t('password_req_length', 'Au moins 8 caractères'); ?></li>
                            <li id="req-uppercase"><i class="fas fa-circle"></i> <?php echo t('password_req_uppercase', 'Une lettre majuscule'); ?></li>
                            <li id="req-lowercase"><i class="fas fa-circle"></i> <?php echo t('password_req_lowercase', 'Une lettre minuscule'); ?></li>
                            <li id="req-number"><i class="fas fa-circle"></i> <?php echo t('password_req_number', 'Un chiffre'); ?></li>
                        </ul>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="telephone">
                        <i class="fas fa-phone"></i>
                        <?php echo t('phone_number', 'Numéro de Téléphone'); ?>
                        <span class="required">*</span>
                    </label>
                    <input 
                        type="tel" 
                        class="form-input" 
                        id="telephone" 
                        name="telephone" 
                        placeholder="<?php echo t('example', 'Ex'); ?>: 0612345678 <?php echo t('or', 'ou'); ?> +212612345678"
                        value="<?php echo htmlspecialchars($telephone ?? ''); ?>"
                        required
                    >
                    <div class="form-help">
                        <i class="fas fa-info-circle"></i>
                        <?php echo t('phone_format', 'Format accepté: 06XXXXXXXX ou +212XXXXXXXXX'); ?>
                    </div>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fas fa-user-check"></i>
                    <?php echo t('create_client_account', 'Créer le Compte Client'); ?>
                </button>
            </form>
        </div>
    </div>

    <script>
        function togglePassword(inputId, button) {
            const input = document.getElementById(inputId);
            const icon = button.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Password strength validation
        document.getElementById('mot_de_passe').addEventListener('input', function(e) {
            const password = e.target.value;
            
            // Length check
            const lengthReq = document.getElementById('req-length');
            if (password.length >= 8) {
                lengthReq.classList.add('valid');
            } else {
                lengthReq.classList.remove('valid');
            }
            
            // Uppercase check
            const uppercaseReq = document.getElementById('req-uppercase');
            if (/[A-Z]/.test(password)) {
                uppercaseReq.classList.add('valid');
            } else {
                uppercaseReq.classList.remove('valid');
            }
            
            // Lowercase check
            const lowercaseReq = document.getElementById('req-lowercase');
            if (/[a-z]/.test(password)) {
                lowercaseReq.classList.add('valid');
            } else {
                lowercaseReq.classList.remove('valid');
            }
            
            // Number check
            const numberReq = document.getElementById('req-number');
            if (/[0-9]/.test(password)) {
                numberReq.classList.add('valid');
            } else {
                numberReq.classList.remove('valid');
            }
        });

        // Phone number validation
        document.getElementById('telephone').addEventListener('input', function(e) {
            // Remove any non-numeric characters except + at the start
            let value = e.target.value;
            if (value.startsWith('+')) {
                value = '+' + value.slice(1).replace(/\D/g, '');
            } else {
                value = value.replace(/\D/g, '');
            }
            e.target.value = value;
        });

        // Form validation before submit
        document.getElementById('clientForm').addEventListener('submit', function(e) {
            const password = document.getElementById('mot_de_passe').value;
            const username = document.getElementById('username').value;
            
            // Check password strength
            if (password.length < 8 || 
                !/[A-Z]/.test(password) || 
                !/[a-z]/.test(password) || 
                !/[0-9]/.test(password)) {
                e.preventDefault();
                alert('<?php echo t('password_requirements_alert', 'Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule et un chiffre.'); ?>');
                return false;
            }
            
            // Check username format
            if (username.length < 3) {
                e.preventDefault();
                alert('<?php echo t('username_min_length_alert', 'Le nom d\'utilisateur doit contenir au moins 3 caractères.'); ?>');
                return false;
            }
        });
    </script>

<?php include 'includes/footer.php'; ?>
