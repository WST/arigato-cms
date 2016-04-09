<?php

define ('CMS', true);

require_once "includes/utils.php";

if ( ban (0) )
{
	error (tpl ($f_config['error2'], $global_tag), tpl ($f_config['error_cap'], $global_tag));
	exit;
}

$sid = anti_inj ($_POST['sid']);
$r = db_query ("select num from $INFO[db_prefix]nums where sid='$sid'");
if ( ! ($f = db_fetch_assoc ($r)) || $f['num'] != $_POST['num'] )
{
	error (tpl ($f_config['error16'], $global_tag), tpl ($f_config['error_cap'], $global_tag));
	exit;
}

if ( anti_spam (0) )
{
	error (tpl ($f_config['error1'], $global_tag), tpl ($f_config['error_cap'], $global_tag));
	exit;
}

slash_control ();

if ( isset ($_POST['post']) )
{
	$name = str_replace (array('\"', "\'", "\\"), array('', '', ''), substr (trim ($_POST['your_name']), 0, 16));
	$good = true;
	if ( ! $admin )
	{
		$r = db_query ("select bname from $INFO[db_prefix]badname");
		while ( $f = db_fetch_assoc ($r) )
			if ( strpos ($name, $f['bname']) !== false )
			{
				$good = false;
				break;
			}
		$rcn = db_query ("select * from $INFO[db_prefix]censor");
		$lownm = strtolower ($name);
		while ($fcn = db_fetch_assoc ($rcn))
		{
			if ( strpos ($lownm, strtolower ($fcn['bad'])) !== false )
			{
				$good = false;
				break;
			}
		}
	}
	if ( ! $good )
	{
		$tag = $global_tag;
		$tag['NAME'] = protection ($name);
		error (tpl ($f_config['error12'], $tag), tpl ($f_config['error_cap'], $global_tag));
		exit;
	}
	$ind = anti_inj ($_POST['index']);
	$r = db_query ("select cont from $INFO[db_prefix]art where k_art='$ind'");
	if ( ! ($f = db_fetch_assoc ($r)) || $f['cont'] != 1 )
	{
		error (tpl ($f_config['error13'], $global_tag), tpl ($f_config['error_cap'], $global_tag));
		exit;
	}
	$email = substr (trim ($_POST['your_email']), 0, 40);
	$url = substr (url_control (trim ($_POST['your_url'])), 0, 60);
	$icq = substr (trim ($_POST['your_icq']), 0, 9);
	if ( $icq !== preg_replace ('#[^0-9]#', ' ', $icq) ) $icq = '';
	$ip = $_SERVER['REMOTE_ADDR'];
	$date = time ();
	$message = substr (trim ($_POST['your_message']), 0, $f_config['meslen']);

	if ( ! $name ) 
	{
		error (tpl ($f_config['error4'], $global_tag), tpl ($f_config['error_cap'], $global_tag));
		exit;
	}
	$mes = plain_text ($message);
	if ( strlen ($mes) < 4 )
	{
		error (tpl ($f_config['error5'], $global_tag), tpl ($f_config['error_cap'], $global_tag));
		exit;
	}
	
	if ( ! mail_correct ($email) )
	{
		error (tpl ($f_config['error6'], $global_tag), tpl ($f_config['error_cap'], $global_tag));
		exit;
	}
	
	db_query ("delete from $INFO[db_prefix]nums where sid='$sid' or stime<" . (time() - 60 * 60));
	
	if ( ! $admin ) $message = str_replace ('[html]', '[no][html][/no]', $message);

	if ( $ind !== '' )
	{
		db_query ("insert into $INFO[db_prefix]book (k_art, user, post, email, url, icq, ip, mesdate)
					values ('$ind', '$name', '$message', '$email', '$url', '$icq', '$ip', '$date')");
		SetCookie ("AUTHORNAME", $name, time() + 60 * 60 * 24 * 30 * 12);
		SetCookie ("AUTHOREMAIL", $email, time() + 60 * 60 * 24 * 30 * 12);
		SetCookie ("URL", $url, time() + 60 * 60 * 24 * 30 * 12);
		SetCookie ("ICQ", $icq, time() + 60 * 60 * 24 * 30 * 12);
	}
	db_query ("insert into $INFO[db_prefix]spam (mode, ip, posttime) values (0, '$_SERVER[REMOTE_ADDR]', " . time() . ")");
}

header ("location:index.php?art=$_POST[index]#comment");

?>