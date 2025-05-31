<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

include_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode(array("error" => "Method not allowed"));
    exit();
}

try {
    $database = new Database();
    $db = $database->getConnection();

    $input = json_decode(file_get_contents('php://input'), true);
    if (!isset($input['id']) || !is_numeric($input['id'])) {
        throw new Exception("Invalid photo ID");
    }

    $photo_id = $input['id'];

    // Get filename before deleting from database
    $query = "SELECT filename FROM photos WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $photo_id);
    $stmt->execute();

    $photo = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$photo) {
        throw new Exception("Photo not found");
    }

    // Delete from database
    $query = "DELETE FROM photos WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $photo_id);

    if (!$stmt->execute()) {
        throw new Exception("Failed to delete photo from database");
    }

    // Delete file from server
    $file_path = "../uploads/" . $photo['filename'];
    if (file_exists($file_path)) {
        if (!unlink($file_path)) {
            throw new Exception("Failed to delete photo file from server");
        }
    }

    echo json_encode(array(
        "success" => true,
        "message" => "Photo deleted successfully"
    ));

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(array("error" => $e->getMessage()));
}
?>