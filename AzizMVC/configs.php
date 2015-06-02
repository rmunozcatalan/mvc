<?php
if(!defined('BASEPATH')){ die();}

/*

AzizMVC By Aziz S. Hussain
http://www.AzizSaleh.com
Licensed under LGPL


File Name: configs.php

Description:
------------
This file contains the database connection information, this is virtually the only file you need to change
to make the model section work
*/

$MVC_Configs						= array();
// Host Name
$MVC_Configs['databaseHost']		= 'localhost';
// User Name
$MVC_Configs['databaseUser'] 		= 'root';
// User Password
$MVC_Configs['databasePassword'] 	= '';
// Database Name
$MVC_Configs['databaseName']		= 'amana';

/* Plugins/Helpers Auto Loads */
/* Plugins */
$MVC_Configs['pluginAuto']			= array('template','database');
/* Helpers */
$MVC_Configs['helperAuto']			= array();

/* Template */
$MVC_Configs['template']			= 'Default';
/* Main controller */
$MVC_Configs['mainController']		= 'welcome';

// End of file /AzizMVC/configs.php