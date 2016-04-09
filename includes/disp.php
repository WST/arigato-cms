<?php

if ( ! defined ('CMS') )
{
	die ("Нарушение прав доступа");
	exit;
}

$email = protection (substr ($_POST['disp'], 0, 40));

if ( ! mail_correct ($email) || empty ($email) )
{
	error (tpl ($f_config['error6'], $global_tag), tpl ($f_config['error_cap'], $global_tag));
	exit;
}

$sid = anti_inj ($_POST['sid']);
$r = db_query ("select num from $INFO[db_prefix]nums where sid='$sid'");
if ( ! ($f = db_fetch_assoc ($r)) || $f['num'] != $_POST['num'] )
{
	error (tpl ($f_config['error16'], $global_tag), tpl ($f_config['error_cap'], $global_tag));
	exit;
}

SetCookie ("AUTHOREMAIL", $email, time() + 60 * 60 * 24 * 30 * 12);

if ( anti_spam (1) )
{
	error (tpl ($f_config['error3'], $global_tag), tpl ($f_config['error_cap'], $global_tag));
	exit;
}

db_query ("delete from $INFO[db_prefix]email where active=0 and qtime<" . (time() - 60 * 60 * 24 * 30));

$rg = db_query ("select COUNT(*) as CNT from $INFO[db_prefix]email where email='$email'");
$fg = db_fetch_assoc ($rg);

$tag = $global_tag;
$tag['MAIL'] = $email;

if ( $fg['CNT'] > 0 )
{
	error (tpl ($f_config['inform4'], $tag), tpl ($f_config['inform_cap'], $global_tag));
	exit;
}

$code = gen_rand_string ();

$tag['ACTIVE'] = $f_config['url'] . "/dispatch.php?action=active&amp;code=$code";
$tag['DEACTIVE'] = $f_config['url'] . "/dispatch.php?action=deactive&amp;code=$code";
$tag['CODE'] = $code;

if ( mask_mail ($email, 'disp.txt', $tag) )
{
	db_query ("insert into $INFO[db_prefix]spam (mode, ip, posttime) values (1, '$_SERVER[REMOTE_ADDR]', " . time() . ")");
	db_query ("insert into $INFO[db_prefix]email (email, ip, cd, active, qtime,send_time)
			values ('$email','$_SERVER[REMOTE_ADDR]','$code',0," . time() . "," . time() . ")");
	db_query ("delete from $INFO[db_prefix]nums where sid='$sid' or stime<" . (time() - 60 * 60));
	error (tpl ($f_config['inform1'], $tag), tpl ($f_config['inform_cap'], $global_tag));
} else error (tpl ($f_config['error17'], $tag), tpl ($f_config['error_cap'], $global_tag));

?>