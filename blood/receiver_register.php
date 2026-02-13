<?php
session_start();

// Check if user is logged in and is a receiver user
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'receiver') {
    header("Location: ../homepage.php");
    exit();
}

include 'db.php';

// Create tables if they don't exist
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

$notifications_sql = "CREATE TABLE IF NOT EXISTS donor_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    donor_id INT NOT NULL,
    request_id INT,
    request_type VARCHAR(20),
    blood_group VARCHAR(5),
    requester_name VARCHAR(100),
    message TEXT,
    notification_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    is_read TINYINT DEFAULT 0,
    INDEX idx_donor (donor_id),
    INDEX idx_read (is_read)
)";

$conn->query($receivers_sql);
$conn->query($blood_requests_sql);
$conn->query($notifications_sql);

// Helper function to get compatible blood types
function get_compatible_donors_for_blood_type($blood_type) {
    $compatibility = [
        'O+' => ['O+', 'A+', 'B+', 'AB+'],
        'O-' => ['O-', 'O+', 'A-', 'A+', 'B-', 'B+', 'AB-', 'AB+'],
        'A+' => ['A+', 'AB+'],
        'A-' => ['A-', 'A+', 'AB-', 'AB+'],
        'B+' => ['B+', 'AB+'],
        'B-' => ['B-', 'B+', 'AB-', 'AB+'],
        'AB+' => ['AB+'],
        'AB-' => ['AB-', 'AB+']
    ];
    
    return isset($compatibility[$blood_type]) ? $compatibility[$blood_type] : [$blood_type];
}

// Track the registered receiver ID
$receiver_id = 0;
$success_msg = "";
$error_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Step 1: Register Receiver
    if (isset($_POST['register_receiver'])) {
        $name = $_POST['name'];
        $city = $_POST['city'];
        $phone = $_POST['phone'];
        $email = $_POST['email'];
        $blood_group_needed = $_POST['blood_group_needed'];

        $sql = "INSERT INTO receivers (name, city, phone, email, blood_group_needed)
                VALUES ('$name', '$city', '$phone', '$email', '$blood_group_needed')";

        if ($conn->query($sql)) {
            $receiver_id = $conn->insert_id; // get the ID of registered receiver
            $success_msg = "✅ Receiver registered successfully!";
        } else {
            $error_msg = "❌ Error: " . $conn->error;
        }
    }

    // Step 2: Blood Request
    if (isset($_POST['blood_request'])) {
        $receiver_id = $_POST['receiver_id'];
        $blood_group = $_POST['blood_group'];
        $quantity = $_POST['quantity'];
        $city = $_POST['city'];

        $sql = "INSERT INTO blood_requests (receiver_id, blood_group, quantity, city, request_date)
                VALUES ('$receiver_id', '$blood_group', '$quantity', '$city', NOW())";

        if ($conn->query($sql)) {
            // Get the request ID
            $request_id = $conn->insert_id;
            
            // Get receiver name
            $receiver_name_query = "SELECT name FROM receivers WHERE id = $receiver_id";
            $receiver_name_result = $conn->query($receiver_name_query);
            $receiver_data = $receiver_name_result->fetch_assoc();
            $receiver_name = $receiver_data['name'] ?? 'A Receiver';
            
            // Find compatible donors and notify them
            $compatible_types = get_compatible_donors_for_blood_type($blood_group);
            $placeholders = implode("','", $compatible_types);
            
            $donors_query = "SELECT id FROM donors WHERE blood_group IN ('$placeholders')";
            $donors_result = $conn->query($donors_query);
            
            // Create notifications for all compatible donors
            if ($donors_result->num_rows > 0) {
                while ($donor_row = $donors_result->fetch_assoc()) {
                    $donor_id = $donor_row['id'];
                    $message = "🩸 A receiver needs blood type $blood_group from $receiver_name in $city";
                    $notif_sql = "INSERT INTO donor_notifications (donor_id, request_id, request_type, blood_group, requester_name, message)
                                VALUES ('$donor_id', '$request_id', 'Receiver', '$blood_group', '$receiver_name', '$message')";
                    $conn->query($notif_sql);
                }
            }
            
            $success_msg = "✅ Blood request submitted successfully! Donors have been notified.";
        } else {
            $error_msg = "❌ Error: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Register as Receiver & Request Blood</title>
<link rel="stylesheet" href="../main.css">
<style>
    body {
        padding-top: 120px;
        background: linear-gradient(135deg, #f0f9ff, #ffffff);
        font-family: 'Segoe UI', Tahoma, sans-serif;
    }
    
    .container {
        max-width: 600px;
        margin: 0 auto;
        background: white;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    h2 {
        text-align: center;
        color: #c0392b;
        margin-bottom: 30px;
    }
    
    h3 {
        color: #1f3c88;
        margin-top: 30px;
        border-bottom: 2px solid #e3f2fd;
        padding-bottom: 10px;
    }
    
    form {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    
    input, select {
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-size: 15px;
        font-family: inherit;
    }
    
    input:focus, select:focus {
        outline: none;
        border-color: #2980b9;
        box-shadow: 0 0 5px rgba(41, 128, 185, 0.3);
    }
    
    button {
        padding: 12px;
        background-color: #c0392b;
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: bold;
        cursor: pointer;
        transition: background-color 0.3s;
    }
    
    button:hover {
        background-color: #e74c3c;
    }
    
    .success-msg {
        background-color: #d4edda;
        color: #155724;
        padding: 12px;
        border-radius: 8px;
        margin-bottom: 20px;
        border: 1px solid #c3e6cb;
    }
    
    .error-msg {
        background-color: #f8d7da;
        color: #721c24;
        padding: 12px;
        border-radius: 8px;
        margin-bottom: 20px;
        border: 1px solid #f5c6cb;
    }
    
    label {
        font-weight: 600;
        color: #333;
        margin-top: 10px;
    }
</style>
</head>
<body>
    <?php include '../navbar.php'; ?>

    <div class="container">
        <h2>🩸 Blood Receiver Registration & Request</h2>

        <?php if ($success_msg != ""): ?>
            <div class="success-msg"><?php echo $success_msg; ?></div>
        <?php endif; ?>

        <?php if ($error_msg != ""): ?>
            <div class="error-msg"><?php echo $error_msg; ?></div>
        <?php endif; ?>

        <!-- Step 1: Receiver Registration -->
        <?php if ($receiver_id == 0): ?>
            <h3>📋 Step 1: Register as a Blood Receiver</h3>
            <form method="POST">
                <label for="name">Full Name *</label>
                <input type="text" id="name" name="name" placeholder="Enter your full name" required>
                
                <label for="city">City *</label>
                <input type="text" id="city" name="city" placeholder="Enter your city" required>
                
                <label for="phone">Phone Number *</label>
                <input type="text" id="phone" name="phone" placeholder="Enter phone number" required>
                
                <label for="email">Email (optional)</label>
                <input type="email" id="email" name="email" placeholder="Enter email address">
                
                <label for="blood_group_needed">Blood Group Needed *</label>
                <select id="blood_group_needed" name="blood_group_needed" required>
                    <option value="">Select Blood Group</option>
                    <option value="A+">A+ (Can receive from A+, A-, O+, O-)</option>
                    <option value="A-">A- (Can receive from A-, O-)</option>
                    <option value="B+">B+ (Can receive from B+, B-, O+, O-)</option>
                    <option value="B-">B- (Can receive from B-, O-)</option>
                    <option value="O+">O+ (Can receive from O+, O-)</option>
                    <option value="O-">O- (Universal Recipient)</option>
                    <option value="AB+">AB+ (Can receive from any blood group)</option>
                    <option value="AB-">AB- (Can receive from AB-, A-, B-, O-)</option>
                </select>
                
                <button type="submit" name="register_receiver">Register as Receiver</button>
            </form>
        <?php endif; ?>

        <!-- Step 2: Blood Request -->
        <?php if ($receiver_id != 0): ?>
            <h3>🩸 Step 2: Submit a Blood Request</h3>
            <p style="color: #666; margin-bottom: 20px;"><strong>Note:</strong> Donors matching your blood type will be notified and can choose to help you.</p>
            <form method="POST">
                <input type="hidden" name="receiver_id" value="<?php echo $receiver_id; ?>">
                
                <label for="blood_group">Blood Group Required *</label>
                <select id="blood_group" name="blood_group" required>
                    <option value="">Select Blood Group</option>
                    <option value="A+">A+</option>
                    <option value="A-">A-</option>
                    <option value="B+">B+</option>
                    <option value="B-">B-</option>
                    <option value="O+">O+</option>
                    <option value="O-">O-</option>
                    <option value="AB+">AB+</option>
                    <option value="AB-">AB-</option>
                </select>
                
                <label for="quantity">Quantity Required (Units) *</label>
                <input type="number" id="quantity" name="quantity" placeholder="Enter quantity in units" min="1" required>
                
                <label for="city">City *</label>
                <input type="text" id="city" name="city" placeholder="Enter your city" required>
                
                <button type="submit" name="blood_request">Submit Blood Request</button>
            </form>
        <?php endif; ?>
    </div>

</body>
</html>
