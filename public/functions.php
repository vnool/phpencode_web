<?

function ismobile(){
	if(is_file('mobiledebug.txt') )  return true;
	
	
	$ua = strtolower($_SERVER['HTTP_USER_AGENT']);

	$uachar = "/(android|micromessenger|qq|nokia|sony|ericsson|mot|samsung|sgh|lg|philips|panasonic|alcatel|lenovo|cldc|midp|mobile)/i";

	if(($ua == '' || preg_match($uachar, $ua))&& !strpos(strtolower($_SERVER['REQUEST_URI']),'wap'))
	{ 
	  return true;
	}else
	{  return false;
	}
}

function jsonPback($obj){ 
    $back = $_REQUEST['callback'] .'('.json_encode($obj) .');';
    echo $back;
	return;
}
function quit($js){ 

} 


function getBrowser(){
$sys = $_SERVER['HTTP_USER_AGENT'];
if(stripos($sys, "NetCaptor") > 0){
   $exp[0] = "NetCaptor";
   $exp[1] = "";
}elseif(stripos($sys, "Firefox/") > 0){
   preg_match("/Firefox\/([^;)]+)+/i", $sys, $b);
   $exp[0] = "Mozilla Firefox";
   $exp[1] = $b[1];
}elseif(stripos($sys, "MAXTHON") > 0){
   preg_match("/MAXTHON\s+([^;)]+)+/i", $sys, $b);
   preg_match("/MSIE\s+([^;)]+)+/i", $sys, $ie);
  // $exp = $b[0]." (IE".$ie[1].")";
   $exp[0] = $b[0]." (IE".$ie[1].")";
   $exp[1] = $ie[1];
}elseif(stripos($sys, "MSIE") > 0){
   preg_match("/MSIE\s+([^;)]+)+/i", $sys, $ie);
   //$exp = "Internet Explorer ".$ie[1];
   $exp[0] = "IE";
   $exp[1] = $ie[1];
}elseif(stripos($sys, "Netscape") > 0){
   $exp[0] = "Netscape";
   $exp[1] = "";
}elseif(stripos($sys, "Opera") > 0){
   $exp[0] = "Opera";
   $exp[1] = "";
}elseif(stripos($sys, "Chrome") > 0){
    $exp[0] = "Chrome";
    $exp[1] = "";
}else{
   $exp = "Î´Öªä¯ÀÀÆ÷";
   $exp[1] = "";
}
return $exp;
}