<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pad_id']) && isset($_POST['content'])) {
    try {
        $pdo = new PDO('sqlite:' . DB_FILE);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Encrypt the content before saving
        $encrypted_content = encrypt($_POST['content']);
        
        $stmt = $pdo->prepare("UPDATE pads SET content = ? WHERE id = ?");
        $stmt->execute([$encrypted_content, $_POST['pad_id']]);
        
        header("Location: pad.php?id=" . $_POST['pad_id']);
        exit;
    } catch(PDOException $e) {
        die("Error: " . $e->getMessage());
    }
} else {
    header("Location: index.php");
    exit;
}
?> 