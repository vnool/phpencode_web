<?php


function db_backup($filename){
	global	$db_link, $db_host,$db_db,$db_user,$db_password;
	
	// 设置要导出的表
	$tables = list_tables($db_db);
	 
	$fp = fopen($filename, 'w');
	foreach ($tables as $table) {
	    dump_table_data($table, $fp);	 
	}
	fclose($fp);

}
function db_table_dump($filename,$table){
	$fp = fopen($filename, 'w');
	  dump_table_data($table, $fp);	 	 
	fclose($fp);
	
}

function db_restore($filename){
	/* 如果冲突 请先用 db_clean_table 清空表*/
	require_once($filename );
}

function list_tables($database)
{
    $rs = mysql_list_tables($database);
    $tables = array();
    while ($row = mysql_fetch_row($rs)) {
        $tables[] = $row[0];
    }
    mysql_free_result($rs);
    return $tables;
}



function dump_table_data($table, $fp = null)
{ 
	fwrite($fp,'<? ' );
	$data = db_read_array($table);
	foreach($data as $dat){		 
		 $cmd =  var_export($dat,1) ;
		 $cmd = "db_write('$table', ".$cmd.");\r\n";
		 fwrite($fp,$cmd );
	}
	fwrite($fp,"?> \r\n" );
}


/*
function db_backup($filename)
{
  
//mysql_query("set names 'utf8'");
$mysql = "set charset utf8;\r\n";
$q1 = mysql_query("show tables");
while ($t = mysql_fetch_array($q1))
{
    $table = $t[0];
    $q2 = mysql_query("show create table `$table`");
    $sql = mysql_fetch_array($q2);
    $mysql .= $sql['Create Table'] . ";\r\n";
    $q3 = mysql_query("select * from `$table`");
    while ($data = mysql_fetch_assoc($q3))
    {
        $keys = array_keys($data);
        $keys = array_map('addslashes', $keys);
        $keys = join('`,`', $keys);
        $keys = "`" . $keys . "`";
        $vals = array_values($data);
        $vals = array_map('addslashes', $vals);
        $vals = join("','", $vals);
        $vals = "'" . $vals . "'";
        $mysql .= "insert into `$table`($keys) values($vals);\r\n";
    } 
} 

//$filename =  date('YmdHis') . ".sql"; //存放路径 
$fp = fopen($filename, 'w');
fputs($fp, $mysql);
fclose($fp);
//echo "数据备份成功";
 

}

function db_restore($filename)
{ 
  if (!file_exists($filename)) {
  	 echo "MySQL备份文件不存在，请检查文件路径是否正确！";
  	 return;
  }
  
   $sql_value="";
   $cg=0;
   $sb=0;
   $sqls=file($filename);
   
   foreach($sqls as $sql)
   {
    $sql_value.=$sql;
   }
   $a=explode(";\r\n", $sql_value);  //根据";\r\n"条件对数据库中分条执行
   $total=count($a)-1;
   
   
   mysql_query("set names 'utf8'");
   for ($i=0;$i<$total;$i++)
   {
    mysql_query("set names 'utf8'");
    //执行命令
    if(mysql_query($a[$i]))
    {
     $cg+=1;
    }
    else
    {
     $sb+=1;
     $sb_command[$sb]=$a[$i];
    }
   }
   echo "操作完毕，共处理 $total 条命令，成功 $cg 条，失败 $sb 条";
   //显示错误信息 
   if ($sb>0)
   {
     echo "<hr><br><br>失败命令如下：<br>";
     for ($ii=1;$ii<=$sb;$ii++)
     {
      echo "<p><b>第 ".$ii." 条命令（内容如下）：</b><br>".$sb_command[$ii]."</p><br>";
     }
   }   //-----------------------------------------------------------
   

	
}
*/
?>