<?php
include('connection.php');
session_start();

try{

//user input
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $name = htmlentities(trim($_POST['name']??''));
    $surname = htmlentities(trim($_POST['surname']??''));
    $identity_number = htmlentities(trim($_POST['identity_number'] ?? ''));
    $date_of_birth = htmlentities(trim($_POST['date_of_birth']??''));
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $phone = htmlentities(trim($_POST['phone']??''));
    $password = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');
    $hashed_password = null;
    $role = 'Customer';

    if ($password !== $confirm_password) {
        // Passwords don't match
        echo '<script>alert("Passwords do not match!");</script>';
    } else {
        // Passwords match → proceed (hash and save)
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    } 
    
    if ($action === 'Create Account' && $name && $surname && $identity_number && $date_of_birth && $email && $phone && $hashed_password) {

        $stmt = $pdo->prepare("INSERT INTO users (name,surname,identity_number,date_of_birth,email,phone,password_hash,role) VALUES (?,?,?,?,?,?,?,?)");
        $stmt->execute([$name,$surname,$identity_number,$date_of_birth,$email,$phone,$hashed_password,$role]);

         echo  '<script>
         alert("Registration successful! You can now login")
         window.location.href = "login.php";
         </script>';

        
         exit();

        } else {
            // Validation failed - show which fields are missing
            $missing = [];
            if (!$name) $missing[] = "Name";
            if (!$surname) $missing[] = "Surname";
             if (!$identity_number) {
                $missing[] = "Identity Number";
                } elseif (!preg_match('/^\d{13}$/', $identity_number)) {
                $missing[] = "Identity Number (must be exactly 13 digits)";
                }
            if (!$date_of_birth) $missing[] = "Date of Birth";
            if (!$email) $missing[] = "Email";
            if (!$phone) {
                $missing[] = "Phone";
            } elseif  (!preg_match('/^(\+?\d{1,3})?\d{7,15}$/', $phone)) {
             $missing[] = "Invalid phone number format.";
        }
            if (!$password) $missing[] = "Password";
             if (!$confirm_password) $missing[] = "Confirm Password";
            
            $errorMsg = "Missing or invalid fields: " . implode(", ", $missing);
            echo '<script>alert("' . $errorMsg . '");</script>';
        }

   

}
} catch (Exception $e) {
    // General error handler
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
    <h2> Register to start shopping </h2>
    <form method="POST">
        <label for="name">Name</label>
        <input type="text" id="name" name="name" required>
        <br>
        <label for="surname">Surname</label>
        <input type="text" id="surname" name="surname" required>
        <br>
        <label for="date_of_birth">Date of Birth</label>
        <input type="date" id="date_of_birth" name="date_of_birth" required>
        <br>
        <label for="identity_number">ID Number</label>
        <input type="number" id="identity_number" name="identity_number" required>
        <br>
        <label for="email">Email</label>
        <input type="email" id="email" name="email" placeholder="your@gmail.com" required>
        <br>
        <label for="phone">Phone</label>
        <input type="tel" id="phone" name="phone" required>
        <br>
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>
        <br>
        <label for="confirm_password">Confirm Password </label>
        <input type="password" id="confirm_password" name="confirm_password" required>
        <br><br>
        <input type="submit" name="action" value="Create Account">
        <br>
        <p> Already have an account? <a href ="login.php">Login </a> </p>
    </form>
</body>
</html>