<?php
   require_once('cache.class.php');

   $openid = $_REQUEST['openid'];
   if(strlen($openid ) > 5)
   {
	   $db = cache_read('DB.weixin');
	   if($db[$openid])
	   {
           if($db[$openid]['type']=='time')
	   	   {
		   	  	if($db[$openid]['endtime'] < time()){
					$msg = "<center><br><br>vip授权码时间已过";
					$msg .= "<br><a href='http://phpc.sinaapp.com/?view=index&sub=contact'>请联系客服QQ: 43906333 </a>";
					$msg .= "</center>center>";
					exitApp($msg);
					$RLT['state']= 'err';
					return $RLT;
		   	  	}else{
		   	  		$RLT['msg']= 'ok';
					$RLT['state']= 'ok';
					return $RLT;
		   	  	}   	     

	        }else if(array_key_exists('count', $db[$openid])){

					if($db[$openid]['count'] >=1 ){ 
						$db[$openid]['count'] = $db[$openid]['count']-1;
						cache_write('DB.weixin', $db);
						$RLT['msg']= 'ok';
						$RLT['state']= 'ok';
						return $RLT;
					}else if($db[$openid]['count'] < 1){
						$msg = "<center><br><br>vip授权码已经被使用过30次";
						$msg .= "<br><a href='http://phpc.sinaapp.com/?view=index&sub=contact'>请联系客服QQ: 43906333 </a>";
						$msg .='</center>';
						exitApp($msg);
						$RLT['state']= 'err';
						return $RLT;
				  }			      
			
	        }else{
	        	$msg = "<center>找不到用户信息";
				$msg .= "<br><a href='http://phpc.sinaapp.com/?view=index&sub=contact'>请联系客服QQ: 43906333 </a>";
				$msg .='</center>';
				exitApp($msg);
				$RLT['state']= 'err';
				return $RLT;
	        }

	   }else{ 
            $RLT['state']= 'err';
			$RLT['msg']= 'openid error';
			return $RLT;
	   }
   }else{ 
      $RLT['state']= 'err';
	  $RLT['msg']= 'plase login.';
	  return $RLT;
   }
//----------------------------------------------------------------->


$vipkeystr ='ETRWEGNGOW2342C, WEORJ23O45I3465JN,
JO34580GN0QWELN8, W4053JT34J3T3F00K,
JSDFGERTW45JVG3G3, GJ9348EG9E5DCRY9PL,
DG340T93GJ34ERG349, DRRNJ34T03GJ0G33T4,
DJET034T850EG59889, K40JVE98TU5GJOOD8OD5,
K98TGUE9HG459M045VOL---, FJ249FU304558555,
W984UFG39J84J9G4,  F49T830GJ0550GJ045,
JFO3480F8G34FJ0G4G, J209I0IJ0G0JDPSPOP,
J304IG0LPDE9O4PDPD, G0699RPKGPDFLDERJP,
W39FNEGE5IO0DKLOEES, JSORWEOPLSPW0WL,
F349RLDTUI34OJSRTE,
XUTO40030G4990G04,XORTOIRJGOB03049U09,
X98504986549650994,X00994ERW33455388DI,
XJ34G9485UG549UG80,X0G45900G9540400459,
X0495T0459IU9I000I,X004549J45V555VGRV5';

$vipkeys = explode(',', $vipkeystr );


if(is_file('vipkeys.old.php')) include('vipkeys.old.php');

//包年
$BAONIAN ="--;252620425-REEOKDC;";
if(strpos($BAONIAN, ';'.$_POST['vipcode'].';') > 0){ 
   		 $RLT['msg']= 'ok';
		 $RLT['state']= 'ok';
		 return $RLT;
}
//普通
foreach( $vipkeys as $k){
	$k = trim($k);
	if($_POST['vipcode']==$k){
		 $vipkeysSave[$k]++;
		 if($vipkeysSave[$k] > 30){		 	
		 	//header("Content-type: text/html");
			$msg = "<center><br><br>vip授权码已经被使用过30次";
			$msg .= "<br><a href='http://phpc.sinaapp.com/?view=index&sub=contact'>请联系客服QQ: 43906333 </a>";
			exitApp($msg);
		 	return $RLT;
		 }
		 
		 $savestr = var_export($vipkeysSave,1);
		 $savestr ="<?  \$vipkeysSave = $savestr ;";
		 file_put_contents('vipkeys.old.php', $savestr);
		 $RLT['msg']= 'ok';
		 $RLT['state']= 'ok';
		 return $RLT;		 
	}
	
}

$RLT['msg']= 'VIP授权码错误';
$RLT['state']= 'codeerror';
    //header("Content-type: text/html");
	$msg = "<center><br><br>vip授权码错误";
	$msg .= "<br><br><a href='http://phpc.sinaapp.com/?view=index&sub=contact'>请联系客服QQ: 43906333 </a>";
	exitApp($msg);
return $RLT;