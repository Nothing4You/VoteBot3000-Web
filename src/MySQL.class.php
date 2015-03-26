<?php

/***
 * MySQL class
 *
 * basic MySQL functions
 **/

 class MySQL {

	// Declaration & Configuration
	private $host = "localhost";
	private $user = "root";
	private $password = "";
	private $database = "skypebot";
     
    public $debug = false;

	private $db;

	/***
	 * __construct()
	 * public function
	 *
	 * connects to database
	 **/
	public function __construct()
	{
		// connect with mysqli
		$this->db = new mysqli($this->host, $this->user, $this->password, $this->database);

		// check connection
		if(mysqli_connect_errno()){
			echo "MySQL connection failed: ".mysqli_connect_error();
			exit;
		}
	}


	/***
	 * Query()
	 * public function
	 *
	 * sends a query
	 * @param	string $sql 	SQL Query
	 * @return	bool			success of query
	 * @return	object			mysqli object
	 **/
	public function Query($sql)
	{
		$res = $this->db->query($sql);

		// debugging options
		if($this->debug){
            // start alert depending on success
            if($res) echo '<div class="alert success">';
            else echo '<div class="alert error">';
            		
            	// print out sql
                echo $sql."<br /><br />";
            
            	// print out error 
                if(!$res) echo $this->Error();
            echo '</div>';
        }

		return $res;
	}

	/***
	 * Escape()
	 * public function
	 *
	 * escapes a string for mysql
	 * @param 	string $string		string to escape
	 * @return	string				escaped string
	 **/
	public function Escape($string)
	{
        $string = utf8_encode($string);
		return $this->db->real_escape_string($string);
	}
     
    /***
	 * LastID()
	 * public function
	 *
	 * returns last inserted id
	 * @return int	id
	 **/
    public function LastID()
    {
        return mysqli_insert_id($this->db);
    }

	/***
	 * Error()
	 * public function
	 *
	 * returns last error
	 * @return string	error
	 **/
	public function Error()
	{
		return "MySQl Error #".mysqli_errno($this->db).": ".mysqli_error($this->db);
	}
	
	
	/***
	 * Exists()
	 * public function
	 *
	 * check if row(s) exist(s)
	 * @param 	string $table		table name
	 * @param 	array $where		where parameters in scheme: array("column"=>"value", "column2"=>"value2");
	 * @return	boolean				row exists or not
	 **/
	public function Exists($table, $where)
	{
		// Use Select() function
		$sel = $this->Select($table, $where);
		if($sel) return true;
		else return false;
	}
	 

	/***
	 * Insert()
	 * public function
	 *
	 * inserts a row
	 * @param	string $table	table name
	 * @param	array $data		data in this scheme: array("column"=>"value","column2"=>"value2")
	 * @return		boolean		success of insert query
	 **/
	public function Insert($table, $data)
	{
		// declaration
		$columns = '';
		$values = '';

		// fetch data
		foreach($data as $column=>$value){

			// read colum and escape (not really necessary but more secure)
			$columns .= '`'.$this->Escape($column).'`,';

			// read value (numeric or string) and escape (necessary!)
			if(is_numeric($value)){
				$values .= $this->Escape($value).',';
			}
			else {
				$values .= "'".$this->Escape($value)."',";
			}
		}
		// Remove ',' at end of strings
		$columns = substr($columns, 0, -1);
		$values = substr($values, 0, -1);

		// escape tablename (i know..., but its safer, ok?)
		$table = $this->Escape($table);

		// Build & perform query
		$res = $this->Query("INSERT INTO `".$table."` (".$columns.") VALUES (".$values.")");

		// Return success of query
		if($res) return true;
		else return false;
	}

	/***
	 * Update()
	 * public function
	 *
	 * @param	string $table		table name
	 * @param 	array $where		WHERE parameters in this scheme: array("column"=>"value","column2"=>"value2")
	 * @param 	array $data			data to update in this scheme: array("column"=>"value","column2"=>"value2")
	 * @param	string $connector	[optional] connector between where clauses, default: &&
	 * @return 		boolean		success of update query
	 **/
	public function Update($table, $where, $data, $connector = "&&")
	{
		// escape table
		$table = $this->Escape($table);
		
		// start building query
		$query = "UPDATE `".$this->Escape($table)."` SET ";

		// fetch data
		foreach($data as $column=>$value){

			// escape column
			$column = $this->Escape($column);

			// escape value
			$value = $this->Escape($value);

			// add data to query (numeric or string)
			if(is_numeric($value)) $query .= "`".$column."`=".$value.", ";
			else $query .= "`".$column."`='".$value."', ";
		}
		
		// remove whitespace
		$query = trim($query);
		$query = substr($query, 0, -1);
		
		// check if where is needed
		if(count($where) > 0){

			// start where part
			$query .= " WHERE ";

			// fetch where parameters
			foreach($where as $column=>$value){

				// escape column
				$column = $this->Escape($column);

				// read value and escape
				$value = $this->Escape($value);
	
				// add data to query (numeric or string)
				if(is_numeric($value)) $query .= "`".$column."`=".$value." ".$connector." ";
				else $query .= "`".$column."`='".$value."' ".$connector." ";
			}
		}

		// remove whitespace
		$query = trim($query);
		$query = substr($query, 0, -strlen($connector));
		
		// perform query
		$res = $this->Query($query);

		// return success
		if($res) return true;
		else return false;
	}

	/***
	 * Delete()
	 * public function
	 *
	 * deletes (a) row(s)
	 * @param	string $table	 	table name
	 * @param	array $where	 	where paramters in this scheme: array("column"=>"value","column2"=>"value2")
	 * @param	string $connector	[optional] connector between where clauses, default: &&
	 * @return		boolean		success of delete query
	 **/
	public function Delete($table, $where, $connector = "&&")
	{
		// escape table
		$table = $this->Escape($table);
		
		// start building query
		$query = "DELETE FROM `".$table."` ";
		
		// check if where is needed
		if(count($where) > 0){
			
			//start where
			$query .= "WHERE ";
			
			// fetch where
			foreach($where as $column=>$value){
								
				// escape column
				$column = $this->Escape($column);
				
				// escape value
				$value = $this->Escape($value);
				
				// add to query (numeric or string)
				if(is_numeric($value)) $query .= "`".$column."`=".$value." ".$connector." ";
				else $query .= "`".$column."`='".$value."' ".$connector." ";
			}
			
			// remove whitespace
			$query = trim($query);
			$query = substr($query, 0, -strlen($connector));
		}
		else {
			// remove whitespace (in case of no where)		
			$query = trim($query);
		}
		
		// perform query
		$res = $this->Query($query);

		// return success
		if($res) return true;
		else return false;
	}
	

	/***
	 * Select()
	 * public function
	 *
	 * get rows from database
	 * @param	string $table	 	table name
	 * @param	array $where	 	[optional] where paramters in this scheme: array("column"=>"value","column2"=>"value2")
	 * @param	array $columns		[optional] columns to select, if empty all columns will be returned. scheme: array("column1","column2")
	 * @param	string $connector	[optional] connector between where clauses, default: &&
	 * @param 	string $addsql		[optional] add additional sql to query (WARNING: YOU NEED TO ESCAPE ON YOUR OWN)
	 * @return	boolan				success of select query
	 * @return	array				result of query
	 **/
	public function Select($table, $where = array(), $columns = array(), $connector = "&&", $addsql = "")
	{
		// escape table
		$table = $this->Escape($table);
		
		// start building query
		$query = "SELECT ";
		
		// check if columns are given
		if(count($columns)>0){
			
			// fetch columns
			foreach($columns as $column){
				
				// escape column
				$column = $this->Escape($column);
				
				// add column to string
				$query .= "`".$column."`, ";
			}
			
			//remove whitespace
			$query = substr($query, 0, -2);
			
		}
		else {
			// select all columns
			$query .= "*";
		}

		// continue query
		$query .= " FROM `".$table."`";
		
		// check if where is given
		if(count($where) > 0){
			
			// start where query
			$query .= " WHERE ";
			
			// fetch where
			foreach($where as $column=>$value){
								
				// escape column
				$column = $this->Escape($column);
				
				// escape value
				$value = $this->Escape($value);
				
				// add to query (numeric or string)
				if(is_numeric($value)) $query .= "`".$column."`=".$value." ".$connector." ";
				else $query .= "`".$column."`='".$value."' ".$connector." ";
			}
			
			// remove whitespace
			$query = trim($query);
			$query = substr($query, 0, -strlen($connector));
		}
		
        // check for additional sql
        if($addsql != ""){
            // add additional sql to query
            $query = $query . " " . $addsql;
        }
        
        
		// Execute query
		$res = $this->Query($query);
		
		// check if query failed
		if(!$res) return false;
		else {
			// fetch on success
			$return = array();
			while($row = $res->fetch_assoc()){
				$return[] = $row;
			}
			if(count($return)==0) return false;
			else return $return;
		}
	}
	
	


 }




?>