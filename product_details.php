<?php 
include('connection.php');

try{


if (isset($_GET['rim_id']) && is_numeric($_GET['rim_id'])){
    $rim_id = trim($_GET['rim_id']);
    $stmt = $pdo->prepare("SELECT * FROM rims WHERE rim_id = ?");
    $stmt->execute([$rim_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        echo "Product not found";
        exit();
        
    }
} else {
    echo "Invalid request";
    exit();
}
} catch (Exception $e) {
    echo "Error: " . $e->getmessage();
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    
</body>
</html>