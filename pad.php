<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

try {
    $pdo = new PDO('sqlite:' . DB_FILE);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->prepare("SELECT * FROM pads WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $pad = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$pad) {
        header("Location: index.php");
        exit;
    }

    // Check if password is required and if it's correct
    if (!empty($pad['password'])) {
        $password_verified = false;
        
        // Start session to check for stored password
        session_start();
        $session_password_key = 'pad_' . $pad['id'] . '_password';
        
        // Check if password was submitted or stored in session
        if (isset($_POST['password'])) {
            $password_verified = password_verify($_POST['password'], $pad['password']);
            if ($password_verified) {
                // Store the password in session for future use
                $_SESSION[$session_password_key] = $_POST['password'];
            }
        } elseif (isset($_SESSION[$session_password_key])) {
            $password_verified = password_verify($_SESSION[$session_password_key], $pad['password']);
        }
        
        if (!$password_verified) {
            // Show password form
            ?>
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Password Required - Julipad</title>
                <link rel="stylesheet" href="style.css">
            </head>
            <body>
                <div class="container">
                    <div class="password-form">
                        <h1>Password Required</h1>
                        <p>This pad is password protected.</p>
                        <?php if (isset($_POST['password'])): ?>
                            <p class="error">Incorrect password. Please try again.</p>
                        <?php endif; ?>
                        <form method="POST">
                            <div class="form-group">
                                <input type="password" name="password" placeholder="Enter password" required>
                            </div>
                            <button type="submit">Access Pad</button>
                        </form>
                        <a href="index.php" class="back-link">← Back to Pads</a>
                    </div>
                </div>
            </body>
            </html>
            <?php
            exit;
        }
    }

    // Decrypt the content for display
    $decrypted_content = decrypt($pad['content']);
    file_put_contents('debug.log', "Decrypted content: " . print_r($decrypted_content, true) . "\n", FILE_APPEND);
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pad['name']); ?> - Julipad</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="pad-header">
            <h1><?php echo htmlspecialchars($pad['name']); ?></h1>
            <a href="index.php" class="back-link">← Back to Pads</a>
        </div>
        
        <div class="pad-editor">
            <form action="save_pad.php" method="POST" onsubmit="document.getElementById('pad-content').value = document.getElementById('editor').innerHTML;">
                <input type="hidden" name="pad_id" value="<?php echo $pad['id']; ?>">
                <div id="toolbar" style="margin-bottom:8px;">
                    <button type="button" onclick="document.execCommand('bold',false,null);return false;"><b>B</b></button>
                    <button type="button" onclick="document.execCommand('italic',false,null);return false;"><i>I</i></button>
                    <button type="button" onclick="document.execCommand('underline',false,null);return false;"><u>U</u></button>
                    <button type="button" onclick="document.execCommand('insertUnorderedList',false,null);return false;">• List</button>
                    <button type="button" onclick="document.execCommand('insertOrderedList',false,null);return false;">1. List</button>
                </div>
                <div id="editor" contenteditable="true" style="min-height:300px; border:1px solid #ddd; border-radius:4px; padding:1rem; margin-bottom:1rem; background:#fff;">
<?php echo $decrypted_content; ?>
                </div>
                <textarea name="content" id="pad-content" style="display:none;"></textarea>
                <button type="submit">Save Changes</button>
            </form>
        </div>
    </div>
    <script>
    document.querySelector('.pad-editor form').addEventListener('submit', function(e) {
        document.getElementById('pad-content').value = document.getElementById('editor').innerHTML;
    });
    </script>
</body>
</html> 