<?php
session_start();

$inactive = 3 * 60 * 60; // 3 hours
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $inactive) {
    session_unset();
    session_destroy();

    $message = "<script>alert('Session has expired. Please log in again.');</script>";   
    echo $message; 
    echo "<script> window.location.href='/index.php'; </script>";
    exit();
}
?>