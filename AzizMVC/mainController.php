<?php
if(!defined('BASEPATH')){ die();}

/*

File Name: mainController.php

Class:
------
class mainController extends load (from /AzizMVC/load.php)

Description:
------------
This class gets extended by every controller, which inherently extends the loader

Variables:
----------
protected params = string array()	--> List of URL paramaters
public model = string				-->	Default model handler

Methods:
--------
void 	__construct					--> Construct paramters, paths & helper/plugin autoload
string 	segments(index as int)		--> Returns the selected paramters index

string	getPost(input as string, doClean as boolean = true)
									--> Returns a POST value and cleans it by default
string	getGet(input as string, doClean as boolean = true)
									--> Returns a GET value and cleans it by default

string	cleanMe(input as string, doClean as boolean = true)
									--> Returns input or cleans it before returning it.
string	escape(input as string)		--> Similar to mysql_real_escape_string, but additional it urlencodes equal signs										

*/

class mainController extends load
{
	// Paramters holder
	protected $params = array();
	
	// Default Model handler
	public $model = NULL;
	
	// Base Path
	public $BASEPATH;
	
	// Site URL
	public $site_url;
	
	// Base URL
	public $base_url;
	
	// Construct paths
	public function __construct()
	{
		// Set BASEPATH		
		$this->BASEPATH = BASEPATH;

		// Set params
		if(isset($_SERVER['REQUEST_URI']))
		{
			$this->params = explode('/',$_SERVER['REQUEST_URI']);	
			$this->params = checkIndex($this->params);
		}
		
		// Site/base URL
		$temp = basename($_SERVER['SCRIPT_NAME']);
		$this->base_url = 'http://'.$_SERVER['HTTP_HOST'].str_replace($temp,'',$_SERVER['SCRIPT_NAME']);
		$this->site_url = $this->base_url.'index.php/';
		
		// Include database oonfigurations
		include(BASEPATH.'AzizMVC/configs.php');
		
		// Load all plugins on autoload
		if(count($MVC_Configs['pluginAuto']) > 0)
		{
			foreach($MVC_Configs['pluginAuto'] as $plugin)
			{
				$this->loadPlugin($plugin);
			}
		}
		// Load all helpers on autoload		
		if(count($MVC_Configs['helperAuto']) > 0)
		{
			foreach($MVC_Configs['helperAuto'] as $helper)
			{
				$this->loadHelper($helper);
			}
		}		
	}
	
	// Function used to retrieve paramater
	public function segments($index,$doClean = true)
	{
		settype($index,"integer");
		if(isset($this->params[$index])){
			return $this->cleanMe($this->params[$index],$doClean);
		} else {
			return '';	
		}
	}
	
	// Function to get clean post variables
	public function getPost($input,$doClean = true)
	{
		if(isset($_POST[$input]))
		{
			return $this->cleanMe($_POST[$input],$doClean);
		} else {
			return '';	
		}
	}
	
	// Function to get clean get variables
	public function getGet($input,$doClean = true)
	{
		if(isset($_GET[$input]))
		{
			return $this->cleanMe($_GET[$input],$doClean);
		} else {
			return '';	
		}
	}
	
	/* This function will return input or clean it before doing so */
	private function cleanMe($input,$doClean)
	{
		if(!$doClean){ return $input;}
		return $this->escape($input);
	}
	
	// MySQL escape function
	public function escape($string)
	{
		$search = array("\x00", "\n", "\r", '\\', "'", '"', "\x1a",'=');
		$replace = array("\\x00", "\\n", "\\r", "\\\\" ,"\'", '\"', "\\x1a",'%3D');
		return str_replace($search,$replace,$string);
	}
}

// End of file /AzizMVC/mainController.php