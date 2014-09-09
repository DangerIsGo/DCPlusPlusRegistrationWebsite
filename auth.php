<?php
$str = (isset($_GET['str']))?$_GET['str']:"";

$Host = 'localhost';
$DBUser = 'DcRegSite';
$DBPass = 'supersecret';
$DBName = 'dcplusplus';
$table = 'dc_users';

if ($str != "")
{
	//general db functions
	$link = mysql_connect ($Host, $DBUser, $DBPass) or die(mysql_error());
	if(!$link){die("Could not connect to MySQL server: " . mysql_error());}
	$currdb = mysql_select_db ($DBName, $link);
	if(!$currdb){die("Could not connect to database: " . mysql_error());}

	$sql = "select * from `$table` where Auth_Str = '$str'";
	$query = mysql_query($sql);
	$fetchedNick = "";
	$fetchedPass = "";
	$fetchedVer = -1;
	if(!$query){print(mysql_error());print("<br /><br /></body></html>");exit;}
	if($found = mysql_fetch_array($query))
	{
		// There will only be one record returned
		$fetchedNick = $found['Nick'];
		$fetchedPass = $found['Password'];
		$fetchedVer = $found['Verified'];
	}

	if ($fetchedVer == 0)
	{
		$filename = '/home/dangerisgo/.dbhub/foofile';

		if(is_writable($filename))
		{
			$lines = file($filename);

			//add nick to reglist
			if(!$handle = fopen($filename, 'a'))
			{
				echo "Cannot open file ($filename)<br>";
				exit;
			}

			$line = $fetchedNick . " " . $fetchedPass . " 1\n";
			
			if(fwrite($handle,$line) === FALSE)#$a==1)#
			{
				echo "ERROR: Cannot write to registered user list file<br>";
				exit;
			}
			
			#echo "Successfully wrote to file ($filename)<br>";
	
			fclose($handle);
			//done writing to reglist

			// Update database
			$sql = "update `$table` set Verified = 1 where Auth_Str = '$str'";
			$query = mysql_query($sql);
			if(!$query){print(mysql_error());print("<br /><br /></body></html>");exit;}

			echo "You have been successfully registered.";
		}
		else
		{
			echo "Can't open file";
		}
	}
	else
	{
		echo "You have already been registered.";
	}
}

?>

<form action="/?auth" method="POST" name="hubreg">

</form>
