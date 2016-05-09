<?php
ini_set('display_errors', 'on');

session_start();
error_reporting(E_ALL^E_NOTICE);
date_default_timezone_set('PRC');
set_error_handler('myErrorHandler');
register_shutdown_function('PageOnShutdown'); 






/*****************************/
if(count($_REQUEST) < 3) exitApp('sorry!');

$CopyRight = '/* encoded by http://phpc.sinaapp.com */';

$HISTORYBACK="<script>setTimeout('history.back();',3000);</script>";
  
$TRACE = FALSE;
{
	  $trcmsg = date("Y-m-d H:i:s",time())."\r\n";
	 $trcmsg .= var_export($_REQUEST,1). "\r\n";	  
	 file_put_contents('trc.txt', $trcmsg, FILE_APPEND);	
	 
	 $customcopy=$_POST['copyright'];
	 if(strpos('---'.$customcopy,'sinaapp') < 1){
	 	$trcmsg = date("Y-m-d H:i:s",time())."----";
	    $trcmsg .= $customcopy ."\r\n";	  
	 	file_put_contents('customer.txt', $trcmsg, FILE_APPEND);
	 } 
}
/**************************/

if(strtolower($_REQUEST['verifycheck']) != $_SESSION['verifycode'] )
{
	exitApp('验证码错误！'. $_SESSION['verifycode']. $HISTORYBACK) ;
}
$_SESSION['verifycode'] = rand();

$MAXfileSize = 1;
$isVIPer = false; 
$RLT = include('vipkeys.php');  


if(strpos('---'.$_POST['copyright'], 'phpc.sinaapp.com') < 1)
{     if($RLT['state']!='ok'){
		 exitApp($RLT['msg']);
	  } 	   
	  if($RLT['state']=='ok'){
	  	
		 $isVIPer = true;		
		 $CopyRight =   '/*'.$_POST['copyright'] .'*/';
		 $GLOBALS['MAXfileSize'] = 10;
	 }
}






include('phpEncode.class.php');
include("zip2.php");
loglog('new coming...'); 


$originalcode ='<?php
echo "hi";
return 555;
?>';


$workdir = 'encoded/'.rand();
mkdir($workdir );
//chmod($workdir, 0777);
copy('_inc.php', $workdir.'/_inc.php');
//chmod($workdir.'/_inc.php', 0666);
 

$isPOST = false;
$isMakeOneFile = false;
if($_POST['type']=='zip') /************************** zip files   ********************/
{    if(!$isVIPer){ 
        exitApp('非VIP 不提供zip包加密功能，请直接联系客服: 43906333');
     }
	//
    $isPOST = true;
	$zip_file = $_FILES['zipfile']['tmp_name'];
    checksize(filesize($zip_file));
    
    
	$z = new PclZip($zip_file);		
	if(isbadZip($z)) exitApp('bad zip file'); 
	
    $z->extract($workdir)==0?"Unzipping files failed: ".$z->errorInfo(true):"";
	
}else if($_POST['type']=='files'){  /************************** encode files   ********************/
	 $isPOST = true;
	 
	 
	if(!is_file($_FILES['userfile']['tmp_name'][0])   ){	   
   	  exitApp("请上传文件$HISTORYBACK");
	} 
	if( count($_FILES['userfile']['name'] ) > 1 && !$isVIPer){	   
   	  exitApp('非VIP用户，不提供多文件同时加密，请联系客服:QQ: 43906333');
	}
	
	
	checkLocker($CopyRight);
	
	if( count($_FILES['userfile']['name'] )==1 && !$_POST['incrun']){
	   $isMakeOneFile = true;
	   $OneCode = file_get_contents($_FILES['userfile']['tmp_name'][0]);
	   //exitApp('one file');
	}
	 
	
   if($isMakeOneFile==false){	
	  copyfiles($workdir);
   }
	 
}/**********************/	 

if(!$isPOST)  return;
 
$filter = '';
foreach($_POST['filter'] as $ft){
	$filter .= $ft.'|';
}
$filter=strtr($filter, array(' '=>''));
 
 
 //exit;

$ENC = new phpEncode();
$ENC->makeinc=false; //copy the inc
$ENC->fixedkey();
if($_POST['tagerr']==1) {
	$ENC->autoclosetags=true;
}

 
$ENC->copyright = $CopyRight;

if($isVIPer==true){ 
        $ENC->curdomain ='phpclib';
        $ENC->makeinc= true; //new inc 
}        
	
 


if($isMakeOneFile==true)
{
	$fullcode = $ENC->selfdecode($OneCode); 
    $tofile = $workdir.'/'.$_FILES['userfile']['name'][0];     
    file_put_contents($tofile, $fullcode);    
    @unlink( $workdir.'/_inc.php');
   
}else{
  $ENC->folder2folder($workdir.'/', $workdir.'/', $filter); 
}
 
 
 
 /*
 require_once "zip.php"; 
$zip = new PHPZip(); 
//$zip -> createZip("要压缩的文件夹目录地址", "压缩后的文件名.zip");　　 //只生成不自动下载 
$zip->downloadZip($workdir, "phpc.sinaapp.com.zip"); 
*/



header("Content-type: application/octet-stream");
header("Content-disposition: attachment; filename=\"phpc.sinaapp.com.zip\"");
 
$zipfile = 'encoded/zip.zip';
 
$zip = new PclZip($zipfile );//压缩文件
$zip->create($workdir.'/'); 
readfile($zipfile);
unlink($zipfile);
deldir ($workdir.'/');

loglog('well done.');

/*
//$buf = $ENC->code2file($originalcode,'test.php' ); 
 

//$buf = $ENC->selfdecode($originalcode ); 
//file_put_contents('fullencode.php',$buf);
//$buf = $ENC->folder2folder('C:\Users\28848500\DISK\work2014\washcar',  'C:\Users\28848500\DISK\work2014\washcar-encode'); 
 $ENC->makeinc=false;
 $ENC->fixedkey();
 //$ENC->folder2folder('from',  'to'); 
 $buf = $ENC->file2file('test.php',  'test2.php'); 
//$buf = $ENC->file2file('from/DEN.php',  'to/DEN.php', false); 
 //$buf  = $ENC->selfdecode($originalcode);  
*/


function copyfiles($workdir){
	$filesize = 0;
	 foreach( $_FILES['userfile']['name'] as $id=> $filename)
	 {    if(!$filename) continue;
	 	  $tmppath = $_FILES['userfile']['tmp_name'][$id];
	 	  $filesize +=filesize($tmppath); 
	 }
	 checksize($filesize);
	 
	 foreach( $_FILES['userfile']['name'] as $id=> $filename)
	 {
	 	     if(!$filename) continue;
	 	  
	 	    $tmppath = $_FILES['userfile']['tmp_name'][$id];
	 	    $topath = $frompath = $workdir.'/'.$filename;
	 	    move_uploaded_file($tmppath,  $topath );
	 	    // chmod($topath, 0666);
	 	    
	 	    //$topath = $workdir.'/xxx.php';
	 	    // $buf = $ENC->file2file($frompath, $topath); 
	 	    // $buf = $ENC->code2file($originalcode, $topath ); 
	 	    
	 }
	
}
function isbadZip($z){
		$zlist = ( $z->listContent());
	foreach($zlist as $list){
		$filename = $list['filename'];
		if(substr($filename,0,2)=='..'  or  substr($filename,0,1)=='/' or  substr($filename,0,1)=='\\')
		{
			exitApp('bad zip file');
		   return true;
		}
    } 
	
}

function checkLocker($CopyRight){
	$lockcode=<<<'EXITFUNC'
	function phpc_exitapp_($msg){
	echo "<HTML><HEAD><TITLE>PHP 加密</TITLE>
    <META http-equiv=Content-Type content='text/html; charset=utf-8'>
   </HEAD><BODY><br><br><center><h2>".$msg
     ."<br><br> _COPYRIGHT_</h2></body></html>";
   exit;
   	}
EXITFUNC;

$lockcode=str_replace('_COPYRIGHT_',  $CopyRight, $lockcode);

	$lockdomain = trim($_REQUEST['lockdomain']);
	$lockdays = trim($_REQUEST['lockdays']);
	if($lockdomain){
		$lockdomain=str_replace('；', ';',$lockdomain);
	   $lockcode1=<<<'LOCKDOMAIN'
	    $_phpc_domain_=$_SERVER['SERVER_NAME'];    
        if(!$_phpc_domain_)   $_phpc_domain_=$_SERVER['HTTP_HOST'];
        if(!$_phpc_domain_)  phpc_exitapp_( 'please run in web environment.');

        if(strpos(';'.$_phpc_domain_.';', ';_LOCKDOMAIN_;')  < 1  && $_phpc_domain_!='localhost') 
        {phpc_exitapp_( '无法在此域名下运行');}
LOCKDOMAIN;
         $lockcode1 = str_replace('_LOCKDOMAIN_', $lockdomain, $lockcode1);     
         $lockcode .= $lockcode1; 
	}
	
	if($lockdays > 0){
	    $lockcode2 = <<<'LOCKDAYSx'
	    date_default_timezone_set('PRC');
       if(time()-_CURTIME_ > _LOCKDAYS_ *3600*24)  phpc_exitapp_( '权限已过期'); 
LOCKDAYSx;
         $lockcode2 = str_replace('_LOCKDAYS_', $lockdays, $lockcode2); 
         $lockcode2 = str_replace('_CURTIME_', time(), $lockcode2); 
         $lockcode .= $lockcode2;
	}
	
 //exit( '>>>'.$lockcode);
	if($lockdomain ||  $lockdays > 0){
		$lockcode  = '<?php '. $lockcode .' ?>';
		$codepath = $_FILES['userfile']['tmp_name'][0];
		$Codebuffer = file_get_contents($codepath);
		$Codebuffer = $lockcode.$Codebuffer;
		file_put_contents($codepath,$Codebuffer);
	}
	
}
 function checksize($filesize ){
 	require_once('cache.class.php');
     $counter = cache_read('counter');
     $counter['count']++;
     $counter['size']+=$filesize;
     cache_write('counter', $counter);
     
     $sizM = $GLOBALS['MAXfileSize'];
     if($sizM < 1 ) $sizM = 1;
     
     if($filesize > $sizM* 1024000)
	 {
	    deldir ($workdir.'/');
	    exitApp('files size is too big (1M)');
	    return ;
	 }	
 	
 }
 function loglog($msg){
 	$msg = "\r\n".date('Y-m-d H:i:s',time()) . '----'.$msg;
 	file_put_contents('log.txt', $msg, FILE_APPEND);
 }
 
 
 function deldir($dir) {
  //先删除目录下的文件：
  $dh=opendir($dir);
  while ($file=readdir($dh)) {
    if($file!="." && $file!="..") {
      $fullpath=$dir."/".$file;
      if(!is_dir($fullpath)) {
          unlink($fullpath);
      } else {
          deldir($fullpath);
      }
    }
  }
 
  closedir($dh);
  //删除当前文件夹：
  if(rmdir($dir)) {
    return true;
  } else {
    return false;
  }
}

function exitApp($msg){
  echo "<HTML><HEAD><TITLE>PHP 加密</TITLE>
    <META http-equiv=Content-Type content='text/html; charset=utf-8'>
   </HEAD><BODY><br><br><center><h2>
   ";
  echo $msg;
  echo "</h2></BODY></HTML>";
   
   exit;
	
}


function PageOnShutdown()
{       $err = error_get_last();
        if(!$err) return;
         $errstr= $err['message'];
         $errfile = $err['file'];
         $errline = $err['line'];
        // var_dump($err);
       echo ("[PHP.ERROR] Fatal:  $errstr \n($errfile :$errline)"); 
       exit;
       //echo($err);
}
function myErrorHandler($errno, $errstr, $errfile, $errline)  
{
// $errfile=str_replace(getcwd(),"",$errfile);
//$errstr=str_replace(getcwd(),"",$errstr); 
    
    switch($errno) {
 
    case E_USER_ERROR:  
       echo("[PHP.ERROR][$errno] $errstr  \n($errfile :$errline)"); 
       echo(debug_backtrace());
        exit(1);  
        break;  

    case E_USER_WARNING:  
        echo("[PHP.WARINIG][$errno] $errstr  \n ($errfile :$errline)"); 
        break;  

    case E_USER_NOTICE:  
       // echo( "[PHP.NOTICE][$errno] $errstr \n($errfile :$errline)");  
        break;  

    default:  
         //echo( "[PHP.Unknown][$errno] $errstr \n($errfile :$errline)");  
        break;  
    }  
 
    /* Don't execute PHP internal error handler */  
    return true;  
}  
