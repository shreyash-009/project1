<?php
header('Content-Type: application/json');

if(isset($_POST['message'])){
    $symptom = trim($_POST['message']);
    
    // ======= CONFIGURE YOUR API KEYS HERE =======
    $app_id = 'YOUR_APP_ID';
    $app_key = 'YOUR_APP_KEY';
    
    // ======= Prepare API data =======
    $data = [
        "sex" => "male",   // default, can be dynamic
        "age" => 25,       // default, can be dynamic
        "evidence" => [
            ["id" => $symptom, "choice_id" => "present"]
        ]
    ];

    $ch = curl_init("https://api.infermedica.com/v3/diagnosis");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "App-Id: $app_id",
        "App-Key: $app_key"
    ]);

    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if($error){
        echo json_encode(["error" => "API request failed"]);
    } else {
        echo $response;
    }
} else {
    echo json_encode(["error" => "No symptom provided"]);
}
