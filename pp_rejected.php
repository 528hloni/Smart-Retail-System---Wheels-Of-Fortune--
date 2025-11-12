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

    // Get order info
    $stmt = $pdo->prepare("SELECT order_id, amount FROM payments WHERE payment_id = ?");
    $stmt->execute([$payment_id]);
    $payment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$payment) throw new Exception("Payment not found.");

    $order_id = $payment['order_id'];
    $refund_amount = $payment['amount'];

    // Insert into refunds
    $stmt = $pdo->prepare("INSERT INTO refunds (order_id, reason, refund_amount) VALUES (?, ?, ?)");
    $stmt->execute([$order_id, 'Payment received but product(s) out of stock.', $refund_amount]);

    // Update orders + payments
    $stmt = $pdo->prepare("UPDATE orders SET status='Cancelled', payment_status='Refunded' WHERE order_id=?");
    $stmt->execute([$order_id]);

    $stmt = $pdo->prepare("UPDATE payments SET status='Refunded' WHERE payment_id=?");
    $stmt->execute([$payment_id]);

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
    <title>Payment Rejected</title>
    <style>
        body { font-family: Arial, sans-serif; text-align:center; padding:40px; background:#fff8f8; }
        .box { border:1px solid #ccc; padding:30px; background:white; border-radius:10px; display:inline-block; }
        button { padding:10px 20px; margin-top:20px; background:#c0392b; color:white; border:none; border-radius:6px; cursor:pointer; }
    </style>
</head>
<body>

<div class="box">
    <h1> Payment Rejected - Refund Initiated</h1>
    <p>Payment #<?= htmlentities($payment_id) ?> has been refunded due to stock unavailability.</p>
    <form action="payment_processor_dashboard.php" method="get">
        <button type="submit">Return to Dashboard</button>
    </form>
</div>

</body>
</html>