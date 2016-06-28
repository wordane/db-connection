<?php
/*
 * connect to a MySQL (InnoDB) database and query for all
 * records with the following fields: (name, age, job_title) from a table called 'exads_test'.
 * Write a sanitised record to the same table.
*/

/*
* Mysql database class - only one connection alowed
*/
class Database {
	private $_connection;
	private static $_instance; //The single instance
	private $_host = "localhost";
	private $_username = "root";
	private $_password = "";
	private $_database = "exads";
	/*
	Get an instance of the Database
	@return Instance
	*/
	public static function getInstance() {
		if(!self::$_instance) { // If no instance then make one
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	// Constructor
	private function __construct() {
		$this->_connection = new mysqli($this->_host, $this->_username, $this->_password, $this->_database);
	
		// Error handling
		if(mysqli_connect_error()) {
			trigger_error("Failed to conencto to MySQL: " . mysqli_connect_error(),E_USER_ERROR);
		}
	}
	// Magic method clone is empty to prevent duplication of connection
	private function __clone() { }
	// Get mysqli connection
	public function getConnection() {
		return $this->_connection;
	}
    //closing the connection
    public function destruct() {
        $this->_connection->close();
    }
}

$db = Database::getInstance();
$mysqli = $db->getConnection(); 

// Creating
$sql_query = "CREATE TABLE IF NOT EXISTS `exads_test` (".
    "`name` varchar(255) NOT NULL DEFAULT '',".
    "`age` int(2) UNSIGNED NOT NULL DEFAULT 0,".
    "`job_title` varchar(255) NOT NULL DEFAULT ''".
    ");";

// Running the query
if ($mysqli->query($sql_query)) {
    echo "Table created.\n", $mysqli->affected_rows;
} else {
    echo "Error \n" . mysqli_error($db);
}

// Insert Query Sanitized Parameters 
$name = $mysqli->real_escape_string("John's Doe");
$age = 28;
$job_title = $mysqli->real_escape_string("Developer's role");

$insert_query = "INSERT INTO exads_test VALUES ('$name', $age, '$job_title')";

// Executing the Query
if ($mysqli->query($insert_query)) {
    printf("%d Row inserted.\n", $mysqli->affected_rows);
} else {
    echo "Error \n" . mysqli_error($db);
}

$sql_fetch  =   "SELECT name, age, job_title from `exads`.`exads_test`;";
/* 
 * Executing the Query and fecthing the result in 
 * numeric array
 * Associative array
 * Both types
 */
if ($result = $mysqli->query($sql_fetch)) {
    
    /*
     * Checking if the mysqlnd driver is installed on the server or not
     * Mysqlnd is required for fetch_all
     */ 
    $mysqlnd = function_exists('mysqli_fetch_all');
    
    if ($mysqlnd) {
        echo '\n mysqlnd enabled!';
        /* numeric array
         * Also use: $row = $result->fetch_all(MYSQLI_ASSOC)
         * OR use : $row = $result->fetch_all(MYSQLI_BOTH)
         */ 
        $rows = $result->fetch_all(MYSQLI_NUM);
        foreach( $rows as $row ){
          foreach( $row as $value ){
              echo $value . "\t";  
          }
            printf ("\n");
        }
    } else {
        printf ("mysqlnd disabled \n");
        
        printf ("Trying as an numeric array \n ");
        while($row = $result->fetch_array())
        printf ("%s (%s) (%s)\n", $row[0], $row[1], $row[2]);
        
        // Optionally fetch each row as an associative array
        while($row = $result->fetch_assoc())
        printf ("Trying as an Associative array \n %s (%s) (%s)\n", $row["name"], $row["age"], $row["job_title"]);

        // Optionally fetch each row as an associative and numeric array
        while($row = $result->fetch_array(MYSQLI_BOTH))
        printf ("Trying both as numeric and associative array \n %s (%s) (%s)\n", $row[0], $row["age"], $row[2]);
    }
    
    
} else {
    echo "Error \n" . mysqli_error($db);
}

// Closing DB connection
$mysqli = $db->destruct(); 

?>