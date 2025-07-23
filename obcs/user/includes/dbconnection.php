<?php 
// DB credentials.
// define('DB_HOST','localhost');
define('DB_HOST','obcsdb.c3iw8eosk08q.ap-south-1.rds.amazonaws.com');
// define('DB_USER','root');
define('DB_USER','admin');
// define('DB_PASS','');
define('DB_PASS','Admin#2417');
define('DB_NAME','obcsdb');
// Establish database connection.
try
{
$dbh = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME,DB_USER, DB_PASS,array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
}
catch (PDOException $e)
{
exit("Error: " . $e->getMessage());
}
?>
