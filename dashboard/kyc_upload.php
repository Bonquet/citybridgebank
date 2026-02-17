<?php
require_once '../includes/config.php';
if (!Security::isLoggedIn()) {
    redirectWithMessage('../public/login.php', 'Please login first.', 'danger');
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    redirectWithMessage('security.php', 'Invalid upload request.', 'danger');
}
if (!isset($_FILES['kyc_document']) || $_FILES['kyc_document']['error'] !== UPLOAD_ERR_OK) {
    redirectWithMessage('security.php', 'Please upload a valid document.', 'danger');
}

$file = $_FILES['kyc_document'];
$allowedMime = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'application/pdf' => 'pdf'];
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($file['tmp_name']);
if (!isset($allowedMime[$mime]) || $file['size'] > 5 * 1024 * 1024) {
    redirectWithMessage('security.php', 'Unsupported file type or size too large.', 'danger');
}

$uploadDir = __DIR__ . '/../uploads/kyc_docs';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}
$filename = 'kyc_' . $_SESSION['user_id'] . '_' . time() . '.' . $allowedMime[$mime];
$dest = $uploadDir . '/' . $filename;
if (!move_uploaded_file($file['tmp_name'], $dest)) {
    redirectWithMessage('security.php', 'Failed to save document.', 'danger');
}

try {
    $db = Database::getInstance()->getConnection();
    $db->beginTransaction();
    $stmt = $db->prepare("UPDATE users SET kyc_document = ?, kyc_status = 'pending', kyc_review_reason = NULL, kyc_reviewed_by = NULL, kyc_reviewed_at = NULL WHERE user_id = ?");
    $stmt->execute([$filename, $_SESSION['user_id']]);

    $log = $db->prepare("INSERT INTO kyc_logs (user_id, admin_id, action, reason) VALUES (?, NULL, 'upload', NULL)");
    $log->execute([$_SESSION['user_id']]);

    Security::logAudit('kyc_upload', 'User uploaded KYC document', $_SESSION['user_id']);
    $db->commit();
    redirectWithMessage('security.php', 'KYC document uploaded successfully. Status set to pending review.', 'success');
} catch (Throwable $e) {
    if (isset($db) && $db->inTransaction()) { $db->rollBack(); }
    error_log('kyc_upload error: ' . $e->getMessage());
    redirectWithMessage('security.php', 'Failed to submit KYC document.', 'danger');
}
