<?php
/*
Written by Aziz S. Hussain
@
www.AzizSaleh.com
Produced under LGPL license
@
http://www.gnu.org/licenses/lgpl.html


# Class structure
class database ()
{
	protected databaseName, databaseHost, databaseUser, databasePassword
	protected connectionLink, mysqlError
	protected lastInsertID
	
	resource query(sql as string, arrayReplace as array)
									# Executes mysql_query and stores lastInsertID, if any
									# If arrayReplace is set, it will search sql for any question marks
									# and replace them with matching index of arrayReplace
									
	array result(query as (string|object),isObject as boolean = false)
									# Querys the (query string|query object) and returns array or object result
									
	(array|string) cleanInput(array as (array|string)
									# Executes mysql_real_escape_string on the (array|string)
									# (array|string) also is escaped for inserting/adding using the ''

	(number|string) escape(fieldValue as (number|string), isNumber as boolean = false)
									# Execute mysql_real_escape_string if isNumber is true
									# Runs preg_replace instead
									
	void showError(string theError)	# Outputs MySQL Error along with an end of line
	
	string getPrimaryField(string tableName)
									# Returns the primary field name of the specified table
									
	resource delete(string tableName,(string|array) keyDelete,string keyField = NULL)
									# This function is overloaded in three ways as:
	resource delete(string tableName,string keyDelete)
									# Will delete record value keyDelete using primary field
	resource delete(string tableName,string keyDelete,string keyField)
									# Will delete record value keyDelete using keyField field
	resource delete(string tableName,array keyDelete)
									# Will delete record based on multiple fieldname with associated fieldvalues
									
	resource insert(string tableName,array ARRAY_VARS)
									# This function will insert or update values of tableName from an array 
									# Like POST, GET, SESSION,...
									# The way this works, is that it retrieves a lits of fields for that table and checks
									# Them against the ARRAY_VARS. If the primary key exist in ARRAY_VARS, then it updates,
									# Otherwise it does an insert to the table
	
	string (mysql query) findMatches(string tableName,array ARRAY_VARS)	
									# Find relationship between tablename and ARRAY_VARS, create MySQL statement
									
	array (resource,string tablename) createTable(array ARRAY_VARS, string tableName = NULL)
									# Create a database table based on ARRAY_VARS, if tableName is NULL a random string is
									# Generated to use for the table name
									
	void close()					# Closes the database connection
	
	string generateRandom(integer strLen)
									# Generate a random string of strLen length
}																	
*/

// Check if class already called
if(class_exists('database')){ return;}
							 
class database
{
	# Variables needed to connect
	protected $databaseName, $databaseHost, $databaseUser, $databasePassword;
	# Connection link and error holder
	protected $connectionLink, $mysqlError;
	# Last insert ID (primary keys)
	protected $lastInsertID;
	# Holds the current ? replace
	protected $curReplace, $arrayReplace;
	
	# Construct database with information
	function __construct(&$theLink)
	{
		$this->connectionLink 	= $theLink;
	}
	
	# Regular query with ? replace style
	function query($sql,$arrayReplace = NULL)
	{		
		$this->curReplace = -1;
		
		$arrayReplace = $this->cleanInput($arrayReplace);
		$this->arrayReplace = $arrayReplace;
		
		if(isset($arrayReplace))
		{			
			if(is_array($arrayReplace))
			{
				$arrayReplaces = array_fill(0,count($arrayReplace),'?');
				$query = preg_replace_callback('/\?/Uism','database::doSwitch',$sql);				
			} else {
				$query = str_replace('?',$arrayReplace,$sql);
			}
		} else {
			$query = $sql;	
		}

		$queryLink = @mysql_query($query,$this->connectionLink) or $this->showError(mysql_error($this->connectionLink));
		return $queryLink;
	}
	
	# This is a helper function to preg_replace all question marks
	protected function doSwitch($result)
	{
		$this->curReplace++;
		return $this->arrayReplace[$this->curReplace];		
	}
	
	# Returns array or object result set assoc of an object or string query
	function result($query,$isObject = false)
	{
		if(!is_resource($query)){ $query = $this->query($query);}		

		$records = array();
		while($eachRecord = @mysql_fetch_assoc($query)){ $records[] = $eachRecord;}

		if($isObject == true)
		{
			$records = (object) $records;
		} 
		return $records;
	}
	
	# Sanitize an array & organize into mysql style ''
	function cleanInput($array)
	{
		if(!isset($array)){ return;}
		if(!is_array($array)){ return "'".$this->escape($array)."'";}
		
		$newArray = array();
		foreach($array as $item)
		{
			$newArray[] = "'".$this->escape($item)."'";	
		}
		return $newArray;
	}
	
	# Sanitize input for Query
	function escape($fieldValue,$isNumber = false)
	{
		if($isNumber == true)
		{
			return preg_replace('/[^0-9\.]/iUsm','',$fieldValue);
		} else {
			return mysql_real_escape_string($fieldValue,$this->connectionLink);
		}
	}
	
	# Show error sent
	function showError($theError)
	{		
		echo $this->mysqlError = $theError;
		echo PHP_EOL;
	}
	
	# Return the primary key of the specified table
	function getPrimaryField($tableName)
	{
		$result = $this->query("SELECT k.column_name
			FROM   information_schema.key_column_usage as k
			WHERE  table_schema = schema()
			AND    constraint_name = 'PRIMARY'
			AND    table_name = '".$tableName."'");
		list($theKeyField) = $this->result($result);
		return $theKeyField['column_name'];
	}
	
	# Delete a record based on one keyfield (leave empty to do primary) that matches keyDelete
	# To delete a record based on a number of fields, pass the array to keyDelete
	function delete($tableName,$keyDelete,$keyField = NULL)
	{
		# if keyDelete in form of array = array('fieldname' => 'fieldvalue','fieldname' => 'fieldvalue'...)
		if(is_array($keyDelete))
		{
			$queryAdd = 'WHERE ';
			foreach($keyDelete as $field => $value)
			{
				$queryAdd .= "`$field` = '$value' AND";
			}
			$queryAdd = substr($queryAdd,0,strlen($queryAdd)-4);
			$query = "DELETE FROM `$tableName` $queryAdd";
			return $this->query($query);
		}
		if($keyField == NULL){ $keyField = $this->getPrimaryField($tableName);}
		$query = "DELETE FROM `$tableName` WHERE `$keyField`='$keyDelete'";
		return $this->query($query);
	}
	
	# Insert/update into table values from ARRAY
	# variable names must match those found on table
	# Primary must exist for an update
	function insert($tableName,$ARRAY_VARS)
	{
		# Check if we are sending anything?
		if($ARRAY_VARS == NULL){ return;}		
		# Return fields matches between array and table structure
		$queryDo = $this->findMatches($tableName,$ARRAY_VARS,'INSERT');
		# Do query		
		return $this->query($queryDo);
	}

	# Find matches
	function findMatches($tableName,$ARRAY_VARS)
	{
		# We need two arrays to store fields/values
		$arrayFields = array();
		$arrayFieldValue = array();
		$arrayUpdates = array();
		
		# Primary field (will update if found, otherwise insert)
		$primaryField = $this->getPrimaryField($tableName);
		$primaryFound = false;
		
		# Get list of fields for the table
		$tableFields = $this->result("SHOW COLUMNS FROM `$tableName`",'assoc');

		foreach($tableFields as $fieldInfo)
		{
			# Check if the field exist in ARRAY_VARS
			if(array_key_exists($fieldInfo['Field'],$ARRAY_VARS))
			{
				$fieldNameFormatted = "`".$fieldInfo['Field']."`";
				$fieldValueFormatted = "'".$this->escape($ARRAY_VARS[$fieldInfo['Field']])."'";
				$arrayFields[] = $fieldNameFormatted;
				$arrayFieldValue[] = $fieldValueFormatted;
				if($fieldInfo['Field'] == $primaryField && $ARRAY_VARS[$primaryField] > 0){
					# Insure parimary field is a number
					settype($ARRAY_VARS[$primaryField],"integer");
					$primaryFound = true;
				}
				$arrayUpdates[] = $fieldNameFormatted.' = '.$fieldValueFormatted;
			}
		}
		
		# Are we doing insert or update
		if($primaryFound == TRUE)
		{
			$finalQuery = "UPDATE `$tableName` SET ".implode(',',$arrayUpdates);
			$finalQuery .= " WHERE `$primaryField`='".$ARRAY_VARS[$primaryField]."'";
		} else {
			$finalQuery = "INSERT INTO `$tableName` (".implode(',',$arrayFields).") VALUES (".implode(',',$arrayFieldValue).")";
		}
		return $finalQuery;
	}
	
	# Create table based on Array Schema
	function createTable($ARRAY_VARS,$tableName = NULL)
	{
		# If no table name sent, generate one
		if($tableName == NULL){ $tableName = $this->generateRandom(4);}

		# Setup query
		$createQuery = "CREATE TABLE `$tableName` (
		`primaryKey` BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY ,";
		
		# Get fieldnames to add to creation query
		foreach(array_keys($ARRAY_VARS) as $fieldName)
		{
			$createQuery .= "
			`$fieldName` VARCHAR( 255 ) NOT NULL ,";		
		}
		$createQuery = substr($createQuery,0,strlen($createQuery)-1);
		$createQuery .= "
		);";

		$result = $this->query($createQuery);
		return array($result,$tableName);
	}
	
	# Close database connection
	function close()
	{
		@mysql_close($this->connectionLink) or $this->showError(mysql_error($this->connectionLink));
	}
	
	# Generate a random number at x length
	function generateRandom($strLen)
	{
  		return substr(md5(uniqid(rand(),1)),1,$strLen);
	}
}

// End of file /AzizMVC/Plugins/Database.php