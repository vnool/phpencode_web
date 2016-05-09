<?php 

 

function db_backup2($filename){
	global	$db_link, $db_host,$db_db,$db_user,$db_password;
	
	// 设置要导出的表
	$tables = list_tables($db_db);
	 
	$fp = fopen($filename, 'w');
	foreach ($tables as $table) {
	    //dump_table($table, $fp);
	    $dbc = get_table_content($table);
	     fwrite($fp, $dbc);
	}
	fclose($fp);

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
function dump_table($table, $fp = null)
{
    $need_close = false;
    if (is_null($fp)) {
        $fp = fopen($table . '.sql', 'w');
        $need_close = true;
    }
    fwrite($fp, "-- \n-- {$table}\n-- \n");
    $rs = mysql_query("SELECT * FROM `{$table}`");
    while ($row = mysql_fetch_row($rs)) {
        fwrite($fp, get_insert_sql($table, $row));
    }
    mysql_free_result($rs);
    if ($need_close) {
        fclose($fp);
    }
    fwrite($fp, "\n\n");
}
function get_insert_sql($table, $row)
{
    $sql = "INSERT INTO `{$table}` VALUES (";
    $values = array();
    foreach ($row as $value) {
        $values[] = "'" . addslashes($value) . "'";
    }
    $sql .= implode(', ', $values) . ");\n";
    return $sql;
}


function   get_table_content(  $table,   $crlf="\r\n")
{
          $schema_create   =   "";
          $temp   =   "";
          $result   =   mysql_query( "SELECT   *   FROM   $table");
          $i   =   0;
          while($row   =   mysql_fetch_row($result))
          {
                  $schema_insert   =   "INSERT INTO `$table` VALUES   (";
                  for($j=0;   $j<mysql_num_fields($result);$j++)
                  {
                          if(!isset($row[$j]))
                                  $schema_insert   .=   " NULL,";
                          elseif($row[$j]   !=   "")
                                  $schema_insert   .=   " '". addslashes( $row[$j] )."',";
                          else
                                  $schema_insert   .=   " '',";
                  }
                  $schema_insert   =   ereg_replace(",$", "",$schema_insert);
                  $schema_insert   .=   ");$crlf";
                  $temp   =   $temp.$schema_insert   ;
                  $i++;
          }
          return   $temp;
}
?>