<?php
if(!defined('BASEPATH')){ die();}

/*

AzizMVC By Aziz S. Hussain
http://www.AzizSaleh.com
Licensed under LGPL

File Name: welcome.php

Description:
-----------
This is an example of how you would create a model.

--> the classname must be the same as loaded or in the following variations:
	controllermodel, controller_model (case insensitive)
	or any other c l a s s (MVC will search for the c l a ss name)

*/

class welcomemodel extends model
{
	// Construct function
	public function __construct()
	{
		// Initialize parent
		parent::__construct();		
	}
	
	// Test function
	public function test()
	{
		echo "Test, Connection Link: ".$this->getLink()."<br />";
	}
}

// End of file /AzizMVC/Model/welcome.php