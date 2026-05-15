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

// ── Fetch all messages ordered by newest first ────────────────────────────────
try {
    $db   = getDB();
    $stmt = $db->query('SELECT id, name, email, phone, subject, message, created_at FROM contacts ORDER BY created_at DESC');
    $rows = $stmt->fetchAll();

    echo json_encode(['status' => 'success', 'data' => $rows]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to fetch messages.']);
}
