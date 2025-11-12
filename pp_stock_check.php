<?php
include('connection.php');
session_start();

// Session check
//if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'Payment Processor'){
//    header("Location: login.php");
//    exit();
//}

if (!isset($_GET['payment_id']) || !is_numeric($_GET['payment_id'])) {
    echo "Invalid request.";
    exit();
}

$payment_id = intval($_GET['payment_id']);

try {
    // Fetch order info from payment_id
    $stmt = $pdo->prepare("
        SELECT o.order_id, o.status AS order_status, o.payment_status, u.name, u.surname
        FROM payments p
        JOIN orders o ON p.order_id = o.order_id
        JOIN users u ON o.user_id = u.user_id
        WHERE p.payment_id = ?
        LIMIT 1
    ");
    $stmt->execute([$payment_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        echo "Payment not found.";
        exit();
    }

    $order_id = $order['order_id'];

    // Fetch order items + stock
    $stmt = $pdo->prepare("
        SELECT 
        r.rim_id,
        r.rim_name,
        r.model,
        r.quantity AS stock_available,
        oi.quantity AS quantity_ordered
    FROM order_items oi
    JOIN rims r ON oi.rim_id = r.rim_id
    WHERE oi.order_id = ? 
    ");
    $stmt->execute([$order_id]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stock_ok = true;
    foreach ($items as $item) {
        if ($item['stock_available'] < $item['quantity_ordered']) {
            $stock_ok = false;
            break;
        }
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Stock Verification</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        table { width: 90%; border-collapse: collapse; margin: 20px auto; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
        th { background: #333; color: #fff; }
        tr.out { background: #ffe6e6; }
        .center { text-align: center; }
        button { padding: 10px 16px; margin: 10px; border: none; border-radius: 6px; cursor: pointer; }
        .btn-yes { background: #2ecc71; color: #fff; }
        .btn-no { background: #e74c3c; color: #fff; }
        button:disabled { background: #aaa; cursor: not-allowed; }
    </style>
</head>
<body>

<h1 class="center">Stock Check for Payment #<?= htmlentities($payment_id) ?></h1>
<h3 class="center">Customer: <?= htmlentities($order['name'] . ' ' . $order['surname']) ?></h3>

<table>
    <thead>
        <tr>
            <th>Product</th>
            <th>Model</th>
            <th>Quantity Ordered</th>
            <th>Stock Available</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($items as $i): ?>
            <tr class="<?= ($i['stock_available'] < $i['quantity_ordered']) ? 'out' : '' ?>">
                <td><?= htmlentities($i['rim_name']) ?></td>
                <td><?= htmlentities($i['model']) ?></td>
                <td><?= $i['quantity_ordered'] ?></td>
                <td><?= $i['stock_available'] ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div class="center">
    <form action="pp_authorized.php" method="get" style="display:inline;">
        <input type="hidden" name="payment_id" value="<?= $payment_id ?>">
        <button type="submit" class="btn-yes" <?= $stock_ok ? '' : 'disabled' ?>>Yes - In Stock - Authorize</button>
    </form>

    <form action="pp_rejected.php" method="get" style="display:inline;">
        <input type="hidden" name="payment_id" value="<?= $payment_id ?>">
        <button type="submit" class="btn-no">No - Out of Stock - Refund</button>
    </form>
</div>

<div class="center">
    <a href="payment_processor_dashboard.php">Return to Dashboard</a>
</div>

</body>
</html>