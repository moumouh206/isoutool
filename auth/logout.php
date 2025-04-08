<?php
require_once '../includes/init.php';

// Destroy session
session_unset();
session_destroy();

// Redirect to home page
header('Location: ' . SITE_URL);
exit; 