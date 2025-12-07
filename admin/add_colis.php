<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'includes/language_manager.php';

$admin_nom = $_SESSION['admin_nom'] ?? 'Admin';
$admin_role = $_SESSION['admin_role'] ?? 'Support';

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once '../db.php';
    
    $code_suivi = trim($_POST['code_suivi'] ?? '');
    $client_id = trim($_POST['client_id'] ?? '');
    $statut_actuel = trim($_POST['statut_actuel'] ?? '');
    $date_expedition = trim($_POST['date_expedition'] ?? '');
    $date_livraison_prevue = trim($_POST['date_livraison_prevue'] ?? '');
    $nom_client = trim($_POST['nom_client'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $phone_client = trim($_POST['phone_client'] ?? '');
    $phone_comp = trim($_POST['phone_comp'] ?? '');
    $poids = trim($_POST['poids'] ?? '');
    
    // Validation
    if (empty($code_suivi) || empty($client_id) || empty($nom_client) || empty($location) || empty($price) || empty($phone_client) || empty($poids)) {
        $error = t('all_required_fields', 'Tous les champs obligatoires doivent être remplis.');
    } else {
        // Check if tracking code already exists
        $stmt = $conn->prepare("SELECT colis_id FROM colis WHERE code_suivi = ?");
        $stmt->bind_param("s", $code_suivi);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = t('tracking_code_exists', 'Ce code de suivi existe déjà.');
        } else {
            // Insert new colis with all required fields
            $stmt = $conn->prepare("INSERT INTO colis (code_suivi, client_id, statut_actuel, date_expedition, date_livraison_estimee, nom_client, location, price, phone_client, phone_comp, poids) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sisssssissd", $code_suivi, $client_id, $statut_actuel, $date_expedition, $date_livraison_prevue, $nom_client, $location, $price, $phone_client, $phone_comp, $poids);
            
            if ($stmt->execute()) {
                $success = t('package_added_success', 'Colis ajouté avec succès !');
                // Clear form
                $code_suivi = $client_id = $statut_actuel = $date_expedition = $date_livraison_prevue = $nom_client = $location = $price = $phone_client = $phone_comp = $poids = "";
            } else {
                $error = t('package_add_error', 'Erreur lors de l\'ajout du colis: ') . $stmt->error;
            }
        }
        $stmt->close();
    }
}

// Get list of clients for dropdown (reuse existing connection or create new one)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    require_once '../db.php';
}
$clients_result = $conn->query("SELECT client_id, nom, telephone FROM clients ORDER BY nom ASC");
if (!$clients_result) {
    die(t('database_error', 'Erreur de requête: ') . $conn->error);
}
$conn->close();

// Set page variables for header
$page_title = t('add_new_package', 'Ajouter un Nouveau Colis') . " - Admin";
$page_icon = "fas fa-plus-circle";
$page_heading = "Ashkili - " . t('add_new_package', 'Ajouter un Colis');

// Include header
include 'includes/header.php';
?>

<style>
        .form-card {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }

        .form-group label .required {
            color: #dc3545;
            margin-left: 3px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 14px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            transition: all 0.3s ease;
            background: var(--input-bg);
            color: var(--text-main);
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.1);
        }

        [data-theme="dark"] .form-group input,
        [data-theme="dark"] .form-group select,
        [data-theme="dark"] .form-group textarea {
            border-color: var(--input-border);
        }

        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            padding-top: 30px;
            border-top: 2px solid #f0f0f0;
        }

        .btn-submit {
            flex: 1;
            padding: 15px 30px;
            background: var(--primary-gradient);
            color: #333;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(var(--primary-rgb), 0.3);
        }

        .btn-reset {
            padding: 15px 30px;
            background: transparent;
            color: #666;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .info-box {
            background: #fff9e6;
            border-left: 4px solid var(--primary-color);
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-size: 14px;
            color: #856404;
        }
</style>

    <div class="container">
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-plus-circle"></i>
                <?php echo t('add_new_package', 'Ajouter un Nouveau Colis'); ?>
            </h1>
        </div>

        <div class="form-card">
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <div class="info-box">
                <strong>💡 <?php echo t('important_info', 'Information importante'); ?></strong>
                <?php echo t('fill_required_fields_info', 'Remplissez tous les champs obligatoires (*) pour enregistrer un nouveau colis dans le système. Les champs incluent: code de suivi, client, nom du destinataire, lieu, téléphone, prix et poids.'); ?>
            </div>

            <form method="POST" action="" id="colisForm">
                <div class="form-row">
                    <div class="form-group">
                        <label for="code_suivi">
                            <?php echo t('tracking_code', 'Code de Suivi'); ?> <span class="required">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="code_suivi" 
                            name="code_suivi" 
                            required 
                            placeholder="<?php echo t('example', 'Ex'); ?>: TRK-2025-001234"
                            value="<?php echo isset($_POST['code_suivi']) ? htmlspecialchars($_POST['code_suivi']) : ''; ?>"
                        >
                    </div>

                    <div class="form-group">
                        <label for="client_id">
                            <?php echo t('client', 'Client'); ?> <span class="required">*</span>
                        </label>
                        <select id="client_id" name="client_id" required>
                            <option value=""><?php echo t('select_client', '-- Sélectionner un client --'); ?></option>
                            <?php while ($client = $clients_result->fetch_assoc()): ?>
                                <option value="<?php echo $client['client_id']; ?>" 
                                    data-nom="<?php echo htmlspecialchars($client['nom']); ?>"
                                    data-telephone="<?php echo htmlspecialchars($client['telephone']); ?>"
                                    <?php echo (isset($_POST['client_id']) && $_POST['client_id'] == $client['client_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($client['nom']) . ' (' . htmlspecialchars($client['telephone']) . ')'; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="statut_actuel">
                            <?php echo t('current_status', 'Statut Actuel'); ?>
                        </label>
                        <select id="statut_actuel" name="statut_actuel">
                            <option value=""><?php echo t('select_status', '-- Sélectionner un statut --'); ?></option>
                            <option value="En préparation" <?php echo (isset($_POST['statut_actuel']) && $_POST['statut_actuel'] == 'En préparation') ? 'selected' : ''; ?>><?php echo t('in_preparation', 'En préparation'); ?></option>
                            <option value="Expédié" <?php echo (isset($_POST['statut_actuel']) && $_POST['statut_actuel'] == 'Expédié') ? 'selected' : ''; ?>><?php echo t('expedited', 'Expédié'); ?></option>
                            <option value="En transit" <?php echo (isset($_POST['statut_actuel']) && $_POST['statut_actuel'] == 'En transit') ? 'selected' : ''; ?>><?php echo t('in_transit', 'En transit'); ?></option>
                            <option value="En cours de livraison" <?php echo (isset($_POST['statut_actuel']) && $_POST['statut_actuel'] == 'En cours de livraison') ? 'selected' : ''; ?>><?php echo t('in_delivery', 'En cours de livraison'); ?></option>
                            <option value="Livré" <?php echo (isset($_POST['statut_actuel']) && $_POST['statut_actuel'] == 'Livré') ? 'selected' : ''; ?>><?php echo t('delivered', 'Livré'); ?></option>
                            <option value="Retardé" <?php echo (isset($_POST['statut_actuel']) && $_POST['statut_actuel'] == 'Retardé') ? 'selected' : ''; ?>><?php echo t('delayed', 'Retardé'); ?></option>
                            <option value="Retourné" <?php echo (isset($_POST['statut_actuel']) && $_POST['statut_actuel'] == 'Retourné') ? 'selected' : ''; ?>><?php echo t('returned', 'Retourné'); ?></option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="date_expedition">
                            <?php echo t('expedition_date', 'Date d\'Expédition'); ?>
                        </label>
                        <input 
                            type="date" 
                            id="date_expedition" 
                            name="date_expedition"
                            value="<?php echo isset($_POST['date_expedition']) ? htmlspecialchars($_POST['date_expedition']) : ''; ?>"
                        >
                    </div>
                </div>

                <div class="form-group">
                    <label for="date_livraison_prevue">
                        <?php echo t('expected_delivery_date', 'Date de Livraison Prévue'); ?>
                    </label>
                    <input 
                        type="date" 
                        id="date_livraison_prevue" 
                        name="date_livraison_prevue"
                        value="<?php echo isset($_POST['date_livraison_prevue']) ? htmlspecialchars($_POST['date_livraison_prevue']) : ''; ?>"
                    >
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="nom_client">
                            <?php echo t('client_name', 'Nom du Client'); ?> <span class="required">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="nom_client" 
                            name="nom_client" 
                            required 
                            placeholder="<?php echo t('full_recipient_name', 'Nom complet du destinataire'); ?>"
                            value="<?php echo isset($_POST['nom_client']) ? htmlspecialchars($_POST['nom_client']) : ''; ?>"
                        >
                    </div>

                    <div class="form-group">
                        <label for="location">
                            <?php echo t('delivery_wilaya', 'Wilaya de Livraison'); ?> <span class="required">*</span>
                        </label>
                        <select id="location" name="location" required>
                            <option value=""><?php echo t('select_wilaya', 'Sélectionner une wilaya'); ?></option>
                            <option value="01 - Adrar" <?php echo (isset($_POST['location']) && $_POST['location'] === '01 - Adrar') ? 'selected' : ''; ?>>01 - Adrar</option>
                            <option value="02 - Chlef" <?php echo (isset($_POST['location']) && $_POST['location'] === '02 - Chlef') ? 'selected' : ''; ?>>02 - Chlef</option>
                            <option value="03 - Laghouat" <?php echo (isset($_POST['location']) && $_POST['location'] === '03 - Laghouat') ? 'selected' : ''; ?>>03 - Laghouat</option>
                            <option value="04 - Oum El Bouaghi" <?php echo (isset($_POST['location']) && $_POST['location'] === '04 - Oum El Bouaghi') ? 'selected' : ''; ?>>04 - Oum El Bouaghi</option>
                            <option value="05 - Batna" <?php echo (isset($_POST['location']) && $_POST['location'] === '05 - Batna') ? 'selected' : ''; ?>>05 - Batna</option>
                            <option value="06 - Béjaïa" <?php echo (isset($_POST['location']) && $_POST['location'] === '06 - Béjaïa') ? 'selected' : ''; ?>>06 - Béjaïa</option>
                            <option value="07 - Biskra" <?php echo (isset($_POST['location']) && $_POST['location'] === '07 - Biskra') ? 'selected' : ''; ?>>07 - Biskra</option>
                            <option value="08 - Béchar" <?php echo (isset($_POST['location']) && $_POST['location'] === '08 - Béchar') ? 'selected' : ''; ?>>08 - Béchar</option>
                            <option value="09 - Blida" <?php echo (isset($_POST['location']) && $_POST['location'] === '09 - Blida') ? 'selected' : ''; ?>>09 - Blida</option>
                            <option value="10 - Bouira" <?php echo (isset($_POST['location']) && $_POST['location'] === '10 - Bouira') ? 'selected' : ''; ?>>10 - Bouira</option>
                            <option value="11 - Tamanrasset" <?php echo (isset($_POST['location']) && $_POST['location'] === '11 - Tamanrasset') ? 'selected' : ''; ?>>11 - Tamanrasset</option>
                            <option value="12 - Tébessa" <?php echo (isset($_POST['location']) && $_POST['location'] === '12 - Tébessa') ? 'selected' : ''; ?>>12 - Tébessa</option>
                            <option value="13 - Tlemcen" <?php echo (isset($_POST['location']) && $_POST['location'] === '13 - Tlemcen') ? 'selected' : ''; ?>>13 - Tlemcen</option>
                            <option value="14 - Tiaret" <?php echo (isset($_POST['location']) && $_POST['location'] === '14 - Tiaret') ? 'selected' : ''; ?>>14 - Tiaret</option>
                            <option value="15 - Tizi Ouzou" <?php echo (isset($_POST['location']) && $_POST['location'] === '15 - Tizi Ouzou') ? 'selected' : ''; ?>>15 - Tizi Ouzou</option>
                            <option value="16 - Alger" <?php echo (isset($_POST['location']) && $_POST['location'] === '16 - Alger') ? 'selected' : ''; ?>>16 - Alger</option>
                            <option value="17 - Djelfa" <?php echo (isset($_POST['location']) && $_POST['location'] === '17 - Djelfa') ? 'selected' : ''; ?>>17 - Djelfa</option>
                            <option value="18 - Jijel" <?php echo (isset($_POST['location']) && $_POST['location'] === '18 - Jijel') ? 'selected' : ''; ?>>18 - Jijel</option>
                            <option value="19 - Sétif" <?php echo (isset($_POST['location']) && $_POST['location'] === '19 - Sétif') ? 'selected' : ''; ?>>19 - Sétif</option>
                            <option value="20 - Saïda" <?php echo (isset($_POST['location']) && $_POST['location'] === '20 - Saïda') ? 'selected' : ''; ?>>20 - Saïda</option>
                            <option value="21 - Skikda" <?php echo (isset($_POST['location']) && $_POST['location'] === '21 - Skikda') ? 'selected' : ''; ?>>21 - Skikda</option>
                            <option value="22 - Sidi Bel Abbès" <?php echo (isset($_POST['location']) && $_POST['location'] === '22 - Sidi Bel Abbès') ? 'selected' : ''; ?>>22 - Sidi Bel Abbès</option>
                            <option value="23 - Annaba" <?php echo (isset($_POST['location']) && $_POST['location'] === '23 - Annaba') ? 'selected' : ''; ?>>23 - Annaba</option>
                            <option value="24 - Guelma" <?php echo (isset($_POST['location']) && $_POST['location'] === '24 - Guelma') ? 'selected' : ''; ?>>24 - Guelma</option>
                            <option value="25 - Constantine" <?php echo (isset($_POST['location']) && $_POST['location'] === '25 - Constantine') ? 'selected' : ''; ?>>25 - Constantine</option>
                            <option value="26 - Médéa" <?php echo (isset($_POST['location']) && $_POST['location'] === '26 - Médéa') ? 'selected' : ''; ?>>26 - Médéa</option>
                            <option value="27 - Mostaganem" <?php echo (isset($_POST['location']) && $_POST['location'] === '27 - Mostaganem') ? 'selected' : ''; ?>>27 - Mostaganem</option>
                            <option value="28 - M'Sila" <?php echo (isset($_POST['location']) && $_POST['location'] === '28 - M\'Sila') ? 'selected' : ''; ?>>28 - M'Sila</option>
                            <option value="29 - Mascara" <?php echo (isset($_POST['location']) && $_POST['location'] === '29 - Mascara') ? 'selected' : ''; ?>>29 - Mascara</option>
                            <option value="30 - Ouargla" <?php echo (isset($_POST['location']) && $_POST['location'] === '30 - Ouargla') ? 'selected' : ''; ?>>30 - Ouargla</option>
                            <option value="31 - Oran" <?php echo (isset($_POST['location']) && $_POST['location'] === '31 - Oran') ? 'selected' : ''; ?>>31 - Oran</option>
                            <option value="32 - El Bayadh" <?php echo (isset($_POST['location']) && $_POST['location'] === '32 - El Bayadh') ? 'selected' : ''; ?>>32 - El Bayadh</option>
                            <option value="33 - Illizi" <?php echo (isset($_POST['location']) && $_POST['location'] === '33 - Illizi') ? 'selected' : ''; ?>>33 - Illizi</option>
                            <option value="34 - Bordj Bou Arréridj" <?php echo (isset($_POST['location']) && $_POST['location'] === '34 - Bordj Bou Arréridj') ? 'selected' : ''; ?>>34 - Bordj Bou Arréridj</option>
                            <option value="35 - Boumerdès" <?php echo (isset($_POST['location']) && $_POST['location'] === '35 - Boumerdès') ? 'selected' : ''; ?>>35 - Boumerdès</option>
                            <option value="36 - El Tarf" <?php echo (isset($_POST['location']) && $_POST['location'] === '36 - El Tarf') ? 'selected' : ''; ?>>36 - El Tarf</option>
                            <option value="37 - Tindouf" <?php echo (isset($_POST['location']) && $_POST['location'] === '37 - Tindouf') ? 'selected' : ''; ?>>37 - Tindouf</option>
                            <option value="38 - Tissemsilt" <?php echo (isset($_POST['location']) && $_POST['location'] === '38 - Tissemsilt') ? 'selected' : ''; ?>>38 - Tissemsilt</option>
                            <option value="39 - El Oued" <?php echo (isset($_POST['location']) && $_POST['location'] === '39 - El Oued') ? 'selected' : ''; ?>>39 - El Oued</option>
                            <option value="40 - Khenchela" <?php echo (isset($_POST['location']) && $_POST['location'] === '40 - Khenchela') ? 'selected' : ''; ?>>40 - Khenchela</option>
                            <option value="41 - Souk Ahras" <?php echo (isset($_POST['location']) && $_POST['location'] === '41 - Souk Ahras') ? 'selected' : ''; ?>>41 - Souk Ahras</option>
                            <option value="42 - Tipaza" <?php echo (isset($_POST['location']) && $_POST['location'] === '42 - Tipaza') ? 'selected' : ''; ?>>42 - Tipaza</option>
                            <option value="43 - Mila" <?php echo (isset($_POST['location']) && $_POST['location'] === '43 - Mila') ? 'selected' : ''; ?>>43 - Mila</option>
                            <option value="44 - Aïn Defla" <?php echo (isset($_POST['location']) && $_POST['location'] === '44 - Aïn Defla') ? 'selected' : ''; ?>>44 - Aïn Defla</option>
                            <option value="45 - Naâma" <?php echo (isset($_POST['location']) && $_POST['location'] === '45 - Naâma') ? 'selected' : ''; ?>>45 - Naâma</option>
                            <option value="46 - Aïn Témouchent" <?php echo (isset($_POST['location']) && $_POST['location'] === '46 - Aïn Témouchent') ? 'selected' : ''; ?>>46 - Aïn Témouchent</option>
                            <option value="47 - Ghardaïa" <?php echo (isset($_POST['location']) && $_POST['location'] === '47 - Ghardaïa') ? 'selected' : ''; ?>>47 - Ghardaïa</option>
                            <option value="48 - Relizane" <?php echo (isset($_POST['location']) && $_POST['location'] === '48 - Relizane') ? 'selected' : ''; ?>>48 - Relizane</option>
                            <option value="49 - Timimoun" <?php echo (isset($_POST['location']) && $_POST['location'] === '49 - Timimoun') ? 'selected' : ''; ?>>49 - Timimoun</option>
                            <option value="50 - Bordj Badji Mokhtar" <?php echo (isset($_POST['location']) && $_POST['location'] === '50 - Bordj Badji Mokhtar') ? 'selected' : ''; ?>>50 - Bordj Badji Mokhtar</option>
                            <option value="51 - Ouled Djellal" <?php echo (isset($_POST['location']) && $_POST['location'] === '51 - Ouled Djellal') ? 'selected' : ''; ?>>51 - Ouled Djellal</option>
                            <option value="52 - Béni Abbès" <?php echo (isset($_POST['location']) && $_POST['location'] === '52 - Béni Abbès') ? 'selected' : ''; ?>>52 - Béni Abbès</option>
                            <option value="53 - In Salah" <?php echo (isset($_POST['location']) && $_POST['location'] === '53 - In Salah') ? 'selected' : ''; ?>>53 - In Salah</option>
                            <option value="54 - In Guezzam" <?php echo (isset($_POST['location']) && $_POST['location'] === '54 - In Guezzam') ? 'selected' : ''; ?>>54 - In Guezzam</option>
                            <option value="55 - Touggourt" <?php echo (isset($_POST['location']) && $_POST['location'] === '55 - Touggourt') ? 'selected' : ''; ?>>55 - Touggourt</option>
                            <option value="56 - Djanet" <?php echo (isset($_POST['location']) && $_POST['location'] === '56 - Djanet') ? 'selected' : ''; ?>>56 - Djanet</option>
                            <option value="57 - El M'Ghair" <?php echo (isset($_POST['location']) && $_POST['location'] === '57 - El M\'Ghair') ? 'selected' : ''; ?>>57 - El M'Ghair</option>
                            <option value="58 - El Meniaa" <?php echo (isset($_POST['location']) && $_POST['location'] === '58 - El Meniaa') ? 'selected' : ''; ?>>58 - El Meniaa</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="phone_client">
                            Téléphone Client <span class="required">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="phone_client" 
                            name="phone_client" 
                            required 
                            placeholder="Ex: 0612345678"
                            value="<?php echo isset($_POST['phone_client']) ? htmlspecialchars($_POST['phone_client']) : ''; ?>"
                        >
                    </div>

                    <div class="form-group">
                        <label for="phone_comp">
                            Téléphone Complémentaire
                        </label>
                        <input 
                            type="text" 
                            id="phone_comp" 
                            name="phone_comp" 
                            placeholder="Ex: 0698765432"
                            value="<?php echo isset($_POST['phone_comp']) ? htmlspecialchars($_POST['phone_comp']) : ''; ?>"
                        >
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="price">
                            Prix (DA) <span class="required">*</span>
                        </label>
                        <input 
                            type="number" 
                            id="price" 
                            name="price" 
                            required 
                            min="0"
                            placeholder="Ex: 250"
                            value="<?php echo isset($_POST['price']) ? htmlspecialchars($_POST['price']) : ''; ?>"
                        >
                    </div>

                    <div class="form-group">
                        <label for="poids">
                            Poids (kg) <span class="required">*</span>
                        </label>
                        <input 
                            type="number" 
                            id="poids" 
                            name="poids" 
                            required 
                            min="0"
                            step="0.01"
                            placeholder="Ex: 2.5"
                            value="<?php echo isset($_POST['poids']) ? htmlspecialchars($_POST['poids']) : ''; ?>"
                        >
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-submit">
                        ✓ <?php echo t('save_package', 'Enregistrer le Colis'); ?>
                    </button>
                    <button type="reset" class="btn-reset">
                        ↺ <?php echo t('reset', 'Réinitialiser'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Auto-fill client information when selecting a client
        document.getElementById('client_id').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const clientNom = selectedOption.getAttribute('data-nom');
            const clientTelephone = selectedOption.getAttribute('data-telephone');
            
            if (clientNom && clientTelephone) {
                // Fill nom_client field
                document.getElementById('nom_client').value = clientNom;
                // Fill phone_client field
                document.getElementById('phone_client').value = clientTelephone;
            } else {
                // Clear fields if no client selected
                document.getElementById('nom_client').value = '';
                document.getElementById('phone_client').value = '';
            }
        });

        // Auto-generate tracking code if empty
        document.getElementById('colisForm').addEventListener('submit', function(e) {
            const codeInput = document.getElementById('code_suivi');
            if (!codeInput.value.trim()) {
                const date = new Date();
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                const random = Math.floor(Math.random() * 10000).toString().padStart(4, '0');
                codeInput.value = `TRK-${year}${month}${day}-${random}`;
            }
        });

        // Set minimum date for expedition to today
        document.getElementById('date_expedition').min = new Date().toISOString().split('T')[0];
        
        // When expedition date changes, set minimum delivery date
        document.getElementById('date_expedition').addEventListener('change', function() {
            document.getElementById('date_livraison_prevue').min = this.value;
        });
    </script>

<?php include 'includes/footer.php'; ?>
