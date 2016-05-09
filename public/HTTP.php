<?php
/* 
@dingchengliang

[history]
*   2014.2.9   function 'http'
*   2013.12.9 support: chunck,  deflate,gzip
*  
*
*
*       '$para' is an array,  can  contain all the HTTP header items
*        $para['Referer'] = '';
*        $para['Referer'] = '';
*
*       '$para' special items:   
*          $para['ext']['files']:    files array  for upload (more file with keys)
*          $para['ext']['fields']:  form fiels name for the files
*          $para['ext']['postfile']:  only post the file to server(1 file)
*          $para['ext']['content']:  content for post
*          $para['ext']['cookie']:    add external cookie
*          $para['ext']['fullurl']:     http call url use full path
*          $para['ext']['getreturn']: return contents(default:true; if set false, touch the url only).   
*          $para['ext']['back_keyword']  return right now, if meet the keyword
*          $para['ext']['httpver']   http version(default:1.1)
*          $para['ext']['timeout']  default:10s;
*/

//  define('HTTP_DEBUG_MSG', 1);

function http($url, $para=array()){
	$buf = http_request($url, $para);
	$buf = http_body($buf);
	return $buf;
}

function http_request($url, $para=array())
{

   HTTPcheckRunning();
  
   $myHead = HTTP_Head($url,$para);
	 
   $fp = fsockopen( $myHead['host'], $myHead['port'], $errno, $errstr, 30);
	
  if (!$fp) 
  {
    wmlog( 'fail: '. $url ."\r\n". $errstr ." errno(" .$errno . ")");
    return false;    
  }
  else 
  {
  	  fwrite($fp, $myHead['head']);
  	  
  	    	    	  
  	 $timeout  =  $para['ext']['timeout'];
     if($timeout < 10) $timeout = 10;     
  	 stream_set_timeout($fp, $timeout);
  	 
  	return HTTP_getContent($fp,$para);   
	 
		 		 
	}
	
  
}

/****
*    $request[id]['url']
     $request[id]['para']:  'para' like in 'http_request'
*    $request[id]['proc']:  call bakc function for each url return contents
     $request[id]['timeout']: 
 
 $sockets[id]['state'] 
 $sockets[id]['socket']  
 $sockets[id]['header']  

*/
  
function http_request_parl($request,$maxThread=8)
{  
    
   $sockets = array(); 
   $SELECT_TIMEOUT = 10;
   
 while (1) {
 	
     HTTPcheckRunning();  //check whether to stop
	 	 
	 	   
		checkQueue($sockets, $request,$maxThread);
		
		if(count($sockets)< 1) break;
		
	 $read =  socketArray($sockets);
	 $write = socketNeed2write($sockets);
	 
   if(count($write)== 0 && count($read)== 0)
   break;
   
    
   if(count($write)> 0 )
	 { 
		 $n = stream_select($er =null, $write, $e = null, $SELECT_TIMEOUT);
		 
		 if (count($write)>0)
		 {
		 		/* writeable sockets can accept an HTTP request */			  
			  foreach ($write as $w) {
			     $id = socket_search($w, $sockets);
			     if(!$sockets[$id]['written'])
			     { 
			         $sockets[$id]['written'] = true;
			         $sockets[$id]['state'] = "write";		         
			         fwrite($w, $sockets[$id]['header']);	
			         usleep(50);			        
			       
			       // unset($sockets[$id]['socket']);     
			       // stream_set_blocking($w,0);  
			     }
			  }
		 } 
		 else 
		 { 
		    foreach ($write as $id => $s) {
		     $sockets[$id]['state'] = "timeout";
		    }
		   // continue;
		 }
	 }
	 
	 
	  HTTPcheckRunning();  //check whether to stop
	   
	 if (count($read) > 0)	  
	 { $n = stream_select($read, $ew =null, $e = null, $SELECT_TIMEOUT);	  		   
		  if (count($read)>0)    
      {             	 
       	 foreach ($read as $r) 
       	 {
			      $id = socket_search($r, $sockets);
			      		        
	          $sockets[$id]['state'] = "read";
			      
			      $para = $request[$id]['para'];
			       		      
			      $sockets[$id]['data'] .=  HTTP_getContent($r,$para,false);	      
			       
		        if($r==null) 
			      {		
			      	finish_socket($sockets,$request,$id);
			      }      
		     
		     }
		  } 
		  else { 
		    foreach ($read as $id => $s) {
		     $sockets[$id]['state'] = "timeout";
		    }
	      //continue;
	    }
	 }
 	 
	 checkReadTimeout($sockets, $request);
	 usleep(10);
	 
 }//loop



}


function finish_socket(&$sockets,$request,$id)
{
  //file_put_contents($id.'.htm', $sockets[$id]['data']);       	    
        $sockets[$id]['state'] = "closed";
        unset($sockets[$id]['socket']); 
        usleep(100);
        
        $difftime =  time() - $sockets[$id]['time'];
        
        $callback = $request[$id]['proc'];
        if($callback) 
          $callback($sockets[$id]['data'],$id, $difftime);            
        
        unset($sockets[$id]['data']); 
        unset($sockets[$id]['header']); 
       
}

function checkQueue(&$sockets, $request,$maxThread=8)
{	  
	 $runningnum = 0;   
	 foreach ($request as $id => $req) {
		 if(in_array($sockets[$id]['state'],array('connect','write','read')  )) //running
		 {
		 	   $runningnum++;
		 }
	}
	 
	if( $runningnum > $maxThread)
	{  
		return;
  }
  
	foreach ($request as $id => $req) {
		
			if( $runningnum > $maxThread)
			{  
				return;
		  } 
		  
	 	 if(!$sockets[$id]['state'])
		 {	  
		 	   $runningnum++;
		 	   
	 	    $url  = $req['url'];
	 	    $para = $req['para'];
			  $myHead = HTTP_Head($url,$para);
			 
			  $sockets[$id]['header'] = $myHead['head'];  //save header
			 
			  $s = stream_socket_client($myHead['host'].":".$myHead['port'], $errno, $errstr, 30,
				     STREAM_CLIENT_ASYNC_CONNECT|STREAM_CLIENT_CONNECT);
				if ($s) {
				 	 				 	 
				 	 stream_set_timeout($s, 10);
				   $sockets[$id]['socket'] = $s;
				   $sockets[$id]['time'] = time();
				   $sockets[$id]['state'] = "connect";
				   	 usleep($id > 8 ? $id * 8: 100);	  
			      // break;  //每次只启动一个  
			       
				} else {
				   $sockets[$id]['state'] = "failed";
				}
		 }		 
	}	
	
  usleep(40);
  
}


 
function checkReadTimeout(&$sockets, $request)
{   
	 foreach($sockets as $id => $s)
	 { if(in_array($s['state'], array('connect','write','read')  )) //running
		 {
		 	  $difftime =  time() - $s['time'];
		 	  if($difftime > $request[$id]['timeout'])
		 	  { 
		 	  	//debuglog('check.txt', "\r\n id:". $id .', out reason: checkReadTimeout'); 
		 	  	finish_socket($sockets,$request,$id);
		 	  }
		 }
	 }  
	 
 
}

function socket_search($need, $sockets)
{
	foreach($sockets as $id => $s)
	{
		if($s['socket'] == $need)
		return $id;		
	}
	
}

function socketArray($sockets)
{  $socks = array();	 
	 foreach($sockets as $id => $s)
	 { if(in_array($s['state'], array('connect','write','read')  )) //running
		 {
		 	  $socks[$id] = $s['socket'];			 	   
		 }
	 } 	 
	 return $socks;
}

function socketNeed2write($sockets)
{  $socks = array();
	 foreach($sockets as $id => $s)
	 { if(in_array($s['state'], array('connect','write','read')  )) //running
		 {
		 	  if(!$s['written'])
		 	   $socks[$id] = $s['socket'];
		 }
	 }
	 
	 return $socks;
}



function http_body($buf)
{
	$pH= strpos($buf,"\r\n\r\n");
	if ($pH < 1)
	return;
	
	$ischunked = false;  
	$isgzip = false; $isdeflate = false;
	
	$head = substr($buf,0,$pH); 
	$header = explode("\r\n",$head);
   
  // print_r($header); exit;
  
	foreach($header as $hv){
		 $h  = explode(':',$hv);
		 if(strtolower(trim($h[0]))=='transfer-encoding' && strtolower(trim($h[1]))=='chunked')
		 {
		 	 $ischunked = true;
		 }
		 if(strtolower(trim($h[0]))=='content-encoding' && strtolower(trim($h[1]))=='gzip')
		 {
		 	 $isgzip = true;
		 } 
		 if(strtolower(trim($h[0]))=='content-encoding' && strtolower(trim($h[1]))=='deflate')
		 {
		 	 $isdeflate = true;
		 }	
		 
	}
  $buf = substr($buf, $pH+4);
  
  if($ischunked) 
	  $buf = removeChunk($buf);
   
  if($isdeflate) 
    $buf = gzuncompress (($buf));   
  else if($isgzip)
    $buf = gzinflate(substr($buf,10));
   
   
    
    
	return $buf;	
}



function HTTP_Head($url,$para)
{
		$urlx = parse_url($url);	
	if(!$urlx['path']) $urlx['path'] ='/';
	if($urlx['query']) $urlx['path'] .=('?'.$urlx['query']);
	if($para['ext']['fullurl']) $urlx['path']= $url;
	
	$header['User-Agent']=  'Mozilla/5.0 (Windows; U; Windows NT 5.1; zh-CN; rv:1.9.2.21) Gecko/20110830 Firefox/3.6.21 ( .NET CLR 3.5.30729)';
	$header['Host'] = $urlx['host'];
	$header['Accept-Language'] = 'en-US';//'zh-CN';
	$header['Content-Type']    = 'application/x-www-form-urlencoded';
	$header['Accept-Encoding'] = 'deflate';		
	$header['UA-CPU']  = 'x86';
	$header['Accept'] = '*/*';
	$header['Cookie'] = '';
	$header['Cache-Control'] = 'no-cache';
	$header['Connection'] = 'Close';
	
	
	
	if($GLOBALS['Referer']) 
	  $header['Referer'] = $GLOBALS['Referer'];
	else
	  $header['Referer'] = 'http://'.$urlx['host'];
	
	if(!$GLOBALS['Referer'])
	{ $GLOBALS['Referer'] = $url;
  }
	 $GLOBALS['lasturl'] = $url; 
	 
	
	if($GLOBALS['cookie']) 	$header['Cookie'] = $GLOBALS['cookie'];
		
	if($para['ext']['files'])
	{
		$header['Content-Type'] = "multipart/form-data; boundary=---------------------------7de3a12d150e4c";
		$postcontent = file_contents($para['ext']['files'],$para['ext']['fields']);
		 $postcontent .="\r\n";
	}
	else if($para['ext']['postfile'])
	{
		 $postcontent = file_get_contents($para['ext']['postfile']);
	}
	else
	{
		$postcontent = $para['ext']['content'];
	}	
		
  $header['Content-Length'] = strlen($postcontent);
	$method = $header['Content-Length'] > 0 ? "POST" : "GET";
	$method = count($para['ext']['files']) > 0 ? "POST" : $method;
  

  if(count($para))	
  foreach($para as $k => $v)
  {
  	 if ($k == 'ext') continue;
  	 
  	 $header[$k] = $v ;
  }
	
	if($para['ext']['cookie']) $header['Cookie'].=$para['ext']['cookie'];
	
 
//	wmlog("\r\n\r\n=====Call: ".$url);
	
	$GLOBALS['location'] = $url;	
	
	
	$httpVer = $para['ext']['httpver'];
	if(!$httpVer) $httpVer = '1.1';
	
	$X = $method." ". $urlx['path']." HTTP/" . $httpVer;
	//$X = $X . "\r\nAccept: image/gif, image/x-xbitmap, image/jpeg, image/pjpeg, application/vnd.ms-excel, application/vnd.ms-powerpoint, application/msword, application/x-ms-application, application/x-ms-xbap, application/vnd.ms-xpsdocument, application/xaml+xml, application/x-shockwave-flash, */*";
  
  foreach($header as $k => $v)
  {
     //unset($header[$k]);
    // $k = strtoupper($k);   //remove same keys with different ucase/lcase
      $header[$k] = $v;
    // if(!$v) unset($header[$k]);
  }	
  foreach($header as $k => $v)
  {
  	 if(trim($v)) $X = $X . "\r\n$k: $v";
  }	
 
	$X = $X . "\r\n";
	//$X = $X . "\r\n" . $postcontent;
  
 
 
	$port = intval($urlx['port']);
	if(!$port )
	{ if(stripos('xxx'.$url,'http://')) 
		{ $port = 80;}
		else if(stripos('xxx'.$url,'https://')) 
		{ $port = 443; 
			$urlx['host'] ='ssl://' .$urlx['host'];
		}
  }
	
	wmlog ("\r\n---send --\r\n" .$X);
	//if(strlen($postcontent)> 2000) 
	//   wmlog ("\r\n" .substr($postcontent,0,2000) . "\r\n[....contents...]");
	//else
	   wmlog ("\r\n" .$postcontent);

  
  
	$myHead['head'] = $X . "\r\n". $postcontent ; 
	$myHead['port'] = $port;
	$myHead['host'] = $urlx['host'] ;
	
	if(!$myHead['host'])
	$myHead['host']= $_SERVER['HTTP_HOST'];
	   	   
	   
	return $myHead;
	
}


function HTTP_getContent(&$fp, $para, $allcontent=true)
{      
      $buf = '';	 
      $continue_read = true;      
  
      
			while (!feof($fp) ) 
			{	    	 					               
        
           $tmp = @fread($fp, 1024); 
                       
           $buf .= $tmp;
       

           if($allcontent == false && strlen($tmp) == 0)
           {  $act_word = 'break';
           	  break; 
           }		           	              
	         
	         if($continue_read)
	         {   
	         	$act_word = readReceiveHTML($buf,$para);
	            if($act_word == 'break')
	            break;
	         }		           
	   
	        $data_status = stream_get_meta_data($fp);
					if($data_status['timed_out']) 
					{ //超时了
						$act_word = 'break';							 
					  // break;
					}
          
		  }    
		   
		  
      if(  feof($fp)  ||  $act_word == 'break') //读完了
	    { fclose($fp); 
	      update_cookie($buf); 
	      $fp = null;
	    } 
		     
		   // if(is_file('ding_stop'))
		   //  wmlog("\r\n [check]----".$buf ."---[/check]\r\n");		  
		     
	 return   $buf;  	
		     
}



function readReceiveHTML(&$buf,$para)
{     
	     
	     $act_word = '';
	   
	     $back_keyword = $para['ext']['back_keyword'];
	    
	    if( isset($para['ext']['getreturn']))
	      $getreturn =$para['ext']['getreturn'] ;
	    else
	      $getreturn = true;  //get return contents by default
	      

       if($getreturn == false) //不想要返回内容
       {   
       	if(strlen($buf) > 0)
       	{ $act_word = 'break';       		 
       	}
       }
       
       $http_state = http_state($buf);
       if( 100 == $http_state )
       { 
       	 $p2 = strpos($buf,"\r\n\r\n");
       	 if($p2 > 1)  $buf = substr($buf,$p2+4) ;
       }
       if( 504 == $http_state )
       { 
       	  $act_word = 'break';
       }
	     if( 302 != $http_state )
			 { $GLOBALS['Referer'] = $GLOBALS['lasturl'] ; 
		   }
       
          //redirect--
       $p1 = stripos($buf,'Location:');		$p2 = strpos($buf,"\r\n\r\n");
			 if($p1 > 0 && $p2 > $p1 && !$GLOBALS['HTTP_NO_REDIRECT'])				
			 {  update_cookie($buf);
			 	  $buf = auto_redirect($buf, $GLOBALS['Referer']);			 	   
			 	  $act_word = 'break';
			 }
  
       if($back_keyword) //遇到关键字则停止抓取
       {  if( stripos($buf, $back_keyword) )	
       	  { $suddenBack = true;
            $act_word = 'break';
          }		            
       }
       
    return $act_word;
}


function http_state($buf)
{ $p1 = strpos($buf,"\r\n");
	$fline = substr($buf,0,$p1);
	$p1 = strpos($fline," ");   
	if($p1 > 0) 
	{$p2 = strpos($fline," ",$p1+1);
		if($p2 > 0)   
	    $state = trim(substr($fline,$p1,$p2-$p1));
  }
  return $state;
}
function auto_redirect($buf,$refer)
{ 	 
	   
	
	  $url = get_returnURL($buf);
	  $GLOBALS['HTTP_REDIRECT'] = $url;
	 // $url = strtr($url,array('https://'=>'http://'));
		wmlog( "\r\nredirect: ".$url);
		$para['Referer'] = $GLOBALS['Referer'];
	 
		$buf = http_request($url,$para,true);
		return $buf;
 
	
}




function update_cookie($content)
{  
	
	$p2=0;$cookie='';
	while(1)
	{
	 $p=stripos($content,"Set-Cookie:",$p2);
	 if(!$p) break;
	 $p2=strpos($content,";",$p);
	 $cookie .= substr($content,$p+12,$p2-$p-11);
  }
 
 
	$GLOBALS['cookie'] =  fixCookie( $GLOBALS['cookie'] . ' ; '. $cookie);	
	
	//------- 抓一下接受到的包HTTP头
	$rechead = substr($content,0,strpos($content,"\r\n\r\n"));
	wmlog( "\r\n-----receive----\r\n" . $rechead);
 

}
/* browser side set cookie */
function ieupdatecookie($cookiestr)
{
	$GLOBALS['cookie'] =  fixCookie( $GLOBALS['cookie'] . ' ; '. $cookiestr);
}

function fixCookie($cookieStr)
{
 // echo "\n\n update cookie: $cookieStr \n";
 $co =explode(';',$cookieStr);	
 foreach($co as  $vs)
 {   
 	  $p = strpos($vs,"="); 
 	  $k = trim(substr($vs,0,$p));
 	  $v = trim(substr($vs,$p+1));
 	 if($k)
 	 {  
 	   $cookie[$k]=$v;
 	   if(empty($v)) 	unset($cookie[$k]);
 	 }
 }
 
 if(count($cookie)>0)
 foreach($cookie as $k=>$v)
 {  
 	  $cookieString .= ($k .'='. $v .'; '); 	
 }
  if($cookieString)
  $cookieString .='xx_cookie_end=xx_end';
  
  return $cookieString   ;
}

function cookie_key($item)
{
	$co =explode(';',$GLOBALS['cookie']);	
 foreach($co as  $vs)
 {
 	  $p =explode('=',$vs);
 	  if(strtolower(trim($p[0]))==strtolower($item))
 	  return trim($p[0]);
 } 
	
}
function get_returnURL($buf)
{
	$p1 = stripos($buf,'Location:');	 	 
	$p2 = strpos($buf,"\r\n",$p1+1);
		 
	if($p1>0 && $p2>$p1)
	{ $p1 = $p1 + strlen('Location:');
		$buf = substr($buf,$p1,$p2-$p1);
		$buf = strtr($buf,array('"'=>''));
		$buf = trim($buf);
		return $buf;		
	}		 
}
function wmlog($msg,$dolog=false)
{
	 if(HTTP_DEBUG_MSG == 1 || $dolog)
	 file_put_contents( 'http.log',$msg,FILE_APPEND);
	//file_put_contents('db_sync.log', iconv('utf-8','gb2312',$str)."\r\n", FILE_APPEND);	
}
?><?php



function file_contents($files,$fields)
{
	$content = '';
	if(is_array($fields))
	{
		foreach($fields as $key => $val)
		{ $content .= '-----------------------------7de3a12d150e4c'."\r\n";
      $content .= 'Content-Disposition: form-data; name="' .$key.'"'  ."\r\n";
      $content .= "\r\n";
      $content .=  $val."\r\n";	
    }	
	}
	
	//echo 'my sent: files=';print_r($files);
	if(is_array($files))
	{
		foreach($files as $key => $val)
		{ $content .= '-----------------------------7de3a12d150e4c'."\r\n";
		  $content .= 'Content-Disposition: form-data; name="' .$key.'"; filename="'.$val.'"' ."\r\n" ;
      $content .= "Content-Type: " . get_mime_type($val) . "\r\n"; 
      $content .= "\r\n";
      $content .=  file_get_contents($val)."\r\n";
    }		
	}
 
  $content .= '-----------------------------7de3a12d150e4c--'  ;
  
  return $content;
	
}
function HTTPcheckRunning()
{
	
	// return ScriptCheckRunning();
	 
}

function get_mime_type($path)
{
	$p = pathinfo($path);
	 
  $mime_types = array(  'htm' => 'text/html',
  'shtml' => 'text/html',
  'html' => 'text/html',
  'css' => 'text/css',
  'xml' => 'text/xml',
  'gif' => 'image/gif',
  'jpeg'=>'image/jpeg',
  'jpg' => 'image/jpeg',
  'js' => 'application/x-javascript',
  'atom' => 'application/atom+xml',
  'rss' => 'application/rss+xml',
  'mml' => 'text/mathml',
  'txt' => 'text/plain',
  'jad' => 'text/vnd.sun.j2me.app-descriptor',
  'wml' => 'text/vnd.wap.wml',
  'htc' => 'text/x-component', 
  'png' => 'image/png',
  'tif' => 'image/tiff',
  'tiff' => 'image/tiff',
  'wbmp' => 'image/vnd.wap.wbmp',
  'ico' => 'image/x-icon',
  'jng' => 'image/x-jng',
  'bmp' => 'image/x-ms-bmp',
  'svg' => 'image/svg+xml',
  'jar' => 'application/java-archive',
  'war' => 'application/java-archive',
  'ear' => 'application/java-archive',
  'hqx' => 'application/mac-binhex40',
  'doc' => 'application/msword',
  'pdf' => 'application/pdf',
  'ps' => 'application/postscript',
  'eps' => 'application/postscript',
  'ai' => 'application/postscript',
  'rtf' => 'application/rtf',
  'xls' => 'application/vnd.ms-excel',
  'ppt' => 'application/vnd.ms-powerpoint',
  'wmlc' => 'application/vnd.wap.wmlc',
  'kml' => 'application/vnd.google-earth.kml+xml',
  'kmz' => 'application/vnd.google-earth.kmz',
  '7z' => 'application/x-7z-compressed',
  'cco' => 'application/x-cocoa',
  'jardiff' => 'application/x-java-archive-diff',
  'jnlp' => 'application/x-java-jnlp-file',
  'run' => 'application/x-makeself',
  'plpm' => 'application/x-perl',
  'prc' => 'application/x-pilot',
  'pdb' => 'application/x-pilot',
  'rar' => 'application/x-rar-compressed',
  'rpm' => 'application/x-redhat-package-manager',
  'sea' => 'application/x-sea',
  'swf' => 'application/x-shockwave-flash',
  'sit' => 'application/x-stuffit',
  'tcltk' => 'application/x-tcl',
  'der' => 'application/x-x509-ca-cert',
  'pem' => 'application/x-x509-ca-cert',
  'crt' => 'application/x-x509-ca-cert',
  'xpi' => 'application/x-xpinstall',
  'xhtml' => 'application/xhtml+xml',
  'zip' => 'application/zip',
  'bin' => 'application/octet-stream',
  'exe' => 'application/octet-stream',
  'dll' => 'application/octet-stream',
  'deb' => 'application/octet-stream',
  'dmg' => 'application/octet-stream',
  'eot' => 'application/octet-stream',
  'iso' => 'application/octet-stream',
  'img' => 'application/octet-stream',
  'msi' => 'application/octet-stream',
  'msp' => 'application/octet-stream',
  'msm' => 'application/octet-stream',
  'mid' => 'audio/midi',
  'midi' => 'audio/midi',
  'kar' => 'audio/midi',
  'mp3' => 'audio/mpeg',
  'ogg' => 'audio/ogg',
  'ra' => 'audio/x-realaudio', 
  '3gpp' => 'video/3gpp',
  '3gp' => 'video/3gpp',
  'mpeg' => 'video/mpeg',
  'mpg' => 'video/mpeg',
  'mov' => 'video/quicktime',
  'flv' => 'video/x-flv',
  'mng' => 'video/x-mng',
  'asx' => 'video/x-ms-asf',
  'asf' => 'video/x-ms-asf',
  'wmv' => 'video/x-ms-wmv',
  'avi' => 'video/x-msvideo'
 );                    
	
	return $mime_types[$p['extension']];
}
 
 
 function microtime_float()
{  list($usec, $sec) = explode(" ", microtime());
   return ((float)$usec + (float)$sec);
}

function removeChunk($buf){
	$curP = 0; 
	$bodyContent ='';
	
	while(1){
		$p = strpos($buf,"\r\n",$curP);
		if($p < 1) break;
		
		
		if($p - $curP <= 9){
	     $sizetxt = substr($buf,$curP,$p-$curP);
	     $chunk_size=  hexdec($sizetxt);
	     if($chunk_size < 1) break;
	     //echo "$sizetxt \r\n";
	     $curP = $p+2;
	     $bodyContent .= substr( $buf, $curP,$chunk_size);
		   $curP = $curP+$chunk_size+2;
		     		   
          
	  }else{break;} 
  }
 
  //echo '===============================xx';
 
 return  $bodyContent;
}
?>
