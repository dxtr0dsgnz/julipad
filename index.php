<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Debug output
file_put_contents('debug.log', "Index page accessed\n", FILE_APPEND);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Julipad - Collaborative Text Editor</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="logo" style="text-align:center; margin-bottom: 20px;">
            <img src="julipad.png" alt="Julipad Logo" style="max-width: 180px; height: auto;" />
        </div>        
        <div class="create-pad">
            <h2>Create New Pad</h2>
            <?php if (isset($_GET['captcha_error'])): ?>
                <div class="error" style="margin-bottom:1rem;">Incorrect captcha answer. Please try again.</div>
            <?php endif; ?>
            <?php if (isset($_GET['admin_error'])): ?>
                <div class="error" style="margin-bottom:1rem;">Incorrect admin password. Please try again.</div>
            <?php endif; ?>
            <form action="create_pad.php" method="POST" onsubmit="console.log('Form submitted')">
                <div class="form-group">
                    <input type="text" name="pad_name" placeholder="Enter pad name" required>
                </div>
                <div class="form-group">
                    <input type="password" name="pad_password" placeholder="Enter password (optional)">
                    <small>Leave empty for no password protection</small>
                </div>
                <div class="form-group">
                    <input type="password" name="admin_password" placeholder="Admin Password (required)" required>
                    <small>Required for pad creation</small>
                </div>
                <button type="submit">Create Pad</button>
            </form>
        </div>

        <div class="pad-list">
            <h2>Available Pads</h2>
            <?php
            require_once 'config.php';
            
            try {
                $pdo = new PDO('sqlite:' . DB_FILE);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                $stmt = $pdo->query("SELECT * FROM pads ORDER BY created_at DESC");
                $pads = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (count($pads) > 0) {
                    echo "<ul>";
                    foreach ($pads as $pad) {
                        $lock_icon = !empty($pad['password']) ? '🔒 ' : '';
                        echo "<li><a href='pad.php?id=" . htmlspecialchars($pad['id']) . "'>" . 
                             $lock_icon . htmlspecialchars($pad['name']) . "</a> - Created: " . 
                             date('Y-m-d H:i', strtotime($pad['created_at'])) .
                             " <form action='delete_pad.php' method='POST' style='display:inline;'>\n" .
                             "<input type='hidden' name='pad_id' value='" . htmlspecialchars($pad['id']) . "'>\n" .
                             "<input type='password' name='admin_password' placeholder='Admin Password' required style='width:120px; margin-left:5px;'>\n" .
                             "<button type='submit' style='margin-left:10px;'>🗑️ Delete</button>\n" .
                             "</form>\n" .
                             "</li>";
                    }
                    echo "</ul>";
                } else {
                    echo "<p>No pads available. Create your first pad!</p>";
                }
            } catch(PDOException $e) {
                echo "<p>Error: " . $e->getMessage() . "</p>";
            }
            ?>
        </div>
    </div>
    <footer style="text-align:center; margin-top: 40px; color: #888; font-size: 0.95rem;">
        &copy; Julian Radünz 2025 &ndash; Version <?php echo defined('JULIPAD_VERSION') ? JULIPAD_VERSION : '1.0.0'; ?>
    </footer>
</body>
</html> 