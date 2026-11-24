<?php
require_once '../../../../wp-load.php';

header('Content-Type: application/json');

if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request method']);
    exit;
}

$postData = json_decode(file_get_contents('php://input'), true);

if (!current_user_can('manage_options')) {
    http_response_code(403);
    echo json_encode(['error' => 'You do not have permission to perform this action.']);
    exit;
}

if (!wp_verify_nonce($postData['wpnonce'] ?? '', 'whizzie_nonce')) {
    http_response_code(403);
    echo json_encode(['error' => 'Security check failed.']);
    exit;
}

$action = $postData['action'] ?? 'getProducts';
$url = '';

switch ($action) {
    case 'getProducts':
        $url = EDIOT_SHOPIFY_LICENCE_ENDPOINT .'getFilteredProducts';
        $productHandle = $postData['productHandle'] ?? "";
        $data = [
            "collectionHandle" => $productHandle !== "" ? "" : ($postData['collectionHandle'] ?? ""),
            "productHandle" => $productHandle,
            "paginationParams" => $postData['paginationParams'] ?? [
                "first" => 12,
                "afterCursor" => null,
                "beforeCursor" => null,
                "reverse" => true
            ]
        ];

        break;
    case 'getCollections':
        $url = EDIOT_SHOPIFY_LICENCE_ENDPOINT .'getCollections';
        $data = []; // Use an empty array to send a proper POST request
        break;
    default:
        echo json_encode(['error' => 'Invalid action']);
        exit;
}

$args = [
    'method'    => 'POST',
    'body'      => json_encode($data),
    'headers'   => [
        'Content-Type' => 'application/json',
    ]
];

$response = wp_remote_post($url, $args);

// Check for errors
if (is_wp_error($response)) {
    echo json_encode(['error' => 'Request failed: ' . $response->get_error_message()]);
    exit;
}

// Check the response code
$http_code = wp_remote_retrieve_response_code($response);
if ($http_code !== 200) {
    echo json_encode(['error' => 'HTTP error: ' . $http_code]);
    exit;
}

$response_body = wp_remote_retrieve_body($response);
$data = json_decode($response_body, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['error' => 'Invalid JSON format received from the external API']);
    exit;
}

// Output the fetched data as JSON
echo json_encode($data);