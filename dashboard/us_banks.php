<?php
require_once '../includes/config.php';
header('Content-Type: application/json');

if (!Security::isLoggedIn()) {
    echo json_encode(['success' => false, 'banks' => [], 'message' => 'Unauthorized']);
    exit;
}

$query = trim(Security::sanitizeInput($_GET['q'] ?? ''));
if ($query === '' || strlen($query) < 2) {
    echo json_encode(['success' => true, 'banks' => []]);
    exit;
}

// Maintained source: FDIC Institutions API
$apiUrl = 'https://banks.data.fdic.gov/api/institutions?search=' . rawurlencode($query) . '&fields=NAME,CITY,STALP,CERT&limit=15&format=json';

$body = '';
if (function_exists('curl_init')) {
    $ch = curl_init($apiUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_CONNECTTIMEOUT => 4,
        CURLOPT_TIMEOUT => 8,
        CURLOPT_USERAGENT => 'CityBridgeBank/1.0'
    ]);
    $body = (string)curl_exec($ch);
    curl_close($ch);
} else {
    $ctx = stream_context_create([
        'http' => [
            'timeout' => 8,
            'header' => "User-Agent: CityBridgeBank/1.0\r\n"
        ]
    ]);
    $body = (string)@file_get_contents($apiUrl, false, $ctx);
}

if ($body === '') {
    echo json_encode(['success' => true, 'banks' => [], 'message' => 'Bank directory lookup unavailable. You can enter bank name manually.']);
    exit;
}

$data = json_decode($body, true);
if (!is_array($data) || !isset($data['data']) || !is_array($data['data'])) {
    echo json_encode(['success' => true, 'banks' => [], 'message' => 'No banks found. You can enter bank name manually.']);
    exit;
}

$banks = [];
foreach ($data['data'] as $row) {
    $d = $row['data'] ?? [];
    $name = trim((string)($d['NAME'] ?? ''));
    if ($name === '') {
        continue;
    }
    $city = trim((string)($d['CITY'] ?? ''));
    $state = trim((string)($d['STALP'] ?? ''));
    $cert = trim((string)($d['CERT'] ?? ''));
    $label = $name;
    if ($city !== '' || $state !== '') {
        $label .= ' — ' . trim($city . ', ' . $state, ' ,');
    }
    if ($cert !== '') {
        $label .= ' (FDIC #' . $cert . ')';
    }
    $banks[] = ['name' => $name, 'label' => $label];
}

echo json_encode(['success' => true, 'banks' => $banks]);
