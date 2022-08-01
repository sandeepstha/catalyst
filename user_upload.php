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
// open CSV file with read-only mode
$csv_file = fopen("users.csv", 'r');
 
// Skip the first line
fgetcsv($csv_file);

// Parse data from CSV file line by line
while (($getData = fgetcsv($csv_file, 10000, ",")) !== FALSE)
{
    // Get row data
    $name = ucfirst(strtolower(preg_replace('/[^A-Za-z]/', '',$getData[0])));
    $surname =ucfirst(strtolower(preg_replace('/[^A-Za-z\']/', '',$getData[1])));
    $email = strtolower(preg_replace('/[^A-Za-z0-9\@\.]/', '',$getData[2]));
    $surname = str_replace("'", "\'", $surname);

    // If user already exists in the database with the same email
    $email_check = $conn->query("SELECT id FROM users WHERE email = '" . $getData[2] . "'");

    if ($email_check->num_rows > 0)
    {
        $conn->query("UPDATE users SET name = '" . $name . "', surname = '" . $surname . "', WHERE email = '" . $email . "'");
    }
    else
    {
         $conn->query("INSERT INTO users (name, surname, email) VALUES ('" . $name . "', '" . $surname . "', '" . $email . "')");

    }
}

// Close csv file
fclose($csv_file);

?>