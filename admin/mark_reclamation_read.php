<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    exit();
}
// h
require_once '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reclamation_id = $_POST['reclamation_id'] ?? null;

    if ($reclamation_id) {
        // Check if lu_par_admin column exists first
        $checkCol = $conn->query("SHOW COLUMNS FROM reclamations LIKE 'lu_par_admin'");
        if ($checkCol && $checkCol->num_rows > 0) {
            // Mark as read by admin (lu_par_admin = 1)
            $stmt = $conn->prepare("UPDATE reclamations SET lu_par_admin = 1 WHERE reclamation_id = ?");
            $stmt->bind_param("i", $reclamation_id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => $conn->error]);
            }
            
            $stmt->close();
        } else {
            echo json_encode(['success' => true, 'message' => 'Column lu_par_admin does not exist']);
        }
    }
}

$conn->close();
?>
