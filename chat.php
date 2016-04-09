<?php

define ('CMS', true);

require_once "includes/utils.php";

$room = ( isset ($_GET['room']) ) ? anti_inj ($_GET['room']) : 0;
if ( empty ($room) ) $room = 0;

if ( $_GET['action'] === "clear" && $admin )
{
	db_query ("delete from $INFO[db_prefix]chatmes where k_room='$room'");
	header ("location:$_SERVER[HTTP_REFERER]");
	exit;
}

// Статистика:
$rs = db_query ("select ip,agent from $INFO[db_prefix]counter
					where ip='$_SERVER[REMOTE_ADDR]' and agent='" . protection ($_SERVER['HTTP_USER_AGENT']) . "'");
if ( $fs = db_fetch_assoc ($rs) )
{
	db_query ("update $INFO[db_prefix]counter 
				set reftime=" . time() . "
				where ip='$_SERVER[REMOTE_ADDR]' and agent='" . protection ($_SERVER['HTTP_USER_AGENT']) . "'");
} else
{
	db_query ("insert into $INFO[db_prefix]counter (ip, reftime, url, agent, cnt) 
				values ('$_SERVER[REMOTE_ADDR]'," . time() . ",'$url','" . protection ($_SERVER['HTTP_USER_AGENT']) . "',1)");
}

db_query ("delete from $INFO[db_prefix]chatmes where mesdate<" . (time() - 60 * $f_config['maxtime']));

if ( isset ($_POST['post']) )
{ // Добавление сообщения:
	if ( ! ban (0) || $admin )
	{
		slash_control ();
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
		}
		if ( $good )
		{
			$message = substr (trim ($_POST['your_message']), 0, $f_config['maxlen']);
			if ( ! $admin ) $message = str_replace ('[html]', '[no][html][/no]', $message);
			$mes = plain_text ($message);
			if ( ! empty ($name) && ! empty ($mes) )
			{
				$rcn = db_query ("select * from $INFO[db_prefix]censor");
				$lownm = strtolower ($name);
				while ($fcn = db_fetch_assoc ($rcn))
				{
					if ( strpos ($lownm, strtolower ($fcn['bad'])) !== false )
					{
						$good = false;
						break;
					}
					$message = str_replace ($fcn['bad'], $fcn['good'], $message);
				}
				if ( $good )
				{
					$date = time ();
					$message = addslashes (format_text (stripslashes ($message)));
					db_query ("insert into $INFO[db_prefix]chatmes (k_room,ip,user,mesdate,post)
							values ('$room','$_SERVER[REMOTE_ADDR]','$name','$date','$message')");
					SetCookie ("AUTHORNAME", $name, time() + 60 * 60 * 24 * 30 * 12);
				}
			}
		}

	}
	header ("location:chat.php?room=$room");
}

$rmes = db_query ("select * from $INFO[db_prefix]chatmes where k_room='$room' order by mesdate desc limit $f_config[maxmes]");
$mes = join ('', file ("$f_config[skin]/patterns/chatmes.html"));
$messages = "";
$num = 1;
$head = "";
while ( $fmes = db_fetch_assoc ($rmes) )
{
	if ( $num === 1 ) $head = "Last-Modified: " . gmdate ("D, d M Y H:i:s", $fmes['mesdate']) . " GMT";
	$tag_mes = $global_tag;
	$tag_mes['NUM'] = $num;
	$tag_mes['ROOM'] = $room;
	$tag_mes['AUTHORNAME'] = protection ($fmes['user']);
	$tag_mes['MESSAGE'] = $fmes['post'];
	$tag_mes['IP'] = $fmes['ip'];
	$tag_mes['ID'] = $fmes['k_mes'];
	$tag_mes['DATE'] = date ($f_config['datefrm'], $fmes['mesdate']);
	$messages .= tpl ($mes, $tag_mes);	
	$num++;
}
$body = join ('', file ("$f_config[skin]/patterns/chat.html"));
$refresh = "";
$tag = $global_tag;
$tag['MESSAGES'] = $messages;
$autoref = 0;
if ( isset ($_GET['autoref']) )
{
	if ( $_GET['autoref'] == "1" ) $autoref = 1;
	if ( ! isset ($_COOKIE['AUTOREF']) || $_COOKIE['AUTOREF'] != $autoref )
	{
		no_cache ();
		$head = "";
		SetCookie ("AUTOREF", $autoref, time() + 60 * 60 * 24 * 30 * 12);
	}
} elseif ( isset ($_COOKIE['AUTOREF']) && $_COOKIE['AUTOREF'] == "1" ) $autoref = 1;
if ( $autoref == 1 ) $tag['REFRESH'] = $f_config['refresh'];
$tag['EXPIRES'] = gmdate ("D, d M Y H:i:s", time() + $f_config['maxtime'] * 60) . " GMT";
if ( ! empty ($head) )
{
	@header ($head);
	@header ("Expires: " . $tag['EXPIRES']);
	@header ("Cache-Control: no-cache, must-revalidate");
	@header ("Pramga: no-cache");
}
echo tpl ($body, $tag);

?>