<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$query = "SELECT * FROM photos ORDER BY upload_date DESC";
$stmt = $db->prepare($query);
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

echo json_encode($photos);