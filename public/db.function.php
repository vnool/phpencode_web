<?php
 /*****************
   VER 2013.12.2
 ******************/


if(!defined('DB_USE_MYSQL'))define('DB_USE_MYSQL','1'); //使用mysql保存数据
define('DB_USE_CACHE','1'); //使用文件cache


define('ERR_NUM_DB_ERR','0');

 global $db_host, $db_user, $db_password, $db_db, $tb_prefix;  
 
 
 $db_link = null;
if(DB_USE_MYSQL){
	require_once('db.config.php');
  $db_link = mysql_connect($db_host,$db_user,$db_password)
  or dieX(ERR_NUM_DB_ERR."\n数据库无法连接:" .  mysql_error());
  mysql_select_db($db_db);
  mysql_query("set names utf8;");
}


function dieX($msg){
	echo "<pre> $msg <br>"; 	
	debug_print_backtrace();	
	exit();
}

/*****************************mysql*******************************/

function db_init(){
global	$db_link, $db_host,$db_user,$db_password;
	
$db_link = mysql_connect($db_host,$db_user,$db_password)
      or dieX(ERR_NUM_DB_ERR."\n数据库无法连接:" .  mysql_error());
  mysql_select_db($db_db);
  mysql_query("set names utf8;");
}

function db_deinit(){
global	$db_link, $db_host,$db_user,$db_password;
	
 mysql_close($db_link);

}

function db_query($sql){
  global	$db_link, $db_host,$db_user,$db_password;
	
   $query = SQLquery($sql);
   return $query;
}
function SQLquery($sql){
	return  mysql_query($sql) or dieX(ERR_NUM_DB_ERR."\n数据库执行错误: ".mysql_error() . "\r\n<br>SQL: ". $sql);
}
//如果想取个别字段，请设置$GLOBALS['db_fields']的值， 一次性有效
function SQLselect($sql){
	 
	if($GLOBALS['db_fields'])
	{ 
		$sql = str_replace('*', $GLOBALS['db_fields']  ,$sql);
	}
	$GLOBALS['db_fields'] = '';unset($GLOBALS['db_fields']);
    if(defined('SQLITE_DEBUG')) echo $sql."<br>";
	$query = SQLquery($sql);
	return $query;
	
}
//读文件 $pageset=array('pagesize'=>20, 'start'=>5)
function db_read_array($table,$pageset=null,$container='', $orderby='')
{
	$table = preix_table($table);
	$sql="SELECT * FROM `" .$table. "` " ;
	
	if('' != $container) 
	$sql .= ' WHERE '. $container;
	 
	if(is_array($pageset) && count($pageset))
	{
		$start =  $pageset['start'];
		$sql .= ( " LIMIT " .$start . ",". ($pageset['pagesize'])  );		
	}
  if($orderby) 
  {
    $sql .=' order by '.$orderby;	
  }
	$query = SQLselect($sql) ;
	 
	$result =array();
	while ($row = mysql_fetch_assoc($query)) {
         $result[$row['uid']] = $row;
  }
  return $result;
}

function db_count($table,$container='')
{
	$table = preix_table($table);
	$sql="SELECT count(`uid`) FROM `" .$table. "` " ;
	
	if(''!=$container) 
	$sql .= ' WHERE '. $container;
	 
	  
	$result = SQLquery($sql); 
	 
  return mysql_result($result,0); ;
}
//字段  求和
function db_sum($table,$KEY,$container='')
{
	$table = preix_table($table);
	$sql="SELECT SUM(`$KEY`) FROM `" .$table. "` " ;
	
	if(''!=$container) 
	$sql .= ' where '. $container;
	 
 //echo "\r\n $sql \r\n" ;
	$result = SQLquery($sql); 
	 	 
  return mysql_result($result,0);  
}

function db_read($table,$filter=null,$pageset=null){
	
	if($filter =='')
	{
		 return db_read_array($table,array());		 	 
	}else if(is_numeric($filter)){// read one by id
		$uid = $filter;
		return db_read_one($table,$uid);
	}else if(is_string($filter)){// filter		 
		 if(!is_array($pageset))// 
		 {
		 	  return db_one_byfilter($table,$filter);
		 }else{
		 	  return db_read_array($table,$pageset,$filter);		 	 
		 }
		
	}
	
}
//读记录
function db_read_one($table,$uid)
{  $table = preix_table($table);
	
	$sql="SELECT * FROM `" .$table. "` WHERE uid='".$uid ."'";;
	$query = SQLselect($sql);
	 
	 $row = mysql_fetch_assoc($query);
    
  return $row;
}
//读记录
function db_one_byfilter($table,$container)
{  $row = null;
	$data = db_read_array($table,array(),$container);
   if(count($data)>0)
   $row = current($data);
  return $row;
}

//最后一条记录
function db_read_last($table)
{  $table = preix_table($table);
 

	$sql="SELECT * FROM `" .$table. "`  order by uid desc limit 1";;
	$query = SQLselect($sql);
	 
	$row = mysql_fetch_assoc($query);
    
  return $row;
}

 
//写缓存内容
function db_write($table,$data,$uid='')
{  
	$num_rows =0;
	
	if (!is_array($data))
	{return;}
	
	$uid = $uid ? $uid : $data['uid'] ;
	if ($uid !='')
	{$sql = "SELECT `uid`  FROM `" .preix_table($table). "` WHERE uid='" . $uid ."'" ;
 
	 $query =SQLquery($sql); 
	 $num_rows = mysql_num_rows($query);
	}
	
	if($uid)  $data['uid'] = $uid ; 
	if( $num_rows < 1)
	{		
		db_insert($table,$data);
		return mysql_insert_id();
				
	} 
	else
	{
		
		db_update($table,$data);
		return $data['uid'];
	}
}

function db_update($table,$data,$indexkey='uid')
{ $i=0;$f='';$v='';
	
	$table = preix_table($table);	
	 
	
	foreach($data as $key=>$val)
	{		 
		//if(in_array($key,$field))  strict
		{
			$val = addslashes(trim($val));
			if($val==null)  
			{
				$val = 'null';
		  }else
		  {
		  	$val = "'". $val ."'";
		  }
			
			$d=$i>0?',':'';
			$f.=$d."`".$key."`=".$val;
			$i++;
		}
		
	}
	
	$sql="update  `". ($table)."`  set ";
	$sql.=$f." where ".$indexkey."= '".$data[$indexkey] ."'";
	                      
	//                     
 
	 $result = SQLquery($sql); 
 
	
}



function db_insert($table,$data )
{ $i=0;$f='';$v='';
	$table = preix_table($table);	
	 
	$strict = 0;
	if (!is_array($data))
	{return;}
	foreach($data as $key=>$val)
	{
		  $val = addslashes(trim($val));
		  if($val==null)  
			{
				$val = 'null';
		  }else
		  {
		  	$val = "'". $val ."'";
		  }
		  
			$d=$i>0?',':'';
			$f.=$d.'`'.$key.'`';
			$v.=$d.  (($val));
			$i++;
		 
		
	}
	$replace = 0;
	$type=$replace?'replace':'insert';
	$sql =  $type." into `". ($table)."` (".$f.") values(".$v.")";
	
	// exit($sql);
	 
	$result = SQLquery($sql); 
}

//删除一个条
function db_delete_one($table,$uid)
{  $table = preix_table($table);
	
	$sql="DELETE FROM `". $table ."`   WHERE uid='".$uid ."'  limit 1";;
	$query = SQLquery($sql); 
 
}
//删除n条
function db_delete_more($table,$container='0')
{  $table = preix_table($table);
	
	$sql="DELETE FROM `". $table ."`   WHERE " .$container;;
	$query = SQLquery($sql); 
 
}

//清除数据
function db_clean_table($table)
{ $table = preix_table($table);
	
	$sql="TRUNCATE `". $table ."`";;
	$query = SQLquery($sql); 
 
}


function preix_table($table)
{  global $tb_prefix;
	  
	  if(substr($table,0,strlen($tb_prefix))==$tb_prefix)
	  return $table;
	  
	  return $tb_prefix .$table	;	
}



function debug_assert($msg)
{
	
 	echo "<div style='border；1px solid #339933;background:#ddffdd'>" ;
 	if(is_array($msg))
 	  print_r($msg) ;
 	else
 	  echo $msg;
 	  
 	echo "</div>";
 	exit;
}

?><?php

















//读文件
function cache_read($file)
{  
	$file =  cachefilepath($file,$folder);
	//echo $file . '===';
	if(file_exists($file))
	{  include($file);	 
	   return $content;
	}
}
 
//写缓存内容
function cache_write($file,$content)
{
	$file = cachefilepath($file,$folder);
	if(is_array($content))
	{
		$content= var_export($content,1);
	}
	else
	{
		$content='array()';
	}	 
	/*$content = '<?  $content = unserialize (\'' . serialize($content) . '\');  ?>';	  */
	$content = '<?  $content =    ' .  ($content) . '  ;  ?>';	 
	
	if(function_exists('file_put_contents'))
	{
		file_put_contents($file,$content);
	}
	else
	{
		$fp=fopen($file,'w');
		  flock($fp,2);  //加锁
		fwrite($fp,$content);
			if(flock($fp,3))    //解除锁定
		fclose($fp);
	}
}
function cachefilepath($file)
{  
	$folder=$GLOBALS['db_cache_path'];
	return  $folder.'cache.'.($file) .'.php';
}
function cache_outofdate($file, $long){
  $file = cachefilepath($file);
  if(!is_file($file))  return true;
  
  $t = filemtime ($file);
  if(time()-$t > $long)  return true;
  else return false;
  
}


function err_handler($errno, $message, $errfile, $errline)
{
	$msg = $message   .". (file) $errfile ($errline)";
	 if( strpos("mm" .$msg  ,'Undefined index') > 0 )
	   return;
	 
  switch ($errno) {
  case E_CORE_ERROR:
  case E_USER_ERROR:
  case E_ERROR:
    {echo "\r\n严重错误:". $msg ;
    }break;
  default:
    //mylog( $msg );
   break;
  }
}
 set_error_handler('err_handler');
 
 