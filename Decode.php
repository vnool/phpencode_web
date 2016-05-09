<?php
 



include('phpEncode.class.php');
include("zip2.php");
loglog('new coming...'); 


$originalcode ='<?php
echo "hi";
return 555;
?>';

 

$ENC = new phpEncode();
$ENC->makeinc=false; //copy the inc
$ENC->fixedkey();
if($_POST['tagerr']==1) {
	$ENC->autoclosetags=true;
}

 
$ENC->copyright = $CopyRight;
 
 $ENC->curdomain ='phpclib';
 $ENC->makeinc= true; //new inc 
 
 
 
$ENC->folder2folder( '/', $workdir.'/', $filter); 
 