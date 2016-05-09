<?php

ini_set('display_errors', 'on');
session_start();

define ('ROOT', dirname(__FILE__).'/'); 

require_once(ROOT.'public/debug.php');
require_once(ROOT.'cache.class.php');
//require_once(ROOT.'public/db.function.php');
require_once(ROOT.'public/functions.php');

//auto include;
$myfuncfiles = scandir(ROOT.'func/');
foreach($myfuncfiles as $funcfile){	 
	require_once(ROOT.'func/'.$funcfile);
}



