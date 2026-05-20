<?php
session_start();
include 'db.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password']; 

    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);

        // Plain text comparison
        if ($password === $user['password']) {   
            $user_id = $user['id'];
            
            $_SESSION['user_id'] = $user_id;
            $_SESSION['fullname'] = $user['fullname'];
            $_SESSION['role'] = $user['role'];

            // FIXED: Set only the logging-in user to active 
            // (Setting everyone to 0 kicks out the Admin if a User logs in)
            mysqli_query($conn, "UPDATE users SET is_active = 1 WHERE id = '$user_id'"); 
            
            // Clear hardware commands
            mysqli_query($conn, "UPDATE users SET scan_command = 'IDLE' WHERE id = '$user_id'");

            session_write_close(); 

            // UPDATED: Added check for address
            // If age, sex, OR address is missing, send them to setup
            if (empty($user['age']) || empty($user['sex']) || empty($user['address'])) {
                echo "setup_required";
            } else {
                echo "success";
            }
            exit();
            
        } else {
            echo "Invalid password";
        }
    } else {
        echo "User not found";
    }
}
?>