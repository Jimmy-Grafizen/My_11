<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define('SUBDIRKK', str_replace($_SERVER['DOCUMENT_ROOT'],"",dirname(__FILE__)).'/');
define('ROOT_DIRECTORYKK',$_SERVER['DOCUMENT_ROOT'] . SUBDIRKK);

$FilePath =  strstr(ROOT_DIRECTORYKK, 'admin', true)."admin";
// echo $FilePath.'/global_constants.php';
 include_once $FilePath.'/global_constants.php';

print_r(file_get_contents($FilePath.'/global_constants.php'));

$path = $_SERVER['HTTP_HOST'];

 if(isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&  $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
    $server_scheme = 'https';
 }else {
    $server_scheme = 'http';
 }


$database 	= DB_NAME;
$user 		= DB_USERNAME;
$pass 		= DB_PASSWORD;
$host 		= DB_HOST;
$filename   = 'dump-'.time().'.sql';
$dir 		= $FilePath."/uploaded/".$filename;

$download 	= ($server_scheme.'://'.$path.SUBDIR."uploaded/".$filename);



var_dump(ROOT_DIRECTORY);
if(isset($_GET['db']) && !empty($_GET['db'] )  ){
	echo "<h3>Backing up database to <code>{$dir}</code> <br> Download - $download </h3>";
	exec("mysqldump --user={$user} --password={$pass} --host={$host} {$database} --result-file={$dir} 2>&1", $output);
	var_dump($output);
}