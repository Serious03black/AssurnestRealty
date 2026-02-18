<?php
require_once 'db.php';

if (isset($_GET['id']) && isset($_GET['num'])) {
    $id = intval($_GET['id']);
    $num = intval($_GET['num']);
    
    if ($num < 1 || $num > 5) {
        header("HTTP/1.0 404 Not Found");
        exit;
    }
    
    $column = "image" . $num;
    
    $stmt = $pdo->prepare("SELECT $column FROM properties WHERE property_id = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($row && !empty($row[$column])) {
        header("Content-Type: image/jpeg"); // Assuming JPEG, but browsers usually detect
        echo $row[$column];
    } else {
        // Return a placeholder or 404
        header("HTTP/1.0 404 Not Found");
    }
}
?>
