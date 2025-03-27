

<?php

class User {
    public function checkLogin($email, $password){
        require '../connection/connect.php'; // Database connection
        
        // Prepare the query to check if user exists
        $query = "SELECT userID, user, password FROM users WHERE user = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($userID, $db_email, $db_password);
        
        if ($stmt->fetch()) {
            // Check if password matches
            if (password_verify($password, $db_password)) {
                return $userID;  // Return userID if login is successful
            }
        }
        
        return false;  // Return false if user does not exist or password is incorrect
    }
}
if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $user = new User();

    // Validate user
    $validatedUser = $user->checkLogin($email, $password);

    if ($validatedUser) {
        // Proceed with the email sending or password reset logic
        // For example, initiate the password reset process:
        $_SESSION['user_id'] = $validatedUser; // Store user ID in session
        // Redirect to password reset page
        header("Location: forgot_password.php");
        exit();
    } else {
        $_SESSION['error'] = "Invalid email or password.";
        header("Location: login.php");
        exit();
    }
}
