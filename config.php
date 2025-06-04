<?php
// SQLite database configuration
define('DB_FILE', 'simplepad.db');

// Encryption configuration
define('ENCRYPTION_KEY', bin2hex(random_bytes(32))); // Generate a random 32-byte key
define('ENCRYPTION_METHOD', 'aes-256-cbc');

// App version
const JULIPAD_VERSION = '0.5.1';

// Admin password for pad creation and deletion
const ADMIN_PASSWORD = 'CHANGEME';

// Create database and table if they don't exist
try {
    $pdo = new PDO('sqlite:' . DB_FILE);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create pads table if it doesn't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS pads (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        content TEXT,
        password TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Create trigger for updated_at if it doesn't exist
    $pdo->exec("CREATE TRIGGER IF NOT EXISTS update_pad_timestamp 
                AFTER UPDATE ON pads
                BEGIN
                    UPDATE pads SET updated_at = CURRENT_TIMESTAMP 
                    WHERE id = NEW.id;
                END");
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}

// Encryption functions
function encrypt($data) {
    if (empty($data)) return '';
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length(ENCRYPTION_METHOD));
    $encrypted = openssl_encrypt($data, ENCRYPTION_METHOD, ENCRYPTION_KEY, 0, $iv);
    return base64_encode($iv . $encrypted);
}

function decrypt($data) {
    if (empty($data)) return '';
    $data = base64_decode($data);
    $iv_length = openssl_cipher_iv_length(ENCRYPTION_METHOD);
    $iv = substr($data, 0, $iv_length);
    $encrypted = substr($data, $iv_length);
    return openssl_decrypt($encrypted, ENCRYPTION_METHOD, ENCRYPTION_KEY, 0, $iv);
}
?> 
