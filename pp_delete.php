<?php
include('connection.php');
session_start();

try {
    if (isset($_GET['payment_id'])) {
        $payment_id = intval($_GET['payment_id']); // sanitize input

        // Fetch payment info
        $stmt = $pdo->prepare("SELECT * FROM payments WHERE payment_id = ?");
        $stmt->execute([$payment_id]);
        $payment = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($payment) {
            //  Log failure in failed_payments
            $stmt = $pdo->prepare("
                INSERT INTO failed_payments (payment_id, reason)
                VALUES (?, ?)
            ");
            $reason = "Payment not reflected after 48 hours";
            $stmt->execute([$payment_id, $reason]);

            //  Update payment status instead of deleting
            $stmt = $pdo->prepare("UPDATE payments SET status = 'Failed' WHERE payment_id = ?");
            $stmt->execute([$payment_id]);

            //  Update related order too
            $stmt = $pdo->prepare("UPDATE orders SET payment_status = 'Failed', status = 'Cancelled' WHERE order_id = ?");
            $stmt->execute([$payment['order_id']]);

            echo '<script>
                alert("Payment marked as failed and logged.");
                window.location.href = "payment_processor_dashboard.php";
            </script>';
            exit();

        } else {
            echo '<script>
                alert("Payment record not found.");
                window.location.href = "payment_processor_dashboard.php";
            </script>';
        }

    } else {
        echo '<script>
            alert("Invalid request.");
            window.location.href = "payment_processor_dashboard.php";
        </script>';
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>