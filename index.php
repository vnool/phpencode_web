<?php 
error_reporting(E_ALL^E_NOTICE);
date_default_timezone_set('PRC');
 
 

$baseurl = 'http://ding.scicompound.com/phpencode/';

if(is_file('debug.txt'))
{
	$baseurl = 'http://localhost/phpc/';
}
 




global $APP_VIEW,$APP_MOD,$APP_SUB;
global $APP_TEMPLATE;

$APP_VIEW = $_REQUEST['view'];
//this 2 for customer
$APP_MOD = $_REQUEST['mod'];
$APP_SUB = $_REQUEST['sub'];

require_once('global.php');

//$_SESSION['makertoken']= rand();



if(!$APP_TEMPLATE) $APP_TEMPLATE = 'default';
if(!$APP_VIEW) $APP_VIEW = 'index'; 
if(!$APP_MOD) $APP_MOD = 'index'; 
if(!$APP_SUB) $APP_SUB = 'index'; 
 
$APP_TEMPLATE_PATH = "view/$APP_TEMPLATE";
$file_win = "view/$APP_TEMPLATE/$APP_VIEW.html";
$file_exec =  "act/$APP_VIEW.php";

if(is_file($file_exec))  {;
	require_once($file_exec);	
	$func = 'do_'.$_REQUEST['func'];
	if(function_exists($func)){
		$func();
	}
 }

if(is_file($file_win)) { 
	;require_once($file_win);	
	
}



exit;


