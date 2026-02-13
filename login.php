<?php
session_start();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'user';
    
    // Simple validation (in a real app, query the database and verify password)
    if (!empty($email) && !empty($password)) {
        // Set session variables
        $_SESSION['user_id'] = md5($email); // Simple unique ID
        $_SESSION['user_email'] = $email;
        $_SESSION['user_role'] = $role; // 'donor', 'hospital', or 'receiver'
        
        // Redirect to homepage
        header("Location: homepage.php");
        exit();
    } else {
        $error_message = "Please fill in all fields";
    }
}

// Function to check user role
function getUserRole() {
    return $_SESSION['user_role'] ?? null;
}

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Login</title>

<style>
    body {
        margin: 0;
        height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        background: linear-gradient(135deg, #e3f2fd, #ffffff);
        font-family: 'Segoe UI', Tahoma, sans-serif;
    }

    .login-box {
        width: 360px;
        background: rgba(255, 255, 255, 0.85);
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    }

    .login-box h2 {
        text-align: center;
        color: #1f3c88;
        margin-bottom: 20px;
    }

    .input-group {
        margin-bottom: 15px;
    }

    .input-group label {
        display: block;
        font-size: 14px;
        margin-bottom: 5px;
        color: #333;
    }

    .input-group input {
        width: 100%;
        padding: 10px;
        border-radius: 8px;
        border: 1px solid #ccc;
        font-size: 15px;
        box-sizing: border-box;
    }

    .role-group {
        margin-bottom: 15px;
        font-size: 14px;
        color: #333;
    }

    .login-btn {
        width: 100%;
        padding: 12px;
        background-color: #0d6efd;
        color: white;
        border: none;
        border-radius: 25px;
        font-size: 16px;
        font-weight: bold;
        cursor: pointer;
        transition: background 0.3s;
    }

    .login-btn:hover {
        background-color: #084298;
    }

    .extra {
        text-align: center;
        margin-top: 15px;
        font-size: 14px;
    }

    .extra a {
        color: #0d6efd;
        text-decoration: none;
        font-weight: 500;
    }

    .extra a:hover {
        text-decoration: underline;
    }

    .error-message {
        color: red;
        text-align: center;
        margin-bottom: 15px;
        font-size: 14px;
    }
</style>
</head>

<body>

<div class="login-box">
    <h2>Login</h2>

    <?php if(isset($error_message)): ?>
        <div class="error-message"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <form method="POST">

        <div class="input-group">
            <label>Email or Phone</label>
            <input type="text" name="email" required>
        </div>

        <div class="input-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>

        <div class="role-group">
            <label>Login As:</label>
            <input type="radio" name="role" value="donor" checked> Donor
            &nbsp;&nbsp;
            <input type="radio" name="role" value="hospital"> Hospital
            &nbsp;&nbsp;
            <input type="radio" name="role" value="receiver"> Receiver
        </div>

        <button type="submit" class="login-btn">Login</button>
    </form>

    <div class="extra">
        Don't have an account?
        <a href="signup.html">Sign Up</a>
    </div>
</div>

</body>
</html>
