<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'movie_ticket_db';

$conn = new mysqli($host, $user, $pass);

if ($conn->connect_error) {
    die('Connection Failed: ' . $conn->connect_error);
}

$conn->query("CREATE DATABASE IF NOT EXISTS `$db`");
$conn->select_db($db);

// Check if tables need to be created
$result = $conn->query("SHOW TABLES LIKE 'movies'");
if ($result && $result->num_rows == 0) {
    $sql_file_path = __DIR__ . '/../../movie_ticket_db.sql';
    if (file_exists($sql_file_path)) {
        $sql_script = file_get_contents($sql_file_path);
        if ($sql_script) {
            // Remove the initial DB creation and USE statements from the script to avoid errors
            $sql_script = preg_replace('/^.*-- Users Table/s', '-- Users Table', $sql_script, 1);
            
            if ($conn->multi_query($sql_script)) {
                // Wait for all queries to execute
                do {
                    if ($res = $conn->store_result()) {
                        $res->free();
                    }
                } while ($conn->more_results() && $conn->next_result());
            } else {
                die("Database table creation failed: " . $conn->error);
            }
        }
    } else {
        die("SQL file not found at: " . $sql_file_path);
    }
}

if ($conn->connect_error) {
    die('Connection Failed: ' . $conn->connect_error);
}
?> 