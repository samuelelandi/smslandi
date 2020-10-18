#!/usr/bin/php
<?php
// CONFIGURE THIS VARIABLE
$Smsuser='smsuser';
$Password='aszxqw12';
// END CONFIGURATION
$to=$argv[1];
$body=$argv[2];
syslog(LOG_INFO,"smslandidlr.php - DLR to: ".$to." body: ".$body);
if($body!="Delivered") exit(0);
//BUILD THE HTTP GET
$u= 'http://127.0.0.1:13015/sms?username=';
$u.=urlencode($Smsuser);
$u.='&password=';
$u.=urlencode($Password);
$u.='&to=';
$u.=urlencode($to);
$u.='&dlr-mask=1&dlr-mid=';
$p=strpos($to,"-");
syslog(LOG_INFO,"msgidh: ".$msgidh);
$msgidp=substr($to,0,$p);
$msgidh=str_replace("sip:","",$msgidp);
syslog(LOG_INFO,"msgidh: ".$msgidh);
$msgid=file_get_contents("/tmp/".$msgidh);
if(strlen($msgidh)>0) unlink("/tmp/".$msgidh);
$u.=urlencode($msgid);
syslog(LOG_INFO,"url: ".$u);
//MAKE HTTP CALL
$cURLConnection = curl_init($u);
curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);
$r=curl_exec($cURLConnection);
syslog(LOG_INFO,"curl answer: ".$r);
curl_close($cURLConnection);
exit(0);
?>