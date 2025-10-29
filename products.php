<?php
include('connection.php');
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
        $sql = "SELECT * FROM rims";
        $stmt = $pdo->query($sql);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($results as $row): ?>
    <a href="product_details.php?rim_id=<?= $row['rim_id'] ?>" class="product-link">
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