<?php
/**
 * Handling database connection
 *
 * @author Ravi Tamada
 */

class DbConnect {

    private $conn;

    function __construct() {
        
    }

    function connect() {
        require_once  '../../../global_constants.php';

        
        try {
            $server=DB_HOST;
            $dbname=DB_NAME;
            $charset = 'utf8mb4';
            $collate = 'utf8mb4_unicode_ci';

            $this->conn = new PDO("mysql:host=$server;dbname=$dbname;charset=$charset", DB_USERNAME, DB_PASSWORD,array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET sql_mode=""'));

            // set the PDO error mode to exception
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            //$this->conn->setAttribute(PDO::MYSQL_ATTR_INIT_COMMAND, "SET NAMES $charset COLLATE $collate");
            return $this->conn;
            //echo "Connected successfully";
        }
        catch(PDOException $e)
        {
            echo DB_HOST;
            echo "Connection failed: " . $e->getMessage();
            die;
        }

    }
}

?>
