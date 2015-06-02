<?php
if(!defined('BASEPATH')){ die();}

/*

AzizMVC By Aziz S. Hussain
http://www.AzizSaleh.com
Licensed under LGPL

File Name: databaseModel.php

Class:
------
class model

Description:
------------
This class loads the database connection information based in /AzizMVC/configs.php

Variables:
-----------
public	$cLink						--> Database connection link

Methods:
--------

void			__construct			--> Open the database and assign connection link
void			getLink				--> Returns the connection link
void			closeLink			--> Closes database connection link
*/

// Model class
class model 
{
	/*
	For the sake of encapsulation, variable should be private,
	but I found it much easier referring to the link itself instead of
	having to go through the getLink() function
	You can change it to public if you want
	*/
	private $cLink;
	
	// Constructor, get connection information
	public function __construct()
	{

		// Include database oonfigurations
		include(BASEPATH.'AzizMVC/configs.php');

		$this->cLink = @mysql_connect($MVC_Configs['databaseHost'],
					$MVC_Configs['databaseUser'],$MVC_Configs['databasePassword']) or die(mysql_error());

		# Select database
		@mysql_select_db($MVC_Configs['databaseName'],$this->cLink) or die(mysql_error());
		 
		return $this->cLink;
	}
	
	// Get database link resource
	public function getLink()
	{
		return $this->cLink;	
	}
	
	// Close database link resource
	public function closeLink()
	{
		@mysql_close($this->cLink);	
	}
}

// End of file /AzizMVC/databaseModel.php