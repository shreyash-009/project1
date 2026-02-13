<?php
/**
 * Database Setup Script for Blood Receiver System
 * Run this script once to create the necessary tables for receiver functionality
 */

$host = "localhost";
$user = "root"; // XAMPP default
$pass = "";     // XAMPP default
$db = "blood_donor";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create receivers table
$receivers_sql = "CREATE TABLE IF NOT EXISTS receivers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    city VARCHAR(50) NOT NULL,
    phone VARCHAR(15) NOT NULL,
    email VARCHAR(100),
    blood_group_needed VARCHAR(5) NOT NULL,
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_request_date DATETIME,
    total_requests_fulfilled INT DEFAULT 0
)";

// Create blood_requests table for receiver requests
$blood_requests_sql = "CREATE TABLE IF NOT EXISTS blood_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    receiver_id INT NOT NULL,
    blood_group VARCHAR(5) NOT NULL,
    quantity INT NOT NULL,
    city VARCHAR(50) NOT NULL,
    request_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    request_status VARCHAR(20) DEFAULT 'Pending',
    fulfilled_by_donor_id INT,
    fulfillment_date DATETIME,
    FOREIGN KEY (receiver_id) REFERENCES receivers(id),
    INDEX idx_receiver (receiver_id),
    INDEX idx_status (request_status)
)";

// Execute table creation
$errors = [];

if ($conn->query($receivers_sql)) {
    echo "✅ Receivers table created/verified successfully!<br>";
} else {
    $errors[] = "Error creating receivers table: " . $conn->error;
}

if ($conn->query($blood_requests_sql)) {
    echo "✅ Blood requests table created/verified successfully!<br>";
} else {
    $errors[] = "Error creating blood_requests table: " . $conn->error;
}

// Display results
if (empty($errors)) {
    echo "<hr>";
    echo "<h3 style='color: #27ae60;'>✅ Database setup completed successfully!</h3>";
    echo "<p>The following tables are now available:</p>";
    echo "<ul>";
    echo "<li><strong>receivers</strong> - Stores receiver information</li>";
    echo "<li><strong>blood_requests</strong> - Stores blood requests from receivers</li>";
    echo "</ul>";
    echo "<p style='color: #666; font-style: italic;'>The receiver system is now ready to use. Users can login as 'Receiver' and register to submit blood requests.</p>";
} else {
    echo "<h3 style='color: #c0392b;'>❌ Setup encountered errors:</h3>";
    foreach ($errors as $error) {
        echo "<p style='color: #c0392b;'>$error</p>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Blood Receiver System - Database Setup</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        h1 {
            color: #1f3c88;
            text-align: center;
        }
        h3 {
            color: #333;
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 8px;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🩸 Blood Receiver System - Database Setup</h1>
        
        <div class="success">
            <p><strong>⚠️ Important:</strong> This script initializes the database tables required for the receiver functionality.</p>
            <p>If you see the success messages above, your system is ready to go!</p>
        </div>

        <h3>What Gets Created:</h3>
        <ul>
            <li><strong>receivers</strong> table - Stores receiver profile information (name, city, blood group needed, etc.)</li>
            <li><strong>blood_requests</strong> table - Tracks blood requests submitted by receivers with status tracking</li>
        </ul>

        <h3>Next Steps:</h3>
        <ol>
            <li>Delete this setup file (setup.php) for security</li>
            <li>Users can now login with the role "Receiver"</li>
            <li>Receivers can register and submit blood requests</li>
            <li>Donors will be notified of matching blood requests</li>
        </ol>
    </div>
</body>
</html>
