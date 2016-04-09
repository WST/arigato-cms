<?php

if ( ! file_exists ("install.lock") )
{
	header ("location:install.php");
	exit;
}

define ('CMS', true);

require_once "includes/utils.php";

$error_msg = "";

slash_control ();

if ( $admin && strpos ($_SERVER['HTTP_REFERER'], $f_config['url'] . '/admin.php') === 0 )
{
	if ( $_GET['action'] === "art_del" )
	{
		db_query ("delete from $INFO[db_prefix]art where k_art='$_GET[art]'");
		db_query ("delete from $INFO[db_prefix]book where k_art='$_GET[art]'");
		db_query ("delete from $INFO[db_prefix]news where k_art='$_GET[art]'");
		db_query ("delete from $INFO[db_prefix]photos where k_art='$_GET[art]'");
		header ("location:admin.php?action=content");
		exit;
	}

	if ( $_GET['action'] === "sp_del" )
	{
		db_query ("delete from $INFO[db_prefix]email where k_mail='$_GET[k_mail]'");
		header ("location:admin.php?action=spamlist");
		exit;
	}

	if ( $_GET['action'] === "sp_act" )
	{
		db_query ("update $INFO[db_prefix]email set active=1 where k_mail='$_GET[k_mail]'");
		header ("location:admin.php?action=spamlist");
		exit;
	}
	
	if ( $_GET['action'] === "dlcnt_del" )
	{
		db_query ("delete from $INFO[db_prefix]dlcnt where k_dl='$_GET[k_dl]'");
		header ("location:admin.php?action=dlcnt");
		exit;
	}
	
	if ( $_GET['action'] === "ip_del" )
	{
		$r = db_query ("select mode from $INFO[db_prefix]ban where k_ban='$_GET[k_ban]'");
		db_query ("delete from $INFO[db_prefix]ban where k_ban='$_GET[k_ban]'");
		$ban = '';
		if ( ($f = db_fetch_assoc ($r)) )
		{
			switch ( $f['mode'] )
			{
				case 0: $ban = 'g_ban'; break;
				case 1: $ban = 'a_ban'; break;
				case 2: $ban = 's_ban'; break;
				case 3: $ban = 'm_ban'; break;
			}
		}
		header ("location:admin.php?action=$ban");
		exit;
	}

	if ( $_GET['action'] === "cns_del" )
	{
		db_query ("delete from $INFO[db_prefix]censor where k_cns='$_GET[k_cns]'");
		header ("location:admin.php?action=censor");
		exit;
	}

	if ( $_GET['action'] === "link_del" )
	{
		db_query ("delete from $INFO[db_prefix]links where k_link='$_GET[k_link]'");
		header ("location:admin.php?action=link");
		exit;
	}

	if ( $_GET['action'] === "bname_del" )
	{
		db_query ("delete from $INFO[db_prefix]badname where k_bn='$_GET[k_bn]'");
		header ("location:admin.php?action=badname");
		exit;
	}
	
	if ( $_GET['action'] === "pool_del" )
	{
		db_query ("delete from $INFO[db_prefix]pools where k_pl='$_GET[k_pl]'");
		db_query ("delete from $INFO[db_prefix]answers where k_pl='$_GET[k_pl]'");
		header ("location:admin.php?action=pool");
		exit;
	}
	
	if ( $_GET['action'] === "pool_close" )
	{
		db_query ("update $INFO[db_prefix]pools set active=0, enddate=" . time() . " where k_pl='$_GET[k_pl]'");
		header ("location:admin.php?action=pool");
		exit;
	}

	if ( $_GET['action'] === "pool_open" )
	{
		db_query ("update $INFO[db_prefix]pools set active=1, enddate=0 where k_pl='$_GET[k_pl]'");
		header ("location:admin.php?action=pool");
		exit;
	}
	
	if ( isset ($_POST['restore']) )
	{
		if ( isset ($_POST['yes']) )
		{
			include ("backup/$_POST[restore]");
			header ("location:index.php");
		} else header ("location:admin.php?action=backup");
		exit;
	}

	if ( isset ($_POST['backup']) )
	{
		$fname = $INFO['db_prefix'] . date ('d_m_Y_H_i') . ".php";
		if ( $f = @fopen ("backup/$fname", "w") )
		{
			$tables = $_POST['table'];
			flock ($f, LOCK_EX);
			fputs ($f, "<?php\n\n");
			fputs ($f, "// $f_config[title]\n");
			fputs ($f, "// Бэкап базы данных системы Arigato CMS $version от " . date ('d.m.Y H:i') . "\n");
			fputs ($f, "// Таблицы:\n");
			for ($i = 0; $i < count ($tables); $i++) fputs ($f, "// $tables[$i]\n");

			fputs ($f, "\nif ( ! defined ('CMS') )\n");
			fputs ($f, "{\n");
			fputs ($f, "	die ('Нарушение прав доступа');\n");
			fputs ($f, "	exit;\n");
			fputs ($f, "}\n");

			for ($i = 0; $i < count ($tables); $i++)
			{
				$table = $tables[$i];
				fputs ($f, "\n\n// $table:\n");
				fputs ($f, "mysql_query (\"DROP TABLE IF EXISTS \$INFO[db_prefix]$table\") or die (__FILE__ . ':' . __LINE__ . ' - ' . mysql_error());\n");
				$tf = db_query ("show create table $INFO[db_prefix]$table");
				if ( $tr = mysql_fetch_row ($tf) )
				{
					$creat = str_replace ($tr[0], "\$INFO[db_prefix]$table", $tr[1]);
					$creat = substr ($creat, 0, strrpos ($creat, ")"));
					fputs ($f, "\nmysql_query (\"$creat)\") or die (__FILE__ . ':' . __LINE__ . ' - ' . mysql_error());\n");
				}
				$tf = db_query ("select * from $INFO[db_prefix]$table");
				while ( $tr = mysql_fetch_row ($tf) )
				{
					fputs ($f, "\nmysql_query (\"INSERT INTO \$INFO[db_prefix]$table VALUES (\n");
					for ($j = 0; $j < count ($tr); $j++)
					{
						if ( $j > 0 ) fputs ($f, ",");
						fputs ($f, "'" . str_replace (array ('\\', '"', '$'), array('\\\\', '\\"', '\\$'), 
							str_replace (array ('\\', "'"), array ('\\\\', "\\'"), $tr[$j])) . "'");
					}
					fputs ($f, "\n)\") or die (__FILE__ . ':' . __LINE__ . ' - ' . mysql_error());\n");
				}
			}

			fputs ($f, "\n?>");
			flock ($f, LOCK_UN);
			fclose ($f);
		}

		header ("location:admin.php?action=backup");
		exit;
	}
	
	if ( isset ($_POST['del']) )
	{
		$file = $_POST['file'];
		for ($i = 0; $i < count ($file); $i++)
		{
			$name = $file[$i];
			if ( file_exists ("backup/$name") && strpos ("backup/$name", "..") === false )
				unlink ("backup/$name");
		}	
		header ("location:admin.php?action=backup");
		exit;
	}

	if ( isset ($_POST['send']) )
	{
		$r = db_query ("select email from $INFO[db_prefix]email where active=1");
		while ($f = db_fetch_assoc ($r)) send_mail ($f['email'], $_POST['subj'], $_POST['body']);
		header ("location:admin.php");
		exit;
	}
	
	if ( isset ($_POST['message']) )
	{
		$command = "update";
		$rtest = db_query ("select count(*) from $INFO[db_prefix]messages");
		if ( ! ($ftest = db_fetch_row ($rtest)) || ($ftest[0] == 0) ) $command = "insert";
		db_query ("$command $INFO[db_prefix]messages set error_cap='$_POST[error_cap]',inform_cap='$_POST[inform_cap]',
			error1='$_POST[error1]',error2='$_POST[error2]',error3='$_POST[error3]',error4='$_POST[error4]',error5='$_POST[error5]',
			error6='$_POST[error6]',error7='$_POST[error7]',error8='$_POST[error8]',error9='$_POST[error9]',error10='$_POST[error10]',
			error11='$_POST[error11]',error12='$_POST[error12]',error13='$_POST[error13]',error14='$_POST[error14]',error15='$_POST[error15]',
			error16='$_POST[error16]',error17='$_POST[error17]',inform1='$_POST[inform1]',inform2='$_POST[inform2]',inform3='$_POST[inform3]',
			inform4='$_POST[inform4]',inform5='$_POST[inform5]',inform6='$_POST[inform6]',inform7='$_POST[inform7]'");
		header ("location:admin.php");
		exit;
	}
	
	if ( isset ($_POST['gbmesid']) )
	{
		db_query ("update $INFO[db_prefix]book set user='$_POST[your_name]',email='$_POST[your_email]',url='$_POST[your_url]',
				icq='$_POST[your_icq]',post='$_POST[your_message]' where k_post='$_POST[gbmesid]'");
		header ("location:$_POST[refer]");
		exit;
	}
	
	if ( isset ($_POST['newmesid']) )
	{
		db_query ("update $INFO[db_prefix]news set title='$_POST[title]',post='$_POST[newmessage]' where k_new='$_POST[newmesid]'");
		header ("location:$_POST[refer]");
		exit;
	}
	
	if ( isset ($_POST['photoid']) )
	{
		db_query ("update $INFO[db_prefix]photos set pictur='$_POST[pictur]',title='$_POST[title]',caption='$_POST[caption]' where k_photo='$_POST[photoid]'");
		header ("location:$_POST[refer]");
		exit;
	}

	if ( isset ($_POST['password']) )
	{
		if ( $_POST['newpass1'] !== $_POST['newpass2'] )
		{
			header ("location:admin.php?action=password&err=1");
			exit;
		}
		if ( md5 ($_POST['oldpass']) !== $f_config['passwrd'] )
		{
			header ("location:admin.php?action=password&err=2");
			exit;
		}
		$pass = md5 ($_POST['newpass1']);
		db_query ("update $INFO[db_prefix]config set passwrd='$pass'");
		header ("location:admin.php");
		exit;
	}

	if ( isset ($_POST['add']) )
	{
		$date = time ();
		if ( $_POST['position'] == 1 )
		{
			$r = db_query ("select max(pos) as mx from $INFO[db_prefix]art");
			if ( $f = db_fetch_assoc ($r) ) $pos = $f['mx'] + 1;
			else $pos = 1;
		} else
		{
			$r = db_query ("select min(pos) as mn from $INFO[db_prefix]art");
			if ( $f = db_fetch_assoc ($r) ) $pos = $f['mn'] - 1;
			else $pos = 1;
		}
		$path = ( isset ($_POST['path']) ) ? 1 : 0;
		$inmenu = ( isset ($_POST['inmenu']) ) ? 1 : 0;
		$post = $_POST['post'];
		if ( $_POST['format'] == 2 ) $post = preg_replace ("/<\?(.*?)\?>/s", "", $post);
		if ( $_POST['format'] == 1 ) $post = plain_text ($post);
		$shot = strip_tags ($_POST['shot']);
		$mnem = strtolower ($_POST['mnemonic']);
		if ( ! empty ($mnem) )
		{
			$mn = $mnem;
			$ind = 0;
			do
			{
				$rm = db_query ("select k_art from $INFO[db_prefix]art where mnemonic='$mn'");
				$nm = db_num_rows ($rm);
				if ( $nm > 0 )
				{
					$mn = $mnem . (++$ind);
				}
				db_free_result ($rm);
			} while ( $nm > 0 );
			if ( $ind > 0 ) $mnem .= $ind;
		}
		db_query ("insert into $INFO[db_prefix]art
				(mesdate,cont,dynamic,caption,title,author,post,shot,mnemonic,keywords,comment,icon,words,sup,pos,cnt,format,p_n,path,inmenu)
				values ('$date','$_POST[answer]','$_POST[dynamic]','$_POST[caption]','$_POST[shotcap]','$_POST[author]','$_POST[post]','$_POST[shot]','$mnem',
				'$_POST[keywords]','$_POST[comment]','$_POST[icon]','','$_POST[sup]','$pos',0,'$_POST[format]','$_POST[p_n]','$path',
				'$inmenu')");
		artindex (db_insert_id());
		SetCookie ("FORMAT", $_POST['format'], time() + 60 * 60 * 24 * 30 * 12);
		SetCookie ("ANSWER", $_POST['answer'], time() + 60 * 60 * 24 * 30 * 12);
		SetCookie ("DYNAMIC", $_POST['dynamic'], time() + 60 * 60 * 24 * 30 * 12);
		SetCookie ("P_N", $_POST['p_n'], time() + 60 * 60 * 24 * 30 * 12);
		SetCookie ("PATH", $path, time() + 60 * 60 * 24 * 30 * 12);
		SetCookie ("INMENU", $inmenu, time() + 60 * 60 * 24 * 30 * 12);
		SetCookie ("POS", $_POST['position'], time() + 60 * 60 * 24 * 30 * 12);
		header ("location:admin.php?action=content");
		exit;
	}

	if ( isset ($_POST['edit']) )
	{
		$path = ( isset ($_POST['path']) ) ? 1 : 0;
		$inmenu = ( isset ($_POST['inmenu']) ) ? 1 : 0;
		$post = $_POST['post'];
		if ( $_POST['format'] == 2 ) $post = preg_replace ("/<\?(.*?)\?>/s", "", $post);
		if ( $_POST['format'] == 1 ) $post = plain_text ($post);
		$shot = strip_tags ($_POST['shot']);
		$mnem = strtolower ($_POST['mnemonic']);
		if ( ! empty ($mnem) )
		{
			$mn = $mnem;
			$ind = 0;
			do
			{
				$rm = db_query ("select k_art from $INFO[db_prefix]art where mnemonic='$mn' and k_art<>'$_POST[art]'");
				$nm = db_num_rows ($rm);
				if ( $nm > 0 )
				{
					$mn = $mnem . (++$ind);
				}
				db_free_result ($rm);
			} while ( $nm > 0 );
			if ( $ind > 0 ) $mnem .= $ind;
		}
		db_query ("update $INFO[db_prefix]art set cont='$_POST[answer]',dynamic='$_POST[dynamic]',caption='$_POST[caption]',title='$_POST[shotcap]',
			author='$_POST[author]',post='$_POST[post]',shot='$_POST[shot]',mnemonic='$mnem',keywords='$_POST[keywords]',comment='$_POST[comment]',
			icon='$_POST[icon]',words='',sup='$_POST[sup]',format='$_POST[format]',p_n='$_POST[p_n]',path='$path',inmenu='$inmenu'
			where k_art='$_POST[art]'");
		artindex ($_POST['art']);
		SetCookie ("FORMAT", $_POST['format'], time() + 60 * 60 * 24 * 30 * 12);
		SetCookie ("ANSWER", $_POST['answer'], time() + 60 * 60 * 24 * 30 * 12);
		SetCookie ("DYNAMIC", $_POST['dynamic'], time() + 60 * 60 * 24 * 30 * 12);
		SetCookie ("P_N", $_POST['p_n'], time() + 60 * 60 * 24 * 30 * 12);
		SetCookie ("PATH", $path, time() + 60 * 60 * 24 * 30 * 12);
		SetCookie ("INMENU", $inmenu, time() + 60 * 60 * 24 * 30 * 12);
		header ("location:admin.php?action=content");
		exit;
	}
	
	if ( isset ($_POST['pool']) )
	{
		$enddate = ( $_POST['duration'] == 0 ) ? 0 : time() + $_POST['duration'] * 60 * 60 * 24;
		db_query ("insert into $INFO[db_prefix]pools (question, active, begdate, enddate, cnt)
				values ('$_POST[question]', 1, " . time() . ", $enddate, 0)");
		$k_pl = db_insert_id();
		$ans = explode ("\n", str_replace ("\r", "\n", $_POST['answer']));
		for ($i = 0; $i < count ($ans); $i++)
		{
			$a = trim ($ans[$i]);
			if ( $a !== '' ) db_query ("insert into $INFO[db_prefix]answers (k_pl, vote, answer, pos) values ('$k_pl', 0, '$a', $i)");
		}
		header ("location:admin.php?action=pool");
		exit;
	}

	if ( isset ($_POST['ban']) )
	{
		$ip = trim ($_POST['ip']);
		if ( (strlen ($ip) > 6) && ($_POST['mode'] != 1 || $ip[0] !== '*') ) 
		{
			$r = db_query ("select ip from $INFO[db_prefix]ban where ip='$ip' and mode='$_POST[mode]'");
			if ( db_num_rows ($r) == 0 ) db_query ("insert into $INFO[db_prefix]ban (ip, mode) values ('$ip', '$_POST[mode]')");
		}
		$ban = '';
		switch ( $_POST['mode'] )
		{
			case 0: $ban = 'g_ban'; break;
			case 1: $ban = 'a_ban'; break;
			case 2: $ban = 's_ban'; break;
			case 3: $ban = 'm_ban'; break;
		}
		header ("location:admin.php?action=$ban");
		exit;
	}
	
	if ( isset ($_POST['badname']) )
	{
		$name = str_replace (array('\"', "\'", "\\"), array('', ''), substr (trim ($_POST['bname']), 0, 16));
		$r = db_query ("select k_bn from $INFO[db_prefix]badname where bname='$name'");
		if ( db_num_rows ($r) == 0 ) db_query ("insert into $INFO[db_prefix]badname (bname) values ('$name')");
		header ("location:admin.php?action=badname");
		exit;
	}

	if ( isset ($_POST['censor']) )
	{
		$bad = protection ($_POST['bad']);
		$r = db_query ("select k_cns from $INFO[db_prefix]censor where bad='$bad'");
		if ( db_num_rows ($r) == 0 ) db_query ("insert into $INFO[db_prefix]censor (bad, good) values ('$bad', '$_POST[good]')");
		else db_query ("update $INFO[db_prefix]censor set good='$good' where bad='$bad'");
		header ("location:admin.php?action=censor");
		exit;
	}

	if ( isset ($_POST['blocks']) )
	{
		$find = ( isset ($_POST['find']) ) ? 1 : 0;
		$spam = ( isset ($_POST['spam']) ) ? 1 : 0;
		$mail = ( isset ($_POST['mail']) ) ? 1 : 0;
		$command = "update";
		$rtest = db_query ("select count(*) from $INFO[db_prefix]blocks");
		if ( ! ($ftest = db_fetch_row ($rtest)) || ($ftest[0] == 0) ) $command = "insert";
		db_query ("$command $INFO[db_prefix]blocks set banner='$_POST[banner]',c_title='$_POST[c_title]',lastnew='$_POST[lastnew]',find='$find',
				spam='$spam',mail='$mail',durat='$_POST[durat]',b_stat='$_POST[b_stat]'");
		header ("location:admin.php");
		exit;
	}
	
	if ( isset ($_POST['chat']) )
	{
		$maxlen = $_POST['maxlen'];
		if ( $maxlen < 16 ) $maxlen = 16;
		if ( $maxlen > 65535 ) $maxlen = 65535;
		$chatblock = ( isset ($_POST['chatblock']) ) ? 1 : 0;
		$command = "update";
		$rtest = db_query ("select count(*) from $INFO[db_prefix]chatconf");
		if ( ! ($ftest = db_fetch_row ($rtest)) || ($ftest[0] == 0) ) $command = "insert";
		db_query ("$command $INFO[db_prefix]chatconf set chatblock='$chatblock',maxmes='$_POST[maxmes]',maxtime='$_POST[maxtime]',
				maxlen='$maxlen',refresh='$_POST[refresh]'");
		header ("location:admin.php");
		exit;
	}
	
	if ( isset ($_POST['link']) )
	{
		$r = db_query ("select max(pos) as mx from $INFO[db_prefix]links");
		if ( $f = db_fetch_assoc ($r) ) $pos = $f['mx'] + 1;
		else $pos = 1;
		db_query ("insert into $INFO[db_prefix]links (title,url,pos) values ('$_POST[title]','$_POST[url]',$pos)");
		header ("location:admin.php?action=link");
		exit;
	}
	
}

if ( $admin )
{
	if ( isset ($_POST['config']) )
	{
		$url = get_url ($_POST['url']);
		$meslen = $_POST['meslen'];
		$new_send = ( isset ($_POST['new_send']) ) ? 1 : 0;
		if ( $meslen < 16 ) $meslen = 16;
		if ( $meslen > 65535 ) $meslen = 65535;
		$sketchwidth = ( $_POST['sketchwidth'] > 16 ) ? $_POST['sketchwidth'] : 16;
		$sketchheight = ( $_POST['sketchheight'] > 16 ) ? $_POST['sketchheight'] : 16;
		$photocols = ( $_POST['photocols'] > 1 ) ? $_POST['photocols'] : 1;
		$command = "update";
		$rtest = db_query ("select count(*) from $INFO[db_prefix]config");
		if ( ! ($ftest = db_fetch_row ($rtest)) || ($ftest[0] == 0) ) $command = "insert";
		db_query ("$command $INFO[db_prefix]config set title='$_POST[title]',url='$url',skin='$_POST[skin]',datefrm='$_POST[datefrm]',
			menuwidth='$_POST[width]',mpp='$_POST[mpp]',hst='$_POST[hst]',nws='$_POST[nws]',stat='$_POST[stat]',sp_book='$_POST[sp_book]',
			sp_mail='$_POST[sp_mail]',sp_send='$_POST[sp_send]',email='$_POST[email]',signature='$_POST[signature]',afrom='$_POST[from]',
			new='$_POST[new]',meslen='$meslen',new_send='$new_send',sketchwidth='$sketchwidth',sketchheight='$sketchheight',
			photocols='$photocols'");
		if ( $f_config['new_send'] == 0 && $new_send == 1 ) db_query ("update $INFO[db_prefix]email set send_time=" . time());
		header ("location:admin.php");
		exit;
	}
	
	if ( count ($_POST) > 0 )
	{
		header ("location:admin.php?action=config&err=1");
		exit;	
	}

	if ( $_GET['action'] === "logout" )
	{
		if ( isset ($_COOKIE['SID']) )
		{
			$r = db_query ("select sid,k_ses from $INFO[db_prefix]sessions");
			while ( $f = db_fetch_assoc ($r) )
				if ( sid ($f['sid']) === $_COOKIE['SID'] ) db_query ("delete from $INFO[db_prefix]sessions where k_ses=$f[k_ses]");
			SetCookie ("SID", '', 0);
		}
		header ("location:$_SERVER[HTTP_REFERER]");
		exit;
	}

	if ( $_GET['action'] === "delete" )
	{
		$id = $_GET['id'];
		db_query ("delete from $INFO[db_prefix]book where k_post='$id'");
		header ("location:$_SERVER[HTTP_REFERER]");
		exit;
	}

	if ( $_GET['action'] === "photo_del" )
	{
		$id = $_GET['id'];
		$r = db_query ("select k_art from $INFO[db_prefix]photos where k_photo='$id'");
		if ( $f = db_fetch_assoc ($r) )
		{
			$ind = $f['k_art'];
			db_query ("delete from $INFO[db_prefix]photos where k_photo='$id'");
			artindex ($ind);
		}
		header ("location:$_SERVER[HTTP_REFERER]");
		exit;
	}
	
	if ( $_GET['action'] === "p_up" || $_GET['action'] === "p_down" )
	{
		$id = $_GET['id'];
		$r = db_query ("select k_art,pos from $INFO[db_prefix]photos where k_photo='$id'");
		if ( $f = db_fetch_assoc ($r) )
		{
			$pos = $f['pos'];
			$k_art = $f['k_art'];
			db_free_result ($r);
			$r = ($_GET['action'] === "p_down")
				? db_query ("select k_photo,pos from $INFO[db_prefix]photos
						where pos>$pos and k_art='$k_art' order by pos limit 1")
				: db_query ("select k_photo,pos from $INFO[db_prefix]photos
						where pos<$pos and k_art='$k_art' order by pos desc limit 1");
			if ( $f = db_fetch_assoc ($r) )
			{
				db_query ("update $INFO[db_prefix]photos set pos='$pos' where k_photo='$f[k_photo]'");
				db_query ("update $INFO[db_prefix]photos set pos='$f[pos]' where k_photo='$id'");
			}
		}
		header ("location:$_SERVER[HTTP_REFERER]");
		exit;
	}

	if ( $_GET['action'] === "new_del" )
	{
		$id = $_GET['id'];
		$r = db_query ("select k_art from $INFO[db_prefix]news where k_new='$id'");
		if ( $f = db_fetch_assoc ($r) )
		{
			$ind = $f['k_art'];
			db_query ("delete from $INFO[db_prefix]news where k_new='$id'");
			artindex ($ind);
		}
		header ("location:$_SERVER[HTTP_REFERER]");
		exit;
	}
	
	if ( $_GET['action'] === "up" || $_GET['action'] === "down" )
	{
		$id = $_GET['id'];
		$r = db_query ("select k_art,mesdate from $INFO[db_prefix]news where k_new='$id'");
		if ( $f = db_fetch_assoc ($r) )
		{
			$dt = $f['mesdate'];
			$k_art = $f['k_art'];
			db_free_result ($r);
			$r = ($_GET['action'] === "up")
				? db_query ("select k_new,mesdate from $INFO[db_prefix]news
						where mesdate>$dt and k_art='$k_art' order by mesdate limit 1")
				: db_query ("select k_new,mesdate from $INFO[db_prefix]news
						where mesdate<$dt and k_art='$k_art' order by mesdate desc limit 1");
			if ( $f = db_fetch_assoc ($r) )
			{
				db_query ("update $INFO[db_prefix]news set mesdate='$dt' where k_new='$f[k_new]'");
				db_query ("update $INFO[db_prefix]news set mesdate='$f[mesdate]' where k_new='$id'");
			}
		}
		header ("location:$_SERVER[HTTP_REFERER]");
		exit;
	}

	$admins = "";
	$r = db_query ("select stime,ip,agent from $INFO[db_prefix]sessions order by stime desc");
	while ( $f = db_fetch_assoc ($r) )
	{
		$dt = date ('d.m.Y H:i', $f['stime']);
		$admins .= "<tr><td>$f[ip]</td>\n";
		$admins .= "<td>$dt</td>\n";
		$admins .= "<td>$f[agent]</td></tr>\n";
	}

	$body = "<h3>Администрирование</h3>
	<table border=1>
	<tr><th>Управление</th><th>Блоки</th><th>Запреты</th><th>Статистика</th></tr>
	<tr><td style='vertical-align:baseline;'>
	<a href=admin.php?action=content>Управление контентом сайта</a><br />
	<a href=admin.php?action=config>Настройка конфигурации</a><br />
	<a href=admin.php?action=message>Информационные сообщения</a><br />
	<a href=admin.php?action=spamlist>Список адресов рассылки</a><br />
	<a href=admin.php?action=sendspam>Отправка рассылки</a><br />
	<a href=admin.php?action=shell>Управление файлами</a><br />
	</td><td style='vertical-align:baseline;'>
	<a href=admin.php?action=blocks>Настройка блоков</a><br />
	<a href=admin.php?action=link>Блок ссылок</a><br />
	<a href=admin.php?action=pool>Блок голосований</a><br />
	<a href=admin.php?action=chat>Чат сайта</a><br />
	</td><td style='vertical-align:baseline;'>
	<a href=admin.php?action=a_ban>Запреты IP в админке</a><br />
	<a href=admin.php?action=s_ban>Запреты IP на доступ</a><br />
	<a href=admin.php?action=m_ban>Запреты IP на отправку письма</a><br />
	<a href=admin.php?action=g_ban>Запреты IP в комментариях</a><br />
	<a href=admin.php?action=badname>Запреты имен в комментариях</a><br />
	<a href=admin.php?action=censor>Цензура в комментариях</a><br />
	</td><td style='vertical-align:baseline;'>
	<a href=admin.php?action=comment>Комментарии</a><br />
	<a href=admin.php?action=dlcnt>Счетчики скачиваний</a><br />
	<a href=admin.php?action=find>Поисковые запросы</a><br />
	<a href=admin.php?action=refer>Обратные ссылки</a><br />
	<a href=admin.php?action=visitor>Посетители за месяц</a><br />
	<a href=admin.php?action=online>Кто на сайте</a><br />
	</td></tr>
	</table><br />
	Сейчас в админке:<br />
	<table border=1>
	<tr><th>IP-адрес</th><th>Время входа</th><th>Браузер</th></tr>
	$admins
	</table><br />
	<a href=admin.php?action=backup>Бэкап базы данных</a><br />
	<a href=admin.php?action=password>Смена пароля</a><br />
	<a href=admin.php?action=logout>Выход</a><br />
	";

	if ( $_GET['action'] === 'config' ) include ('includes/adm_config.php');
	
	if ( $_GET['action'] === 'blocks' ) include ('includes/adm_blocks.php');
	
	if ( $_GET['action'] === 'link' ) include ('includes/adm_link.php');
	
	if ( $_GET['action'] === 'shell' ) include ('includes/adm_shell.php');

	if ( $_GET['action'] === 'message' ) include ('includes/adm_message.php');
	
	if ( $_GET['action'] === 'spamlist' ) include ('includes/adm_list.php');
	
	if ( $_GET['action'] === 'sendspam' ) include ('includes/adm_send.php');

	if ( $_GET['action'] === 'refer' ) include ('includes/adm_refer.php');
	
	if ( $_GET['action'] === 'online' ) include ('includes/adm_online.php');

	if ( $_GET['action'] === 'visitor' ) include ('includes/adm_visitor.php');
	
	if ( $_GET['action'] === 'find' ) include ('includes/adm_find.php');
	
	if ( $_GET['action'] === 'censor' ) include ('includes/adm_censor.php');
	
	if ( $_GET['action'] === 'badname' ) include ('includes/adm_badname.php');
	
	if ( $_GET['action'] === 'comment' ) include ('includes/adm_comment.php');
	
	if ( $_GET['action'] === 'news' ) include ('includes/adm_news.php');
	
	if ( $_GET['action'] === 'photo' ) include ('includes/adm_photo.php');
	
	if ( $_GET['action'] === 'dlcnt' ) include ('includes/adm_dlcnt.php');
	
	if ( $_GET['action'] === 'pool' ) include ('includes/adm_pool.php');
	
	if ( $_GET['action'] === 'chat' ) include ('includes/adm_chat.php');
	
	if ( $_GET['action'] === 'backup' ) include ('includes/adm_backup.php');

	if ( $_GET['action'] === 'password' )
	{
		if ( isset ($_GET['err']) )
		{
			if ( $_GET['err'] === '1' ) $error_msg = 'Неверно введен новый пароль';
			if ( $_GET['err'] === '2' ) $error_msg = 'Неверно введен старый пароль';
		} else $error_msg = '';
		include ('includes/adm_password.php');
	}

	if ( $_GET['action'] === 'content' ) include ('includes/adm_content.php');
	
	if ( $_GET['action'] === 'g_ban' )
	{
		$mode = 0;
		include ('includes/adm_ban.php');
	}

	if ( $_GET['action'] === 'a_ban' )
	{
		$mode = 1;
		include ('includes/adm_ban.php');
	}

	if ( $_GET['action'] === 's_ban' )
	{
		$mode = 2;
		include ('includes/adm_ban.php');
	}
	
	if ( $_GET['action'] === 'm_ban' )
	{
		$mode = 3;
		include ('includes/adm_ban.php');
	}
	
	$hacks = "";
	$cnt = 0;
	$r = db_query ("select ip,hacktime,pass from $INFO[db_prefix]hacks order by hacktime desc");
	while ( $f = db_fetch_assoc ($r) )
	{
		$dt = date ('d.m.Y H:i', $f['hacktime']);
		$hacks .= "<tr><td>IP: $f[ip]</td><td>$dt</td><td>$f[pass]</td></tr>\n";
		$cnt++;
	}
	if ( $hacks !== "" )
	{
		$hacks = "<br /><br />
		<script language='Javascript'><!--
			window.open('hack.html', 'hacks', 'HEIGHT=225,WIDTH=400');
		//--></script>
		<font color='red'><big><b>Попытка атаки администраторской панели:</b></big></font><br /><br />
		<table border='1'>
		$hacks
		</table><br />
		Всего атак: <b>$cnt</b><br />
		Сохраните эти данные, так как история атак удалена из базы данных.";
		mysql_query ("delete from $INFO[db_prefix]hacks") or die (mysql_error ());				
	}
	
	no_cache();

	echo "<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.0 Transitional//EN'>
	<html>
	<head>
	<meta http-equiv='pragma' content='no-cache'>
	<meta http-equiv='cache-control' content='no-cache'>
	<meta http-equiv='Content-Type' content='text/html; charset=windows-1251'>
	<title>Панель администратора</title>
	<link href='includes/admincss.css' type='text/css' rel='stylesheet' />
	</head>
	<body>
	<center><table width='90%'><tr><td>
	<a href='$f_config[url]' target='_blank'>Сайт</a>
	$body
	$hacks
	</td></tr></table>
	<font size='1'>&copy; Акатов Алексей, 2005 - 2006</font></center>
	</body></html>";
	
	exit;
}

if ( isset ($_POST['go']) && ! ban (1) )
{
	if ( anti_spam (2) )
	{
		$error_msg = "Работает защита администраторской панели.<br />Повторите попытку входа через 30 секунд.";
	} else
	{
		$password = md5 ($_POST['pass']);
		if ( $f_config['passwrd'] === $password )
		{
			$sid = gen_rand_string ();
			db_query ("insert into $INFO[db_prefix]sessions (sid, ip, agent, stime)
					values ('$sid', '$_SERVER[REMOTE_ADDR]', '" . protection ($_SERVER['HTTP_USER_AGENT']) . "', " . time() . ")");
			SetCookie ("SID", sid ($sid), time() + 60 * 60 * 24 * 30);
			if ( strpos ($_SERVER['HTTP_REFERER'], '/admin.php') !== false ) header ("location:$_SERVER[HTTP_REFERER]");
													else header ("location:admin.php");
			exit;
		} else
		{
			$error_msg = "Пароль неверен.<br />Повторите попытку входа через 30 секунд.";
			db_query ("insert into $INFO[db_prefix]spam (mode, ip, posttime) values (2, '$_SERVER[REMOTE_ADDR]', " . time() . ")");
			db_query ("insert into $INFO[db_prefix]hacks (ip, pass, hacktime) values
					('$_SERVER[REMOTE_ADDR]', '" . protection ($_POST['pass']) . "', " .time() . ")");
		}
	}
}

$form = "
<form action='admin.php' method='post'>
<input type='hidden' name='go' value='ok' />
<input type='password' name='pass' />
<input type='submit' value='Вход' />
</form>";

if ( ban (1) )
{
	$error_msg = "Вам запрещено пользоваться панелью администратора.";
	$form = '';
}

if ( ! empty ($error_msg) ) $error_msg = "<div align='center' style='border:1px solid #000000;background:#FFA0A0;color:#000000;'><b>$error_msg</b></div>";

echo "<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.0 Transitional//EN'>
<html>
<head>
<title>Вход в режим администратора</title>
<meta http-equiv='pragma' content='no-cache'>
<meta http-equiv='cache-control' content='no-cache'>
<meta http-equiv='Content-Type' content='text/html; charset=windows-1251'>
<link href='includes/admincss.css' type='text/css' rel='stylesheet' />
</head>
<body>
<center><table><tr><td align='center'>
<h3>Администрирование</h3>
$error_msg
$form
</td></tr></table></center>
</body></html>
";

?>