<?php
include('connection.php');
session_start();

//session check: only admin is allowed here
//if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'Payment Processor') {
//    header("Location: login.php");
//    exit();
//}


try{
    // fetch data to display in table
$sql = "SELECT 
            p.payment_id,
            p.order_id,
            CONCAT(u.name, ' ', u.surname) AS customer,
            p.amount,
            p.method
        FROM payments p
        JOIN orders o ON p.order_id = o.order_id
        JOIN users u ON o.user_id = u.user_id
        ORDER BY p.payment_id 
    ";
$stmt = $pdo->query($sql);

 // If this is an AJAX request, return JSON
    if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
        echo json_encode($payments);
        exit; // stop the rest of the page from loading
    }

//store all payments in an array
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

//button action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
   

    if ($action ==='Logout'){
        session_destroy();
        header('Location: login.php');
        exit();
    }
}
} catch (Exception $e) {
    // Handle general errors
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
    <h1>Pending Payments</h1>
    <br>

    <table id="paymentsBody" border="1">
    <thead>
        <tr>
            <th>Payment ID</th>
            <th>Order ID</th>
            <th>Customer</th>
            <th>Amount</th>
            <th>Method</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        
        <?php foreach ($payments as $row): ?>
        <tr>
            <td><?php echo htmlentities($row['payment_id']); ?></td>
            <td><?php echo htmlentities($row['order_id']); ?></td>
            <td><?php echo htmlentities($row['customer']); ?></td>
            <td><?php echo htmlentities($row['amount']); ?></td>
            <td><?php echo htmlentities($row['method']); ?></td>
             <td>
                <a href="pp_verification.php?payment_id=<?= $row['payment_id'] ?>">Verify Payment</a>  
             </td>   
            
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<script>  //Javascript real time update
 const paymentsBody = document.getElementById('paymentsBody');

  function fetchPayments() {
      fetch('<?= basename(__FILE__) ?>?ajax=1')
          .then(res => res.json())
          .then(data => {
              paymentsBody.innerHTML = '';
              data.forEach(row => {
                  const tr = document.createElement('tr');
                  tr.innerHTML = `
                      <td>${row.payment_id}</td>
                      <td>${row.order_id}</td>
                      <td>${row.customer}</td>
                      <td>${parseFloat(row.amount).toFixed(2)}</td>
                      <td>${row.method}</td>
                      <td><a href="pp_verification.php?payment_id=${row.payment_id}">Verify Payment</a></td>
                  `;
                  paymentsBody.appendChild(tr);
                });
          })
          .catch(err => console.error(err));
    }

  // Refresh every 5 seconds
  setInterval(fetchPayments, 5000);

  // Initial load
  fetchPayments();
  </script>
    
</body>
</html>