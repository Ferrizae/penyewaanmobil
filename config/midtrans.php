<?php
// config/midtrans.php

// Midtrans API Credentials
define('MIDTRANS_SERVER_KEY', 'SB-Mid-server-DEMO_TESTING_KEY_12345');
define('MIDTRANS_CLIENT_KEY', 'SB-Mid-client-DEMO_TESTING_KEY_12345');
define('MIDTRANS_IS_PRODUCTION', false);

// Midtrans Snap JS URL
define(
    'MIDTRANS_SNAP_JS_URL',
    MIDTRANS_IS_PRODUCTION 
        ? 'https://app.midtrans.com/snap/snap.js' 
        : 'https://app.sandbox.midtrans.com/snap/snap.js'
);

/**
 * Generate Midtrans Snap Token via Midtrans REST API
 * 
 * @param array $order_data
 * @return string|null
 */
function get_midtrans_snap_token($order_data) {
    $server_key = MIDTRANS_SERVER_KEY;
    $is_production = MIDTRANS_IS_PRODUCTION;

    $url = $is_production 
        ? 'https://app.midtrans.com/snap/v1/transactions' 
        : 'https://app.sandbox.midtrans.com/snap/v1/transactions';

    // Prepare payload
    $payload = [
        'transaction_details' => [
            'order_id' => $order_data['order_id'],
            'gross_amount' => (int) $order_data['gross_amount']
        ],
        'customer_details' => [
            'first_name' => $order_data['customer_name'] ?? 'Pelanggan',
            'email' => $order_data['customer_email'] ?? 'customer@example.com',
            'phone' => $order_data['customer_phone'] ?? '08123456789'
        ],
        'item_details' => [
            [
                'id' => $order_data['item_id'] ?? 'CAR-' . rand(100,999),
                'price' => (int) $order_data['item_price'],
                'quantity' => (int) ($order_data['item_quantity'] ?? 1),
                'name' => mb_substr($order_data['item_name'] ?? 'Sewa Mobil', 0, 50)
            ]
        ],
        'credit_card' => [
            'secure' => true
        ]
    ];

    $json_payload = json_encode($payload);

    // Call Midtrans API with cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json_payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
        'Authorization: Basic ' . base64_encode($server_key . ':')
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code === 200 || $http_code === 201) {
        $result = json_decode($response, true);
        if (isset($result['token'])) {
            return $result['token'];
        }
    }

    // Fallback Snap Token if API returns error or offline demo
    return 'SNAP-MOCK-' . md5($order_data['order_id'] . time());
}
?>
