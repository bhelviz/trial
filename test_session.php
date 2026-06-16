<?php
require_once 'config.php';
$role = isset($_GET['role']) ? $_GET['role'] : 'Security';

if ($role === 'Security') {
    $_SESSION["HOU_ID"] = "6203345";
    $_SESSION["HOU_NAME"] = "P SRINIVASA KUMAR";
    $_SESSION["HOU_ROLE"] = "Security";
} elseif ($role === 'Recommender') {
    $_SESSION["HOU_ID"] = "2767333";
    $_SESSION["HOU_NAME"] = "Partha Das";
    $_SESSION["HOU_ROLE"] = "Recommender";
} elseif ($role === 'Approver') {
    $_SESSION["HOU_ID"] = "6269583";
    $_SESSION["HOU_NAME"] = "S N V S RAMESH";
    $_SESSION["HOU_ROLE"] = "Approver";
} else {
    $_SESSION["HOU_ID"] = "30011";
    $_SESSION["HOU_NAME"] = "Srujana Engineering";
    $_SESSION["HOU_ROLE"] = "Vendor";
}

header("Location: main.php");
exit();
