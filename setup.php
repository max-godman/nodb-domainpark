<?php
/**
 * NoDB-DomainPark
 * Author: max-godman (max_godman@foxmail.com)
 * GitHub: https://github.com/max-godman
 * 
 * Setup script for initial installation
 * This script handles the first-time setup of the domain parking system
 */

// Check if admin configuration file already exists
// If it exists, exit silently (installation already completed)
if (file_exists(__DIR__ . '/inc/sys_admin.php')) {
    exit();
}

// Include SHA256 encryption utility
require_once __DIR__ . '/inc/inc-sha.php';

// Get current host from HTTP request and clean it
// Remove port number, protocol (http/https), and www prefix
$currentHost = $_SERVER['HTTP_HOST'] ?? 'your-domain.com';
$currentHost = preg_replace('/:\d+$/', '', $currentHost);
$currentHost = preg_replace('#^https?://(www\.)?#', '', strtolower($currentHost));
$currentHost = rtrim($currentHost, '/');

// Handle form submission for installation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get admin username and domain from POST data
    $userid = trim($_POST['userid'] ?? '');
    $userdomain = trim($_POST['userdomain'] ?? '');
    
    // Validate required fields
    if (empty($userid) || empty($userdomain)) {
        $error = "Admin username and domain are required.";
    } else {
        // Generate password: username + current date (YYMMDD)
        $datePart = date('ymd');
        $rawPassword = $userid . $datePart;
        $userpwd = sha256_hash($rawPassword);
        
        // Generate dynamic value for session security (YYMMDDHHMM)
        $userint = date('ymdHi');
        
        // Clean the admin domain input (remove protocol and www)
        $userdomain = preg_replace('#^https?://(www\.)?#', '', strtolower($userdomain));
        $userdomain = rtrim($userdomain, '/');
        
        // Create admin configuration content
        $adminConfig = "<?php\n";
        $adminConfig .= "/**\n * Admin configuration\n * Auto-generated, do not modify manually\n */\n";
        $adminConfig .= "\$admin_config = [\n";
        $adminConfig .= "    'userid' => '$userid',\n";
        $adminConfig .= "    'userpwd' => '$userpwd',\n";
        $adminConfig .= "    'userint' => '$userint',\n";
        $adminConfig .= "    'userdomain' => '$userdomain'\n";
        $adminConfig .= "];\n";
        $adminConfig .= "?>";
        
        // Write admin configuration file
        file_put_contents(__DIR__ . '/inc/sys_admin.php', $adminConfig);
        
        // Clean up old data files during reinstallation
        if (file_exists(__DIR__ . '/inc/domain.php')) {
            unlink(__DIR__ . '/inc/domain.php');
        }
        $dataDir = __DIR__ . '/data/';
        if (is_dir($dataDir)) {
            $files = glob($dataDir . '*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
        
        // Create empty domain configuration file
        file_put_contents(__DIR__ . '/inc/domain.php', "<?php\n\$domains = [];\n?>");
        
        // Redirect to login page after successful installation
        header("Location: adm/login.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup - NoDB-DomainPark</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"] { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        button { background: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #005a87; }
        .error { color: red; margin-bottom: 15px; }
    </style>
</head>
<body>
    <h1>NoDB-DomainPark - Setup</h1>
    
    <?php if (!empty($error)): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <form method="POST">
        <div class="form-group">
            <label for="userid">Admin Username:</label>
            <input type="text" id="userid" name="userid" value="<?php echo htmlspecialchars($_POST['userid'] ?? ''); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="userdomain">Admin Domain (e.g., domain.com):</label>
            <input type="text" id="userdomain" name="userdomain" value="<?php echo htmlspecialchars($_POST['userdomain'] ?? $currentHost); ?>" required>
            <small>Enter the domain where admin panel will be accessible (without http:// or www)</small>
        </div>
        
        <button type="submit">Initialize Setup</button>
    </form>
    
    <p><strong>Note:</strong> Password will be automatically generated as <code>username + YYMMDD</code> (e.g., admin<?php echo date('ymd'); ?>)</p>
<div style="text-align:center;padding:15px;">NoDB-DomainPark &copy; 2026</div>
</body>
</html>
