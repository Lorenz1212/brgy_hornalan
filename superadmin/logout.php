<?php
session_start();
unset($_SESSION['username']); // Burahin lang ang admin session
session_regenerate_id(true); // Baguhin ang session ID para sa seguridad

// Prevent browser from caching the previous page
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

// Redirect sa admin login page
header("Location: index.php");
exit();
?>
