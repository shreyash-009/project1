<?php
session_start();

// Check if user is logged in and is a hospital user
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'hospital') {
    header("Location: ../homepage.php");
    exit();
}

include 'db.php';

$blood_group = $city = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $blood_group = $_POST['blood_group'];
    $sql = "SELECT * FROM donors WHERE blood_group='$blood_group' AND city='$city'";
    $result = $conn->query($sql);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Search Donor</title>
<link rel="stylesheet" href="../main.css">
</head>
<body>
    <?php include '../navbar.php'; ?>

<h2 style="text-align:center;margin-top:30px;">Search Blood Donors</h2>

<form method="POST">
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
    <button type="submit">Search Donors</button>
</form>

<?php
if(isset($_POST['blood_group'])) {
    $blood_group = $_POST['blood_group'];
    $sql = "SELECT name, age, blood_group, phone, last_donation 
            FROM donors 
            WHERE blood_group='$blood_group'";
    $result = $conn->query($sql);

    if($result->num_rows > 0) {
        echo "<table>
                <tr>
                    <th>Name</th>
                    <th>Age</th>
                    <th>Blood Group</th>
                    <th>Phone</th>
                    <th>Last Donation</th>
                </tr>";

        while($row = $result->fetch_assoc()) {
            // Highlight rare blood groups
            $bg_color = in_array($row['blood_group'], ['AB-', 'O-']) ? 'style=\"background-color:#f9d6d5;\"' : '';
            
            echo "<tr $bg_color>
                    <td>".$row['name']."</td>
                    <td>".$row['age']."</td>
                    <td>".$row['blood_group']."</td>
                    <td>".$row['phone']."</td>
                    <td>".$row['last_donation']."</td>
                  </tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='text-align:center;margin-top:20px;color:#c0392b;font-weight:bold;'>No donors found.</p>";
    }
}
?>

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
