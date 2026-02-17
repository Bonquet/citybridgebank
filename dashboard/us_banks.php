<?php
require_once '../includes/config.php';
header('Content-Type: application/json');

if (!Security::isLoggedIn()) {
    echo json_encode(['success' => false, 'banks' => [], 'message' => 'Unauthorized']);
    exit;
}

function fetchApi(string $url): string {
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => 6,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_USERAGENT => 'CityBridgeBank/1.0'
        ]);
        $body = (string)curl_exec($ch);
        curl_close($ch);
        return $body;
    }

    $ctx = stream_context_create([
        'http' => [
            'timeout' => 15,
            'header' => "User-Agent: CityBridgeBank/1.0\r\n"
        ]
    ]);
    return (string)@file_get_contents($url, false, $ctx);
}

function parseFdicRows(array $data): array {
    $banks = [];
    foreach (($data['data'] ?? []) as $row) {
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
    return $banks;
}

function getAllBanks(): array {
    $cacheDir = __DIR__ . '/../cache';
    $cacheFile = $cacheDir . '/us_banks_all.json';

    if (!is_dir($cacheDir)) {
        @mkdir($cacheDir, 0775, true);
    }

    if (is_file($cacheFile) && (time() - filemtime($cacheFile)) < 86400) {
        $json = (string)@file_get_contents($cacheFile);
        $cached = json_decode($json, true);
        if (is_array($cached) && isset($cached['banks']) && is_array($cached['banks'])) {
            return $cached['banks'];
        }
    }

    // Maintained source: FDIC Institutions API.
    // Large fetch for scroll/search list.
    $url = 'https://banks.data.fdic.gov/api/institutions?fields=NAME,CITY,STALP,CERT&limit=10000&format=json';
    $body = fetchApi($url);
    if ($body === '') {
        return [];
    }

    $decoded = json_decode($body, true);
    if (!is_array($decoded)) {
        return [];
    }

    $banks = parseFdicRows($decoded);

    // Deduplicate by label and sort for scrolling.
    $unique = [];
    foreach ($banks as $b) {
        $unique[$b['label']] = $b;
    }
    ksort($unique, SORT_NATURAL | SORT_FLAG_CASE);
    $banks = array_values($unique);

    @file_put_contents($cacheFile, json_encode(['banks' => $banks]));
    return $banks;
}

$allMode = isset($_GET['all']) && $_GET['all'] === '1';
if ($allMode) {
    $banks = getAllBanks();
    if (empty($banks)) {
        echo json_encode(['success' => true, 'banks' => [], 'message' => 'Bank directory unavailable. You can enter bank name manually.']);
        exit;
    }

    echo json_encode(['success' => true, 'banks' => $banks]);
    exit;
}

$query = trim(Security::sanitizeInput($_GET['q'] ?? ''));
if ($query === '' || strlen($query) < 2) {
    echo json_encode(['success' => true, 'banks' => []]);
    exit;
}

$banks = getAllBanks();
if (empty($banks)) {
    echo json_encode(['success' => true, 'banks' => [], 'message' => 'Bank directory unavailable. You can enter bank name manually.']);
    exit;
}

$q = strtolower($query);
$filtered = [];
foreach ($banks as $bank) {
    if (stripos($bank['label'], $q) !== false || stripos($bank['name'], $q) !== false) {
        $filtered[] = $bank;
    }
    if (count($filtered) >= 200) {
        break;
    }
}

echo json_encode(['success' => true, 'banks' => $filtered]);
