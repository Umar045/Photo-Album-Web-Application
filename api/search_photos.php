<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

include_once '../config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    $search_term = $_GET['q'] ?? '';
    $search_term = trim($search_term);

    if (empty($search_term)) {
        // Return all photos if no search term
        $query = "SELECT * FROM photos ORDER BY upload_date DESC";
        $stmt = $db->prepare($query);
    } else {
        $query = "SELECT * FROM photos WHERE title LIKE :search OR description LIKE :search ORDER BY upload_date DESC";
        $stmt = $db->prepare($query);
        $search_param = '%' . $search_term . '%';
        $stmt->bindParam(':search', $search_param);
    }

    $stmt->execute();

    $photos = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $photo = array(
            "id" => $row['id'],
            "title" => $row['title'],
            "description" => $row['description'],
            "filename" => "uploads/" . $row['filename'],
            "upload_date" => $row['upload_date']
        );
        array_push($photos, $photo);
    }

    echo json_encode(array(
        "success" => true,
        "photos" => $photos
    ));

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(array("error" => $e->getMessage()));
}
?>