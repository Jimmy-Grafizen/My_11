<?php error_reporting(E_ALL);
ini_set('display_errors', 1);
$server='localhost';
$dbname='my11option1';
$charset = 'utf8mb4';
$collate = 'utf8mb4_unicode_ci';


$conn = new PDO("mysql:host=$server;dbname=$dbname;charset=$charset", 'my11option', 'mcb96J0*2', 
     array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET sql_mode=""'));

$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);