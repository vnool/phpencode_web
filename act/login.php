<?php
/*
 
DB.keys = array(
   'key'=>'openid'
 )
DB.weixin = array(
   'openid'=>data,
);

*/
/*
PC检查登陆key是否注册
*/

$_REQUEST['key'] = strtoupper($_REQUEST['key']);;

function do_newkey(){	
   $keys = cache_read('DB.keys');
   $key = 'PHP'.rand(0,10000);
   for($i=0; $i<10;$i++)
   {   if(!$keys[$key] || time() - $keys[$key]['time'] > 3600*24*10){ 
   	      $keys[$key] = array('openid'=>'wait', 'time'=>time());
          cache_write('DB.keys', $keys);

   	      $rlt['state']='ok';
   	      $rlt['key'] = $key;
          jsonPback($rlt); 
          return;
       }
       $key++;

   }
   
}

//echo $_REQUEST['key'];
function do_checkkey(){ 
	$key = $_REQUEST['key'];
	$keys = cache_read('DB.keys');
	$k = $keys[$key];
	if( $k ){
		if(time() - $k['time'] > 24*3600*10 ){ 
           //已经被使用过
           $rlt['state']='timeout'; 
		}else if($k['openid'] =='wait' ){
			//等待验证
 			$rlt['state']='wait'; 
 		}else if(strlen($k['openid']) < 5 ){
			//等待验证
 			$rlt['state']='wait'; 
		}else{ 

			$rlt['state']='ok'; 
			$rlt['openid']= $k['openid'] ;
	        $_COOKIE['openid']  =$k['openid'] ;
	        setcookie("openid", $k['openid'] , time()+3600*24*365);
        	
        	$keys[$key]['time'] = -1; //回收
   			cache_write('DB.keys', $keys);
        
        }
	}else{ 
        $rlt['state']='no'; 
	}

    jsonPback($rlt);
    return;
    
}

/*微信上根据key和openid一起发来 */

function do_login(){ 
   $key = $_REQUEST['key'];
   $openid = $_REQUEST['openid'];

   $keys= cache_read('DB.keys');
   if(time() - $keys[$key]['time'] > 24*3600 *10 ){ 
   	 echo "页面超时，请刷新" .$key ;
     return;
   }
   $keys[$key]['openid'] = $openid;
   cache_write('DB.keys', $keys);


   echo "php加密 登陆成功！";

   $db = cache_read('DB.weixin');
   if(!$db[$openid]){ 
   	   $db[$openid] = array('count'=>0,);
       cache_write('DB.weixin', $db);
   }
   
}


function do_state(){ 
   $openid = $_REQUEST['openid'];
   $db = cache_read('DB.weixin');
   if($db[$openid]){ 
      $rlt['state']='ok';
      $rlt['data'] = $db[$openid];
   }else{
   	  $rlt['state']='no';
   	  $rlt['msg']='no user information';
   }
    jsonPback($rlt);
    return;
 
}

$GLOBALS['KEYS'] = "XSJOEF98890S0EF--nouser;
[XFWOIE4FJ90X7F];[FU390FJ03I0090F]
[FOWEIF090F2F20];[JF40I904GJGK30];
[F03403JG304JK0];[JGF40G9309GI88];
[3049R340F34T4M];[340F0V0SJFG04X];
[FJW340F9340OOF--];[3F049ICBR3430V---];
[F3409F0JG00G00--];[40XVB34FSFSXFS---];
[year30jsjfv034];[year034fdxvrg7]";
 
/*根据VIP码 给相应的次数*/
function do_setvipcode(){ 
	$openid = $_REQUEST['openid'];
	$code = $_REQUEST['code'];
    if(strlen($code) < 5 ) {
    	$rlt['state']='err';
    	$rlt['msg']='too short';
    	jsonPback($rlt);
        return;
    }

    $OldKeyPath = 'vip.old.keys.php';
    if(!is_file($OldKeyPath)){ 
      file_put_contents($OldKeyPath, '<?php  ;');
    }
    $oldkeys = file_get_contents($OldKeyPath);
    if(strpos($oldkeys, ';'.$code.';') > 0 ){         
        $rlt['state']='err';
    	$rlt['msg']='VIP码已经被使用过，请重新提供';
    	jsonPback($rlt);
        return;

    }
    
    if(strpos($GLOBALS['KEYS'], '['.$code.']') > 0){ 
		  $db = cache_read('DB.weixin');
      if(substr($code,0,4) =='year'){
        $db[$openid]['type'] ='time';  
        $db[$openid]['endtime'] = time()+24*3600*365;  
      }else{
         $db[$openid]['count'] += 30;    
      }
	    
	    cache_write('DB.weixin', $db);
	    file_put_contents($OldKeyPath, $code .';', FILE_APPEND);
    }else{
    	$rlt['state']='err';
    	$rlt['msg']='VIP码不正确';
    	jsonPback($rlt);
        return;
    }
    

    
    
    $rlt['state']='ok';
	$rlt['msg']='';
	jsonPback($rlt);
	return;
	
}