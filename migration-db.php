<?php

$host = 'localhost';
$port = 3306;
$dbname = 'glpitelemetry';
$dbuser = 'postgres';
$dbpassword = 'Vokuro0106!';

//connection to Postgres database
$dsn = "pgsql:host=$host;port=5432;dbname=$dbname;user=$dbuser;password=$dbpassword";

try {
    $conn = new PDO($dsn);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if($conn){
        echo "connexion ok ";
    }
} catch (PDOException $e){
    echo $e->getMessage();
 }

//Set query to retreive data, and manage data
try {

$sql = "SELECT * FROM reference LIMIT 5";
$stmt = $conn->prepare($sql);
$stmt->execute();
$stmt->setFetchMode(PDO::FETCH_ASSOC);
$result = $stmt->fetchAll();

echo "data retreived ok ";
//print_r($result);

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}

$conn = null;

