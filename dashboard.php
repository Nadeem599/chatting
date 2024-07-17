<?php

include_once('logout_user_auto.php');

if(isset($_GET['logout'])){
    session_unset();
    session_destroy();
    header("Location: /index.php");
    exit();
}


if (!isset($_SESSION['phoneNumber'])) {
    header("Location: /index.php");
    exit();
}


?>



<!DOCTYPE html>
<html lang="en">
<head>
  <link rel="manifest" href="/manifest.json">
  
  <script src="https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.page.js" defer></script>
<script>
  window.OneSignalDeferred = window.OneSignalDeferred || [];
  OneSignalDeferred.push(function(OneSignal) {
    OneSignal.init({
      appId: "85c43487-7948-4b6a-a760-f4c620eb7411",
    });
  });
</script>



</head>
<body>
    <div class="main-dashboard" >
        <h3>Dashboard</h3>
        <h3><a href="dashboard.php/?logout">Logout</a></h3>
    </div> 
    <h3><a href="plan_subscription.php">Subscribe Plan</a></h3>
    
</body>
</html>