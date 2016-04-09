<?php

define ('CMS', true);

require_once "includes/utils.php";

if ( $f_config['mail'] == 0 )
{
	header ("location: $f_config[url]");
	exit;
}

if ( anti_spam (3) )
{
	error (tpl ($f_config['error14'], $global_tag), tpl ($f_config['error_cap'], $global_tag));
	exit;
}

if ( ban (3) )
{
	error (tpl ($f_config['inform6'], $global_tag), tpl ($f_config['inform_cap'], $global_tag));
	exit;
}

$sid = anti_inj ($_POST['sid']);
$r = db_query ("select num from $INFO[db_prefix]nums where sid='$sid'");
if ( ! ($f = db_fetch_assoc ($r)) || $f['num'] != $_POST['num'] )
{
	error (tpl ($f_config['error16'], $global_tag), tpl ($f_config['error_cap'], $global_tag));
	exit;
}

slash_control ();

if ( isset ($_POST['body']) )
{
	if ( ! isset ($_POST['email']) || empty ($_POST['email']) || ! mail_correct ($_POST['email']) )
	{
		error (tpl ($f_config['error6'], $global_tag), tpl ($f_config['error_cap'], $global_tag));
		exit;
	}
	
	if ( ! isset ($_POST['subj']) || empty ($_POST['subj']) || strpos ($_POST['subj'], "\n") !== false || strpos ($_POST['subj'], "\r") !== false  )
	{
		error (tpl ($f_config['error15'], $global_tag), tpl ($f_config['error_cap'], $global_tag));
		exit;
	}
	
	$email = substr ($_POST['email'], 0, 40);
	$subj = substr ($_POST['subj'], 0, 128);
	$body = substr ($_POST['body'], 0, 65535) . "\n\n";
	if ( isset ($_COOKIE['AUTHORNAME']) ) $body .= "NAME: $_COOKIE[AUTHORNAME]\n";
	if ( isset ($_COOKIE['AUTHOREMAIL']) && $_COOKIE['AUTHOREMAIL'] != $email ) $body .= "EMAIL: $_COOKIE[AUTHOREMAIL]\n";
	if ( isset ($_COOKIE['URL']) ) $body .= "URL: $_COOKIE[URL]\n";
	if ( isset ($_COOKIE['ICQ']) ) $body .= "ICQ: $_COOKIE[ICQ]\n";
	if ( isset ($_SERVER['HTTP_USER_AGENT']) ) $body .= "BROWSER: $_SERVER[HTTP_USER_AGENT]\n";
	$body .= "IP: $_SERVER[REMOTE_ADDR]\n";
	if ( isset ($_SERVER['HTTP_X_FORWARDED_FOR']) ) $body .= "FORWARDED: $_SERVER[HTTP_X_FORWARDED_FOR]\n";
	
	SetCookie ("AUTHOREMAIL", $email, time() + 60 * 60 * 24 * 30 * 12);
	
	$headers  = "From: $email\n";
	$headers .= "MIME-Version: 1.0\n";
	$headers .= "Content-Type: text/plain; charset=\"windows-1251\"";

	if ( @mail ($f_config['email'], $subj, $body, $headers) )
	{
		db_query ("insert into $INFO[db_prefix]spam (mode, ip, posttime) values (3, '$_SERVER[REMOTE_ADDR]', " . time() . ")");
		db_query ("delete from $INFO[db_prefix]nums where sid='$sid' or stime<" . (time() - 60 * 60));
		error (tpl ($f_config['inform6'], $global_tag), tpl ($f_config['inform_cap'], $global_tag));
	} else error (tpl ($f_config['error17'], $global_tag), tpl ($f_config['error_cap'], $global_tag));
} else error (tpl ($f_config['error15'], $global_tag), tpl ($f_config['error_cap'], $global_tag));

?>