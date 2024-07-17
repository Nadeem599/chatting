<?php
session_start();
if (isset($_SESSION['phoneNumber'])) {
    header("Location: home.php");
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>


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
    <h1>Login</h1>
    <form id="loginForm">

        <label for="phoneNumber">Phone Number:</label>
        <input type="text" id="phoneNumber" name="phoneNumber" required><br><br>
        
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required><br><br>
        
        <button type="submit">Login</button>
    </form>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>

$(document).ready(function() {
    $('#loginForm').submit(function(event) {
        event.preventDefault();
        
        var phoneNumber = $('#phoneNumber').val();
        var password = $('#password').val();
        // var name = $('#name').val();
        
        $.ajax({
            type: 'POST',
            url: 'loginajax.php',
            data: {
                phoneNumber: phoneNumber,
                password: password,
                // name: name
            },
            success: function(response) {
                //console.log(response);
                var response = JSON.parse(response);
                if (response.success == true){
                    if (response.customer == 1) { 
                        alert ("Credentials are not correct!");
                    }else if(response.customer == 2) {
                        alert('Successfully Loginned' );
                        window.location.href = "home.php";
                    }else{
                        alert('No any customer is registered');
                    }
                }
            },
            error: function(xhr, status, error) {
                alert('Login Failed: '+ error);
            }
        });
    });
});



    </script>
</body>
</html>
