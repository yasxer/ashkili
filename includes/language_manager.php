<?php
// Language Manager for Landing Page
if (!isset($_SESSION)) {
    session_start();
}

// Handle language change
if (isset($_POST['change_language'])) {
    $lang = $_POST['language'];
    $_SESSION['language'] = $lang;
    setcookie('site_language', $lang, time() + (86400 * 30), "/"); // 30 days
    
    // Redirect to prevent form resubmission
    $redirect_url = $_SERVER['PHP_SELF'];
    if (!empty($_SERVER['QUERY_STRING'])) {
        $redirect_url .= '?' . $_SERVER['QUERY_STRING'];
    }
    header("Location: " . $redirect_url);
    exit();
}

// Handle language change via GET parameter
if (isset($_GET['lang']) && in_array($_GET['lang'], ['fr', 'en', 'ar'])) {
    $lang = $_GET['lang'];
    $_SESSION['language'] = $lang;
    setcookie('site_language', $lang, time() + (86400 * 30), "/"); // 30 days
    
    // Redirect to remove the lang parameter from URL (cleaner URL)
    $redirect_url = strtok($_SERVER["REQUEST_URI"], '?');
    $query_params = $_GET;
    unset($query_params['lang']);
    if (!empty($query_params)) {
        $redirect_url .= '?' . http_build_query($query_params);
    }
    header("Location: " . $redirect_url);
    exit();
}

// Handle language change via POST (simple)
if (isset($_POST['lang']) && in_array($_POST['lang'], ['fr', 'en', 'ar'])) {
    $lang = $_POST['lang'];
    $_SESSION['language'] = $lang;
    setcookie('site_language', $lang, time() + (86400 * 30), "/"); // 30 days
    
    // Redirect to prevent form resubmission
    $redirect_url = $_SERVER['PHP_SELF'];
    if (!empty($_SERVER['QUERY_STRING'])) {
        $redirect_url .= '?' . $_SERVER['QUERY_STRING'];
    }
    header("Location: " . $redirect_url);
    exit();
}

// Set default language
if (isset($_SESSION['language'])) {
    $current_language = $_SESSION['language'];
} elseif (isset($_COOKIE['site_language']) && in_array($_COOKIE['site_language'], ['fr', 'en', 'ar'])) {
    $current_language = $_COOKIE['site_language'];
    $_SESSION['language'] = $current_language;
} else {
    $current_language = 'fr';
    $_SESSION['language'] = 'fr';
}

// Load language file
$lang = [];
$language_file = __DIR__ . "/languages/{$current_language}.php";
if (file_exists($language_file)) {
    require_once $language_file;
} else {
    // Fallback to French if language file doesn't exist
    require_once __DIR__ . "/languages/fr.php";
}

function t($key, $default = '') {
    global $lang;
    return $lang[$key] ?? $default;
}

function getCurrentLanguage() {
    global $current_language;
    return $current_language;
}

function isRTL() {
    global $current_language;
    return $current_language === 'ar';
}
?>
