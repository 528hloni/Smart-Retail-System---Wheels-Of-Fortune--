<?php
include('connection.php');
session_start();

// Session check
//if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'Payment Processor'){
//    header("Location: login.php");
//    exit();
//}

// Determine which screen to show
$page = isset($_GET['page']) ? $_GET['page'] : 'verify';

// Determine payment_id (GET for initial load, POST for buttons)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['payment_id']) && is_numeric($_POST['payment_id'])) {
        $payment_id = trim($_POST['payment_id']);
    } else {
        echo "Invalid request.";
        exit();
    }
} else {
    if (isset($_GET['payment_id']) && is_numeric($_GET['payment_id'])) {
        $payment_id = trim($_GET['payment_id']);
    } else if ($page === 'verify') {
        echo "Invalid request.";
        exit();
    } 
    // For other pages, you can still allow no payment_id
}

// Fetch payment data only if on verify page
if ($page === 'verify') {
    $stmt = $pdo->prepare("SELECT 
        p.payment_id,
        CONCAT(u.name, ' ', u.surname) AS customer,
        p.amount,
        p.method
    FROM payments p
    JOIN orders o ON p.order_id = o.order_id
    JOIN users u ON o.user_id = u.user_id
    WHERE p.payment_id = ?
    ORDER BY p.payment_id");
    $stmt->execute([$payment_id]);
    $payment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$payment) {
        echo "Payment not found.";
        exit();
    }
}

// Handle button actions (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // Keep it in the same file using ?page=
    if ($action === 'No-Failed') {
        header('Location: ' . $_SERVER['PHP_SELF'] . '?page=failed&payment_id=' . urlencode($payment_id));
        exit();
    }

    if ($action === 'Yes-Received') {
        header('Location: ' . $_SERVER['PHP_SELF'] . '?page=received&payment_id=' . urlencode($payment_id));
        exit();
    }

    if ($action === 'No-Not Received Yet') {
        header('Location: payment_processor_dashboard.php'); // another file
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Payment Verification</title>
</head>
<body>

<?php if ($page === 'verify'): ?>
    <h1>Verification</h1>
    <p>Payment ID: <?= htmlentities($payment['payment_id']) ?></p>
    <p>Customer: <?= htmlentities($payment['customer']) ?></p>
    <p>Amount: <?= htmlentities($payment['amount']) ?></p>
    <p>Method: <?= htmlentities($payment['method']) ?></p>

    <form method="POST">
        <input type="hidden" name="payment_id" value="<?= htmlentities($payment['payment_id']) ?>">
        <input type="submit" name="action" value="No-Not Received Yet">
        <input type="submit" name="action" value="No-Failed">
        <input type="submit" name="action" value="Yes-Received">
    </form>

<?php elseif ($page === 'failed'): ?>
    <h1>Payment Failed</h1>
    <p>This payment (ID <?= htmlentities($_GET['payment_id']) ?>) will be reviewed or deleted.</p>
    <a href="<?= $_SERVER['PHP_SELF'] ?>">Back to Verify</a>
    <a href="payment_processor_dashboard.php">Back to Home</a>


<?php elseif ($page === 'received'): ?>
    <h1>Payment Received</h1>
    <p>Now checking stock for payment #<?= htmlentities($_GET['payment_id']) ?>...</p>
    <a href="<?= $_SERVER['PHP_SELF'] ?>">Back to Verify</a>
    <a href="payment_processor_dashboard.php">Back to Home</a>


<?php else: ?>
    <h1>Unknown page</h1>
    <a href="<?= $_SERVER['PHP_SELF'] ?>">Go back</a>
    <a href="payment_processor_dashboard.php">Back to Home</a>

<?php endif; ?>

</body>
</html>