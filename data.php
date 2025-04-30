<?php
$mysqli = new mysqli("localhost", "root", "", "pos");

if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: " . $mysqli->connect_error;
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $result = $mysqli->query("SELECT * FROM employees");
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode(['data' => $data]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $first_name = $mysqli->real_escape_string($_POST['first_name']);
    $last_name = $mysqli->real_escape_string($_POST['last_name']);
    $position = $mysqli->real_escape_string($_POST['position']);
    $office = $mysqli->real_escape_string($_POST['office']);
    $start_date = $mysqli->real_escape_string($_POST['start_date']);
    $salary = $mysqli->real_escape_string($_POST['salary']);

    if ($id > 0) {
        $mysqli->query("UPDATE employees SET 
            first_name='$first_name', 
            last_name='$last_name', 
            position='$position', 
            office='$office', 
            start_date='$start_date', 
            salary='$salary' 
            WHERE id=$id
        ");
    } else {
        $mysqli->query("INSERT INTO employees (first_name, last_name, position, office, start_date, salary) 
            VALUES ('$first_name', '$last_name', '$position', '$office', '$start_date', '$salary')");
    }

    echo json_encode(["success" => true]);
}
?>
