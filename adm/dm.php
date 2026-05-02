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

if (file_exists(__DIR__ . '/../inc/domain.php')) {
    require_once __DIR__ . '/../inc/domain.php';
} else {
    $domains = [];
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $domain = trim(strtolower($_POST['domain'] ?? ''));
        if (!empty($domain)) {
            $domain = preg_replace('#^https?://(www\.)?#', '', $domain);
            $domain = rtrim($domain, '/');
            
            $exists = false;
            foreach ($domains as &$d) {
                if ($d['domain'] === $domain) {
                    $exists = true;
                    break;
                }
            }
            
            if (!$exists) {
                $title = !empty($_POST['title']) ? trim($_POST['title']) : $domain;
                $about = trim($_POST['about'] ?? '');
                $url = trim($_POST['url'] ?? '');
                $userdata = 10;
                
                $newDomain = [
                    'domain' => $domain,
                    'type' => 1,
                    'title' => $title,
                    'about' => $about,
                    'url' => $url,
                    'userdata' => $userdata
                ];
                
                $domains[] = $newDomain;
                
                $dataFile = __DIR__ . "/../data/{$domain}";
                if (!file_exists($dataFile)) {
                    $domainData = [
                        'click' => 0,
                        'useragent' => []
                    ];
                    file_put_contents($dataFile, json_encode($domainData, JSON_PRETTY_PRINT));
                }
                
                $message = "Domain added successfully.";
            } else {
                $message = "Domain already exists.";
            }
        } else {
            $message = "Domain is required.";
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'update') {
        $index = intval($_POST['index'] ?? -1);
        if ($index >= 0 && $index < count($domains)) {
            $domain = trim(strtolower($_POST['domain'] ?? ''));
            if (!empty($domain)) {
                $domain = preg_replace('#^https?://(www\.)?#', '', $domain);
                $domain = rtrim($domain, '/');
                
                $type = intval($_POST['type'] ?? $domains[$index]['type']);
                $title = !empty($_POST['title']) ? trim($_POST['title']) : $domain;
                $about = trim($_POST['about'] ?? '');
                $url = trim($_POST['url'] ?? '');
                $userdata = !empty($_POST['userdata']) ? intval($_POST['userdata']) : 10;
                
                $isValid = true;
                $errorMessage = '';
                
                if ($type == 4 && empty($about)) {
                    $errorMessage = "About text is required for display mode.";
                    $isValid = false;
                }
                
                if (($type == 2 || $type == 3) && empty($url)) {
                    $errorMessage = "URL is required for redirect mode.";
                    $isValid = false;
                }
                
                if ($isValid) {
                    $oldDomain = $domains[$index]['domain'];
                    if ($oldDomain !== $domain) {
                        $oldDataFile = __DIR__ . "/../data/{$oldDomain}";
                        $newDataFile = __DIR__ . "/../data/{$domain}";
                        if (file_exists($oldDataFile)) {
                            rename($oldDataFile, $newDataFile);
                        }
                    }
                    
                    $domains[$index] = [
                        'domain' => $domain,
                        'type' => $type,
                        'title' => $title,
                        'about' => $about,
                        'url' => $url,
                        'userdata' => $userdata
                    ];
                    
                    $message = "Domain updated successfully.";
                } else {
                    $message = $errorMessage;
                }
            } else {
                $message = "Domain is required.";
            }
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $index = intval($_POST['index'] ?? -1);
        if ($index >= 0 && $index < count($domains)) {
            if ($domains[$index]['type'] == 0) {
                $domainToDelete = $domains[$index]['domain'];
                unset($domains[$index]);
                $domains = array_values($domains);
                
                $dataFile = __DIR__ . "/../data/{$domainToDelete}";
                if (file_exists($dataFile)) {
                    unlink($dataFile);
                }
                
                $message = "Domain deleted successfully.";
            } else {
                $message = "Only domains with status 'Disabled' can be deleted.";
            }
        }
    }
    
    $domainConfig = "<?php\n";
    $domainConfig .= "/**\n * Domain configuration\n * Automatically generated\n */\n";
    $domainConfig .= "\$domains = " . var_export($domains, true) . ";\n";
    $domainConfig .= "?>";
    file_put_contents(__DIR__ . '/../inc/domain.php', $domainConfig);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'get_visits') {
    $domain = trim($_POST['domain'] ?? '');
    $dataFile = __DIR__ . "/../data/{$domain}";
    
    header('Content-Type: application/json');
    
    if (!file_exists($dataFile)) {
        echo json_encode(['error' => 'No data file found']);
        exit();
    }
    
    $domainData = json_decode(file_get_contents($dataFile), true);
    if (!is_array($domainData) || empty($domainData['useragent'])) {
        echo json_encode(['error' => 'No visit records']);
        exit();
    }
    
    echo json_encode(['records' => $domainData['useragent']]);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Domain Management - Admin Panel</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], textarea, select { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        button { background: #007cba; color: white; padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; margin-right: 10px; }
        button:hover { background: #005a87; }
        .delete-btn { background: #dc3545; }
        .delete-btn:hover { background: #c82333; }
        .message { padding: 10px; margin-bottom: 20px; border-radius: 4px; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f8f9fa; }
        .status-0 { color: #6c757d; }
        .status-1 { color: #ffc107; }
        .status-2 { color: #28a745; }
        .status-3 { color: #17a2b8; }
        .status-4 { color: #007bff; }
        .click-count { cursor: pointer; text-decoration: underline; }
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); }
        .modal-content { background-color: white; margin: 5% auto; padding: 20px; border-radius: 5px; width: 80%; max-width: 600px; max-height: 70vh; overflow-y: auto; }
        .close { float: right; font-size: 24px; font-weight: bold; cursor: pointer; }
        .status-pending-highlight { border: 2px solid #ffc107; background-color: #fff8e1; }
        .pending-prompt { color: #e65100; font-size: 12px; margin-top: 4px; display: none; }
    </style>
</head>
<body>
    <div class="container">
        <nav style="margin-bottom: 20px; padding: 10px; background-color: #f8f9fa; border-radius: 5px;">
            <a href="dm.php" style="margin-right: 15px; text-decoration: none; color: #007cba; font-weight: bold;">Domain Management</a>
            <a href="check.php?set=on" style="margin-right: 15px; text-decoration: none; color: #6c757d;">Settings</a>
            <a href="check.php?links=on" style="margin-right: 15px; text-decoration: none; color: #6c757d;">Friendship Links</a>
            <a href="login.php?logout=1" style="text-decoration: none; color: #dc3545;">Logout</a>
        </nav>
        <h1>Domain Management</h1>
        
        <?php if (!empty($message)): ?>
            <div class="message <?php echo strpos($message, 'successfully') !== false ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <h2>Add New Domain</h2>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label for="add_domain">Domain:</label>
                <input type="text" id="add_domain" name="domain" placeholder="example.com" required>
            </div>
            <div class="form-group">
                <label for="add_title">Title:</label>
                <input type="text" id="add_title" name="title" placeholder="Leave empty to use domain as title">
            </div>
            <div class="form-group">
                <label for="add_about">About (for display mode):</label>
                <textarea id="add_about" name="about" rows="3"></textarea>
            </div>
            <div class="form-group">
                <label for="add_url">URL (for redirect mode):</label>
                <input type="text" id="add_url" name="url" placeholder="https://example.com">
            </div>
            <button type="submit">Add Domain</button>
        </form>
        
        <h2>Existing Domains <button onclick="location.reload();" style="margin-left: 10px; font-size: 14px; padding: 5px 10px;">Refresh</button></h2>
        <?php if (empty($domains)): ?>
            <p>No domains configured yet.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Domain</th>
                        <th>Status</th>
                        <th>Title</th>
                        <th>Clicks</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($domains as $index => $domain): ?>
                        <tr data-about="<?php echo htmlspecialchars($domain['about'] ?? ''); ?>" data-url="<?php echo htmlspecialchars($domain['url'] ?? ''); ?>" data-userdata="<?php echo htmlspecialchars($domain['userdata'] ?? '10'); ?>">
                            <td><a href="https://<?php echo htmlspecialchars($domain['domain']); ?>" target="_blank"><?php echo htmlspecialchars($domain['domain']); ?></a></td>
                            <td>
                                <?php 
                                $statusText = ['Disabled', 'Pending', 'Redirect 301', 'Redirect 302', 'Display'];
                                $statusClass = "status-{$domain['type']}";
                                echo "<span class='{$statusClass}'>{$statusText[$domain['type']]}</span>";
                                ?>
                            </td>
                            <td><?php echo htmlspecialchars($domain['title']); ?></td>
                            <td>
                                <?php 
                                $dataFile = __DIR__ . "/../data/{$domain['domain']}";
                                $clickCount = 0;
                                if (file_exists($dataFile)) {
                                    $domainData = json_decode(file_get_contents($dataFile), true);
                                    $clickCount = $domainData['click'] ?? 0;
                                }
                                ?>
                                <span class="click-count" onclick="showVisits('<?php echo htmlspecialchars($domain['domain']); ?>')">
                                    <?php echo $clickCount; ?>
                                </span>
                            </td>
                            <td>
                                <button onclick="editDomain(<?php echo $index; ?>)">Edit</button>
                                <?php if ($domain['type'] == 0): ?>
                                    <button class="delete-btn" onclick="deleteDomain(<?php echo $index; ?>)">Delete</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        
        <div id="editForm" style="display: none; margin-top: 30px; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">
            <h3>Edit Domain</h3>
            <form method="POST" id="editDomainForm">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="index" id="editIndex">
                <div class="form-group">
                    <label for="edit_domain">Domain:</label>
                    <input type="text" id="edit_domain" name="domain" required>
                </div>
                <div class="form-group">
                    <label for="edit_type">Status:</label>
                    <select id="edit_type" name="type" required>
                        <option value="0">Disabled</option>
                        <option value="1">Pending</option>
                        <option value="2">Redirect 301</option>
                        <option value="3">Redirect 302</option>
                        <option value="4">Display</option>
                    </select>
                    <span id="pendingPrompt" class="pending-prompt">* This domain is pending review. Please change the status to activate it.</span>
                </div>
                <div class="form-group">
                    <label for="edit_title">Title:</label>
                    <input type="text" id="edit_title" name="title">
                </div>
                <div class="form-group">
                    <label for="edit_about">About:</label>
                    <textarea id="edit_about" name="about" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label for="edit_url">URL:</label>
                    <input type="text" id="edit_url" name="url">
                </div>
                <div class="form-group">
                    <label for="edit_userdata">User Data (records to keep):</label>
                    <input type="number" id="edit_userdata" name="userdata" min="1" value="10">
                </div>
                <button type="submit">Update Domain</button>
                <button type="button" onclick="cancelEdit()">Cancel</button>
            </form>
        </div>
        
        <form method="POST" id="deleteForm" style="display: none;">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="index" id="deleteIndex">
        </form>
        
        <div id="visitsModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeVisitsModal()">&times;</span>
                <h3 id="visitsDomain"></h3>
                <div id="visitsContent"></div>
            </div>
        </div>
    </div>
    
    <script>
        function editDomain(index) {
            const row = document.querySelectorAll('tbody tr')[index];
            const domain = row.cells[0].textContent;
            const statusText = row.cells[1].textContent;
            const title = row.cells[2].textContent;
            
            const about = row.getAttribute('data-about') || '';
            const url = row.getAttribute('data-url') || '';
            const userdata = row.getAttribute('data-userdata') || '10';
            
            document.getElementById('editIndex').value = index;
            document.getElementById('edit_domain').value = domain;
            document.getElementById('edit_title').value = title;
            document.getElementById('edit_about').value = about;
            document.getElementById('edit_url').value = url;
            document.getElementById('edit_userdata').value = userdata;
            
            const statusSelect = document.getElementById('edit_type');
            const statusMap = {'Disabled': 0, 'Pending': 1, 'Redirect 301': 2, 'Redirect 302': 3, 'Display': 4};
            statusSelect.value = statusMap[statusText.trim()];
            
            const pendingPrompt = document.getElementById('pendingPrompt');
            if (statusSelect.value == '1') {
                statusSelect.classList.add('status-pending-highlight');
                pendingPrompt.style.display = 'block';
            } else {
                statusSelect.classList.remove('status-pending-highlight');
                pendingPrompt.style.display = 'none';
            }
            
            statusSelect.onchange = function() {
                statusSelect.classList.remove('status-pending-highlight');
                pendingPrompt.style.display = 'none';
            };
            
            document.getElementById('editForm').style.display = 'block';
            document.getElementById('editForm').scrollIntoView({behavior: 'smooth'});
        }
        
        function cancelEdit() {
            document.getElementById('editForm').style.display = 'none';
        }
        
        function deleteDomain(index) {
            if (confirm('Are you sure you want to delete this domain? This action cannot be undone.')) {
                document.getElementById('deleteIndex').value = index;
                document.getElementById('deleteForm').submit();
            }
        }
        
        function showVisits(domain) {
            document.getElementById('visitsDomain').textContent = 'Visits for: ' + domain;
            document.getElementById('visitsContent').innerHTML = '<p>Loading...</p>';
            document.getElementById('visitsModal').style.display = 'block';
            
            fetchVisits(domain);
        }
        
        function fetchVisits(domain) {
            const formData = new FormData();
            formData.append('action', 'get_visits');
            formData.append('domain', domain);
            
            fetch('dm.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    document.getElementById('visitsContent').innerHTML = '<p>' + data.error + '</p>';
                } else if (data.records && data.records.length > 0) {
                    let html = '<table style="width:100%; border-collapse: collapse;">';
                    html += '<tr><th style="padding:8px; border-bottom:2px solid #ddd; text-align:left;">Time</th><th style="padding:8px; border-bottom:2px solid #ddd; text-align:left;">IP</th><th style="padding:8px; border-bottom:2px solid #ddd; text-align:left;">Country</th><th style="padding:8px; border-bottom:2px solid #ddd; text-align:left;">User Agent</th></tr>';
                    
                    data.records.forEach(record => {
                        html += '<tr>';
                        html += '<td style="padding:8px; border-bottom:1px solid #ddd;">' + record.time + '</td>';
                        html += '<td style="padding:8px; border-bottom:1px solid #ddd;">' + record.ip + '</td>';
                        html += '<td style="padding:8px; border-bottom:1px solid #ddd;">' + record.usercountry + '</td>';
                        html += '<td style="padding:8px; border-bottom:1px solid #ddd; word-break: break-all;">' + record.useragent + '</td>';
                        html += '</tr>';
                    });
                    
                    html += '</table>';
                    document.getElementById('visitsContent').innerHTML = html;
                } else {
                    document.getElementById('visitsContent').innerHTML = '<p>No visit records found.</p>';
                }
            })
            .catch(error => {
                document.getElementById('visitsContent').innerHTML = '<p>Error loading records: ' + error.message + '</p>';
            });
        }
        
        function closeVisitsModal() {
            document.getElementById('visitsModal').style.display = 'none';
        }
        
        window.onclick = function(event) {
            const modal = document.getElementById('visitsModal');
            if (event.target === modal) {
                closeVisitsModal();
            }
        }
    </script>
<div style="text-align:center;padding:15px;">NoDB-DomainPark &copy; 2026</div>
</body>
</html>
