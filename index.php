<?php
/**
 * NoDB-DomainPark
 * Author: max-godman (max_godman@foxmail.com)
 * GitHub: https://github.com/max-godman
 */

$fullHost = $_SERVER['HTTP_HOST'] ?? '';
$fullHost = strtolower(trim($fullHost));

function extractRootDomain($host) {
    $host = preg_replace('/:\d+$/', '', $host);
    
    if (substr($host, 0, 4) === 'www.') {
        $host = substr($host, 4);
    }
    
    $parts = explode('.', $host);
    
    if (count($parts) <= 1) {
        return $host;
    }
    
    $specialTlds = [
        'co.uk', 'com.au', 'co.jp', 'com.cn', 'net.cn', 'org.cn',
        'gov.cn', 'ac.uk', 'sch.uk', 'co.nz', 'com.br', 'com.mx'
    ];
    
    if (count($parts) >= 3) {
        $lastTwo = $parts[count($parts) - 2] . '.' . $parts[count($parts) - 1];
        if (in_array($lastTwo, $specialTlds)) {
            return implode('.', array_slice($parts, -3));
        }
    }
    
    return implode('.', array_slice($parts, -2));
}

$rootDomain = extractRootDomain($fullHost);

$domains = [];
if (file_exists(__DIR__ . '/inc/domain.php')) {
    require_once __DIR__ . '/inc/domain.php';
}

$matchedDomain = null;
foreach ($domains as $domainConfig) {
    if ($domainConfig['domain'] === $rootDomain) {
        $matchedDomain = $domainConfig;
        break;
    }
}

if ($matchedDomain === null) {
    echo htmlspecialchars($rootDomain) . "\n";
    
    if (file_exists(__DIR__ . '/inc/link.php')) {
        include __DIR__ . '/inc/link.php';
    } else {
        echo "<!-- txt -->";
    }
    exit();
}

$dataFile = __DIR__ . "/data/{$rootDomain}";
$domainData = ['click' => 0, 'useragent' => []];

if (file_exists($dataFile)) {
    $domainData = json_decode(file_get_contents($dataFile), true);
    if (!is_array($domainData)) {
        $domainData = ['click' => 0, 'useragent' => []];
    }
}

function getRealIp() {
    if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
        return $_SERVER['HTTP_CF_CONNECTING_IP'];
    }
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return trim($ips[0]);
    }
    if (!empty($_SERVER['HTTP_X_REAL_IP'])) {
        return $_SERVER['HTTP_X_REAL_IP'];
    }
    return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
}

$currentIp = getRealIp();

$shouldRecord = true;
if (!empty($domainData['useragent']) && isset($domainData['useragent'][0]['ip'])) {
    if ($domainData['useragent'][0]['ip'] === $currentIp) {
        $shouldRecord = false;
    }
}

if ($shouldRecord) {
    $domainData['click'] = ($domainData['click'] ?? 0) + 1;
    
    $userAgentRecord = [
        'time' => date('Y-m-d H:i:s'),
        'ip' => $currentIp,
        'useragent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        'usercountry' => $_SERVER['HTTP_CF_IPCOUNTRY'] ?? 'unknown'
    ];
    
    $maxRecords = intval($matchedDomain['userdata'] ?? 10);
    if ($maxRecords <= 0) {
        $maxRecords = 10;
    }
    
    array_unshift($domainData['useragent'], $userAgentRecord);
    if (count($domainData['useragent']) > $maxRecords) {
        $domainData['useragent'] = array_slice($domainData['useragent'], 0, $maxRecords);
    }
    
    file_put_contents($dataFile, json_encode($domainData, JSON_PRETTY_PRINT));
}

$type = intval($matchedDomain['type']);
$title = !empty($matchedDomain['title']) ? $matchedDomain['title'] : $rootDomain;
$about = $matchedDomain['about'] ?? '';
$url = $matchedDomain['url'] ?? '';

if ($type == 0 || $type == 1) {
    echo htmlspecialchars($rootDomain) . "\n";
    
    if (file_exists(__DIR__ . '/inc/link.php')) {
        include __DIR__ . '/inc/link.php';
    } else {
        echo "<!-- txt -->";
    }
} elseif ($type == 2) {
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: " . $url);
    exit();
} elseif ($type == 3) {
    header("Location: " . $url);
    exit();
} elseif ($type == 4) {
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
