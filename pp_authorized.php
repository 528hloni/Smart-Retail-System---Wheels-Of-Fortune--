<?php
include('connection.php');
session_start();

if (!isset($_GET['payment_id']) || !is_numeric($_GET['payment_id'])) {
    echo "Invalid request.";
    exit();
}

$payment_id = intval($_GET['payment_id']);

try {
    $pdo->beginTransaction();

    // Get order_id and all items
    $stmt = $pdo->prepare("SELECT order_id FROM payments WHERE payment_id = ?");
    $stmt->execute([$payment_id]);
    $order_id = $stmt->fetchColumn();

    if (!$order_id) throw new Exception("Payment not found.");

    // Fetch items
    $stmt = $pdo->prepare("SELECT rim_id, quantity FROM order_items WHERE order_id = ?");
    $stmt->execute([$order_id]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($items as $i) {
        // Deduct stock
        $stmt = $pdo->prepare("UPDATE rims SET quantity = quantity - ? WHERE rim_id = ?");
        $stmt->execute([$i['quantity'], $i['rim_id']]);
    }

    // Update payments + orders
    $stmt = $pdo->prepare("UPDATE payments SET status = 'Successful' WHERE payment_id = ?");
    $stmt->execute([$payment_id]);

    $stmt = $pdo->prepare("UPDATE orders SET status = 'Completed', payment_status = 'Paid' WHERE order_id = ?");
    $stmt->execute([$order_id]);

    $pdo->commit();

} catch (Exception $e) {
    $pdo->rollBack();
    echo "Error: " . $e->getMessage();
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Payment Authorized</title>
    <style>
        body { font-family: Arial, sans-serif; text-align:center; padding:40px; background:#f7fff7; }
        .box { border:1px solid #ccc; padding:30px; background:white; border-radius:10px; display:inline-block; }
        button { padding:10px 20px; margin-top:20px; background:#27ae60; color:white; border:none; border-radius:6px; cursor:pointer; }
    </style>
</head>
<body>

<div class="box">
    <h1>✅ Payment Authorized Successfully</h1>
    <p>Payment #<?= htmlentities($payment_id) ?> has been verified and the order is now completed.</p>
    <form action="payment_processor_dashboard.php" method="get">
        <button type="submit">Return to Dashboard</button>
    </form>
</div>

</body>
</html>