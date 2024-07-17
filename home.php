<?php
include_once('logout_user_auto.php');

if (!isset($_SESSION['phoneNumber'])) {
    header("Location: /index.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Page</title>
</head>
<body>
<h1>Home Page</h1>
<h1><a href="dashboard.php">Dashboard</a></h1>

<?php include_once('conversation.php'); ?>
</body>
</html>