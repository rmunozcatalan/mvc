<?php
if(!defined('BASEPATH')){ die();}

/*

AzizMVC By Aziz S. Hussain
http://www.AzizSaleh.com
Licensed under LGPL

File Name: load.php

Class:
------
class load

Description:
------------
This is the loader class, which loads controllers, models, views, plugins and helpers (CMVPH)

NOTICE:
------
Depending on your hosting, you might have to make sure that your
CVMPH file names as lowercase. Note that the class names in PHP are case
insensitive, so it doesn't matter how you case them.

>>> Class naming style is:  <<<

Controllers: 	same as first url param, case insensitive
Models:			custom naming 
Views:			custom naming

Methods:
--------
void 			__construct								--> Checks for the basepath
void 			loadcontroller(controller as string)	--> Loads controller

(string|void)	loadViewer(viewer as string, variableList as array = null,saveOutput = false)		   
													   	--> Load viewer. If variable list passed as array, it is passed to viewer.
														--> If saveOutput true, returns viewer as variable

void			loadModel(model as string, handler as string)
														--> Loads mysql model based on the following class naming conventions:
														model, modelModel, mode_model, or search the model file for class name
void			loadHelper(helperName as string)		--> Load helper based on helperName or helperName_helper
void			loadPlugin(pluginName as string)		--> Load helper based on pluginName or pluginName_plugin

void	private	checkClassInclude(className as string,handler as string)
														--> Call class and assign it to the model handler
														--> If handler specified, then assign it to that handler
(void|string)	getClassName							--> Open model file and check for the first class name and return it														
*/

class load
{
	// Constructor
	public function __construct()
	{
		/*
		The only reason that I am duplicating the BASEPATH to this is to keep the
		feeling of OOP, nothing much, users can still use the constant BASEPATH
		*/
		
		if(!isset($this->BASEPATH)){ $this->BASEPATH = BASEPATH;}	
	}
	
	// Load controllers
	public function loadcontroller($controller)
	{
		// Make sure filenames are lowercase
		$controller = strtolower($controller);
		
		// check if controller exist
		if(is_file($this->BASEPATH.'Controller/'.$controller.'.php'))
		{
			require_once($this->BASEPATH.'Controller/'.$controller.'.php');			
		} else {
			die("Controller: $controller.php does not exist");
		}				
	}
	
	// Load viewer
	public function loadViewer($viewer,$variableList = NULL,$saveOutput = false)
	{
		// Make sure filenames are lowercase
		$viewer = strtolower($viewer);
		
		// check if viewer exist
		if(is_file($this->BASEPATH.'View/'.$viewer.'.php'))
		{
			// Load variables if any
			if(is_array($variableList) && count($variableList) > 0)
			{
				foreach($variableList as $varName => $varValue)
				{
					${$varName}	 = $varValue;
				}
			}
			
			// Are we saving output
			if($saveOutput == true)
			{
				ob_start();
			}
			
			// Show viewer
			require_once($this->BASEPATH.'View/'.$viewer.'.php');
			
			// Return output
			if($saveOutput == true)
			{
				$content = ob_get_contents();
				ob_end_clean();
				return $content;
			}			
		} else {
			die("Viewer: $viewer.php does not exist");
		}
	}
	
	// Load model
	public function loadModel($model,$handler = NULL)
	{
		$className = '';
		if(isset($handler)){ $this->$handler = '';}
		
		// check if model exist
		if(is_file($this->BASEPATH.'Model/'.$model.'.php'))
		{
			require_once($this->BASEPATH.'Model/'.$model.'.php');
		} else {
			die("Model: $model.php does not exist");
		}
		
		// Check name style "controller_model" / case insensitive
		$className = $model.'_model';
		$this->checkClassInclude($className,$handler);
				
		// Check name style "controllermodel"
		if((isset($handler) && !is_object($this->$handler)) ||
			  (!isset($handler) && !is_object($this->model)))
		{
			$className = $model.'model';		
			$this->checkClassInclude($className,$handler);
		}
			
		// If all fail, just open the file and get the class name
		/* Users should not resort to this method as it will slow the application down */
		if((isset($handler) && !is_object($this->$handler)) ||
			  (!isset($handler) && !is_object($this->model)))
		{
			$className = $this->getClassName($this->BASEPATH.'Model/'.$model.'.php');
			$this->checkClassInclude($className,$handler);
		}

		// If all fail, die
		if((isset($handler) && !is_object($this->$handler)) ||
			  (!isset($handler) && !is_object($this->model)))
		{
			die("Model: $model.php, unable to locate main class");	
		}
	}
	
	// Load helper function
	public function loadHelper($helperName)
	{
		$helper = strtolower($helperName).'.php';
		
		// check if model exist
		if(is_file($this->BASEPATH.'Helpers/'.$helper))
		{
			require_once($this->BASEPATH.'Helpers/'.$helper);	
		} else {
			// If not found, try _helper style
			if(strstr($helperName,'_helper') == false)
			{
				$this->loadHelper($helperName.'_helper');
			} else {
				die("Helpers: $helper/".str_replace('_helper','',$helper)." do not exist");
			}
		}
	}
	
	// Load plugin function
	public function loadPlugin($pluginName)
	{
		$plugin = strtolower($pluginName).'.php';
		
		// check if model exist
		if(is_file($this->BASEPATH.'Plugins/'.$plugin))
		{
			require_once($this->BASEPATH.'Plugins/'.$plugin);			
		} else {
			// If not found, try _plugin style
			if(strstr($pluginName,'_plugin') == false)
			{
				$this->loadPlugin($pluginName.'_plugin');
			} else {
				die("Plugins: $plugin/".str_replace('_plugin','',$plugin)." do not exist");
			}
		}
	}

	// Private method to check if the model exist and then include it
	private function checkClassInclude($className,$handler)
	{
		if(class_exists($className))
		{
			if(isset($handler))
			{
				$this->$handler = new $className;
			} else {				
				// Assign model handler
				$this->model 	= new $className;
			}
		}
	}

	// Private function to check for custom model names
	private function getClassName($fileName)
	{
		$fileContent = file_get_contents($fileName);

		// i modifier for lower/upper case - 
		preg_match('/class ([a-zA-Z0-9_]+)/i',$fileContent,$result);

		if(is_array($result) && count($result) > 0)
		{
			return trim($result[1]);
		}
		
		return false;
	}
}

// End of file /AzizMVC/load.php