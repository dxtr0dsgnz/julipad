<?php
require_once 'config.php';

if (!isset($_POST['admin_password']) || $_POST['admin_password'] !== ADMIN_PASSWORD) {
    header('Location: index.php?delete_error=' . urlencode('Incorrect admin password.'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pad_id = $_POST['pad_id'] ?? '';
    $password = $_POST['password'] ?? '';
    $encrypted = $_POST['encrypted'] ?? '0';
    $error = '';

    try {
        $pdo = new PDO('sqlite:' . DB_FILE);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $pdo->prepare('SELECT * FROM pads WHERE id = ?');
        $stmt->execute([$pad_id]);
        $pad = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$pad) {
            $error = 'Pad not found.';
        }

        if (!$error) {
            $del = $pdo->prepare('DELETE FROM pads WHERE id = ?');
            $del->execute([$pad_id]);
        }
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }

    if ($error) {
        header('Location: index.php?delete_error=' . urlencode($error));
        exit;
    } else {
        header('Location: index.php');
        exit;
    }
} else {
    header('Location: index.php');
    exit;
} 