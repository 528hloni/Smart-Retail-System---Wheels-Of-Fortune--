<?php 
include('connection.php');
session_start();




//user input
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $password = trim($_POST['password']??'');
   

    try{


    if ($action === 'Login' && $email && $password){ //checking if button Login was clicked and all inputs are filled
         $sql = "SELECT * FROM users WHERE email = ?"; // query to find user with matching email
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

           

            //if matching user is found then compare passwords(input and database)
            if ($user) {

                if (password_verify($password, $user['password_hash'])) {
        // Redirect based on role
        $role = $user['role'];

        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $role;
        $_SESSION['loggedin'] = true;

        if ($role === 'Sales Associate') {
            header('Location: sa_dashboard.php'); // update to your actual dashboard
            exit();
        } elseif ($role === 'Inventory Manager') {
            header('Location: im_dashboard.php');
            exit();
        } elseif ($role === 'Payment Processor') {
            header('Location: pp_dashboard.php');
            exit();
        } elseif ($role === 'Customer') {
            header('Location: customer_dashboard.php?user_id=' . $user['user_id']);
            
            exit();
        }

                


                }else{ //alert if password is incorrect
                    echo '<script>  
                    alert("Login failed, Invalid email or password ")
                    </script>';
                }
            
            } else { //alert if user not found
     echo '<script>  
    alert("Login failed, Invalid email or password!")
    </script>';
   
}

    }

} catch (Exception $e) {
    // Handle general errors
    echo "Error: " . $e->getMessage();
}
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
    <h1> Wheels Of Fortune </h1>
    <br><br>
    <h3> Where Every Wheel Is A Win! </h3>
    <br><br><br>
    <form method="POST">
        <p> Login to your account </p>
        <br>
        <label for="email"> Email </label>
        <input type="email" id="email" name="email" placeholder="your@gmail.com" required>
        <br>
        <label for="password">Password </label>
        <input type="password" id="password" name="password" required>
        <br><br>
        <input type="submit" name="action" value="Login">
        <br>
        <p>Do not have an account? <a href ="register.php">Register </a> </p>
    </form>
</body>
</html>