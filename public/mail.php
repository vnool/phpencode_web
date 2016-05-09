<?php

require_once('class.phpmailer.php');

function sendmail($sendTo, $title, $body){
	 
	 $CUR_TAG = 'SITE';   
   $config = get_config($CUR_TAG);
   
   
   
		try {
			$mail = new PHPMailer(true); //New instance, with exceptions enabled
		
			//$body             = file_get_contents('contents.html');
			$body             = preg_replace('/\\\\/','', $body); //Strip backslashes
		
			$mail->IsSMTP();                           // tell the class to use SMTP
			$mail->SMTPAuth   = true;                  // enable SMTP authentication
			$mail->Port       = 25;
			if($config['SITE_smtpssl']){
		   	$mail->Port       = 465;                    // set the SMTP server port
		    $mail->SMTPSecure = "ssl";		     
		  } 
		  
		  
			$mail->Host       = $config['SITE_smtp']; // SMTP server
			$mail->Username   = $config['SITE_smtpname'];;     // SMTP server username
			$mail->Password   = $config['SITE_smtppsw'];            // SMTP server password
		
		
			//$mail->IsSendmail();  // tell the class to use Sendmail
		
			//$mail->AddReplyTo("name@domain.com","First Last");
		
			$mail->From       = $config['SITE_mail'];
			
			if($config['SITE_smtpfromname']) 
			 $mail->FromName   = $config['SITE_smtpfromname'];
		
			//$to = "someone@example...com";
			
			
		  if(is_array($sendTo))
		  { foreach($sendTo as $to){
			    $mail->AddAddress($to);
			  }
		  }else{
		  	$mail->AddAddress($sendTo);
		  	
		  }
		  
			$mail->Subject  = $title;
		
			$mail->AltBody    = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test
			$mail->WordWrap   = 80; // set word wrap
		
			$mail->MsgHTML($body);
		
			$mail->IsHTML(true); // send as HTML
		
		//echo "<pre>";		 var_dump($mail); //exit;
		 
      $rlt['state'] = $mail->Send();
			
			return $rlt;
			
		} catch (phpmailerException $e) {
			//echo "<pre>";var_dump($e);
			$rlt['msg'] = $e->errorMessage();
			$rlt['state'] = false;
		}
		//var_dump($rlt);
		return $rlt;
}


?>