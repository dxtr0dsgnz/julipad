<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Debug output
file_put_contents('debug.log', "Request received: " . print_r($_POST, true) . "\n", FILE_APPEND);

require_once 'config.php';

if (!isset($_POST['admin_password']) || $_POST['admin_password'] !== ADMIN_PASSWORD) {
    header('Location: index.php?admin_error=1');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['pad_name'])) {
    try {
        $pdo = new PDO('sqlite:' . DB_FILE);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $password = !empty($_POST['pad_password']) ? password_hash($_POST['pad_password'], PASSWORD_DEFAULT) : null;
        
        $stmt = $pdo->prepare("INSERT INTO pads (name, content, password) VALUES (?, '', ?)");
        $result = $stmt->execute([$_POST['pad_name'], $password]);
        
        if ($result) {
            $pad_id = $pdo->lastInsertId();
            
            // If a password was set, store it in the session
            if (!empty($_POST['pad_password'])) {
                $_SESSION['pad_' . $pad_id . '_password'] = $_POST['pad_password'];
            }
            
            file_put_contents('debug.log', "Pad created with ID: " . $pad_id . "\n", FILE_APPEND);
            
            // Ensure we're not outputting anything before the redirect
            if (!headers_sent()) {
                header("Location: pad.php?id=" . $pad_id);
                exit;
            } else {
                echo "<script>window.location.href='pad.php?id=" . $pad_id . "';</script>";
                exit;
            }
        } else {
            throw new Exception("Failed to create pad");
        }
    } catch(Exception $e) {
        file_put_contents('debug.log', "Error: " . $e->getMessage() . "\n", FILE_APPEND);
        die("Error creating pad: " . $e->getMessage());
    }
} else {
    file_put_contents('debug.log', "Invalid request: " . print_r($_SERVER['REQUEST_METHOD'], true) . "\n", FILE_APPEND);
    header("Location: index.php");
    exit;
}
?> 