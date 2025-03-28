<?php
session_start();
unset($_SESSION['user']); // Burahin lang ang user session
session_regenerate_id(true); // Baguhin ang session ID para sa seguridad

// Only clear the session data for the current user
if (isset($_SESSION['userID'])) {
    unset($_SESSION['userID']); // Remove only the current user's session
    unset($_SESSION['username']);
    session_regenerate_id(true); // Regenerate the session ID for security
}


// Prevent browser from caching the previous page
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

// Redirect sa user login page
header("Location: index.php");
exit();
?>
