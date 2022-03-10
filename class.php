<?php
class DataExchange {
	
	const JSON = "JSON";
	const XML = "XML";
	const SUCCESS = "success";
	const FAILURE = "failure";
	
	private $request_content_type;
	private $response_content_type;
	
	private $err;
	
	public function __construct() {
		
		// Did they send JSON or XML
		if (isset($_SERVER['CONTENT_TYPE'])) {
		
			if (fnmatch("application/json*", $_SERVER['CONTENT_TYPE']) == 1) {

				$this->request_content_type = self::JSON;
			
			} else if (fnmatch("application/xml*", $_SERVER['CONTENT_TYPE']) == 1) {
			
				$this->request_content_type = self::XML;
			} else {
				$this->err = "Unsupported Content-Type";
			}
			
		} else {
			$this->err = "Content-Type not set";
			
		}
		
		
		// Do they want JSON or XML back?
		if (isset($_SERVER['HTTP_ACCEPT'])) {
		
			if (fnmatch("application/json*", $_SERVER['HTTP_ACCEPT']) == 1) {

				$this->response_content_type = self::JSON;
			
			} else if (fnmatch("application/xml*", $_SERVER['HTTP_ACCEPT']) == 1) {
			
				$this->response_content_type = self::XML;
			
			} else {
				
				$this->err = "Unsupported Accept Header";
				
			}
		
		} else {
			
			$this->response_content_type = self::JSON;

		}
		
		return;
	}
	
	
	public function setError($error) {
		$this->err = $error;
		return;
	}
	
	
	public function convertRequestDataToObject() {
		
		if (file_get_contents('php://input') == "") {
			$this->err = "Request Body Empty";
			return false;
		}
		
		switch ($this->request_content_type) {
			
			case self::JSON:
					
					$data = json_decode(file_get_contents('php://input'));
					
					if (json_last_error() != JSON_ERROR_NONE) { 
						$this->err = "JSON malformed";
					}
					
					break;
			
			case self::XML:
				// XML is currently not supported
				$data = xmlrpc_decode(file_get_contents('php://input'));
				
				if (xmlrpc_is_fault($data) == false ) {
					$this->err = "XML malformed";
				}
				
				break;
		}

		return $data;
	}
		
	
	public function respond($data = null) {

		// Check to see if err was set internally or with setError
		if (isset($this->err)) {
			header('Content-Type: application/json; charset=UTF-8');
			$response = json_encode(array('status' => self::FAILURE, 'data' => $this->err));
		} else {
            if ($data != null) {
                // Configure json/xml response with $data
                switch ($this->response_content_type) {
                
                    case self::JSON:
                    
                        header('Content-Type: application/json; charset=UTF-8');
                        $response = json_encode(array('status' => self::SUCCESS, 'data' => $data));
                        break;
                    
                    case self::XML:
                        // XML is currently not supported
                        header('Content-Type: application/xml; charset=UTF-8');
                        $response = xmlrpc_encode($data);
                        break;				
                }
            } else {
                return false;
            }
        }
		// Respond
		return $response;
    }
}
class DatabaseConnection {

	private $hostname;
	private $database;
	private $username;
	private $password;
	
	public $connection;
	
	// Create database connection.
	// Takes a ini file as the only param
	// Must include, hostname, database, username, and password
	public function __construct($ini) {
		
		$dbinfo = parse_ini_file($ini);
				
		$this->hostname = $dbinfo['hostname'];
		$this->database = $dbinfo['database'];
		
		// Handling Windows Authentication
		if (($dbinfo['username'] == '') && ($dbinfo['password'] == '')) {
			$this->username = NULL;
			$this->password = NULL;
		} else {
			// SQL Authentication
			$this->username = $dbinfo['username'];
			$this->password = $dbinfo['password'];
		}
		
		try {
			
			$this->connection = new PDO("sqlsrv:server=$this->hostname;Database = $this->database", $this->username, $this->password);
		
			$this->connection->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			
		} catch (Exception $e) {
			echo $e->getMessage();
		}
		
		return;
	}
	
	// Automatically disconnects from the database when the connection is not longer being used
	public function __destruct() {		
		if (isset($this->connection)) {	
			$this->connection = null;
		}
		return;
	}

}
?>