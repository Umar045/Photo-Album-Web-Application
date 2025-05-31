<?php
header('Content-Type: application/json');

$upload_dir = __DIR__ . '/uploads';
$result = array();

// Check if directory exists
if (!file_exists($upload_dir)) {
    $result['exists'] = false;
    try {
        mkdir($upload_dir, 0777, true);
        $result['created'] = true;
    } catch (Exception $e) {
        $result['created'] = false;
        $result['create_error'] = $e->getMessage();
    }
} else {
    $result['exists'] = true;
}

// Check if directory is writable
$result['is_writable'] = is_writable($upload_dir);

// Get directory permissions
$result['permissions'] = substr(sprintf('%o', fileperms($upload_dir)), -4);

// Check if we can create a test file
$test_file = $upload_dir . '/test.txt';
try {
    $fp = fopen($test_file, 'w');
    if ($fp) {
        fwrite($fp, 'test');
        fclose($fp);
        unlink($test_file);
        $result['can_write_file'] = true;
    } else {
        $result['can_write_file'] = false;
    }
} catch (Exception $e) {
    $result['can_write_file'] = false;
    $result['write_error'] = $e->getMessage();
}

// Get directory owner and group
$result['owner'] = posix_getpwuid(fileowner($upload_dir))['name'];
$result['group'] = posix_getgrgid(filegroup($upload_dir))['name'];

echo json_encode($result); 