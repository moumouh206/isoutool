<?php
// Site configuration
define('SITE_NAME', 'ISOU TOOLS France');
define('SITE_URL', 'http://localhost/isoutool/');
define('SITE_DESCRIPTION', 'Solutions industrielles et composants électroniques');

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'isoutools');
define('DB_USER', 'root');
define('DB_PASS', '');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Session
session_start();

// Timezone
date_default_timezone_set('Europe/Paris');
