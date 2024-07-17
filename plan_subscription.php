<?php 
    
    include_once('logout_user_auto.php');



    if (!isset($_SESSION['phoneNumber'])) {
        header("Location: /index.php");
    }

    $already_subscribed = '';

    $secret_key = 'sk_test_51HoNUhI1jXJADDxK1ocbS5GpRNaDgwvnyyWFRUTD7JlQVRlibBOiDQjt7z3KTfZDqLkYtpeADX0Z5UUrBfs2evYR00O2W0tQ7N';

    // Fetch all plans
    $url = 'https://api.stripe.com/v1/plans';
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, $secret_key . ':');
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    $plans_response = curl_exec($ch);
    curl_close($ch);

    $plans = json_decode($plans_response, true);

//////////////////////////////////////////////////////

if(isset($_POST['submit_payment'])){

    $customerid = $_POST['customerId'];
    $planid     = $_POST['planId'];

    $subscriptions_url = 'https://api.stripe.com/v1/subscriptions';
    $secret_key = 'sk_test_51HoNUhI1jXJADDxK1ocbS5GpRNaDgwvnyyWFRUTD7JlQVRlibBOiDQjt7z3KTfZDqLkYtpeADX0Z5UUrBfs2evYR00O2W0tQ7N';

    $ch = curl_init($subscriptions_url . '?customer=' . $customerid);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, $secret_key . ':');
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    $subscriptions_response = curl_exec($ch);
    curl_close($ch);

    $subscriptions = json_decode($subscriptions_response, true);

    foreach ($subscriptions['data'] as $subscription) {
        if (isset($subscription['plan']['id']) && $subscription['plan']['id'] == $planid) {
            $already_subscribed = 'Plan already subscribed! Please choose any other';
            break;
        }
    }


    if(empty($already_subscribed)){

        $secret_key = 'sk_test_51HoNUhI1jXJADDxK1ocbS5GpRNaDgwvnyyWFRUTD7JlQVRlibBOiDQjt7z3KTfZDqLkYtpeADX0Z5UUrBfs2evYR00O2W0tQ7N';
        $YOUR_DOMAIN = 'https://stripe.liveblog365.com/';

        $url = 'https://api.stripe.com/v1/checkout/sessions';
        $data = [
        'line_items' => [[
            'price' => $planid,
            'quantity' => 1,
        ]],
        'mode' => 'subscription',
        'success_url' => $YOUR_DOMAIN . 'success.html',
        'cancel_url' => $YOUR_DOMAIN . 'cancel.html',
        'customer' => $customerid,
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $secret_key . ':');
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        $response = curl_exec($ch);
        curl_close($ch);

        $checkout_session = json_decode($response, true);

        header('Location: ' . $checkout_session['url']);
        exit;
    }

}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscribe to Plan</title>
</head>
<body>
    <h1><a href="dashboard.php">Dashboard</a></h1>
    <h1>Subscribe to Plan</h1>
    <h1><?php if(!empty($already_subscribed)){ echo $already_subscribed; } ?></h1>
    <form action="" id="subscriptionForm" method="POST">
        <input type="hidden" name="customerId" id="customerId" value="<?php echo $_SESSION['customerid']; ?>">
        <select name="planId" required>
            <option value="">Select a Plan</option>
            <?php foreach ($plans['data'] as $plan): ?>
                <?php if($plan['nickname'] != 'dailybasic') { ?>
                    <option value="<?php echo $plan['id']; ?>"><?php echo $plan['nickname']; ?></option>
                <?php } ?>
            <?php endforeach; ?>
        </select><br><br>
        <input type="submit" name="submit_payment" value="Subscribe">
    </form>
</body>
</html>
