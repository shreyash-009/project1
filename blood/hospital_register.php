<?php
session_start();

// Check if user is logged in and is a hospital user
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'hospital') {
    header("Location: ../homepage.php");
    exit();
}

include 'db.php';

// Create notification table
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

// Track the registered hospital ID
$hospital_id = 0;
$success_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Step 1: Register Hospital
    if (isset($_POST['register_hospital'])) {
        $name = $_POST['name'];
        $city = $_POST['city'];
        $phone = $_POST['phone'];
        $email = $_POST['email'];

        $sql = "INSERT INTO hospitals (name, city, phone, email)
                VALUES ('$name', '$city', '$phone', '$email')";

        if ($conn->query($sql)) {
            $hospital_id = $conn->insert_id; // get the ID of registered hospital
            $success_msg = "Hospital registered successfully!";
        } else {
            die("Error: " . $conn->error);
        }
    }

    // Step 2: Emergency Blood Request
    if (isset($_POST['emergency_request'])) {
        $hospital_id = $_POST['hospital_id'];
        $blood_group = $_POST['blood_group'];
        $quantity = $_POST['quantity'];
        $city = $_POST['city'];

        $sql = "INSERT INTO requests (hospital_id, blood_group, quantity, city, request_date)
                VALUES ('$hospital_id', '$blood_group', '$quantity', '$city', NOW())";

        if ($conn->query($sql)) {
            // Get the request ID
            $request_id = $conn->insert_id;
            
            // Get hospital name
            $hospital_name_query = "SELECT name FROM hospitals WHERE id = $hospital_id";
            $hospital_name_result = $conn->query($hospital_name_query);
            $hospital_data = $hospital_name_result->fetch_assoc();
            $hospital_name = $hospital_data['name'] ?? 'A Hospital';
            
            // Find compatible donors and notify them
            $compatible_types = get_compatible_donors_for_blood_type($blood_group);
            $placeholders = implode("','", $compatible_types);
            
            $donors_query = "SELECT id FROM donors WHERE blood_group IN ('$placeholders')";
            $donors_result = $conn->query($donors_query);
            
            // Create notifications for all compatible donors
            if ($donors_result && $donors_result->num_rows > 0) {
                while ($donor_row = $donors_result->fetch_assoc()) {
                    $donor_id = $donor_row['id'];
                    $message = "🏥 EMERGENCY: Hospital " . $hospital_name . " needs blood type $blood_group in $city";
                    $notif_sql = "INSERT INTO donor_notifications (donor_id, request_id, request_type, blood_group, requester_name, message)
                                VALUES ('$donor_id', '$request_id', 'Hospital', '$blood_group', '$hospital_name', '$message')";
                    $conn->query($notif_sql);
                }
            }
            
            $success_msg = "Emergency blood request submitted successfully! Donors have been notified.";
        } else {
            die("Error: " . $conn->error);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Register Hospital & Emergency Request</title>
<link rel="stylesheet" href="../main.css">
</head>
<body>
    <?php include '../navbar.php'; ?>

<h2 style="text-align:center;margin-top:30px;">Hospital Registration & Emergency Blood Request</h2>

<?php if ($success_msg != ""): ?>
    <p style="text-align:center;color:green;font-weight:bold;"><?php echo $success_msg; ?></p>
<?php endif; ?>

<!-- Step 1: Hospital Registration -->
<?php if ($hospital_id == 0): ?>
<form method="POST">
    <input type="text" name="name" placeholder="Hospital Name" required>
    <input type="text" name="city" placeholder="City" required>
    <input type="text" name="phone" placeholder="Phone" required>
    <input type="email" name="email" placeholder="Email (optional)">
    <button type="submit" name="register_hospital">Register Hospital</button>
</form>
<?php endif; ?>

<!-- Step 2: Emergency Blood Request -->
<?php if ($hospital_id != 0): ?>
<form method="POST" style="margin-top:40px;">
    <h3 style="text-align:center;">Submit Emergency Blood Request</h3>
    <input type="hidden" name="hospital_id" value="<?php echo $hospital_id; ?>">
    <select name="blood_group" required>
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
    <input type="number" name="quantity" placeholder="Quantity (Units)" min="1" required>
    <input type="text" name="city" placeholder="City" value="<?php echo $_POST['city'] ?? ''; ?>" required>
    <button type="submit" name="emergency_request">Submit Request</button>
</form>
<?php endif; ?>

</body>
</html>

</body>
</html>
