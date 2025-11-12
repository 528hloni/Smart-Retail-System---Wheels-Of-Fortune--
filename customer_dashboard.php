<?php
include('connection.php');
session_start();

//session check:
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'Customer') {
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

    <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f7f7f7;
      margin: 0;
      padding: 0;
    }

    header {
      background-color: #222;
      color: #fff;
      text-align: center;
      padding: 15px 0;
    }

    .container {
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 40px;
      flex-wrap: wrap;
      padding: 60px 20px;
    }

    .card {
      background-color: #fff;
      width: 260px;
      height: 180px;
      border-radius: 15px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
      display: flex;
      justify-content: center;
      align-items: center;
      text-align: center;
      text-decoration: none;
      color: #333;
      font-size: 20px;
      font-weight: bold;
      transition: all 0.3s ease;
    }

    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 6px 16px rgba(0, 0, 0, 0.25);
      background-color: #ff0d00ff;
      color: white;
    }

    footer {
      text-align: center;
      margin-top: 50px;
      color: #555;
    }
  </style>
</head>

<header>
  <h1>Welcome, <?= htmlentities($customer['name']) ?> <?= htmlentities($customer['surname']) ?> </h1>
  <h2>Customer Dashboard</h2>
</header>

<body>
<div class="container">
  <a href="products.php?user_id=<?= urlencode($user_id) ?>" class="card"> Browse Products</a>
  <a href="c_order_history.php?user_id=<?= urlencode($user_id) ?>" class="card"> My Order History</a>
  <a href="c_cart.php?user_id=<?= urlencode($user_id) ?>" class="card"> My Cart</a>
</div>
    
</body>
</html>