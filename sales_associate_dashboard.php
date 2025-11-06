<?php
include('connection.php');
session_start();

//session check: only admin is allowed here
//if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'Inventory Manager') {
//    header("Location: login.php");
//    exit();
//}


try {
    // Total sales today
    $stmt = $pdo->prepare("
        SELECT SUM(total_amount) AS total_sales_today
        FROM orders
        WHERE status = 'Completed' 
          AND DATE(order_date) = CURDATE()
    ");
    $stmt->execute();
    $today_sales = $stmt->fetch(PDO::FETCH_ASSOC)['total_sales_today'] ?? 0;

    // Total sales this week
    $stmt = $pdo->prepare("
        SELECT SUM(total_amount) AS total_sales_week
        FROM orders
        WHERE status = 'Completed'
          AND YEARWEEK(order_date, 1) = YEARWEEK(CURDATE(), 1)
    ");
    $stmt->execute();
    $week_sales = $stmt->fetch(PDO::FETCH_ASSOC)['total_sales_week'] ?? 0;

    // Total sales this month
    $stmt = $pdo->prepare("
        SELECT SUM(total_amount) AS total_sales_month
        FROM orders
        WHERE status = 'Completed'
          AND MONTH(order_date) = MONTH(CURDATE())
          AND YEAR(order_date) = YEAR(CURDATE())
    ");
    $stmt->execute();
    $month_sales = $stmt->fetch(PDO::FETCH_ASSOC)['total_sales_month'] ?? 0;

    // Top 3 selling rims
    $stmt = $pdo->prepare("
        SELECT 
            r.rim_name,
            r.model,
            SUM(oi.quantity) AS total_sold,
            SUM(oi.quantity * oi.unit_price) AS total_revenue
        FROM order_items oi
        JOIN rims r ON oi.rim_id = r.rim_id
        JOIN orders o ON oi.order_id = o.order_id
        WHERE o.status = 'Completed'
        GROUP BY r.rim_id, r.rim_name, r.model
        ORDER BY total_sold DESC
        LIMIT 3
    ");
    $stmt->execute();
    $top_rims = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
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
    <h1>Sales Associate Dashboard</h1>
    <br><br>
    <h3>Total Sales this month</h3>  <p> <?=htmlentities($month_sales) ?> </p>
    <h3>Total Sales this week</h3> <p> <?=htmlentities($week_sales) ?> </p>
    <h3>Total Sales today</h3> <p> <?=htmlentities($today_sales) ?> </p>
    <br>
    <div class="top-products">
    <h2>Top 3 Selling Wheels</h2>
    <div class="top-rims-container" style="display:flex; gap:20px; flex-wrap:wrap;">
        <?php if (count($top_rims) > 0): ?>
            <?php foreach ($top_rims as $rim): ?>
                <div class="rim-card" style="border:1px solid #ccc; padding:10px; width:250px; text-align:center;">
                    <img src="<?= htmlentities($rim['image_url']); ?>" alt="<?= htmlentities($rim['rim_name']); ?>" style="width:100%; height:auto; margin-bottom:10px;">
                    <h3><?= htmlentities($rim['rim_name']); ?></h3>
                    <p>Model: <?= htmlentities($rim['model']); ?></p>
                    <p>Total Sold: <?= htmlentities($rim['total_sold']); ?></p>
                    <p>Total Revenue: R<?= number_format($rim['total_revenue'], 2); ?></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No top rims available.</p>
        <?php endif; ?>
    </div>
</div>
    <br>
    <form method="POST">
        <h3>All Orders</h3>
        <input type="text" id="search_input" name="search_input" placeholder="Search Customer...">
         <select id="filter" name="filter">
            <option value="" >All Order</option>
            <option value="Completed">Completed</option>
            <option value="Pending">Pending</option>
            <option value="Cancelled">Cancelled</option>
        </select>
        <input type="submit" name="action" value="Export">
        <br><br>


        <table id="orders" border="1">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Date</th>
                <th>Total</th>
                <th>Status</th>
                <th>Actions</th>
                
            </tr>
       </thead>



      
    </form>
</body>
</html>