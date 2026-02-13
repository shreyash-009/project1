<?php
include "db.php";
header('Content-Type: application/json');

if(isset($_POST['message'])){
    $userInput = strtolower($_POST['message']);

    $symptoms = $conn->query("SELECT * FROM symptoms");
    $matchedSymptoms = [];
    while($row = $symptoms->fetch_assoc()){
        if(strpos($userInput, strtolower($row['name'])) !== false){
            $matchedSymptoms[] = $row['id'];
        }
    }

    if(count($matchedSymptoms) > 0){
        $ids = implode(",", $matchedSymptoms);
        $query = "
            SELECT d.name, COUNT(ds.symptom_id) as match_count
            FROM diseases d
            JOIN disease_symptom ds ON d.id = ds.disease_id
            WHERE ds.symptom_id IN ($ids)
            GROUP BY d.id
            ORDER BY match_count DESC
            LIMIT 5
        ";
        $result = $conn->query($query);
        $output = [];
        while($row = $result->fetch_assoc()){
            $output[] = $row;
        }
        echo json_encode($output);
    } else {
        echo json_encode([]);
    }
}
?>
