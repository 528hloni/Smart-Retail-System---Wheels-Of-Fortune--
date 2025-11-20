<?php
include('connection.php');
session_start();

//session check: only admin is allowed here
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'Sales Associate') {
    header("Location: login.php");
    exit();
}



try {
    // Total sales today
    $stmt = $pdo->prepare("
    SELECT COUNT(*) AS total_sales_today
        FROM orders
        WHERE status = 'Completed' 
          AND DATE(order_date) = CURDATE()
        
    ");
    $stmt->execute();
    $today_sales = $stmt->fetch(PDO::FETCH_ASSOC)['total_sales_today'] ?? 0;

    // Total sales this week
    $stmt = $pdo->prepare("
        SELECT COUNT(*) AS total_sales_week
        FROM orders
        WHERE status = 'Completed'
          AND YEARWEEK(order_date, 1) = YEARWEEK(CURDATE(), 1)
    ");
    $stmt->execute();
    $week_sales = $stmt->fetch(PDO::FETCH_ASSOC)['total_sales_week'] ?? 0;

    // Total sales this month
    $stmt = $pdo->prepare("
        SELECT COUNT(*) AS total_sales_month
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
            r.image_url,
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

//Orders table
    $stmt = $pdo->prepare("
    SELECT 
        o.order_id,
        o.user_id,
        u.name,
        u.surname,
        o.order_date,
        o.total_amount,
        o.status,
        o.payment_status
    FROM orders o
    JOIN users u ON o.user_id = u.user_id
    ORDER BY o.order_date DESC
");
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    <link rel="stylesheet" href="sales_associate_dashboard.css">
    
</head>
<body>

 <nav class="navbar">
    <div class="nav-container">
        <div class="nav-logo">
            Wheels Of Fortune
        </div>
        <ul class="nav-links">
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>
</nav>
<br>
<br>






    <h1>Sales Associate Dashboard</h1>
    <br><br>

    


    <div class="sales-stats">
    <div class="sales-card">
        <h3>Total Sales This Month</h3>
        <p><?= htmlentities($month_sales) ?></p>
    </div>
    <div class="sales-card">
        <h3>Total Sales This Week</h3>
        <p><?= htmlentities($week_sales) ?></p>
    </div>
    <div class="sales-card">
        <h3>Total Sales Today</h3>
        <p><?= htmlentities($today_sales) ?></p>
    </div>
</div>
    <br>
    <div class="top-products">
    <h2>Top 3 Selling Wheels</h2>
    <div class="top-rims-container">
        <?php if (count($top_rims) > 0): ?>
            <?php foreach ($top_rims as $rim): ?>
                <div class="rim-card">
                     
                    <img src="<?= $rim['image_url'] ?>" alt="<?= $rim['rim_name'] ?>" >
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
        <div id="autocomplete-results"></div>
         <select id="filter" name="filter">
            <option value="" >All Order</option>
            <option value="Completed">Completed</option>
            <option value="Pending">Pending</option>
            <option value="Cancelled">Cancelled</option>
        </select>
        
        <form method="post" action="export_report.php">
    <input type="submit" name="action" >Export PDF</button>
</form>
        
        <br><br>


        <table id="orders" border="1">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Date</th>
                <th>Total</th>
                <th>Status</th>
                <th>Payment Status</th>
                <th>Actions [View]</th>
                
            </tr>
       </thead>
       <tbody>
        <?php foreach ($orders as $row): ?>
        <tr>
            <td><?php echo htmlentities($row['order_id']); ?></td>
            <td><?php echo htmlentities($row['name']) ." ". htmlentities($row['surname']); ?></td>
            <td><?php echo htmlentities($row['order_date']); ?></td>
            <td><?php echo htmlentities($row['total_amount']); ?></td>
            <td><?php echo htmlentities($row['status']); ?></td>
            <td><?php echo htmlentities($row['payment_status']) ?></td>
            <td>
                <a href="sa_customer_details.php?user_id=<?= $row['user_id'] ?>">Customer Details</a> | 
                <a href="sa_order_items.php?order_id=<?= $row['order_id'] ?>">Order Items</a>  
                
                 
            </td>
            <?php endforeach; ?>




       </tbody>



      
    </form>




  <div class="pagination" id="pagination"></div>

    <script>
    const searchInput = document.getElementById("search_input");
    const autocompleteList = document.getElementById("autocomplete-results");
    const filterSelect = document.getElementById("filter");
    const rows = Array.from(document.querySelectorAll("#orders tbody tr"));
    const paginationContainer = document.getElementById("pagination");

    let currentPage = 1;
    const rowsPerPage = 10;

    // JAVASCRIPT AUTOCOMPLETE SEARCH 
    searchInput.addEventListener("input", () => {
        const query = searchInput.value.toLowerCase();
        autocompleteList.innerHTML = "";
        if (!query) {
            autocompleteList.style.display = "none";
            applyFilters();
            return;
        }

        const matches = [];
        rows.forEach(row => {
            const customer = row.cells[1].innerText.toLowerCase();
            if (customer.includes(query)) matches.push(customer);
        });

        const uniqueMatches = [...new Set(matches)];
        if (uniqueMatches.length > 0) {
            autocompleteList.style.display = "block";
            uniqueMatches.forEach(name => {
                const div = document.createElement("div");
                div.classList.add("autocomplete-item");
                div.innerText = name;
                div.onclick = () => {
                    searchInput.value = name;
                    autocompleteList.style.display = "none";
                    applyFilters();
                };
                autocompleteList.appendChild(div);
            });
        } else {
            autocompleteList.style.display = "none";
        }

        applyFilters();
    });

    document.addEventListener("click", e => {
        if (e.target !== searchInput) autocompleteList.style.display = "none";
    });

    // JAVASCRIPT FILTER ORDERS
    filterSelect.addEventListener("change", applyFilters);

    function applyFilters() {
        const searchQuery = searchInput.value.toLowerCase();
        const filterValue = filterSelect.value.toLowerCase();

        rows.forEach(row => {
            const customer = row.cells[1].innerText.toLowerCase();
            const status = row.cells[4].innerText.toLowerCase();

            const matchesSearch = customer.includes(searchQuery);
            const matchesStatus = filterValue === "" || status === filterValue;

            if (matchesSearch && matchesStatus) {
                row.classList.remove("hide");
            } else {
                row.classList.add("hide");
            }
        });

        currentPage = 1;
        displayPage();
    }

    //  PAGINATION 
    function displayPage() {
        const visibleRows = rows.filter(row => !row.classList.contains("hide"));
        const totalPages = Math.ceil(visibleRows.length / rowsPerPage);

        visibleRows.forEach((row, index) => {
            const start = (currentPage - 1) * rowsPerPage;
            const end = start + rowsPerPage;
            if (index >= start && index < end) row.style.display = "";
            else row.style.display = "none";
        });

        paginationContainer.innerHTML = "";
        if (totalPages > 1) {
            const prev = document.createElement("button");
            prev.innerText = "Prev";
            prev.disabled = currentPage === 1;
            prev.classList.toggle("disabled", currentPage === 1);
            prev.onclick = () => { if (currentPage > 1) { currentPage--; displayPage(); } };
            paginationContainer.appendChild(prev);

            for (let i = 1; i <= totalPages; i++) {
                const btn = document.createElement("button");
                btn.innerText = i;
                if (i === currentPage) btn.classList.add("active");
                btn.onclick = () => { currentPage = i; displayPage(); };
                paginationContainer.appendChild(btn);
            }

            const next = document.createElement("button");
            next.innerText = "Next";
            next.disabled = currentPage === totalPages;
            next.classList.toggle("disabled", currentPage === totalPages);
            next.onclick = () => { if (currentPage < totalPages) { currentPage++; displayPage(); } };
            paginationContainer.appendChild(next);
        }
    }

    
    applyFilters();
    </script>

</body>
</html>