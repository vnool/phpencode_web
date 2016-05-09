<?
 require_once('global.php');
 
 $target = $_REQUEST['target'];
 if(!$target) return;
 $act_exe = 'act/'.$target.'.php';
 if(is_file($act_exe))
 {
 	  require_once($act_exe);
 	   $func = 'do_'.$_REQUEST['func'];
		if(function_exists($func)){
			$func();
		}
 }
 
 
?>