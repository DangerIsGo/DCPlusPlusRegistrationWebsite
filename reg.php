<?php
$act = (isset($_POST['submit']))?$_POST['submit']:"";
$email = (isset($_POST['email']))?$_POST['email']:"";
$username = (isset($_POST['nick']))?$_POST['nick']:"";
$fullname = (isset($_POST['name']))?$_POST['name']:"";
$pw = (isset($_POST['pw']))?$_POST['pw']:"";
$confpw = (isset($_POST['confpw']))?$_POST['confpw']:"";
$userfail = $pwmismatch = $pwfail = $emailfail = $namefail = "0";

$Host = 'localhost';
$DBUser = 'DcRegSite';
$DBPass = 'supersecret';
$DBName = 'dcplusplus';

if($act == "Submit")
{
	$chars = "./0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
	$salt = "";
	for($i=0;$i<8;$i++)
	{
		$r = rand(0,strlen($chars)-1);
		$salt = $salt . $chars[$r];
	}
	$salt = "\$1\$".$salt."\$";
	$pwhash = crypt($pw, $salt);
	$encemail = sha1(strtolower($email));
	#$encemail = encrypt(strtolower($email), $encryption_key);

	//verify integrity of fields
	if(strlen($username) <3){$userfail="1";}
	if(strlen($username) >25){$userfail="2";}
	if(strlen($fullname) <1){$namefail="1";}
	if(!($pw == $confpw)){$pwmismatch="1";}
	if(strlen($pw) <4 or strlen($confpw)<4){$pwfail="1";}
	if(strlen($email)<5){$emailfail="1";}
	#if(!ctype_alnum($username))	//username can only be alphanumeric
		#{	/* username is not alphanumeric */
			#$tempnick = str_replace("_","",$username);
			#$tempnick = str_replace("-","",$tempnick);
			#$tempnick = str_replace("|","",$tempnick);
			#if(!ctype_alnum($tempnick)){$usernonalpha = 1;}
		#}
	if($userfail=="1" or $userfail=="2" or $pwmismatch=="1" or $pwfail=="1" or $emailfail=="1" or $namefail=="1")
	{	/* don't add the user */
		echo "<font color=\"FF0000\">";
		#if($usernonalpha=="1"){echo "Please enter an alphanumeric nickname. (a-z,A-Z,0-9,_,-,|)<br />";}
		if($userfail=="1"){echo "Please enter a nickname that is 3 or more characters in length.<br />";}
		elseif($userfail=="2"){echo "Please enter a nickname that is 25 characters or shorter.<br />";}
		if($namefail=="1"){echo "Please enter your full name";}
		if($pwmismatch=="1"){echo "Your password and password confirmation do not match.<br />";}
		if($pwfail=="1"){echo "Please enter a password (at least 4 characters) and confirm your password.<br />";}
		if($emailfail=="1"){echo "Please enter your valid @njit email address.<br /><br />";}
		echo "</font>";
	}
	else
	{	/* good past initial checks */

		//general db functions
		$link = mysql_connect ($Host, $DBUser, $DBPass) or die(mysql_error());
		if(!$link){die("Could not connect to MySQL server: " . mysql_error());}
		$currdb = mysql_select_db ($DBName, $link);
		if(!$currdb){die("Could not connect to database: " . mysql_error());}
	
		//set table
		$table = 'dc_users';
		
		//see if user/email address exists in users table
		$u = strtolower($username);
		$e = $encemail;
		$uexists = $eexists = 0;
		$sql = "select * from `$table`;";
		$query = mysql_query($sql);
		if(!$query){print(mysql_error());print("<br /><br /></body></html>");exit;}
		if($found = mysql_fetch_array($query))
		{
			do
			{
				$em = $found['Email'];
				$un = strtolower($found['Nick']);
				if($em == $e){$eexists = 1;}
				if($un == $u){$uexists = 1;}
			} while($found = mysql_fetch_array($query));
		}
		
		if($uexists == 0 and $eexists == 0)
		{
			$confirmed="0";		
			//insert the new user

			#echo "u:$username, pw:$enc, em:$encemail<br /><br />";

			#$sql1 = "DELETE FROM `dc_users_removed` where `nick` = '$username' );";
		
			#$query1 = mysql_query($sql1);

				#proceed to add the user
				$authstr = "";
				for($i=0;$i<25;$i++)
				{
					$str = rand(0,strlen($chars)-1);
					$authstr = $authstr . $chars[$str];
				}

			$sql = "INSERT INTO $table (`Name`, `Email`, `Nick`, `Password`, `Verified`, `Auth_Str`) VALUES ( '$fullname', '$encemail', '$username', '$pwhash', '0', '$authstr' );";
		
			$query = mysql_query($sql);
			if(!$query){print(mysql_error());print("<br /><br />");}
			else
			{
				//Send email
				#$from = "Sandra Sender <sender@example.com>";
				#$subject = "DC++ Registration";
				#$body = "You have registered to use the DC++ hub.  Please click the following link to complete authentication.\n\nhttp://10.1.1.132/auth.php?str=".$authstr;

				#$host = "mail.example.com";
				#$username = "smtp_username";
				#$password = "smtp_password";

				#$headers = array('From' => $from,
				#	'To' => $to,
				#	'Subject' => $subject);
				#$smtp = Mail::factory('smtp',
				#	array('host' => $host,
				#	'auth' => true,
				#	'username' => $username,
				#	'password' => $password));
				#$mail = $smtp->send($to, $headers, $body);
			
					
				//user was added to the db but not to the reglist until confirmation
				echo "<strong>User &lt;$username&gt; has been successfully registered.</strong><br /><br />";
				echo "<font color=\"#FF0000\">However, you need to confirm your account in order to activate it for the hub.</font><br />
						<font size=\"+1\" color=\"#FF0000\">Please check your email for instructions on how to activate your account.</font><br />";
			}
			
			exit;
		}
		else
		{	
			echo "<font color=\"FF0000\">";
			if($eexists==1){echo "Your email address is already registered.<br />";}
			if($uexists==1){echo "Your username is already registered.<br />";}
			echo "</font>";
		}
		
	}
	
}

?>

<form action="" method="POST" name="hubreg">
  <table width="275" border="0">
  <tr>
    <td width="124">Full Name:</td>
    <td width="151"><input name="name" type="text" size="25"  maxlength="20" value="<?php print($fullname); ?>" /></td>
  </tr>
  <tr>
    <td width="124">Nickname:</td>
    <td width="151"><input name="nick" type="text" size="25"  maxlength="20" value="<?php print($username); ?>" /></td>
  </tr>
  <tr>
    <td>Password:</td>
    <td><input name="pw" type="password" id="pw" value="" size="25" maxlength="16" /></td>
  </tr>
  <tr>
    <td>Confirm:</td>
    <td><input name="confpw" type="password" id="confpw" value="" size="25" maxlength="16" /></td>
  </tr>
  <tr>
    <td>NJIT&nbsp;Email:</td>
    <td><input name="email" type="text" size="25" maxlength="64" value="<?php print($email); ?>" /> <font color="#FF0000"><a href="/?whyemail">?</a></font></td>
  </tr>
    <td><input name="submit" type="submit" value="Submit" /></td>
    <td></td>
  </tr>
</table>
</form>
<strong>NOTE</strong>: New accounts will be active immediately upon registration, and will be deleted if not confirmed after a week.<br />
After approximately a week, new accounts must be confirmed before they will be active.<br />
<a href="/?confirm">Click here for instructions to confirm your account.</a><br />
