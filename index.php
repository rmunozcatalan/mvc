<?php

/*

AzizMVC By Aziz S. Hussain
http://www.AzizSaleh.com
Licensed under LGPL

File name: index.php

Function List:
--------------
public function checkIndex(params as array): params as array
public function loadMVC(): void

*/

session_start();

// Get BASEPATH from script filename
$fileName = basename($_SERVER['SCRIPT_FILENAME']);

// Get base bath
define('BASEPATH',str_replace($fileName,'',$_SERVER['SCRIPT_FILENAME']));

// Include main controller configuration
include(BASEPATH.'AzizMVC/configs.php');

// Main controller
define('MAIN_CONTROL',$MVC_Configs['mainController']);
// Main Template
define('MAIN_TEMPLATE',$MVC_Configs['template']);


// Loader
include(BASEPATH.'AzizMVC/load.php');
// Database model
include(BASEPATH.'AzizMVC/databaseModel.php');
// Main controller
include(BASEPATH.'AzizMVC/mainController.php');


// Start MVC with specific controller
loadMVC();

/*
This function will loads us the URL paramaters

public function checkIndex(params as array): params as array
*/
function checkIndex($params)
{	
	if(!isset($params) || strtolower($params[0]) == 'index.php' || count($params) <= 0){ return $params;}

	array_shift($params);

	if(strtolower($params[0]) == 'index.php')
	{
		return $params;
	} else {
		if(count($params) > 0)
		{
			$params = checkIndex($params);
		}
	}
	return $params;
}

/*
This is the main function loader:

public function loadMVC(): void
*/
function loadMVC()
{	
	// See if we are specifying the controller via URL
	if(isset($_SERVER['REQUEST_URI'])){
		$params = explode('/',$_SERVER['REQUEST_URI']);		
		$params = checkIndex($params);	
	}
	// If not, use the welcome controller
	if(isset($params[1])){
		/*
		Controller names must always be lowercase
		Some hosts are case sensitive to filename casing
		*/
		$controller = strtolower($params[1]);
	} else {
		$controller = MAIN_CONTROL;	
	}

	
	// Include the controller
	if(!is_file(BASEPATH.'Controller/'.$controller.'.php'))
	{
		$controller = MAIN_CONTROL;
	}

	require_once(BASEPATH.'Controller/'.$controller.'.php');
	
	// Check if class exists within controller
	if(class_exists($controller))
	{
		$MVC_CONTROL = new $controller();
		// Are we calling any functions
		if(isset($params[2]) && method_exists($MVC_CONTROL,$params[2]))
		{
			call_user_func(array($MVC_CONTROL,$params[2]));
		} else {
			if(method_exists($MVC_CONTROL,'index'))
			{
				call_user_func(array($MVC_CONTROL,'index'));
			}
		}
	} else {
		die("Controller: $controller does not exist");	
	}
}

// End of file /index.php