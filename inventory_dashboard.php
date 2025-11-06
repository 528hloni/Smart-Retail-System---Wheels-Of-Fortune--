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
$stmt = $pdo->prepare("
    SELECT rim_id, rim_name, model, size_inch, price, quantity, image_url
    FROM rims
");
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

//button action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    if ($action ==='Add New Wheel'){
        header('Location: inventory_add_product.php');
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
        <input type="text" id="search_input" name="search_input" placeholder="Search Name Or Model...">
       


        

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
            <td id="stock_<?= $row['rim_id']; ?>"> <!-- For real time stock update -->
                <?php echo htmlentities($row['quantity']); ?>
            </td>
            <td>
                <a href="product_details.php?rim_id=<?= $row['rim_id'] ?>">View</a> | 
                <a href="inventory_update_product.php?rim_id=<?= $row['rim_id'] ?>">Update</a> | 
                
                <a href="inventory_delete_product.php?id=<?php echo $row['rim_id']; // attaches the Rim ID?>"  
                onclick="return confirm('Are you sure you want to delete this wheel?');">
                Delete</a>

                       
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>







<script>
document.addEventListener('DOMContentLoaded', function() {

    /* Autocomplete search logic */
       
    const searchInput = document.getElementById('search_input');
    const table = document.getElementById('inventory_table');
    const rows = table.getElementsByTagName('tr');

    // Create suggestion box
    const suggestionBox = document.createElement('div');
    suggestionBox.style.border = "1px solid #ccc";
    suggestionBox.style.position = "absolute";
    suggestionBox.style.background = "white";
    suggestionBox.style.zIndex = "999";
    searchInput.parentNode.insertBefore(suggestionBox, searchInput.nextSibling);

    searchInput.addEventListener('keyup', function() {
        const filter = searchInput.value.toLowerCase();
        suggestionBox.innerHTML = ''; // Clear suggestions

        if (filter === '') {
            suggestionBox.style.display = 'none';
        } else {
            let matches = [];

            for (let i = 1; i < rows.length; i++) {
                const name = rows[i].getElementsByTagName('td')[1].textContent.toLowerCase();
                const model = rows[i].getElementsByTagName('td')[2].textContent.toLowerCase();
                if (name.includes(filter) || model.includes(filter)) {
                    matches.push(rows[i].getElementsByTagName('td')[1].textContent);
                }
            }

            // Show up to 5 suggestions
            matches.slice(0, 5).forEach(m => {
                const div = document.createElement('div');
                div.textContent = m;
                div.style.padding = '5px';
                div.style.cursor = 'pointer';
                div.addEventListener('click', function() {
                    searchInput.value = m;
                    suggestionBox.innerHTML = '';
                    suggestionBox.style.display = 'none';
                    searchInput.dispatchEvent(new Event('keyup'));
                });
                suggestionBox.appendChild(div);
            });

            suggestionBox.style.display = matches.length > 0 ? 'block' : 'none';
        }

        // Filter table rows
        for (let i = 1; i < rows.length; i++) {
            const name = rows[i].getElementsByTagName('td')[1].textContent.toLowerCase();
            const model = rows[i].getElementsByTagName('td')[2].textContent.toLowerCase();
            rows[i].style.display = (name.includes(filter) || model.includes(filter)) ? '' : 'none';
        }
    });



    /* Real time stock update logic */
        
    function updateStock() {
        fetch('fetch_stock.php')
            .then(response => response.json())
            .then(data => {
                data.forEach(item => {
                    const stockCell = document.getElementById('stock_' + item.rim_id);
                    if (stockCell) {
                        const oldValue = stockCell.textContent;
                        if (oldValue != item.quantity) {
                            // Update value
                            stockCell.textContent = item.quantity;

                            // Flash background color to indicate change
                            stockCell.style.backgroundColor = '#d4edda';
                            setTimeout(() => stockCell.style.backgroundColor = '', 600);
                        }
                    }
                });
            })
            .catch(error => console.error('Error fetching stock:', error));
    }

    // Run immediately on load
    updateStock();

    // Update stock every 10 seconds
    setInterval(updateStock, 10000);

});
</script>

   
</body>
</html>