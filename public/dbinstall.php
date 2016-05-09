<meta http-equiv="content-type" content="text/html;charset=utf-8">
<?php

//以后改成import SQL

 require_once('db.function.php');
 
// mysql_query('update `tao_cache` set `saler`=26');

if(is_file('INSTALL_FINISHED'))
exit('错误:请勿重复安装');

$sub_prefix = '';
// admin
// appcmd 命令入口
// 
$sql = "CREATE TABLE IF NOT EXISTS `" . $tb_prefix .$sub_prefix."class` (
`uid` BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
`name` TEXT ,
`total` INT  DEFAULT '0'

) ;";
$result = mysql_query($sql) or die("数据库访问失败,表user,".mysql_error());



$sql = "CREATE TABLE IF NOT EXISTS `" . $tb_prefix .$sub_prefix."goods` (
`uid` BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
`name` TEXT ,
`price` INT ,
`classid` BIGINT,
`description` LONGTEXT,
`taoid` TEXT 

) ;";
$result = mysql_query($sql) or die("数据库访问失败,表user,".mysql_error());



$sql = "CREATE TABLE IF NOT EXISTS `" . $tb_prefix .$sub_prefix."address` (
`uid` BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
`userid` BIGINT ,
`province` TEXT ,
`city` TEXT,
`area` TEXT,
`detail` TEXT ,
`zip` TEXT,
`tel` TEXT

) ;";
$result = mysql_query($sql) or die("数据库访问失败,表user,".mysql_error());




$sql = "CREATE TABLE IF NOT EXISTS `" . $tb_prefix .$sub_prefix."order` (
`uid` BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
`userid` BIGINT ,
`gid` BIGINT ,
`notes` TEXT,
`count` INT,
`addrid` BIGINT , 
`size` TEXT,
`color` TEXT,
`totalmoney`BIGINT,
`state` TEXT,
`date` BIGINT,
`alipayid` TEXT,
`alistate` TEXT

) ;";
$result = mysql_query($sql) or die("数据库访问失败,表user,".mysql_error());
 
 
$sql = "CREATE TABLE IF NOT EXISTS `" . $tb_prefix .$sub_prefix."pay` (
`uid` BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
`alipayid` TEXT ,
`gids` TEXT,
`money` TEXT,
`date` BIGINT,
`state` TEXT 

) ;";
$result = mysql_query($sql) or die("数据库访问失败,表user,".mysql_error());
 


 
$sql = "CREATE TABLE IF NOT EXISTS `" . $tb_prefix .$sub_prefix."user` (
`uid` BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
`name` TEXT ,
`password` TEXT,
`email` TEXT,
`tel` TEXT,
`state` TEXT 

) ;";
$result = mysql_query($sql) or die("数据库访问失败,表user,".mysql_error());
 
 
 
touch('INSTALL_FINISHED');
echo '数据库安装完成';
exit;//========================================== 退出


 
 