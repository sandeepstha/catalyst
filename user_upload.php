<?php

$options = getopt("h:p:u:", ["file:","create_table","dry_run","help"]);

if(isset($options['file']) && !array_key_exists("dry_run",$options))
{
    list($hostname, $username, $password, $status) = checkConnection($options);

    if($status)
    {
        $conn = new mysqli($hostname, $username, $password, 'catalyst_db');
        createTable($conn);
        if(fileParser($options['file'], $conn,'insert'))
        {
            echo "File parsed and updated to the database successfully"."\n";
        }

    }
    else{
        echo 'Database Connection Failed. Enter valid connection details'."\n";
    }
    
}

if(array_key_exists("create_table",$options))
{
    list($hostname, $username, $password, $status) = checkConnection($options);

    if($status)
    {
        $conn = new mysqli($hostname, $username, $password, 'catalyst_db');
        createTable($conn);

    }
    else{
        echo 'Database Connection Failed. Enter valid connection details'."\n";
    }
}

if(array_key_exists("help",$options))
{
    echo"
    --file='csv file name' - this is the name of the CSV to be parsed \n
    --create_table - this will cause the MySQL users table to be built (and no further action will be taken)\n
    --dry_run - this will be used with the --file directive in case we want to run the script but not \n
    insert into the DB. All other functions will be executed, but the database won't be altered \n
    -u -     MySQL username \n
    -p -     MySQL password \n
    -h -     MySQL host \n
    --help - which will output the above list of directives with details.\n";
}

if(array_key_exists("dry_run",$options))
{
    if(isset($options['file']))
    {
        list($hostname, $username, $password, $status) = checkConnection($options);

        if($status)
        {
            $conn = new mysqli($hostname, $username, $password, 'catalyst_db');
            createTable($conn);
            if(fileParser($options['file'], $conn, ''))
            {
                echo "Dry run successfull"."\n";
            }
            else{
                echo 'Dry run unsuccessfull'."\n";
            }
        }
        else
        {
            echo 'Database Connection Failed. Enter valid connection details'."\n";
        }
    }
    else{echo 'Please add --file argument with its filename as well.'."\n";}
}

function checkConnection($options){
    if(isset($options["h"]) && isset($options["u"]) && isset($options["p"]))
    {
    // $filename = $options["file"];
    $hostname = $options["h"];
    $username = $options["u"];
    $password = $options["p"];

    $status = dbconnection($hostname, $username, $password);
    return array($hostname, $username, $password, $status);
    }
    else{
        echo 'Please provide appropriate connection arguments. i.e. -h, -u, -p'."\n";
        return false;
    }
}

function dbconnection($hostname, $username, $password)
{
    // Create connection
    try
    {
        $createconn = new mysqli($hostname, $username, $password);
        // Create database if not available 
        $stmtcheck = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = 'catalyst_db'";
        $check = $createconn->query($stmtcheck);

        if($check->num_rows <= 0){
            $sql = "CREATE DATABASE catalyst_db";
            if ($createconn->query($sql) === TRUE) {
            echo "Database created successfully "."\n";
            } 
            else {
            echo $createconn->error."\n";
            }
        }
        else{
            echo "Database already exists "."\n";
        }
        return true;
    }
    catch(Exception $e) {
        echo 'ERROR...';
        return false;
    }
}

function createTable($conn)
{
  // create table
  $sql = "CREATE TABLE Users (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
    name VARCHAR(255) NOT NULL,
    surname VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE,
    reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
  )";
  
  if ($conn->query($sql) === TRUE) {
    echo "Table created successfully."."\n";
  } else {
    echo $conn->error."\n";
  }
}

function fileParser($filename, $conn, $flag)
{
    // open CSV file with read-only mode
    $csv_file = fopen($filename, 'r');
    
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
        
        if(!filter_var($email, FILTER_VALIDATE_EMAIL))
        {
            echo 'Invalid Email Format '.$email."\n";
        }
        else
        {
            // If user already exists in the database with the same email
            $email_check = $conn->query("SELECT id FROM users WHERE email = '" . $email . "'");

            if($flag=='insert')
            {
                if ($email_check->num_rows > 0)
                {
                    echo $email.' already exists in the database'."\n";
                    $conn->query("UPDATE users SET name = '" . $name . "', surname = '" . $surname . "', WHERE email = '" . $email . "'");
                }
                else
                {
                    $conn->query("INSERT INTO users (name, surname, email) VALUES ('" . $name . "', '" . $surname . "', '" . $email . "')");

                }
            }
        }

    }
    // Close csv file
    fclose($csv_file);
    return True;
}

?>