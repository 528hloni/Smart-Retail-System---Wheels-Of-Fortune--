 <?php
  
 include('connection.php');
session_start();


if (!isset($_SESSION['loggedin']) || ($_SESSION['role'] !== 'Customer' && $_SESSION['role'] !== 'Payment Processor' && $_SESSION['role'] !== 'Inventory Manager' && $_SESSION['role'] !== 'Sales Associate')) {
    header("Location: login.php");
    exit();
}

try{

//Getting user_id from URL and validating it (using GET method)
if (isset($_GET['user_id']) && is_numeric($_GET['user_id'])) {
    $user_id = trim($_GET['user_id']);

    // Fetching customer
 $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$customer) {
        echo "Customer not found.";
        exit();
    }
} else {
    echo "Invalid request.";
    exit();
}

// Fetch all rims (product list)
    $sql = "SELECT * FROM rims";
    $stmt = $pdo->query($sql);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

}  catch (Exception $e) {
    // General error handler
    echo "Error: " . $e->getMessage();
}







?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="products.css">
</head>
<body>
    <h1> PREMIUM RIMS FOR YOUR RIDE </h1>
    <br><br>
    <h3>Upgrade Your Wheels, Upgrade Your Style</h3>
    <br><br>

<h2>FEATURED RIMS</h2>

    <div class="product-grid">
        <?php
        
        foreach ($results as $row): ?>
    <a href="product_details.php?rim_id=<?= $row['rim_id'] ?>&user_id=<?= $user_id ?>" class="product-link">    
        <div class="product-card"> 
            <img src="<?= $row['image_url'] ?>" alt="<?= $row['rim_name'] ?>"> 
            <h3><?= $row['rim_name'] ?></h3>
            <p><?= $row['size_inch'] ?> inch</p>
            <p><?= $row['color'] ?></p>
            <p class="price">R<?= $row['price'] ?></p>
        </div>
    </a>
<?php endforeach; ?>
     
         


            
    
        
        
    </div>
    
</body>
</html>