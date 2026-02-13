<?php 
session_start();

// Check if user is logged in and is a regular user
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'donor') {
    header("Location: homepage.php");
    exit();
}

include "disease/db.php"; 
?>
<!DOCTYPE html>
<html>
<head>
    <title>Disease Awareness</title>
    <link rel="stylesheet" href="main.css">
</head>
<body>
    <?php include 'navbar.php'; ?>


<div class="container">
    <h1>Welcome to Disease Awareness Portal</h1>
<p>This Disease Awareness Portal is designed to educate users about common and serious health conditions. 
You can explore detailed information about various diseases including their causes, symptoms, prevention methods, 
and general treatment options. Our interactive Symptom Checker allows you to enter your symptoms and receive 
a list of possible conditions based on medical symptom matching. <br><br>

Please note that this platform is for educational purposes only and does not replace professional medical advice. 
Always consult a qualified healthcare provider for proper diagnosis and treatment.
</p>




    <h2 style="
      font-size: 28px;
      color: #000000;
      margin-bottom: 20px;
      text-align: le;
    ">
      Disease Awareness
    </h2>

    <p style="
      font-size: 16px;
      line-height: 1.8;
      color: #444;
      text-align: justify;
      margin-bottom: 15px;
    ">
      Disease awareness plays a crucial role in protecting individuals and communities from serious
      health risks. Many diseases spread due to lack of information about their causes, symptoms,
      and preventive measures. When people are unaware of early warning signs, illnesses often go
      untreated and spread more rapidly within the community.
    </p>

    <p style="
      font-size: 16px;
      line-height: 1.8;
      color: #444;
      text-align: justify;
    ">
      Promoting awareness about hygiene, sanitation, vaccination, and timely medical care helps reduce
      the spread of infectious diseases. Educating people about preventive practices empowers them to
      make informed health decisions, seek early treatment, and contribute to building a healthier,
      safer society.
    </p>
 


</div>
<div style="text-align: right;">
  <a href="disease/chatbot.php"
     style="
       display: inline-block;
       margin-top: 25px;
       padding: 14px 36px;
       background: linear-gradient(135deg, #1f3c88, #3f72ff);
       color: #ffffff;
       text-decoration: none;
       font-size: 16px;
       font-weight: 600;
       border-radius: 30px;
       box-shadow: 0 6px 16px rgba(63, 114, 255, 0.35);
       transition: transform 0.3s ease, box-shadow 0.3s ease;
     ">
     Check Symptoms
  </a>
</div>

</body>
</html>
