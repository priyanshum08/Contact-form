<?php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/config.php';

// ── Auth check ────────────────────────────────────────────────────────────────
if (empty($_SESSION['admin_logged_in'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized.']);
    exit;
}

// ── Only allow POST ───────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed.']);
    exit;
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid message ID.']);
    exit;
}

// ── Delete record ─────────────────────────────────────────────────────────────
try {
    $db   = getDB();
    $stmt = $db->prepare('DELETE FROM contacts WHERE id = :id');
    $stmt->execute([':id' => $id]);

    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Message not found.']);
    } else {
        echo json_encode(['status' => 'success', 'message' => 'Message deleted.']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to delete message.']);
}
