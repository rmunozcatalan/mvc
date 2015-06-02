<?php
if(!defined('BASEPATH')){ die();}

/*

AzizMVC By Aziz S. Hussain
http://www.AzizSaleh.com
Licensed under LGPL

File Name: welcome.php

Description:
-----------
This is an example of how you would create a controller.

--> Basically all controllers must extend the mainController
--> Your constructor must initialize the parent constructor as well
--> Index function will always get called if it exists
--> If you access the controller through this: /index.php/welcome/test, 
	it will try to execute the test() function in the class

*/

class welcome extends mainController
{	
	// load controller
	public function __construct()
	{
		// Initialize parent
		parent::__construct();
	}
	
	// index page
	function index()
	{
		/*
		// Load model (model filename, handler)
		$this->loadModel('welcome','myModel');
		
		// Execute the model test function (basically echos 'test' for now)
		$this->myModel->test();
		*/
		// Custom data to pass to viewer
		$data['testVar'] = ' > Test 123';
		
		// Load viewer and save it to use on the template plugin
		$page = $this->loadViewer('welcome',$data,true);
		
		$templateInfo = array('header', 'footer', 'links');			
		
		// Echo page + template (using auto loaded template plugin)
		// echo showTemplate($page,$templateInfo,'Default',$data); // 11:14 02-06-2015
		echo showTemplate($page,$templateInfo,MAIN_TEMPLATE,$data);
	}	
}

// End of file /AzizMVC/Controller/welcome.php