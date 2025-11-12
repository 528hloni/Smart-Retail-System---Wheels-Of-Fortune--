<?php 
include('connection.php');
session_start();

if (!isset($_SESSION['loggedin']) || ($_SESSION['role'] !== 'Customer' && $_SESSION['role'] !== 'Payment Processor' && $_SESSION['role'] !== 'Inventory Manager' && $_SESSION['role'] !== 'Sales Associate')) {
    header("Location: login.php");
    exit();
}




try{

    $role = $_SESSION['role'];



if (isset($_GET['rim_id']) && is_numeric($_GET['rim_id']) && isset($_GET['user_id']) && is_numeric($_GET['user_id'])){
    $rim_id = trim($_GET['rim_id']);
    $user_id = trim($_GET['user_id']);

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



if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    if ($action ==='ADD TO CART'){

        $quantity = intval($_POST['quantity']); // Get quantity from form
        if ($quantity < 1) $quantity = 1;

        //Adding product to cart
// Check if already in cart
$stmt = $pdo->prepare("SELECT quantity FROM cart WHERE user_id = ? AND rim_id = ? AND status = 'active'");
$stmt->execute([$user_id, $rim_id]);
$existing = $stmt->fetch(PDO::FETCH_ASSOC);

if ($existing) {
    // Update quantity
    $new_qty = $existing['quantity'] + $quantity;
    $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND rim_id = ? AND status = 'active'");
    $stmt->execute([$new_qty, $user_id, $rim_id]);
} else {
    // Insert new
    $stmt = $pdo->prepare("INSERT INTO cart (user_id, rim_id, quantity) VALUES (?, ?, ?)");
   $stmt->execute([$user_id, $rim_id, $quantity]);
}

echo '<script>
        alert("Successfully added product to cart");
    
       </script>';

    }
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
    <img src="<?= $product['image_url'] ?>" alt="<?= $product['rim_name'] ?>"> 
    <br>
    <h3><?= $product['rim_name'] ?></h3>
    <p><strong> <?= $product['model'] ?> </strong> </p>
    <br>
    <p class="price">R<?= number_format($product['price'], 2) ?></p>
    <h4>Specifications:</h4>
    <br>
    <p><strong>Size:</strong> <?= $product['size_inch'] ?> inch </p>
    <p><strong>Color:</strong> <?= $product['color'] ?> </p>
    <br>
    <p><strong>Bolt Pattern:</strong> <?= $product['bolt_pattern'] ?> </p>
    <p><strong>Offset:</strong> <?= $product['offset'] ?> </p>
    <br>
    <p><strong>Center Bore:</strong> <?= $product['center_bore'] ?> </p>
    <br>
    

<?php if ($role === 'Customer'): ?>
<form method="POST">

<div class="quantity-container">
    <label for="quantity">Quantity:</label>
    <div class="quantity-box">
        <button type="button" class="qty-btn" id="decrease">−</button>
        <input type="text" id="quantity" name="quantity" value="1" readonly>
        <button type="button" class="qty-btn" id="increase">+</button>
    </div>
</div>
    
    
    <input type="submit" name="action" value="ADD TO CART">
<?php endif; ?>






<script>
const decreaseBtn = document.getElementById('decrease');
const increaseBtn = document.getElementById('increase');
const quantityInput = document.getElementById('quantity');

decreaseBtn.addEventListener('click', () => {
    let value = parseInt(quantityInput.value);
    if (value > 1) {
        quantityInput.value = value - 1;
    }
});

increaseBtn.addEventListener('click', () => {
    let value = parseInt(quantityInput.value);
    quantityInput.value = value + 1;
});
</script>
</body>
</html>