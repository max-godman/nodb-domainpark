<?php
/**
 * NoDB-DomainPark
 * Author: max-godman (max_godman@foxmail.com)
 * GitHub: https://github.com/max-godman
 * 
 * Frontend domain parking handler
 * This script processes incoming requests and displays appropriate content based on domain configuration
 */

// Get the full host from HTTP request header
$fullHost = $_SERVER['HTTP_HOST'] ?? '';
$fullHost = strtolower(trim($fullHost));

/**
 * Extract root domain from hostname
 * Handles various subdomain formats and special TLDs (like co.uk, com.cn, etc.)
 * 
 * @param string $host The full hostname
 * @return string The extracted root domain
 */
function extractRootDomain($host) {
    // Remove port number if present
    $host = preg_replace('/:\d+$/', '', $host);
    
    // Remove www prefix if present
    if (substr($host, 0, 4) === 'www.') {
        $host = substr($host, 4);
    }
    
    // Split domain into parts
    $parts = explode('.', $host);
    
    // If only one part, return as-is
    if (count($parts) <= 1) {
        return $host;
    }
    
    // Handle special TLDs that have two parts (e.g., co.uk, com.cn)
    $specialTlds = [
        'co.uk', 'com.au', 'co.jp', 'com.cn', 'net.cn', 'org.cn',
        'gov.cn', 'ac.uk', 'sch.uk', 'co.nz', 'com.br', 'com.mx'
    ];
    
    // Check if the last two parts form a special TLD
    if (count($parts) >= 3) {
        $lastTwo = $parts[count($parts) - 2] . '.' . $parts[count($parts) - 1];
        if (in_array($lastTwo, $specialTlds)) {
            // For special TLDs, take last three parts (e.g., example.co.uk)
            return implode('.', array_slice($parts, -3));
        }
    }
    
    // For regular domains, take last two parts (e.g., example.com)
    return implode('.', array_slice($parts, -2));
}

// Extract root domain from current request
$rootDomain = extractRootDomain($fullHost);

// Load domain configuration
$domains = [];
if (file_exists(__DIR__ . '/inc/domain.php')) {
    require_once __DIR__ . '/inc/domain.php';
}

// Find matching domain configuration
$matchedDomain = null;
foreach ($domains as $domainConfig) {
    if ($domainConfig['domain'] === $rootDomain) {
        $matchedDomain = $domainConfig;
        break;
    }
}

// If no matching domain found, display plain text domain name with friendship links
if ($matchedDomain === null) {
    echo htmlspecialchars($rootDomain) . "\n";
    
    if (file_exists(__DIR__ . '/inc/link.php')) {
        include __DIR__ . '/inc/link.php';
    } else {
        echo "<!-- txt -->";
    }
    exit();
}

// Load domain data file (.log extension for security)
$dataFile = __DIR__ . "/data/{$rootDomain}.log";
$domainData = ['click' => 0, 'useragent' => []];

// Load existing domain data if file exists
if (file_exists($dataFile)) {
    $domainData = json_decode(file_get_contents($dataFile), true);
    if (!is_array($domainData)) {
        $domainData = ['click' => 0, 'useragent' => []];
    }
}

/**
 * Get real visitor IP address
 * Handles Cloudflare and other CDN/proxy headers to get actual client IP
 * 
 * @return string The real IP address or 'unknown'
 */
function getRealIp() {
    // Cloudflare connecting IP (highest priority)
    if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
        return $_SERVER['HTTP_CF_CONNECTING_IP'];
    }
    // X-Forwarded-For header (common proxy header)
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return trim($ips[0]);
    }
    // X-Real-IP header (nginx, etc.)
    if (!empty($_SERVER['HTTP_X_REAL_IP'])) {
        return $_SERVER['HTTP_X_REAL_IP'];
    }
    // Fallback to REMOTE_ADDR
    return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
}

// Get current visitor IP
$currentIp = getRealIp();

// Check if we should record this visit (prevent duplicate records from same IP)
$shouldRecord = true;
if (!empty($domainData['useragent']) && isset($domainData['useragent'][0]['ip'])) {
    // Compare with last recorded IP
    if ($domainData['useragent'][0]['ip'] === $currentIp) {
        $shouldRecord = false;
    }
}

// Record visit if not a duplicate
if ($shouldRecord) {
    // Increment click counter
    $domainData['click'] = ($domainData['click'] ?? 0) + 1;
    
    // Create user agent record
    $userAgentRecord = [
        'time' => date('Y-m-d H:i:s'),
        'ip' => $currentIp,
        'useragent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        'usercountry' => $_SERVER['HTTP_CF_IPCOUNTRY'] ?? 'unknown'
    ];
    
    // Get maximum records to keep from domain configuration
    $maxRecords = intval($matchedDomain['userdata'] ?? 10);
    if ($maxRecords <= 0) {
        $maxRecords = 10;
    }
    
    // Add new record to beginning of array and limit total records
    array_unshift($domainData['useragent'], $userAgentRecord);
    if (count($domainData['useragent']) > $maxRecords) {
        $domainData['useragent'] = array_slice($domainData['useragent'], 0, $maxRecords);
    }
    
    // Save updated data to .log file
    file_put_contents($dataFile, json_encode($domainData, JSON_PRETTY_PRINT));
}

// Get domain configuration values
$type = intval($matchedDomain['type']);
$title = !empty($matchedDomain['title']) ? $matchedDomain['title'] : $rootDomain;
$about = $matchedDomain['about'] ?? '';
$url = $matchedDomain['url'] ?? '';

// Handle different domain types
if ($type == 0 || $type == 1) {
    // Type 0: Disabled, Type 1: Pending - display plain text
    echo htmlspecialchars($rootDomain) . "\n";
    
    if (file_exists(__DIR__ . '/inc/link.php')) {
        include __DIR__ . '/inc/link.php';
    } else {
        echo "<!-- txt -->";
    }
} elseif ($type == 2) {
    // Type 2: 301 Permanent Redirect
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: " . $url);
    exit();
} elseif ($type == 3) {
    // Type 3: 302 Temporary Redirect
    header("Location: " . $url);
    exit();
} elseif ($type == 4) {
    // Type 4: Display Page - show HTML content
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars($title); ?></title>
        <style>
            .container { width: 90%; max-width: 800px; margin: 1% auto 0; background-color: #f0f0f0; padding: 15px; border-radius: 5px; }
            ul { padding-left: 0px; list-style-type: none; margin: 0; }
            ul li { width: 50%; float: left; }
            ul:after { content: ""; display: table; clear: both; }
            a { color: #20a53a; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1><?php echo htmlspecialchars($title); ?></h1>
            <p><?php 
            // 
            $formattedAbout = nl2br(htmlspecialchars($about, ENT_NOQUOTES));
            echo str_replace('<Br>', '<br>', $formattedAbout);
            ?></p>
            
            <?php if (file_exists(__DIR__ . '/inc/link.php')): ?>
                <br>Links：<ul><?php include __DIR__ . '/inc/link.php'; ?></ul>
            <?php endif; ?>
        </div>
    </body>
    </html>
    <?php
}
?>
