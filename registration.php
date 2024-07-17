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
    <title>Registration</title>


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
    <h1>Registration</h1>
    <form id="registrationForm">

        <label for="name">Name:</label>
        <input type="text" id="name" name="name" required><br><br>

        <label for="phoneNumber">Phone Number:</label>
        <input type="number" id="phoneNumber" name="phoneNumber" required><br><br>
        
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required><br><br>

        <label for="repassword">Re-enter Password:</label>
        <input type="password" id="repassword" name="repassword" required><br><br>
        
        <button type="submit">Register</button>
    </form>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>


    <script>

$(document).ready(function() {
    $('#registrationForm').submit(function(event) {
        event.preventDefault();
        
        var phoneNumber = $('#phoneNumber').val();
        var password = $('#password').val();
        var repassword = $('#repassword').val();
        var name = $('#name').val();

        if(password != repassword){
            alert('password not match');
            return false;
        }
        
        
        $.ajax({
            type: 'POST',
            url: 'registerajax.php',
            data: {
                phoneNumber: phoneNumber,
                password: password,
                name: name
            },
            dataType: 'json',
            success: function(response) {
                if (response.success == true && response.customer == 1) { 
                    alert (response.field + " is already exist!");
                }else if(response.success == true && response.customer == 2) {
                    alert('Successfully Registered' );
                    window.location.href = "index.php";
                }else{
                    // do nothing
                }
            },
            error: function(xhr, status, error) {
                alert('Registration failed: ' + error);
            }
        });
    });
});

    </script>




</body>
</html>


