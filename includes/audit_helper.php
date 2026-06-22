<?php
/**
 * Catat aktivitas manual ke audit_logs (dipanggil dari PHP, bukan trigger),
 * dipakai khusus untuk aksi Staff yang trigger SQL tidak bisa tangkap
 * (karena trigger tidak tahu siapa yang login di PHP).
 */
function logAudit($conn, $user_id, $action, $table_name, $description) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $sql = "INSERT INTO audit_logs (user_id, `action`, table_name, description, ip_address) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log('Audit log prepare failed: ' . $conn->error . ' SQL: ' . $sql);
        return false;
    }
    $stmt->bind_param('issss', $user_id, $action, $table_name, $description, $ip);
    if (!$stmt->execute()) {
        error_log('Audit log execute failed: ' . $stmt->error);
        $stmt->close();
        return false;
    }
    $stmt->close();
}