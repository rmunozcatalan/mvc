<?php
if(!defined('BASEPATH')){ die();}

/*

AzizMVC By Aziz S. Hussain
http://www.AzizSaleh.com
Licensed under LGPL

File Name: template.php

Plugin Methods:
---------------

string	showTemplate (content as string, templateVars as array, templateName as string, data as string)
				--> This function will replace all occurances of templatevars in content with respect
				-->	to the data variable. templateName contains the name of template to use
string	getContent(fileName as string, data as array)
				--> Open the file, and load the data array for use
string	findFile(fileStart as string)
				--> Will find the file start with fileStart and its extension

*/
if(!function_exists('showTemplate'))
{
	// Function to show view as template
	function showTemplate($content,$templateVars,$templateName,$data = NULL)
	{
		foreach($templateVars as $curTemplate)
		{
			$getFile = findFile(BASEPATH.'Templates/'.$templateName.'/'.$curTemplate);
			$curContent = getContent($getFile,$data);			
			$content = str_replace('{'.$curTemplate.'}',$curContent,$content);
		}		
		return $content;
	}
	
	// Save content to variables via ob_start
	function getContent($fileName,$data = NULL)
	{
		if(isset($data) && is_array($data)){
			foreach($data as $var => $item){ $$var = $item;} 
		}
		ob_start();
		include($fileName);
		$curContent = ob_get_contents();
		ob_end_clean();
		return $curContent;
	}
	
	// Function to find filename (any extension)
	function findFile($fileLoc)
	{
		$file = glob($fileLoc.'.*');
		return $file[0];
	}
}

// End of file /AzizMVC/Plugins/template.php