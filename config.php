<?php
date_default_timezone_set('Asia/Kolkata');
$options = [
    PDO::ATTR_EMULATE_PREPARES => false, // turn off emulation mode for "real" prepared statements
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, //turn on errors in the form of exceptions
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, //make the default fetch be an associative array
];
// DB Handler
$dBhandler = new PDO('mysql:host=localhost;dbname=hpvpbhelweb', 'hpvpbhelweb', 'webbhelhpvp', $options);

// Session configuration
session_start();

// Check if user is already logged in
function isLoggedIn()
{
    return isset($_SESSION['HOU_ID']);
}

// Redirect to login if not authenticated
function requireLogin()
{
    if (!isLoggedIn()) {
        header("Location: index.php?err=Session Expired");
        exit();
    }
}
?>