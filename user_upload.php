<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "catalyst_db";

// Create connection
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
} 
echo "Connected successfully ";

// Create database if not available 
$stmtcheck = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$dbname'";
$check = $conn->query($stmtcheck);

if($check->num_rows <= 0){
    $sql = "CREATE DATABASE $dbname";
    if ($conn->query($sql) === TRUE) {
    echo "Database created successfully ";
    } else {
    echo "Error creating database: " . $conn->error;
    }
}else{
    echo "Database already exist ";
}

$conn = new mysqli($servername, $username, $password, $dbname);

// create table
$sql = "CREATE TABLE Users (
id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
name VARCHAR(255) NOT NULL,
surname VARCHAR(255) NOT NULL,
email VARCHAR(255) UNIQUE,
reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
  echo "Table created successfully";
} else {
  echo "Error creating table: " . $conn->error;
}


// $conn->close();
?>