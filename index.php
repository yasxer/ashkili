<?php
// Include language manager
require_once 'includes/language_manager.php';
?>
<!DOCTYPE html>
<html lang="<?php echo getCurrentLanguage(); ?>" dir="<?php echo isRTL() ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo t('site_name'); ?> - <?php echo t('hero_title_3'); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/theme.css">
    <script src="js/theme.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
        }

        /* RTL Support */
        [dir="rtl"] {
            text-align: right;
        }

        [dir="rtl"] .nav-links {
            flex-direction: row-reverse;
        }

        [dir="rtl"] .hero-container {
            direction: rtl;
        }

        [dir="rtl"] .hero-buttons {
            flex-direction: row-reverse;
        }

        /* Language Selector */
        .language-selector {
            position: relative;
            display: inline-block;
        }

        .lang-btn {
            background: var(--primary-gradient);
            color: #333;
            padding: 8px 15px;
            border: none;
            border-radius: 25px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }

        .lang-btn:hover {
            background: var(--primary-gradient);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(var(--primary-rgb), 0.3);
        }

        .lang-dropdown {
            display: none;
            position: absolute;
            top: 100%;
            right: 0;
            margin-top: 10px;
            background: var(--card-bg);
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            z-index: 1000;
            min-width: 150px;
        }

        [dir="rtl"] .lang-dropdown {
            right: auto;
            left: 0;
        }

        .language-selector.active .lang-dropdown {
            display: block;
        }

        .lang-option {
            padding: 12px 20px;
            cursor: pointer;
            transition: background 0.3s ease;
            display: flex;
            align-items: center;
            gap: 10px;
            border: none;
            background: var(--card-bg);
            width: 100%;
            text-align: left;
            font-size: 14px;
            color: var(--text-main);
        }

        [dir="rtl"] .lang-option {
            text-align: right;
        }

        .lang-option:hover {
            background: var(--hover-bg);
        }

        .lang-option.active {
            background: var(--primary-color);
            color: #333;
            font-weight: 600;
        }

        .lang-option i.fa-circle {
            font-size: 10px;
        }

        /* Navigation */
        .nav {
            position: fixed;
            top: 0;
            width: 100%;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            z-index: 1000;
            padding: 15px 0;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .nav-logo {
            font-size: 1.8rem;
            font-weight: bold;
            color: var(--primary-color);
            text-decoration: none;
        }

        .nav-links {
            display: flex;
            gap: 30px;
            align-items: center;
        }

        .nav-links a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .nav-links a:hover {
            color: var(--primary-color);
        }

        .btn-connect {
            background: var(--primary-gradient);
            color: #333 !important;
            padding: 8px 20px;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none !important;
        }

        .btn-connect:hover {
            background: var(--primary-gradient);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(var(--primary-rgb), 0.3);
            color: #333 !important;
        }

        /* Hero Section */
        .hero {
            padding: 120px 20px 80px;
            text-align: center;
            color: white;
            background: var(--primary-gradient);
            position: relative;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(44, 62, 80, 0.8) 0%, rgba(52, 73, 94, 0.6) 100%);
            z-index: 1;
        }

        .hero-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
            position: relative;
            z-index: 2;
        }

        .hero-content h1 {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .hero-content .highlight {
            color: var(--primary-color);
        }

        .hero-content p {
            font-size: 1.3rem;
            margin-bottom: 40px;
            opacity: 0.9;
        }

        .hero-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            padding: 15px 35px;
            border: none;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .btn-primary {
            background: var(--primary-color);
            color: #333;
        }

        .btn-primary:hover {
            background: var(--primary-color);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(var(--primary-rgb), 0.3);
        }
            

        .btn-secondary {
            background: transparent;
            color: white;
            border: 2px solid white;
        }

        .btn-secondary:hover {
            background: white;
            color: #333;
            transform: translateY(-2px);
        }

        .hero-illustration {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .illustration-svg {
            max-width: 100%;
            height: auto;
            filter: drop-shadow(0 10px 30px rgba(0, 0, 0, 0.2));
        }

        /* Features Section */
        .features {
            padding: 80px 20px;
            background: white;
        }

        .features-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .section-title {
            text-align: center;
            margin-bottom: 60px;
        }

        .section-title h2 {
            font-size: 2.5rem;
            color: #333;
            margin-bottom: 15px;
        }

        .section-title p {
            font-size: 1.2rem;
            color: #666;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 40px;
        }

        .feature-card {
            background: linear-gradient(145deg, #f8f9fa, #e9ecef);
            padding: 40px 30px;
            border-radius: 20px;
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid rgba(var(--primary-rgb), 0.1);
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        .feature-icon {
            width: 80px;
            height: 80px;
            background: var(--primary-gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            font-size: 2rem;
            color: white;
        }

        .feature-card h3 {
            font-size: 1.4rem;
            color: #333;
            margin-bottom: 15px;
        }

        .feature-card p {
            color: #666;
            line-height: 1.6;
        }

        /* Stats Section */
        .stats {
            padding: 80px 20px;
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
        }

        .stats-container {
            max-width: 1200px;
            margin: 0 auto;
            text-align: center;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 40px;
            margin-top: 50px;
        }

        .stat-item {
            padding: 30px;
        }

        .stat-number {
            font-size: 3rem;
            font-weight: 700;
            color: var(--primary-color);
            display: block;
            margin-bottom: 10px;
        }

        .stat-label {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        /* CTA Section */
        .cta {
            padding: 80px 20px;
            background: white;
            text-align: center;
        }

        .cta-container {
            max-width: 800px;
            margin: 0 auto;
        }

        .cta h2 {
            font-size: 2.5rem;
            color: #333;
            margin-bottom: 20px;
        }

        .cta p {
            font-size: 1.2rem;
            color: #666;
            margin-bottom: 40px;
        }

        .cta-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-large {
            padding: 20px 45px;
            font-size: 1.2rem;
            border-radius: 50px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .btn-admin {
            background: linear-gradient(135deg, #2c3e50, #34495e);
            color: white;
        }

        .btn-admin:hover {
            background: linear-gradient(135deg, #1a252f, #2c3e50);
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
        }

        .btn-client {
            background: var(--primary-gradient);
            color: #333;
        }

        .btn-client:hover {
            background: var(--primary-gradient);
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
        }

        /* Footer */
        .footer {
            padding: 40px 20px;
            background: #2c3e50;
            color: white;
            text-align: center;
        }

        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            margin-bottom: 30px;
        }

        .footer-section h3 {
            color: var(--primary-color);
            margin-bottom: 15px;
        }

        .footer-section p {
            opacity: 0.8;
            line-height: 1.6;
        }

        .footer-bottom {
            border-top: 1px solid #34495e;
            padding-top: 20px;
            opacity: 0.7;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }
            
            .hero-container {
                grid-template-columns: 1fr;
                text-align: center;
            }
            
            .hero-content h1 {
                font-size: 2.5rem;
            }
            
            .hero-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .btn {
                width: 100%;
                max-width: 300px;
            }
            
            .features-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .cta-buttons {
                flex-direction: column;
                align-items: center;
            }
        }

        /* Animation Styles */
        .animate-on-scroll {
            opacity: 0;
            transition: all 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }

        .animate-on-scroll.animate {
            opacity: 1;
        }

        .slide-up {
            transform: translateY(50px);
        }

        .slide-up.animate {
            transform: translateY(0);
        }

        .slide-left {
            transform: translateX(-50px);
        }

        .slide-left.animate {
            transform: translateX(0);
        }

        .slide-right {
            transform: translateX(50px);
        }

        .slide-right.animate {
            transform: translateX(0);
        }

        .fade-in {
            opacity: 0;
        }

        .fade-in.animate {
            opacity: 1;
        }

        .scale-in {
            transform: scale(0.8);
            opacity: 0;
        }

        .scale-in.animate {
            transform: scale(1);
            opacity: 1;
        }

        /* Staggered animations */
        .feature-card:nth-child(1) { transition-delay: 0.1s; }
        .feature-card:nth-child(2) { transition-delay: 0.2s; }
        .feature-card:nth-child(3) { transition-delay: 0.3s; }
        .feature-card:nth-child(4) { transition-delay: 0.4s; }
        .feature-card:nth-child(5) { transition-delay: 0.5s; }
        .feature-card:nth-child(6) { transition-delay: 0.6s; }

        .stat-item:nth-child(1) { transition-delay: 0.1s; }
        .stat-item:nth-child(2) { transition-delay: 0.2s; }
        .stat-item:nth-child(3) { transition-delay: 0.3s; }
        .stat-item:nth-child(4) { transition-delay: 0.4s; }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="nav">
        <div class="nav-container">
            <a href="#" class="nav-logo">
                <i class="fas fa-shipping-fast"></i> <?php echo t('site_name'); ?>
            </a>
            <div class="nav-links">
                <!-- Theme Toggle -->
                <button class="theme-toggle" onclick="toggleTheme()" title="Toggle Theme">
                    <i class="fas fa-moon" id="theme-icon"></i>
                </button>

                <a href="#features"><?php echo t('features'); ?></a>
                <a href="#about"><?php echo t('about'); ?></a>
                <a href="#contact"><?php echo t('contact'); ?></a>
                
                <!-- Language Selector -->
                <div class="language-selector">
                    <button type="button" class="lang-btn">
                        <i class="fas fa-language"></i>
                        <span><?php 
                            $langNames = ['fr' => 'FR', 'en' => 'EN', 'ar' => 'ع'];
                            echo $langNames[getCurrentLanguage()] ?? 'FR';
                        ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="lang-dropdown">
                        <form method="POST" id="languageForm">
                            <button type="submit" name="change_language" value="fr" 
                                    class="lang-option <?php echo getCurrentLanguage() === 'fr' ? 'active' : ''; ?>">
                                <input type="hidden" name="language" value="fr">
                                <i class="fas fa-circle" style="color: #0055A4;"></i>
                                <span>Français</span>
                            </button>
                            <button type="submit" name="change_language" value="en" 
                                    class="lang-option <?php echo getCurrentLanguage() === 'en' ? 'active' : ''; ?>">
                                <input type="hidden" name="language" value="en">
                                <i class="fas fa-circle" style="color: #012169;"></i>
                                <span>English</span>
                            </button>
                            <button type="submit" name="change_language" value="ar" 
                                    class="lang-option <?php echo getCurrentLanguage() === 'ar' ? 'active' : ''; ?>">
                                <input type="hidden" name="language" value="ar">
                                <i class="fas fa-circle" style="color: #006233;"></i>
                                <span>العربية</span>
                            </button>
                        </form>
                    </div>
                </div>
                
                <a href="auth/login.php" class="btn-connect">
                    <i class="fas fa-sign-in-alt"></i>
                    <?php echo t('login'); ?>
                </a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-container">
            <div class="hero-content animate-on-scroll slide-left">
                <h1><?php echo t('hero_title_1'); ?> <span class="highlight"><?php echo t('hero_title_2'); ?></span> <?php echo t('hero_title_3'); ?></h1>
                <p><?php echo t('hero_description'); ?></p>
                <div class="hero-buttons">
                    <a href="#features" class="btn btn-primary">
                        <i class="fas fa-rocket"></i>
                        <?php echo t('discover_features'); ?>
                    </a>
                    <a href="#contact" class="btn btn-secondary">
                        <i class="fas fa-phone"></i>
                        <?php echo t('contact_us'); ?>
                    </a>
                </div>
            </div>
            <div class="hero-illustration animate-on-scroll slide-right">
                <svg class="illustration-svg" width="450" height="300" viewBox="0 0 552.94084 367.92049" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" role="img" artist="Katerina Limpitsouni" source="https://undraw.co/">
                    <path d="M213.77996,33.23929h-67.08496c-2.30142,0-4.17392-1.87249-4.17392-4.17392s1.8725-4.17392,4.17392-4.17392h67.08496c2.30142,0,4.17392,1.87249,4.17392,4.17392s-1.8725,4.17392-4.17392,4.17392Z" fill="#e6e7e8"/>
                    <path d="M266.13329,47.3664h-119.43829c-2.30142,0-4.17392-1.87249-4.17392-4.17392s1.8725-4.17392,4.17392-4.17392h119.43829c2.30142,0,4.17392,1.87249,4.17392,4.17392s-1.8725,4.17392-4.17392,4.17392h0Z" fill="#e6e7e8"/>
                    <path d="M266.13329,61.3664h-119.43829c-2.30142,0-4.17392-1.87249-4.17392-4.17392s1.8725-4.17392,4.17392-4.17392h119.43829c2.30142,0,4.17392,1.87249,4.17392,4.17392s-1.8725,4.17392-4.17392,4.17392h0Z" fill="#e6e7e8"/>
                    <path d="M278.78484,134.01856v-2c-23.08527,0-43.24463-18.85779-48.60437-45.12573l8.69812,2.23767c-4.20703-3.98584-8.45721-10.65851-11.09375-16.11194-1.55719,5.85443-4.46582,13.21167-7.84137,17.92249l8.22992-3.89856c5.47247,27.32849,26.49829,46.97607,50.61145,46.97607Z" fill="#3f3d56"/>
                    <path d="M270.05257,57.47993c0,2.20557-1.79443,4-4,4h-52c-2.20557,0-4-1.79443-4-4s1.79443-4,4-4h52c2.20557,0,4,1.79443,4,4Z" fill="var(--primary-color)"/>
                    <g>
                        <g>
                            <path d="M35.90386,280.29741c-.7916-2.75647-2.14717-5.10757-3.76107-6.78473l-5.86366-26.72989-12.78116,3.13107,7.32422,26.77768c-.48165,2.26559-.38528,4.97031,.40632,7.72675,1.8084,6.29712,6.55959,10.4794,10.61208,9.34143,4.0525-1.13797,5.87168-7.16522,4.06328-13.46231h-.00001Z" fill="#ffb6b6"/>
                            <path d="M34.13565,104.95641s-13.80493-.47648-21.80493,8.52352C3.16516,124.67631-3.09991,168.5471,1.6033,171.50123l12.74267,90.48536,18.17083-2.49686-4.66349-108.56891,6.28234-45.96446v.00005Z" fill="var(--primary-color)"/>
                        </g>
                        <polygon points="127.33072 257.47993 124.02328 367.91723 76.52328 367.91723 74.60332 325.34722 66.16331 367.91723 15.25333 367.91723 21.60331 294.73723 28.1633 279.34722 32.60331 268.91723 127.33072 257.47993" fill="#2f2e41"/>
                        <path d="M89.60332,86.91724l-33-2-11.00001,14.00001c-9.50027,2.03415-18.08116,4.66021-24.00001,9,1.41608,41.9259-1.69464,96.44153,11.00003,107l-11.2726,79.56269s4.54595-3.89469,12.1397-2.51969c2.62343,.47503,5.79032,6.38486,9.01396,6.74297,24.76969,2.75164,64.93738-.07811,84.84634-41.22328l-7.7274-59.56268,1.99999-87.00001c-6.25235-4.94229-14.02573-8.15424-23-10l-8.99999-13.99999v-.00002Z" fill="var(--primary-color)"/>
                        <g>
                            <path d="M153.1336,62.57842c.35806,3.46696-.09662,6.72734-1.14365,9.34143l5.49713,32.81189-15.68973,2.15901-3.88701-33.51685c-1.55922-2.34492-2.67064-5.44357-3.02869-8.91052-.81798-7.92024,2.60477-14.76279,7.64491-15.28331s9.78906,5.47809,10.60704,13.39833v.00002h-.00002Z" fill="#ffb6b6"/>
                            <path d="M100.97056,113.42402c-1.64275,2.4238-1.1851,5.14828-1.57732,8.42166-1.66507,13.89642,24.96442,54.95995,43.39029,59.68797,2.41791,.62042,3.7953,3.22858,6.23576,3.75311h.00002c7.22955,1.55386,13.96072-4.18445,13.5705-11.56882l-5.00511-94.70802-21.02719,7.17204,1.34588,49.17056c-.13403-10.18652-9.48758-20.55853-16.57266-24.87259,0,0-14.351-5.92216-20.36016,2.94408Z" fill="var(--primary-color)"/>
                        </g>
                        <g>
                            <circle cx="76.52788" cy="53.26168" r="29.06773" fill="#ffb6b6"/>
                            <path d="M103.60332,31.91723c-.25,.06006,3.82632-3.91316,4-4,4-2-.11005,17.55-3,16.99999-7.11005-1.35004-10.17999-4.03003-12.94,2.65997-.77002,1.85999-1.25,3.96002-2.75,5.29004-2.06,1.81-5.47998,1.65997-7.15997,3.81995-1.35004,1.74005-.96002,4.30005,.03998,6.26001,1,1.97003,2.52002,3.64001,3.42999,5.65002,1.02002,2.26001-3.33002,9.45001-4.98999,13.48999v.01001l-.00987,.02367c-.44109,1.05969-1.48927-1.99294-2.59592-2.29765-1.32423-.36464-3.27507,2.76221-5.38422,2.19397-.01001,0-.02002-.01001-.03003-.01001-2.01001-.56-4.25-1.15997-6.46997-1.76001h-.01001c-6.25-1.69995-7.83002,.74005-8.13,.67004l-3.88-4.06c-1.33156-.9695-1.18106-4.0994-2.12-4.94-.76788-.68747-2.68219,.90109-3.24638,.2403-2.76637-3.24001-3.40286-6.48122-5.03365-12.88031-1.48999-5.84998-4.57996-23.35999,1.77002-24.15002,5.98999-.75,1.60999-9.08997,7.64001-8.77997-.35999-1.52002,.31-3.15002,1.40002-4.27002,1.07996-1.13,2.51996-1.83002,3.96997-2.42999,7.15002-2.91998,5.96002-5.42993,13.5-3.72998,.75-1.04999,10.36738-3.78733,11.72743-3.43729,.09998,.01996-2.09743,4.28733,3.27257,3.43729-.20001,1.23004,2.72743-1.43729,3,3,5.72743-3.43729,8.81,4.78998,9,6,.25,1.51001-2.03003,.73999-.51001,.96997,.90002,.14001,2.44,1.97003,2.29004,2.88,.64996-.88,1.29999-1.75,1.95996-2.63,.12,.01001,.22998,.03003,.35004,.04999,3.03998,.58002-.44522,10.51796-.32001,7.42004,.22998-5.69,4.25995-2.35999,1.22998-1.69h.00002Z" fill="#2f2e41"/>
                        </g>
                    </g>
                    <path d="M474.36993,25.95775c-1.07404,8.8273-2.2475,17.64251-3.52008,26.4434-.51691,3.57451-1.06342,7.19449-2.50153,10.50752-.98163,2.26144-2.35657,4.32654-3.72394,6.37788-1.61578,2.42407-3.25836,4.87904-5.45282,6.7952s-5.04492,3.26164-7.94946,3.03645c7.70642-5.18758,12.8858-13.96159,13.70416-23.21526,.3847-4.34975-.14313-8.72215-.24438-13.08771-.10126-4.36555,.26416-8.88307,2.27832-12.75752s5.99286-6.97589,10.35815-6.86575l-2.94849,2.76579h.00006Z" fill="#2f2e41"/>
                    <g>
                        <path d="M396.26787,214.23064h0c-6.27057,5.08578-13.97736,5.97379-17.21375,1.98346s-.77679-11.3479,5.49371-16.43365c2.74481-2.2262,5.76483-3.64789,8.5647-4.20834l26.84027-21.21652,9.68811,12.80746-27.48755,19.55594c-1.12628,2.62384-3.14075,5.28543-5.88556,7.51163h-.00006l.00012,.00003Z" fill="#a0616a"/>
                        <path d="M401.33776,185.42794l39.99296-37.948,4-3s22.95754-22.36648,28.9023-22.87869c2.51782-.21648,5.03571,.41119,7.1568,1.78918,3.42688,2.22208,5.20166,5.87984,5.20166,9.56644,0,3.08057-1.24091,6.17563-3.78046,8.45538l-36.61359,32.86926-15.57611,13.98169-19.37097,17.39417-1.96954-4.01126-6.659-13.5921-1.28418-2.62607v.00003l.00012-.00003Z" fill="var(--primary-color)"/>
                    </g>
                    <path d="M486.85284,159.74625l-40.04333,12.26463c6.7041,1.88855,9.51105,42.73956,3.27209,44.00848,0,0,66.36469,4.72018,62.74347,0-4.41431-5.754,1.50592-43.99176,4.32867-44.00848l-30.3009-12.26463Z" fill="#a0616a"/>
                    <path d="M540.13634,367.92046l-3.5-42.23999c0-15.10999-.97998-28.76001-2.54999-40.92999-6.28998-48.85001-22.06-73.88998-22.06-73.88998h-59c-.08002,.09-.15997,.16-.23999,.25l-.01001,.01001c-13.91998,14.63-21.15997,51.41998-24.38,73.63-1.53003,10.57001-2.15002,17.84-2.15002,17.84l-5.77002,51.28,4.16998,14.04999h34.33002l-.53998-3.01001,22.09003-80.16,.27002-.98001,.23999,.98001,20.47998,83.17001h38.62v-.00003Z" fill="#2f2e41"/>
                    <path d="M454.23546,135.37935l12.46728-25.16714,10.50494-18.28423,8.49506-8.65739h23.24878l2.16437,8.65739,12.98608,7.21449,4.59332,36.07251-16.36458,78.26495c-19.47919-15.87189-59.62797-20.26772-59.62797-20.26772l-6.50531-18.93073-4.2998-22.80504-.51947-2.72707,12.8573-13.37006h0v.00003Z" fill="var(--primary-color)"/>
                    <g>
                        <path d="M452.73968,180.90541h0c-8.07043-.229-14.49464-4.57792-14.34891-9.71369,.14571-5.13571,6.8062-9.11345,14.8766-8.88439,3.53272,.10023,6.74987,.99004,9.23871,2.38963l34.18456,1.39931-.99826,16.02791-33.59332-3.08063c-2.5642,1.25613-5.82665,1.96211-9.35933,1.86183h-.00001l-.00004,.00004Z" fill="#a0616a"/>
                        <path d="M496.45654,126.19796l32.24621,32.01426-56.16791,5.7518-13.05301,18.36017,78.2884,2.59582c7.06351,2.51549,14.58749-2.36608,15.16884-9.84155v-.00003c.19626-2.52421-15.8874-66.57211-25.62415-72.51812-11.41186-6.96901-30.85838,23.63765-30.85838,23.63765l6.36944-10.60332-6.36944,10.60332Z" fill="var(--primary-color)"/>
                    </g>
                    <circle cx="492.52648" cy="51.43303" r="26.33295" transform="translate(433.15935 543.06627) rotate(-89.07621)" fill="#a0616a"/>
                    <path d="M550.33072,133.47993c-.65997,2.5-5.79999,3.78998-7,7-3.82001,2.17999-1.75-15.63-3.28998-16.79999-1.19-.89001-1.51001,1.25995-1.78003-.59003-1.02002-6.94-7.69-5.39001-12.63-6.51001-.56-1.08997-.98999-2.14996-1.26996-3.17999-.5-1.85999-2.78003-2.51001-4.13-1.14001-.61005-.53998-1.05005-2.45001-1.48004-4.35999-.31-1.39001-.62-2.77997-.97998-3.65997l-3.44-7.76001,1.10999,5.77997c-2.25-.81-4.51996,.03003-5.82996-2.75-1.68005-3.57001-2.35004-7.75-1.13-11.5,1.10999-3.39996,2.97998-10.20996,5.08997-15.84998-2.39001,1.72998-5.26001,2.98999-8.58997,3.62,1.17999-2.23999,2.38-4.52002,2.88-7.01001,.48999-2.48999,.18994-5.26001-1.39001-7.23999-1.29999-1.63-3.22003-1.22003-4.84003-2.53003-1.26001-1.01996-2.34998-3.62994-3.10999-5.06,3.23999,10.77002,5.69,22.21002,1.57001,32.49005-2.85004,7.12-8.85004,12.90997-16.04999,15.53998,3.82996-2.73999,7.57001-5.66003,10.44-9.38,3.06995-3.98999,5.06995-9.03003,4.45996-14.03003-.95996-7.84998-15.51996-32.67999-17.31-37.56,.98004,4.77002,.60004,8.49005-1.97998,10.54004-.66998-6.91998-8.21002-6.29004-12.32001-9.06,0,0-3.17999-17.97003,4.28003-18.08002,3.59998-.04999,7.37-8.51001,10.63995-10.02002,5.22003-2.40997,8.42004-.76996,14.21002-.58997,5.77997,.16998,11.33002,2.65997,14.85999,7.22998,1.85004,2.39001,2.53003,.48999,5.31,1.97998,2.64001,1.41003,4.41998,.10004,7.41003,.19,5.98999,.16003,11.79999,3.36005,15.15997,8.31,3.35999,4.96002,5.42999,4.5,4.09998,12.17004-.01996,.08997-.01996,.26996,0,.53998,.73004,12.08002-1.13995,24.19-5.92999,35.31-1.47998,3.41998,5.75938,6.94984,5.74938,11.21986-.01001,4.96002-7.99938,9.48015-7.25932,11.60015,2.12,10.21002,14.02997,7.94,11.21997,25.03003-.22998,1.38995,3.69,4.43994,3.25,6.10999Z" fill="#2f2e41"/>
                    <path d="M477.37841,102.21068c.21997-.06995,.44-.14996,.65997-.23999-.27997,.20003-.54999,.39001-.82996,.59003l.16998-.35004Z" fill="#2f2e41"/>
                    <path d="M514.47042,17.96025c-2.8645,.42569-5.77454-4.32687-4.14813-10.91754,3.54431,5.41956,5.35599,11.52063,5.64108,18.22217l-1.91016,.5918,.4172-7.89643Z" fill="var(--primary-color)"/>
                    <path d="M529.10567,11.80848c-1.95477,6.92664-11.88171,11.56229-13.63525,9.15176l-.20065,4.13065-1.22723-1.57885c3.5603-5.29441,8.41327-9.35589,15.06305-11.70356h.00009Z" fill="var(--primary-color)"/>
                    <g>
                        <path d="M311.12087,198.59383c0-2.30142,1.8725-4.17392,4.17392-4.17392h67.08496c2.30142,0,4.17392,1.8725,4.17392,4.17392s-1.8725,4.17392-4.17392,4.17392h-67.08496c-2.30142,0-4.17392-1.8725-4.17392-4.17392Z" fill="#e6e7e8"/>
                        <path d="M311.12087,226.59383c0-2.30142,1.8725-4.17392,4.17392-4.17392h67.08496c2.30142,0,4.17392,1.8725,4.17392,4.17392s-1.8725,4.17392-4.17392,4.17392h-67.08496c-2.30142,0-4.17392-1.8725-4.17392-4.17392Z" fill="#e6e7e8"/>
                        <path d="M262.94143,216.89486c-2.30142,0-4.17392-1.8725-4.17392-4.17392s1.8725-4.17392,4.17392-4.17392h119.43829c2.30142,0,4.17392,1.8725,4.17392,4.17392s-1.8725,4.17392-4.17392,4.17392h-119.43829Z" fill="#e6e7e8"/>
                    </g>
                    <g>
                        <path d="M254.06191,176.94701l-8.22992-3.89856c3.37555,4.71082,6.28418,12.06805,7.84137,17.92249,2.63654-5.45343,6.88672-12.1261,11.09375-16.11194l-8.69812,2.23767c5.35974-26.26794,25.5191-45.12573,48.60437-45.12573v-2c-24.11316,0-45.13898,19.64758-50.61145,46.97607Z" fill="#3f3d56"/>
                        <path d="M291.94109,216.97094h-52c-2.20557,0-4-1.79443-4-4s1.79443-4,4-4h52c2.20557,0,4,1.79443,4,4s-1.79443,4-4,4Z" fill="var(--primary-color)"/>
                    </g>
                    <circle cx="291.67334" cy="132.65737" r="15.65737" transform="translate(114.00804 399.31512) rotate(-80.78253)" fill="#3f3d56"/>
                    <circle cx="206.67334" cy="9.65737" r="9.65737" fill="#e6e7e8"/>
                    <circle cx="319.67334" cy="176.65737" r="9.65737" fill="#e6e7e8"/>
                </svg>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="features">
        <div class="features-container">
            <div class="section-title animate-on-scroll fade-in">
                <h2><?php echo t('features_title'); ?></h2>
                <p><?php echo t('features_subtitle'); ?></p>
            </div>
            <div class="features-grid">
                <div class="feature-card animate-on-scroll slide-up">
                    <div class="feature-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h3><?php echo t('feature1_title'); ?></h3>
                    <p><?php echo t('feature1_desc'); ?></p>
                </div>
                
                <div class="feature-card animate-on-scroll slide-up">
                    <div class="feature-icon">
                        <i class="fas fa-desktop"></i>
                    </div>
                    <h3><?php echo t('feature2_title'); ?></h3>
                    <p><?php echo t('feature2_desc'); ?></p>
                </div>
                
                <div class="feature-card animate-on-scroll slide-up">
                    <div class="feature-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3><?php echo t('feature3_title'); ?></h3>
                    <p><?php echo t('feature3_desc'); ?></p>
                </div>
                
                <div class="feature-card animate-on-scroll slide-up">
                    <div class="feature-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3><?php echo t('feature4_title'); ?></h3>
                    <p><?php echo t('feature4_desc'); ?></p>
                </div>
                
                <div class="feature-card animate-on-scroll slide-up">
                    <div class="feature-icon">
                        <i class="fas fa-bell"></i>
                    </div>
                    <h3><?php echo t('feature5_title'); ?></h3>
                    <p><?php echo t('feature5_desc'); ?></p>
                </div>
                
                <div class="feature-card animate-on-scroll slide-up">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3><?php echo t('feature6_title'); ?></h3>
                    <p><?php echo t('feature6_desc'); ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats">
        <div class="stats-container">
            <h2 class="animate-on-scroll fade-in"><?php echo t('stats_title'); ?></h2>
            <p class="animate-on-scroll fade-in"><?php echo t('stats_subtitle'); ?></p>
            <div class="stats-grid">
                <div class="stat-item animate-on-scroll scale-in">
                    <span class="stat-number"><?php echo t('stat1_number'); ?></span>
                    <span class="stat-label"><?php echo t('stat1_label'); ?></span>
                </div>
                <div class="stat-item animate-on-scroll scale-in">
                    <span class="stat-number"><?php echo t('stat2_number'); ?></span>
                    <span class="stat-label"><?php echo t('stat2_label'); ?></span>
                </div>
                <div class="stat-item animate-on-scroll scale-in">
                    <span class="stat-number"><?php echo t('stat3_number'); ?></span>
                    <span class="stat-label"><?php echo t('stat3_label'); ?></span>
                </div>
                <div class="stat-item animate-on-scroll scale-in">
                    <span class="stat-number"><?php echo t('stat4_number'); ?></span>
                    <span class="stat-label"><?php echo t('stat4_label'); ?></span>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section id="about" class="cta">
        <div class="cta-container">
            <h2><?php echo t('cta_title'); ?></h2>
            <p><?php echo t('cta_description'); ?></p>
            <div class="cta-buttons">
                
                
                <a href="auth/login.php" class="btn btn-client btn-large">
                    <i class="fas fa-user"></i>
                    <?php echo t('btn_client'); ?>
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer id="contact" class="footer">
        <div class="footer-container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3><i class="fas fa-shipping-fast"></i> <?php echo t('site_name'); ?></h3>
                    <p><?php echo t('footer_about_text'); ?></p>
                </div>
                <div class="footer-section">
                    <h3><?php echo t('footer_quick_links'); ?></h3>
                    <p>
                        <a href="#features" style="color: white; text-decoration: none;">• <?php echo t('features'); ?></a><br>
                        <a href="#about" style="color: white; text-decoration: none;">• <?php echo t('about'); ?></a><br>
                        <a href="#contact" style="color: white; text-decoration: none;">• <?php echo t('contact'); ?></a><br>
                        <a href="auth/login.php" style="color: white; text-decoration: none;">• <?php echo t('login'); ?></a>
                    </p>
                </div>
                <div class="footer-section">
                    <h3><?php echo t('footer_contact_title'); ?></h3>
                    <p><?php echo t('footer_contact_text'); ?></p>
                </div>
            </div>
            <div class="footer-bottom">
                <p><?php echo t('footer_bottom'); ?></p>
            </div>
        </div>
    </footer>

    <script>
        // Language switcher functionality
        document.addEventListener('DOMContentLoaded', function() {
            const languageSelector = document.querySelector('.language-selector');
            const langBtn = document.querySelector('.lang-btn');
            const langOptions = document.querySelectorAll('.lang-option');
            
            // Toggle dropdown on button click
            if (langBtn) {
                langBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    languageSelector.classList.toggle('active');
                });
            }
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (languageSelector && !languageSelector.contains(e.target)) {
                    languageSelector.classList.remove('active');
                }
            });
            
            // Handle language option clicks
            langOptions.forEach(option => {
                option.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const hiddenInput = this.querySelector('input[name="language"]');
                    const selectedLang = hiddenInput.value;
                    
                    // Create a new form to submit
                    const submitForm = document.createElement('form');
                    submitForm.method = 'POST';
                    submitForm.style.display = 'none';
                    
                    const langInput = document.createElement('input');
                    langInput.type = 'hidden';
                    langInput.name = 'language';
                    langInput.value = selectedLang;
                    
                    const changeInput = document.createElement('input');
                    changeInput.type = 'hidden';
                    changeInput.name = 'change_language';
                    changeInput.value = selectedLang;
                    
                    submitForm.appendChild(langInput);
                    submitForm.appendChild(changeInput);
                    document.body.appendChild(submitForm);
                    submitForm.submit();
                });
            });
        });

        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Enhanced scroll animations
        function initScrollAnimations() {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('animate');
                    }
                });
            }, {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            });

            // Observe all elements with animation classes
            document.querySelectorAll('.animate-on-scroll').forEach(el => {
                observer.observe(el);
            });
        }

        // Number counting animation for stats
        function animateNumbers() {
            const statNumbers = document.querySelectorAll('.stat-number');
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const target = entry.target;
                        const finalValue = target.textContent;
                        
                        // Check if it's a percentage or number
                        if (finalValue.includes('%')) {
                            const number = parseInt(finalValue.replace('%', ''));
                            animateValue(target, 0, number, 1500, '%');
                        } else if (finalValue.includes('h')) {
                            const number = parseInt(finalValue.replace('h', ''));
                            animateValue(target, 0, number, 1500, 'h');
                        } else {
                            const number = parseInt(finalValue);
                            animateValue(target, 0, number, 1500);
                        }
                        
                        observer.unobserve(target);
                    }
                });
            });

            statNumbers.forEach(stat => {
                observer.observe(stat);
            });
        }

        // Animate value function
        function animateValue(element, start, end, duration, suffix = '') {
            const range = end - start;
            const increment = end > start ? 1 : -1;
            const stepTime = Math.abs(Math.floor(duration / range));
            let current = start;
            
            const timer = setInterval(() => {
                current += increment;
                element.textContent = current + suffix;
                
                if (current === end) {
                    clearInterval(timer);
                }
            }, stepTime);
        }

        // Initialize all animations when DOM is loaded
        document.addEventListener('DOMContentLoaded', () => {
            initScrollAnimations();
            animateNumbers();
        });
    </script>
</body>
</html>