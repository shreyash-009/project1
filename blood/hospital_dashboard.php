<?php
session_start();

// Check if user is logged in and is a hospital user
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'hospital') {
    header("Location: ../homepage.php");
    exit();
}

include 'db.php';

// Assuming hospital is identified by ID (e.g., via session or GET param)
$hospital_id = $_GET['id'] ?? 1; // Replace with session logic in real app

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $blood_group = $_POST['blood_group'];
    $quantity = $_POST['quantity'];
    $city = $_POST['city'];

    $sql = "INSERT INTO requests (hospital_id, blood_group, quantity, city)
            VALUES ('$hospital_id', '$blood_group', '$quantity', '$city')";

    if ($conn->query($sql)) {
        echo "<p style='text-align:center;color:green;font-weight:bold;'>Emergency blood request submitted successfully!</p>";
    } else {
        echo "Error: " . $conn->error;
    }
}

// Fetch hospital info
$hospital = $conn->query("SELECT * FROM hospitals WHERE id='$hospital_id'")->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Hospital Dashboard</title>
<link rel="stylesheet" href="../main.css">
</head>
<body>
    <?php include '../navbar.php'; ?>

<h2 style="text-align:center;margin-top:30px;">Welcome, <?php echo $hospital['name']; ?></h2>

<form method="POST">
    <h3 style="text-align:center;">Emergency Blood Request</h3>
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
    <input type="text" name="city" placeholder="City" value="<?php echo $hospital['city']; ?>" required>
    <button type="submit">Submit Request</button>
</form>

</body>
</html>


<!-- Mobile menu script -->
<script>
const toggle = document.querySelector('.menu-toggle');
const menu = document.querySelector('nav ul');
toggle.addEventListener('click', () => {
    menu.classList.toggle('show');
});
</script>

<script>
const toggle = document.querySelector('.menu-toggle');
const menu = document.querySelector('nav ul');
toggle.addEventListener('click', () => {
    menu.classList.toggle('show');
});
</script>
