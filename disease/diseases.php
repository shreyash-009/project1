<?php include "db.php"; ?>
<!DOCTYPE html>
<html>
<head>
    <title>All Diseases</title>
    <link rel="stylesheet" href="../main.css">
</head>
<body>

<nav class="navbar">
    <div class="logo">
        <img src="images.png" alt="SAS Logo">
        <span>SAS Health</span>
    </div>

    <ul class="nav-links">
        <li><a href="index.php">Home</a></li>
        <li><a href="diseases.php" >Diseases</a></li>
        <li><a href="chatbot.php">Symptom Checker</a></li>
    </ul>
</nav>


<div class="container">
    <h1>All Diseases currently enlisted in the Page</h1>

    <?php
    $result = $conn->query("SELECT * FROM diseases");
    while($row = $result->fetch_assoc()){
        echo "<div class='card'>";
        echo "<h3>".$row['name']."</h3>";
        echo "<p>".$row['description']."</p>";
        echo "</div>";
    }
    ?>
</div>

</body>
</html>
