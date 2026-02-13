<?php
session_start();

// Check if user is logged in and is a regular user
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
} elseif ($_SESSION['user_role'] !== 'donor') {
    header("Location: ../homepage.php");
    exit();
}

include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $age = $_POST['age'];
    $blood_group = $_POST['blood_group'];
    $phone = $_POST['phone'];
    $city = $_POST['city'];
    $last_donation = $_POST['last_donation'];

    $sql = "INSERT INTO donors (name, age, blood_group, phone, city, last_donation)
            VALUES ('$name', '$age', '$blood_group', '$phone', '$city', '$last_donation')";

    if ($conn->query($sql)) {
        // Store donor_id and blood_group in session
        $_SESSION['donor_id'] = $conn->insert_id;
        $_SESSION['blood_group'] = $blood_group;
        echo "<p style='text-align:center;color:green;font-weight:bold;'>Donor registered successfully! <a href='index.php'>Go Home</a></p>";
    } else {
        echo "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Register Donor</title>
<link rel="stylesheet" href="../main.css">
</head>
<body>
    <?php include '../navbar.php'; ?>

<h2 style="text-align:center;margin-top:30px;">Register as Blood Donor</h2>
<form method="POST">
    <input type="text" name="name" placeholder="Full Name" required>
    <input type="number" name="age" placeholder="Age" required>
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
    <input type="text" name="phone" placeholder="Phone" required>
    <input type="text" name="city" placeholder="City" required>
    <label>Last Donation Date (optional):</label>
    <input type="date" name="last_donation">
    <button type="submit">Register</button>
</form>
</body>
</html>

<!-- Navbar -->
<nav><!-- Old logo -->
<!-- <div class="logo">BloodDonor</div> -->

<nav class="navbar">
</body>
</html>
