<?php
/**
 * NoDB-DomainPark
 * Author: max-godman (max_godman@foxmail.com)
 * GitHub: https://github.com/max-godman
 */

session_start();

if (!file_exists(__DIR__ . '/../inc/sys_admin.php')) {
    header("Location: ../setup.php");
    exit();
}

require_once __DIR__ . '/../inc/sys_admin.php';
require_once __DIR__ . '/../inc/inc-sha.php';

function validateLogin() {
    global $admin_config;
    
    if (!isset($_COOKIE['userid']) || !isset($_COOKIE['userint'])) {
        return false;
    }
    
    if ($_COOKIE['userid'] !== $admin_config['userid']) {
        return false;
    }
    
    if ($_COOKIE['userint'] !== $admin_config['userint']) {
        return false;
    }
    
    return true;
}

if (!validateLogin()) {
    header("Location: login.php");
    exit();
}

$showSettings = isset($_GET['set']) && $_GET['set'] === 'on';
$showLinks = isset($_GET['links']) && $_GET['links'] === 'on';

if (!$showSettings && !$showLinks) {
    $showSettings = true;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    $oldPassword = trim($_POST['old_password'] ?? '');
    $newPassword = trim($_POST['new_password'] ?? '');
    $newUserdomain = trim($_POST['new_userdomain'] ?? '');
    
    if (empty($oldPassword)) {
        $message = "Old password is required.";
    } else {
        if (sha256_hash($oldPassword) !== $admin_config['userpwd']) {
            $message = "Invalid old password.";
        } else {
            if (!empty($newPassword)) {
                $newUserpwd = sha256_hash($newPassword);
            } else {
                $newUserpwd = $admin_config['userpwd'];
            }
            
            $newUserint = date('ymdHi');
            
            $newUserdomain = preg_replace('#^https?://(www\.)?#', '', strtolower($newUserdomain));
            $newUserdomain = rtrim($newUserdomain, '/');
            
            $updatedConfig = "<?php\n";
            $updatedConfig .= "/**\n * Admin configuration\n * Auto-generated, do not modify manually\n */\n";
            $updatedConfig .= "\$admin_config = [\n";
            $updatedConfig .= "    'userid' => '" . $admin_config['userid'] . "',\n";
            $updatedConfig .= "    'userpwd' => '$newUserpwd',\n";
            $updatedConfig .= "    'userint' => '$newUserint',\n";
            $updatedConfig .= "    'userdomain' => '$newUserdomain'\n";
            $updatedConfig .= "];\n";
            $updatedConfig .= "?>";
            
            file_put_contents(__DIR__ . '/../inc/sys_admin.php', $updatedConfig);
            
            setcookie('userid', '', time() - 3600, '/');
            setcookie('userint', '', time() - 3600, '/');
            
            header("Location: https://$newUserdomain/adm/login.php");
            exit();
        }
    }
}

$linkContent = "<!-- txt -->";
if (file_exists(__DIR__ . '/../inc/link.php')) {
    ob_start();
    include __DIR__ . '/../inc/link.php';
    $linkContent = ob_get_clean();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_link') {
    $linkText = trim($_POST['link_text'] ?? '');
    $linkUrl = trim($_POST['link_url'] ?? '');
    
    if (empty($linkText) || empty($linkUrl)) {
        $message = "Link text and URL are required.";
    } else {
        $newLink = '<li><a href="' . htmlspecialchars($linkUrl) . '" target="_blank">' . htmlspecialchars($linkText) . '</a></li>';
        
        $existingContent = '';
        if ($linkContent !== "<!-- txt -->") {
            $existingContent = $linkContent;
        }
        
        $updatedContent = $existingContent . "\n" . $newLink;
        $linkContent = $updatedContent;
        
        file_put_contents(__DIR__ . '/../inc/link.php', "<?php echo <<<'EOF'\n" . trim($updatedContent) . "\nEOF;\n?>");
        
        $message = "Link added successfully.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_links') {
    $newLinks = trim($_POST['links'] ?? '');
    
    if (empty($newLinks)) {
        $linkContent = "<!-- txt -->";
        file_put_contents(__DIR__ . '/../inc/link.php', "<?php echo \"<!-- txt -->\"; ?>");
    } else {
        $linkContent = $newLinks;
        file_put_contents(__DIR__ . '/../inc/link.php', "<?php echo <<<'EOF'\n$newLinks\nEOF;\n?>");
    }
    
    $message = "Links updated successfully.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $showSettings ? 'Settings' : 'Friendship Links'; ?> - Domain Parking Admin</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="password"], input[type="text"], textarea { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        button { background: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #005a87; }
        .message { padding: 10px; margin-bottom: 20px; border-radius: 4px; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .section { margin-bottom: 30px; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
        h2 { margin-top: 0; }
    </style>
</head>
<body>
    <div class="container">
        <nav style="margin-bottom: 20px; padding: 10px; background-color: #f8f9fa; border-radius: 5px;">
            <a href="dm.php" style="margin-right: 15px; text-decoration: none; color: #6c757d;">Domain Management</a>
            <a href="check.php?set=on" style="margin-right: 15px; text-decoration: none; color: <?php echo $showSettings ? '#007cba; font-weight: bold;' : '#6c757d;'; ?>">Settings</a>
            <a href="check.php?links=on" style="margin-right: 15px; text-decoration: none; color: <?php echo $showLinks ? '#007cba; font-weight: bold;' : '#6c757d;'; ?>">Friendship Links</a>
            <a href="login.php?logout=1" style="text-decoration: none; color: #dc3545;">Logout</a>
        </nav>
        <h1><?php echo $showSettings ? 'Settings' : 'Friendship Links'; ?></h1>
        
        <?php if (!empty($message)): ?>
            <div class="message <?php echo strpos($message, 'successfully') !== false ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($showSettings): ?>
        <div class="section">
            <h2>Password & Domain Settings</h2>
            <p><strong>Note:</strong> Leave New Password empty to keep current password unchanged. Changing the admin domain will require you to log in again at the new domain.</p>
            
            <form method="POST">
                <input type="hidden" name="action" value="change_password">
                <div class="form-group">
                    <label for="old_password">Current Password:</label>
                    <input type="password" id="old_password" name="old_password" placeholder="Enter current password" required>
                </div>
                <div class="form-group">
                    <label for="new_password">New Password (optional):</label>
                    <input type="password" id="new_password" name="new_password" placeholder="Leave empty to keep current password">
                </div>
                <div class="form-group">
                    <label for="new_userdomain">New Admin Domain:</label>
                    <input type="text" id="new_userdomain" name="new_userdomain" value="<?php echo htmlspecialchars($admin_config['userdomain']); ?>" required>
                    <small>Enter the new domain for admin access (without http:// or www)</small>
                </div>
                <button type="submit">Update Settings</button>
            </form>
        </div>
        <?php endif; ?>
        
        <?php if ($showLinks): ?>
        <div class="section">
            <h2>Friendship Links</h2>
            <p>Add or edit friendship links that will appear on all parked domains.</p>
            
            <h3>Add Single Link</h3>
            <form method="POST" style="margin-bottom: 20px;">
                <input type="hidden" name="action" value="add_link">
                <div class="form-group">
                    <label for="link_text">Link Text:</label>
                    <input type="text" id="link_text" name="link_text" placeholder="e.g., Example Site">
                </div>
                <div class="form-group">
                    <label for="link_url">Link URL:</label>
                    <input type="text" id="link_url" name="link_url" placeholder="e.g., https://example.com">
                </div>
                <button type="submit">Add Link</button>
            </form>
            
            <h3>Edit All Links</h3>
            <form method="POST">
                <input type="hidden" name="action" value="update_links">
                <div class="form-group">
                    <label for="links">Links HTML:</label>
                    <textarea id="links" name="links" rows="10" placeholder="<li><a href=&quot;https://example.com&quot; target=&quot;_blank&quot;>Example Link</a></li>"><?php 
                    if ($linkContent !== "<!-- txt -->") {
                        echo htmlspecialchars($linkContent);
                    }
                    ?></textarea>
                    <small>Enter valid HTML links. Submit empty to reset to default.</small>
                </div>
                <button type="submit">Update Links</button>
            </form>
        </div>
        <?php endif; ?>
    </div><div style="text-align:center;padding:15px;">NoDB-DomainPark &copy; 2026</div>
</body>
</html>
