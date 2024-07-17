
<?php
session_start();

// Set your Stripe secret key
$secret_key = 'sk_test_51HoNUhI1jXJADDxK1ocbS5GpRNaDgwvnyyWFRUTD7JlQVRlibBOiDQjt7z3KTfZDqLkYtpeADX0Z5UUrBfs2evYR00O2W0tQ7N';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phoneNumber = $_POST['phoneNumber'];
    $password = $_POST['password'];
    // $name = $_POST['name'];

    try {
        // Retrieve customers from Stripe using API
        $url = 'https://api.stripe.com/v1/customers';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $secret_key . ':');
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        $customers_response = curl_exec($ch);
        curl_close($ch);

        // Check for errors
        if ($customers_response === false) {
            throw new Exception('cURL error: ' . curl_error($ch));
        }

        // Decode the JSON response
        $customers = json_decode($customers_response, true);

        $flag = '';
        if (count($customers['data']) > 0) {
            foreach ($customers['data'] as $customer) {

                // $c_name = $customer['name'];
                $c_phone = $customer['phone'];
                $customerPassword = $customer['metadata']['password'] ?? '';

                if (password_verify($password, $customerPassword) && $phoneNumber == $c_phone ) {
                    $flag = true;
                    $_SESSION['customerid'] = $customer['id'];
                    $_SESSION['last_activity'] = time();
                }
                
            }
            if(empty($flag)){
                echo json_encode(['success' => true, 'customer' => '1']);
                exit;
            }else{
                $_SESSION['phoneNumber'] = $phoneNumber;
                $_SESSION['password'] = $password; 
                // $_SESSION['name'] = $name;
                
                echo json_encode(['success' => true, 'customer' => '2' ]);
                exit;
            }
        } else {
            // Customer not found
            echo json_encode(['success' => true, 'customer' => '3']);
            exit;
        }
    } catch (Exception $e) {
        // Handle login failure
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit;
    }
}
?>

