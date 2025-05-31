<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Debug logging
error_log("Upload request received");
error_log("POST data: " . print_r($_POST, true));
error_log("FILES data: " . print_r($_FILES, true));

include_once '../config/database.php';

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(array("error" => "Method not allowed"));
    exit();
}

try {
    $database = new Database();
    $db = $database->getConnection();

    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (empty($title)) {
        throw new Exception("Title is required");
    }

    // Handle file upload
    if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("No file uploaded or upload error occurred");
    }

    $target_dir = "../uploads/";
    if (!file_exists($target_dir)) {
        if (!mkdir($target_dir, 0777, true)) {
            throw new Exception("Failed to create upload directory");
        }
    }

    if (!is_writable($target_dir)) {
        throw new Exception("Upload directory is not writable");
    }

    $file = $_FILES['photo'];
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    // Validate file type
    $allowed_types = array('jpg', 'jpeg', 'png', 'gif');
    if (!in_array($file_extension, $allowed_types)) {
        throw new Exception("Invalid file type. Allowed types: " . implode(', ', $allowed_types));
    }

    // Generate unique filename
    $filename = uniqid() . '_' . time() . '.' . $file_extension;
    $target_file = $target_dir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $target_file)) {
        throw new Exception("Failed to move uploaded file");
    }

    // Save to database
    $query = "INSERT INTO photos (title, description, filename) VALUES (:title, :description, :filename)";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':filename', $filename);

    if (!$stmt->execute()) {
        // If database insert fails, remove the uploaded file
        unlink($target_file);
        throw new Exception("Failed to save to database");
    }

    echo json_encode(array(
        "success" => true,
        "message" => "Photo uploaded successfully",
        "id" => $db->lastInsertId()
    ));

} catch (Exception $e) {
    error_log("Upload error: " . $e->getMessage());
    http_response_code(400);
    if (isset($target_file) && file_exists($target_file)) {
        unlink($target_file);
    }
    echo json_encode(array("error" => $e->getMessage()));
}
?>