<?

 set_error_handler('debug_handler');
 
function debugX($msg){
	echo "<pre> $msg <br>"; 	
	debug_print_backtrace();	
	exit();
}

function debug_handler($errno, $message, $errfile, $errline)
{
	$msg = $message   .". (file) $errfile ($errline)";
	 if( strpos( $msg  ,'Undefined index') !== false)
	  return;
	 
  switch ($errno) {
  case E_CORE_ERROR:
  case E_USER_ERROR:
  case E_ERROR:
    {
    	debugX( "\r\nError:". $msg );
    }break;
  default:
    //mylog( $msg );
   break;
  }
}
 