<?php
require dirname(__FILE__) . '/_config.php';

try {
    $conn = new PDO("mysql:host={$_config['db_host']};dbname={$_config['db_database']}", $_config['db_user'], $_config['db_password']);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $query = isset($_GET['query']) ? $_GET['query'] : '';
    $sql = "SELECT store_id, store_name, store_image, store_description, store_lat, store_lon, store_address, store_phone, store_author, store_open, store_close, store_keywords 
            FROM stores 
            WHERE store_name LIKE :query 
            OR store_description LIKE :query 
            OR store_keywords LIKE :query";

    $stmt = $conn->prepare($sql);
    $likeQuery = "%$query%";
    $stmt->bindParam(':query', $likeQuery);
    $stmt->execute();

    $stores = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($stores);

} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

$conn = null;
?>