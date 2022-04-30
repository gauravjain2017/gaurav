<?php
$logo_image = $this->session->userdata('themedefault')['imagename'];
/*$url_segment = explode(".", $_SERVER['HTTP_HOST']);
if ($url_segment[0] != 'www') {
$client_uniquename = $url_segment[0];
} else {
$client_uniquename = $url_segment[1];
}*/
$client_uniquename = $this->config->item('subdomain_client'); 

$message=<<<message
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>Email Template</title>

<style type="text/css">
a {
color:#0853a6;
text-decoration:none;
}
body{
padding:0;
margin:0;
}
</style></head>

<body bgcolor="#f2f2f2" style="font-family:Arial, Helvetica, sans-serif;">

<table border="0" cellpadding="0"  cellspacing="0" width="600px" style="padding:10px 0px 0px 0px;" align="center">
<tr>
<td align="center" height="5"  bgcolor="#074181">
</td>
</tr>
<tr>
<td style="border-bottom:2px solid #f2f2f2; background:#fff;">
<table width="600" style="padding:25px;">
<tr>
<td style=" text-align:center;"><img width="130" src="$logo_image" alt=""  /></td>
</tr>
</table>
</td>
</tr>
<tr>
<td width="600" bgcolor="#FFFFFF" style="padding-left:30px; padding-right:30px; padding-top:20px; padding-bottom:20px; font-family:Arial, Helvetica, sans-serif;   font-size:13px; text-align:justify;">

<h3 style="font-size:13px; color:#555; font-weight:bold; font-family:Arial, Helvetica, sans-serif; margin:0px;">Dear $login_user_name ,</h3> <br>
<p style="font-family: Arial, Helvetica, sans-serif;font-size: 13px;line-height: 20px;margin: 0px;">
Thank you for completing the $client_uniquename  business growth simulation tool to create your own customized P&L and growth plan for your business.  You have just completed a nine step process for selecting the products you will be selling, define a set of goals and strategies for growth, and finalize your assumptions for pricing, staffing, and marketing expense for your business.  You can retrieve, review and refine your customized plan at any time by accessing this link below
<br /><br />
<a href="$login_decode_link" >Client url for accessing their report</a>
<br /><br />
Each time you return to your report the latest assumptions and values will be displayed.  You can update your assumptions at any time and when you click on step #10 (finish and save) you'll receive an updated email with this link for your records to confirm any new plan changes
</p>
<br />


<p style=" font-family:Arial, Helvetica, sans-serif; font-size:18px; margin:0px; padding:0px; color:#666; margin:0px;">Kind regards,</p>
<p style="font-family: Helvetica, Arial, sans-serif; font-size: 10px; margin:0px; padding:0px 0px 7px 0px; line-height: 11px; color: #999;">
<br>
<span id="title-input" style="color: #999; font-size:12px;" class="txt">$client_uniquename</span>
<br>
<br>
<br>
<span id="title-input" style="color: #999; font-size:12px;" class="txt">Thanks $client_uniquename Team</span>
</td>
</tr>
<tr>
<td height="70px" align="center" bgcolor="#074181" style="font-family:Arial, Helvetica, sans-serif; color:#FFF; text-align:center; font-size:12px;">
<span style="font-size:12px;display:inline-block; padding: 22px 0px 0px 0px; color: #0CF;"> © Successful Channels Inc.</span>
<p style="padding:0px 0px 20px 0px; font-size:12px; font-family:Arial, Helvetica, sans-serif; line-height:20px;">
PO Box 735 1672 Falmouth Road, Centerville MA, 02632 USA<br>
</p>
</td>
</tr>
</table>
</body>
</html>
message;

$subject =  "From " . $session_uerdata['all_data']['client_name'] . " admin system";
$headers = "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
$headers .= 'From: <'.ADMIN_EMAIL.'>' . "\r\n";
if ($to_email != '') {
mail($to_email,$subject,$message,$headers);
}

?>