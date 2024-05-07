<?php
 
/**
 * Handling database connection
 *
 * @author Ravi Tamada
 */
class DbConnect
{
    private $conn;
 
    function __construct()
	{
		
    }
 
 	function connect() {
        require_once dirname(__FILE__) . '/../../../../global_constants.php';
        
        try {
            $server=DB_HOST;
            $dbname=DB_NAME;
            $this->conn = new PDO("mysql:host=$server;dbname=$dbname;charset=utf8", DB_USERNAME, DB_PASSWORD);
            return $this->conn;
			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            echo DB_HOST;
            echo "Connection failed: " . $e->getMessage();
            die;
        }
    }
}
?>
