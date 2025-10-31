<?php
include('connection.php');
session_start();

//session check: only admin is allowed here
//if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'Inventory Manager') {
//    header("Location: login.php");
//    exit();
//}

try{
    // fetch data to display in table
$sql = "SELECT rim_id, rim_name, model, size_inch, price, quantity, image_url FROM rims";
$stmt = $pdo->query($sql);

//store all students in an array
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

//button action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    if ($action ==='Add New Wheel'){
        header('Location: inventory_add_new_wheel.php');
        exit();
        

    }

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
    <h1>Inventory Dashboard</h1>
    <br>
    <form method="POST">
        <input type="submit" name="action" value="Logout">
        <br><br>
        <input type="submit" name="action" value="Add New Wheel">
        <br><br>
        <input type="text" id="search_input" name="search_input" placeholder="Search Wheel...">

    </form>

    <table id="inventory_table" border="1">
        <thead>
            <tr>
                <th>Image</th>
                <th>Name</th>
                <th>Model</th>
                <th>Size</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Actions</th>
            </tr>
       </thead>

       <tbody>
        <?php foreach ($products as $row): ?>
        <tr>
            
            <td><img src="<?= $row['image_url'] ?>" alt="<?= $row['rim_name'] ?>" width="100">  </td>
            
            <td><?php echo htmlentities($row['rim_name']); ?></td>
            <td><?php echo htmlentities($row['model']); ?></td>
            <td><?php echo htmlentities($row['size_inch']); ?></td>
            <td><?php echo htmlentities($row['price']); ?></td>
            <td><?php echo htmlentities($row['quantity']); ?></td>
            <td>
                <a href="product_details.php?student_id=<?= $row['rim_id'] ?>">View</a> | 
                <a href="update_product.php?student_id=<?= $row['rim_id'] ?>">Update</a> | 
                
                <a href="delete_product.php?id=<?php echo $row['rim_id']; // attaches the Rim ID?>"  
                onclick="return confirm('Are you sure you want to delete this wheel?');">
                Delete</a>

                       
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>





   
</body>
</html>