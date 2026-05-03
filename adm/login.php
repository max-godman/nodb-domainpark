<?php
/**
 * NoDB-DomainPark
 * Author: max-godman (max_godman@foxmail.com)
 * GitHub: https://github.com/max-godman
 * 
 * Admin login page
 * Handles administrator authentication and session management
 */

// Start session for login attempts tracking
session_start();

// Check if installation is completed
if (!file_exists(__DIR__ . '/../inc/sys_admin.php')) {
    header("Location: ../setup.php");
    exit();
}

// Load admin configuration and SHA256 utility
require_once __DIR__ . '/../inc/sys_admin.php';
require_once __DIR__ . '/../inc/inc-sha.php';

// Get current host and clean it (remove www prefix)
$currentHost = $_SERVER['HTTP_HOST'] ?? '';
$currentHost = preg_replace('#^www\.#', '', strtolower($currentHost));
$currentHost = rtrim($currentHost, '/');

// Verify that login is accessed through the correct admin domain
$adminDomain = $admin_config['userdomain'];
if ($currentHost !== $adminDomain && !preg_match('/^.*\.' . preg_quote($adminDomain, '/') . '$/', $currentHost)) {
    // Access denied - return blank page
    exit();
}

// Handle logout request
if (isset($_GET['logout']) && $_GET['logout'] == '1') {
    // Clear authentication cookies
    setcookie('userid', '', time() - 3600, '/');
    setcookie('userint', '', time() - 3600, '/');
    // Set success message in session
    $_SESSION['logout_success'] = true;
    header("Location: login.php");
    exit();
}

// Initialize login attempts counter
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['last_attempt_day'] = date('Y-m-d');
}

// Reset daily login attempts counter
if ($_SESSION['last_attempt_day'] !== date('Y-m-d')) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['last_attempt_day'] = date('Y-m-d');
}

// Check if login is restricted due to too many failed attempts (5 per day limit)
$loginRestricted = ($_SESSION['login_attempts'] >= 5);

// Handle login form submission
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$loginRestricted) {
    $inputUserid = trim($_POST['userid'] ?? '');
    $inputPassword = trim($_POST['password'] ?? '');
    $rememberMe = isset($_POST['remember_me']);
    
    // Validate required fields
    if (empty($inputUserid) || empty($inputPassword)) {
        $error = "Username and password are required.";
    } else {
        // Verify username matches admin configuration
        if ($inputUserid === $admin_config['userid']) {
            // Hash input password and compare with stored hash
            $inputHash = sha256_hash($inputPassword);
            
            if ($inputHash === $admin_config['userpwd']) {
                // Login successful - update dynamic value for session security
                $newUserint = date('ymdHi');
                $admin_config['userint'] = $newUserint;
                
                // Update admin configuration file with new dynamic value
                $updatedConfig = "<?php\n";
                $updatedConfig .= "/**\n * Admin configuration\n * Auto-generated, do not modify manually\n */\n";
                $updatedConfig .= "\$admin_config = [\n";
                $updatedConfig .= "    'userid' => '" . $admin_config['userid'] . "',\n";
                $updatedConfig .= "    'userpwd' => '" . $admin_config['userpwd'] . "',\n";
                $updatedConfig .= "    'userint' => '$newUserint',\n";
                $updatedConfig .= "    'userdomain' => '" . $admin_config['userdomain'] . "'\n";
                $updatedConfig .= "];\n";
                $updatedConfig .= "?>";
                
                file_put_contents(__DIR__ . '/../inc/sys_admin.php', $updatedConfig);
                
                // Set authentication cookies
                $cookieExpire = $rememberMe ? time() + (30 * 24 * 3600) : 0; // 30 days or session-only
                setcookie('userid', $inputUserid, $cookieExpire, '/');
                setcookie('userint', $newUserint, $cookieExpire, '/');
                
                // Redirect to domain management page
                header("Location: dm.php");
                exit();
            }
        }
        
        // Login failed - increment attempt counter
        $_SESSION['login_attempts']++;
        $error = "Invalid username or password.";
    }
}

// Handle logout success message
if (isset($_SESSION['logout_success'])) {
    $successMessage = "Successfully logged out.";
    unset($_SESSION['logout_success']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - NoDB-DomainPark Admin</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 400px; margin: 50px auto; padding: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="password"] { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        input[type="checkbox"] { margin-right: 8px; }
        button { background: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; width: 100%; }
        button:hover { background: #005a87; }
        .error { color: red; margin-bottom: 15px; }
        .success { color: green; margin-bottom: 15px; }
        .restricted { color: red; margin-bottom: 15px; }
    </style>
</head>
<body>
    <h2>Admin Login</h2>
    
    <?php if (!empty($successMessage)): ?>
        <div class="success"><?php echo htmlspecialchars($successMessage); ?></div>
    <?php endif; ?>
    
    <?php if ($loginRestricted): ?>
        <div class="restricted">Too many failed attempts. Login is restricted for today.</div>
    <?php else: ?>
        <?php if (!empty($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="userid">Username:</label>
                <input type="text" id="userid" name="userid" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
                <small>Password format: username + YYMMDD (e.g., admin260501)</small>
            </div>
            
            <div class="form-group">
                <input type="checkbox" id="remember_me" name="remember_me">
                <label for="remember_me" style="display: inline;">Remember me (30 days)</label>
            </div>
            
            <button type="submit">Login</button>
        </form>
    <?php endif; ?><div style="text-align:center;padding:15px;">NoDB-DomainPark &copy; 2026</div>
</body>
</html>
