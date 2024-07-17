<?php


// Set your Stripe secret key
$secret_key = 'sk_test_51HoNUhI1jXJADDxK1ocbS5GpRNaDgwvnyyWFRUTD7JlQVRlibBOiDQjt7z3KTfZDqLkYtpeADX0Z5UUrBfs2evYR00O2W0tQ7N';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phoneNumber = $_POST['phoneNumber'];
    $password = $_POST['password'];
    $name = $_POST['name'];

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    try {
        // Fetch customers
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
        foreach ($customers['data'] as $customer) {
            $c_phone = $customer['phone'];
            $customerPassword = $customer['metadata']['password'] ?? '';

            if ($phoneNumber == $c_phone) {
                $flag = 'Phone No.: ' . $phoneNumber;
                break;
            }
        }

        if (!empty($flag)) {
            echo json_encode(['success' => true, 'customer' => '1', 'field' => $flag]);
            exit;
        }

        // Create a new customer
        $url = 'https://api.stripe.com/v1/customers';
        $data = [
            'phone' => $phoneNumber,
            'name' => $name,
            'metadata' => [
                'password' => $hashedPassword
            ],
            'payment_method' => 'pm_card_visa',
            'invoice_settings' => [
                'default_payment_method' => 'pm_card_visa'
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $secret_key . ':');
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        $customer_response = curl_exec($ch);
        curl_close($ch);

        // Check for errors
        if ($customer_response === false) {
            throw new Exception('cURL error: ' . curl_error($ch));
        }

        // Decode the JSON response
        $customer_data = json_decode($customer_response, true);

        if (!empty($customer_data['id'])) {
                echo json_encode(['success' => true, 'customer' => '2']);
                exit;
        }
    } catch (Exception $e) {
        // Handle registration failure
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit;
    }
}
?>



