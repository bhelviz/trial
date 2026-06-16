<?php
session_start();
session_unset();
session_destroy();
header("Location: index.php?err=Logged%20Out%20Successfully");
exit();
?>