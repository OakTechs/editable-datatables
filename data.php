<?php
$mysqli = new mysqli("localhost", "root", "", "pos");

if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: " . $mysqli->connect_error;
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $result = $mysqli->query("SELECT * FROM employees where is_deleted = 0");
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode(['data' => $data]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mode = $_POST['mode'] ?? 'edit';
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

    if ($mode === 'delete') {
        if ($id > 0) {
            $mysqli->query("UPDATE employees SET is_deleted = 1 WHERE id = $id");
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "message" => "Invalid ID for delete."]);
        }
        exit;
    }
    
    if (isset($_POST['first_name']) && isset($_POST['last_name']) && isset($_POST['position']) && isset($_POST['office']) && isset($_POST['start_date']) && isset($_POST['salary'])) {
        // Full row edit
        $first_name = $mysqli->real_escape_string($_POST['first_name']);
        $last_name = $mysqli->real_escape_string($_POST['last_name']);
        $position = $mysqli->real_escape_string($_POST['position']);
        $office = $mysqli->real_escape_string($_POST['office']);
        $start_date = $mysqli->real_escape_string($_POST['start_date']);
        $salary = $mysqli->real_escape_string($_POST['salary']);

        if ($mode === 'add') {
            // Insert new record
            $mysqli->query("INSERT INTO employees (first_name, last_name, position, office, start_date, salary) 
                VALUES ('$first_name', '$last_name', '$position', '$office', '$start_date', '$salary')");
        } else {
            $mysqli->query("UPDATE employees SET 
                first_name='$first_name', 
                last_name='$last_name', 
                position='$position', 
                office='$office', 
                start_date='$start_date', 
                salary='$salary' 
                WHERE id=$id
            ");
        } 
        echo json_encode(["success" => true]);
    } else {
        // Bubble editing (single field)
        foreach ($_POST as $column => $value) {
            if ($column != 'id') {
                $safe_column = $mysqli->real_escape_string($column);
                $safe_value = $mysqli->real_escape_string($value);
                $mysqli->query("UPDATE employees SET `$safe_column` = '$safe_value' WHERE id = $id");
            }
        }
    }

    echo json_encode(["success" => true]);
}
?>